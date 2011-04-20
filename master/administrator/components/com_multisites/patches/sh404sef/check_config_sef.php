<?php
/**
 * @file       check_config_sef.php
 * @brief      Check if the SH404SEF configuration wrapper is present.
 * @version    1.1.9
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008 Edwin2Win sprlu - all right reserved.
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
 * - V1.1.9 16-APR-2009: Initial version
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkSH404SefWrapper ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_ID') ...
 *   is present
 */
function jms2win_checkSH404SefWrapper( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[IGNORE]|File Not Found';
	}
	
   $str = file_get_contents( $filename);
   // If small files (after the installation of sh4040)
   if ( strlen( $str) <= 20) {
	   return '[IGNORE]|File Not Found';
   }
   // If the "if (!defined('_JEXEC')) ....."
   // is not present, assume that the files does not contain anything
   $pos = strpos( $str, '_JEXEC');
   if ($pos === false) {
	   return '[IGNORE]|File Not Found';
   }
   
   // if 'MULTISITES_ID' is present
   $pos = strpos( $str, 'MULTISITES_ID');
   if ($pos === false) $wrapperIsPresent = false;
   else                $wrapperIsPresent = true;
   
   $result = "";
   $rc = '[OK]';
   if ( !$wrapperIsPresent) {
	   $rc = '[NOK]';
      $result .= JText::_( 'The routing wrapper is not present in the SH404SEF MASTER config.sef file.');
      $result .= '|[ACTION]';
      $result .= '|Add 6 lines containing the routing wrapper to the slave site.';
   }
   
   return $rc .'|'. $result;
}



//------------ jms2win_actionSH404SefWrapper ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionSH404SefWrapper( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'sh404sef' .DS. 'patch_config_sef.php');
   if ( $patchStr === false) {
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
		if (!defined('_JEXEC')) die('Direct Access to this location is not allowed.');
		........
		........
      $version = ".......
		........
		........
		?>
      
      ===========
      and Replace by:
      ===========
		........
		........
      if (!defined('_JEXEC')) die('Direct Access to this location is not allowed.');
      if ( defined( 'MULTISITES_ID') && file_exists( dirname(__FILE__) .DS. 'config.sef.' .MULTISITES_ID. '.php')) {
         include( dirname(__FILE__) .DS. 'config.sef.' .MULTISITES_ID. '.php');
      } else {
		........
		........
      $version = ".......
		........
		........
		........
		........
	   }
		?>

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n .... if (!defined('_JEXEC')) ..... ;\n
      p0                    p1              p2
      
      \n ....
      p3      

      \n .... ?>;
      p6      p7
      
      Produce
      begin -> p3 + INSERT PATCH + p6 + "}" + p6 -> end
      
    */
   
   // Search begin statement: fopen ...
   // p1: Search for global $mosConfig_absolute_path
   $p1 = strpos( $content, '_JEXEC');
   if ( $p1 === false) {
      return false;
   }

 
   // p2: Search for ";"
   $p2 = strpos( $content, ";", $p1);
   if ( $p2 === false) {
      return false;
   }
 
   // p3: Search for "\n"
   $p3 = strpos( $content, "\n", $p2);
   if ( $p3 === false) {
      return false;
   }
   $p3++;

 
   // p7: Search for '? >'
   $p7 = strpos( $content, '?>', $p3);
   if ( $p7 === false) {
      return false;
   }

   // P6: Go to Begin of line
   for ( $p6=$p7; $p6 > 0 && $content[$p6] != "\n"; $p6--);
   $p6++;

 
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p3)
           . $patchStr
           . substr( $content, $p3, $p6-$p3)
           . "//_jms2win_begin\n"
           . "}\n"
           . "//_jms2win_end\n"
           . substr( $content, $p6)
           ;

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

   return true;
}

