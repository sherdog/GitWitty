<?php
/**
 * @file       check_class.php
 * @brief      Check if the 'sh404sef.class.php' files contains the Multi Sites patches 
 *             to allow saving specific configuration file for each websites.
 *
 * @version    1.2.26
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2010 Edwin2Win sprlu - all right reserved.
 * @license    This program is free software; you can redistribute it and/or
 *             modify it under the terms of the GNU General Public License
 *             as published by the Free Software Foundation; either version 2
 *             of the License, or (at your option) any later version.
 *             This program is distributed in the hope that it will be useful,
 *             but WITHOUT ANY WARRANTY; without even the implied warranty of
 *             MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU General Public License for more details.
 *             You should have received a copy of the GNU General Public License
 *             along with this program; if not, write to the Free Software
 *             Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *             A full text version of the GNU GPL version 2 can be found in the LICENSE.php file.
 * @par History:
 * - V1.1.9  17-APR-2009: Initial version
 * - V1.2.26 26-JAN-2010: Update the patch detection to also allow using in on the file "SEFConfig.class.php"
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkSH404Class ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_HOST'))
 *   is present
 */
function jms2win_checkSH404Class( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[IGNORE]|File Not Found';
	}
	
   $str = file_get_contents( $filename);
   
   // if 'MULTISITES_ID' is present
   $pos = strpos( $str, 'MULTISITES_ID');
   if ($pos === false) {
      // If the function "function saveConfig" is not present in the file
      $pos = strpos( $str, 'function saveConfig');
      if ($pos === false) {
         // Ignore the patch
   	   return '[IGNORE]|File Not Found';
      }
      // Otherwise, mark that it is not present
      $wrapperIsPresent = false;
   }
   else {
      $wrapperIsPresent = true;
   }
   
   $result = "";
   $rc = '[OK]';
   if ( !$wrapperIsPresent) {
	   $rc = '[NOK]';
      $result .= JText::_( 'The Multi Sites specific "config.sef" saving for each websites is not present.');
      $result .= '|[ACTION]';
      $result .= '|Add 16 lines to save specific config.sef for each slave site.';
   }
   
   return $rc .'|'. $result;
}


//------------ jms2win_actionSH404Class ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionSH404Class( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr_1 = jms2win_loadPatch( '..' .DS. 'sh404sef' .DS. 'patch_class_1.php');
   if ( $patchStr_1 === false) {
      return false;
   }

   $patchStr_2 = jms2win_loadPatch( '..' .DS. 'sh404sef' .DS. 'patch_class_2.php');
   if ( $patchStr_2 === false) {
      return false;
   }

//	$filename = JPATH_ROOT.DS.$file;
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
   $content = file_get_contents( $filename);
   if ( $content === false) {
      return false;
   }
   
   // Search/Replace the statement
   /*
      ===========
      Search for:
      ===========
		........
		........
		function saveConfig($return_data=0) {
		........
		........
      . 'if (!defined(\'_JEXEC\')) die(\'Direct Access to this location is not allowed.\');' . "\n\n"
		........
		........
      $config_data .= '?'.'>';
      
      ===========
      and Replace by:
      ===========
		........
		function saveConfig($return_data=0) {
		........
		........
      . 'if (!defined(\'_JEXEC\')) die(\'Direct Access to this location is not allowed.\');' . "\n\n"
//_jms2win_begin v1.1.9
if ( defined( 'MULTISITES_ID')) {
$sef_config_file  = JPATH_ADMINISTRATOR.'/components/com_sh404sef/config/config.sef.' .MULTISITES_ID. '.php';
} else {
    $config_data .= "if ( defined( 'MULTISITES_ID') && file_exists( dirname(__FILE__) .DS. 'config.sef.' .MULTISITES_ID. '.php')) {\n"
                 .  "   include( dirname(__FILE__) .DS. 'config.sef.' .MULTISITES_ID. '.php');\n"
                 .  "} else {\n"
                 ;
}
//_jms2win_end
		........
		........
//_jms2win_begin v1.1.9
if ( defined( 'MULTISITES_ID')) {}
else {
    $config_data .= "}\n";
}
//_jms2win_end
      $config_data .= '?'.'>';
		........
		........
		........
		........
	   }
		?>

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n .... function saveConfig .....
      p0      p1              
      
      \n ...._JEXEC ....... \n
             p2             p3
             
      \n ....; ...\n
            p4    p5

      \n .... '?'.'>';
      p6      p7
      
      Produce
      begin -> p5 + INSERT PATCH No 1 + p5->p6 + PATCH no 2 + p6 -> end
      
    */
   
   // p1: Search for "function saveConfig"
   $p1 = strpos( $content, 'function saveConfig');
   if ( $p1 === false) {
      return false;
   }

   // p2: Search for global $mosConfig_absolute_path
   $p2 = strpos( $content, '_JEXEC', $p1);
   if ( $p2 === false) {
      return false;
   }

 
   // p3: Search for "\n"
   $p3 = strpos( $content, "\n", $p2);
   if ( $p3 === false) {
      return false;
   }

   // p4: Search for ";"
   $p4 = strpos( $content, ";", $p3);
   if ( $p4 === false) {
      return false;
   }

   // p5: Search for "\n"
   $p5 = strpos( $content, "\n", $p4);
   if ( $p5 === false) {
      return false;
   }
   $p5++;
 
   // p7: Search for "'?'.'>'"
   $p7 = strpos( $content, "'?'.'>'", $p4);
   if ( $p7 === false) {
      return false;
   }

   // P6: Go to Begin of line
   for ( $p6=$p7; $p6 > 0 && $content[$p6] != "\n"; $p6--);
   $p6++;

 
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p5)
           . $patchStr_1
           . substr( $content, $p5, $p6-$p5)
           . $patchStr_2
           . substr( $content, $p6)
           ;

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

   return true;
}

