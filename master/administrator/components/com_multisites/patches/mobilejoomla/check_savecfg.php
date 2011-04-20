<?php
/**
 * @file       check_savecfg.php
 * @brief      Check if the 'models/alphacontent.php' files contains the Multi Sites patches 
 *             to allow saving specific configuration file for each websites.
 *
 * @version    1.2.45
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
 * - V1.1.9  17-APR-2009: Initial version
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkMobJoomSaveCfg ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_HOST'))
 *   is present
 */
function jms2win_checkMobJoomSaveCfg( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[IGNORE]|File Not Found';
	}
	
   $str = file_get_contents( $filename);
   
   // if 'MULTISITES_ID' is present
   $pos = strpos( $str, 'MULTISITES_ID');
   if ($pos === false) $wrapperIsPresent = false;
   else                $wrapperIsPresent = true;
   
   $result = "";
   $rc = '[OK]';
   if ( !$wrapperIsPresent) {
	   $rc = '[NOK]';
      $result .= JText::_( 'The Multi Sites specific "configuration.php" saving for each websites is not present.');
      $result .= '|[ACTION]';
      $result .= '|Replace 6 lines byt 34 lines to save specific configuration.php file for each slave site.';
   }
   
   return $rc .'|'. $result;
}


//------------ jms2win_actionMobJoomSaveCfg ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionMobJoomSaveCfg( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'mobilejoomla' .DS. 'patch_savecfg.php');
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
		function saveconfig( $task )
		........
		........
		$configfname = JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_mobilejoomla'.DS.'config.php';
		include($configfname);
		........
		........
   	$config = "<?php\n"
   			. "defined( '_JEXEC' ) or die( 'Restricted access' );\n"
   			. "\n"
   			. "\$MobileJoomla_Settings=array(\n"
   			. "'version'=>'".HTML_mobilejoomla::getMJVersion()."',\n"
   			. implode(",\n",$params)."\n"
   			. ");\n"
   			. "?>";
   
   	jimport('joomla.filesystem.file');
   	global $mainframe;
      if(JFile::write($configfname,$config))
		........
		........
      
      ===========
      and Replace by:
      ===========
		........
		function saveconfig( $task )
		........
		........
   	$config = "<?php\n"
   			. "defined( '_JEXEC' ) or die( 'Restricted access' );\n"
   			. "\n"
   			. "\$MobileJoomla_Settings=array(\n"
   			. "'version'=>'".HTML_mobilejoomla::getMJVersion()."',\n"
   			. implode(",\n",$params)."\n"
   			. ");\n"
   			. "?>";
   
   	jimport('joomla.filesystem.file');
   	global $mainframe;
//_jms2win_begin v1.2.45
		// If this is a Slave Site, let use the standard format
		if ( defined( 'MULTISITES_ID')) {
      	$configfname = JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_mobilejoomla'.DS.'config.' .MULTISITES_ID. '.php';
		}
		// If master, add the wrapper
		else {
      	$config = "<?php\n"
      			. "defined( '_JEXEC' ) or die( 'Restricted access' );\n"
      			. "\n"
      			. "//_jms2win_begin v1.2.45\n"
      			. "if ( defined( 'MULTISITES_ID')\n"
      			. "  && file_exists( dirname(__FILE__) .DS. 'config.' .MULTISITES_ID. '.php')) {\n"
      			. "   require_once(  dirname(__FILE__) .DS. 'config.' .MULTISITES_ID. '.php');\n"
      			. "} else if ( empty( $MobileJoomla_Settings)) {\n"
      			. "//_jms2win_end\n"
      			. "\$MobileJoomla_Settings=array(\n"
      			. "'version'=>'".HTML_mobilejoomla::getMJVersion()."',\n"
      			. implode(",\n",$params)."\n"
      			. ");\n"
               . "//_jms2win_begin\n"
               . "}\n"
               . "//_jms2win_end\n"
      			. "?>";
		}
//_jms2win_end
      if(JFile::write($configfname,$config))
		........
		........

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n .... function saveconfig .....
      p0      p1              
      
      \n ....JFile::write ....... \n
      p2     p3

      
      Produce
      begin -> p2 + INSERT PATCH + p2 -> end
      
    */
   
   // p1: Search for "function saveconfig"
   $p1 = strpos( $content, 'function saveconfig');
   if ( $p1 === false) {
      return false;
   }

   // p3: Search for "JFile::write"
   $p3 = strpos( $content, 'JFile::write', $p1);
   if ( $p3 === false) {
      return false;
   }

   // P2: Go to Begin of line
   for ( $p2=$p3; $p2 > 0 && $content[$p2] != "\n"; $p2--);
   $p2++;
 

 
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p2)
           . $patchStr
           . substr( $content, $p2)
           ;

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

   return true;
}

