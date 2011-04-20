<?php
/**
 * @file       check_savecfg.php
 * @brief      Check if the 'lib/config.php' files contains the Multi Sites patches 
 *             to allow saving specific configuration file for each websites.
 *
 * @version    1.1.10
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2009 Edwin2Win sprlu - all right reserved.
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
 * - V1.1.10  20-APR-2009: Initial version
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkJEventSaveCfg ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_HOST'))
 *   is present
 */
function jms2win_checkJEventSaveCfg( $model, $file)
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
      $result .= '|Add 6 lines to save the specific configuration.php file for each slave site.';
   }
   
   return $rc .'|'. $result;
}


//------------ jms2win_actionJEventSaveCfg ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionJEventSaveCfg( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'events' .DS. 'patch_savecfg.php');
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
   	function _getDefaultINIfilePath() {
   		return dirname(dirname(__FILE__)) . '/' . 'events_config.ini.php';
   	}
		........
		........
		........
      
      ===========
      and Replace by:
      ===========
		........
   	function _getDefaultINIfilePath() {
   		if ( defined( 'MULTISITES_ID')) {
      		return dirname(dirname(__FILE__)) . '/' . 'events_config.' . MULTISITES_ID . '.ini.php';
   		}
   
   		return dirname(dirname(__FILE__)) . '/' . 'events_config.ini.php';
   	}
		........
		........

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n .... function _getDefaultINIfilePath .....\n
      p0      p1                                   p2
      
      
      Produce
      begin -> p2 + INSERT PATCH + p2 -> end
      
    */
   
   // p1: Search for "function _getDefaultINIfilePath"
   $p1 = strpos( $content, 'function _getDefaultINIfilePath');
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

