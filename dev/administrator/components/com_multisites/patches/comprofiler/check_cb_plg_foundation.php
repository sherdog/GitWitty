<?php
/**
 * @file       check_cb_plg_foundation.php
 * @brief      Check if the Commuty Builder 'plugin.foundation.php' files contains the Multi Sites patches.
 *
 * @version    1.1.8
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
 * - V1.1.6 10-FEB-2009: File creation
 * - V1.1.8 12-MAR-2009: Ignore the patch in case of CB 1.2 RC2 that does not require the patch
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkCB_plg_foundation ---------------
/**
 * check if 'MULTISITES_' is present
 */
function jms2win_checkCB_plg_foundation( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[IGNORE]|File Not Found';
	}
   $str = file_get_contents( $filename);

   // If CB 1.2 RC2, this is not present
   $p1 = strpos( $str, '/ue_config.php');
   if ( $p1 === false) {
	   return '[IGNORE]|File Not Found';
   }

   
   // if 'MULTISITES_' is present
   $pos = strpos( $str, 'MULTISITES_');
   if ($pos === false) $wrapperIsPresent = false;
   else {
      $wrapperIsPresent = true;
   }
   
   $result = "";
   $rc = '[OK]';
   if ( !$wrapperIsPresent) {
	   $rc = '[NOK]';
      $result .= JText::_( 'The customisation of "ue_config.php" file for the slave sites is not present into the CB controller');
      $result .= '|[ACTION]';
      $result .= '|Replace 1 statements that include the "ue_config.php" file and replace it by 17 lines that select a specific "ue_config_{site_id}.php" configuration file';
   }
   
   return $rc .'|'. $result;
}

//------------ jms2win_actionCB_plg_foundation ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionCB_plg_foundation( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'comprofiler' .DS. 'patch_cb_plg_foundation.php');
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

      include_once( dirname( __FILE__ ) . '/ue_config.php' );
      
      ===========
      and Replace by:
      ===========

      if ( defined( 'MULTISITES_ID')) {
         jimport('joomla.filesystem.file');
         if ( JFile::exists( dirname( __FILE__ ) . '/ue_config_' . MULTISITES_ID . '.php')) {
            include_once( dirname( __FILE__ ) . '/ue_config_' . MULTISITES_ID . '.php' );
         }
         else {
            include_once( dirname( __FILE__ ) . '/ue_config.php' );
         }
      }
      else {
         include_once( dirname( __FILE__ ) . '/ue_config.php' );
      }

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n      ...          '/ue_config.php' ....\n
      p0                   p1               p2

      
      Produce
      begin -> p0 + INSERT PATCH + p2 -> end
      
    */
   // P1: Search begin statement: "/ue_config.php"
   $p1 = strpos( $content, '/ue_config.php');
   if ( $p1 === false) {
      return false;
   }
   // P0: Go to Begin of line
   for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
   
   // p2: Search for end of line
   for ( $p2=$p1; $content[$p2] != "\n"; $p2++);

   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch

   $result  = substr( $content, 0, $p0)
           . $patchStr
           . substr( $content, $p2+1)
           ;

   
   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}
   
   return true;
}
