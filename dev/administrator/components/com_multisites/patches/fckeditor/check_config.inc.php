<?php
/**
 * @file       check_config.inc.php
 * @brief      Check if the 'FCKEditor/...plugin/ImageManger/config.inc.php' files contains the Multi Sites patches 
 *             to allow using the image "deployed" directory instead of the master image directory.
 *
 * @version    1.2.36
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2010 Edwin2Win sprlu - all right reserved.
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
 * - V1.2.23  03-JAN-2010: Initial version
 * - V1.2.36  01-JUN-2010: Add the letter tree directory processing
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkFCKEdCfgInc ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_HOST'))
 *   is present
 */
function jms2win_checkFCKEdCfgInc( $model, $file)
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
      $result .= JText::_( 'The ImageManager "Multisite" image folder directory in "config.inc.php" is not present.');
      $result .= '|[ACTION]';
      $result .= '|Replace 1 lines by 26 lines to compute the image folder directory based on the Multisites ID.';
   }
   // If a patch is present
   else {
      // if the "letter tree" patch is NOT present
      $pos = strpos( $str, 'MULTISITES_ID_PATH');
      if ($pos === false) {
   	   $rc = '[NOK]';
         $result .= JText::_( 'The ImageManager "Multisite" image folder directory in "config.inc.php" is not present.');
         $result .= '|[ACTION]';
         $result .= '|Replace the previous patch <1.2.35 by a new one. This replace the original 1 lines by 27 lines to compute the image folder directory based on the new Multisites ID path.';
         $result .= '|Also update to Jms Multisites version 1.2.30 or higher to make this patch active.';
      }
   }
   
   return $rc .'|'. $result;
}


//------------ jms2win_actionFCKEdCfgInc ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionFCKEdCfgInc( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'fckeditor' .DS. 'patch_config.inc.php');
   if ( $patchStr === false) {
      return false;
   }


//	$filename = JPATH_ROOT.DS.$file;
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
   $content = file_get_contents( $filename);
   if ( $content === false) {
      return false;
   }

   // If a patch was already present
   $p1 = strpos( $content, 'MULTISITES_');
   if ( $p1 === false) {}
   else {
      // Remove potential exising patches
      $content = jms2win_removePatch( $content);
   }
   
   // Search/Replace the statement
   /*
      ===========
      Search for:
      ===========
		........
		........
   	define('JPATH_BASE',$base_folder);
		........
		........
      
      ===========
      and Replace by:
      ===========
		........
//_jms2win_begin v1.2.23
      // Try detect if this is a slave site and this should set the define MULTISITES_ID
      if ( !defined( 'MULTISITES_ID')) {
         if ( !defined( 'JPATH_MULTISITES')) define( 'JPATH_MULTISITES', $base_folder .DIRECTORY_SEPARATOR. 'multisites');
         if ( !defined( '_EDWIN2WIN_')) define( '_EDWIN2WIN_', true);
         @include( $base_folder .DIRECTORY_SEPARATOR. 'includes' .DIRECTORY_SEPARATOR. 'multisites.php');
         if ( defined( 'JMS2WIN_VERSION')) {
            if ( !defined( 'MULTISITES_ADMIN')) define( 'MULTISITES_ADMIN', true);
            if ( class_exists( 'Jms2Win')) Jms2Win::matchSlaveSite();
         }
      }
      
      // If this is a slave site, check if it has a specific deploy directory (if YES, use its path to compute the JPATH_BASE)
      if ( defined( 'MULTISITES_ID')) {
         if ( defined( 'MULTISITES_ID_PATH'))   { $filename = MULTISITES_ID_PATH.DIRECTORY_SEPARATOR.'config_multisites.php'; }
         else                                   { $filename = JPATH_MULTISITES.DS.MULTISITES_ID.DS.'config_multisites.php'; }
         @include($filename);
         if ( isset( $config_dirs) && !empty( $config_dirs) && !empty( $config_dirs['deploy_dir'])) {
            define('JPATH_BASE', $config_dirs['deploy_dir']);
         }
         else {
         	define('JPATH_BASE',$base_folder);
         }
      }
      else {
      	define('JPATH_BASE',$base_folder);
      }
//_jms2win_end
		........
		........

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n .... define('JPATH_BASE',$base_folder); .....\n
      p0      p1                                      p2
      
      Produce
      begin -> p0 + INSERT PATCH + p2 -> end
      
    */
   
   // p1: Search for "JPATH_BASE"
   $p1 = strpos( $content, 'JPATH_BASE');
   if ( $p1 === false) {
      return false;
   }

   // P0: Go to Begin of line
   for ( $p0 = $p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
   $p0++;

   // p2: Search for "\n"
   $p2 = strpos( $content, "\n", $p1);
   if ( $p2 === false) {
      return false;
   }
   $p2++;

 
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p0)
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

