<?php
/**
 * @file       check_showconfig.php
 * @brief      Check if the JEvent "Show configuration" read the correct configuration file.
 * @version    1.1.10
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
 * - V1.1.10 20-APR-2009: Initial version
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkJEventShowConfig ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_ID') ...
 *   is present
 */
function jms2win_checkJEventShowConfig( $model, $file)
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
      $result .= JText::_( 'The selection of the appropriate configuration to display is not present.');
      $result .= '|[ACTION]';
      $result .= '|Add 10 lines that compute the configuration file name to display.';
   }
   
   return $rc .'|'. $result;
}



//------------ jms2win_actionJEventShowConfig ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionJEventShowConfig( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'events' .DS. 'patch_showconfig.php');
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
   	function showConfig( $option, $conf_style) {
		........
		........
		$configfile 	= $mosConfig_absolute_path . '/administrator/components/' . $option . '/events_config.ini.php';
		$cssfile 		= $mosConfig_absolute_path . '/components/' . $option . '/events_css.css';
		........
		........
		........
	   }
		?>
      
      ===========
      and Replace by:
      ===========
		........
		........
   	function showConfig( $option, $conf_style) {
		........
		........
		$configfile 	= $mosConfig_absolute_path . '/administrator/components/' . $option . '/events_config.ini.php';
		if ( defined( 'MULTISITES_ID')) {
         jimport( 'joomla.filesystem.file');
   		$slaveconfigfile 	= $mosConfig_absolute_path . '/administrator/components/' . $option . '/events_config.' . MULTISITES_ID . '.ini.php';
   		if ( !JFile::exists($slaveconfigfile)) {
   		   JFile::copy( $configfile, $slaveconfigfile);
   		}
   		$configfile 	= $slaveconfigfile;
		}
		$cssfile 		= $mosConfig_absolute_path . '/components/' . $option . '/events_css.css';
		........
		........
		........
	   }
	   }
		?>

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n .... events_config.ini.php ...\n
      p0      p1                       p2
      

      
      Produce
      begin -> p2 + INSERT PATCH + p2 -> end
      
    */
   
   // p1: Search for "events_config.ini.php"
   $p1 = strpos( $content, 'events_config.ini.php');
   if ( $p1 === false) {
      return false;
   }

   // p2: Search for "\n"
   $p2 = strpos( $content, "\n", $p1);
   if ( $p2 === false) {
      return false;
   }
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

