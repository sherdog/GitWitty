<?php
/**
 * @file       check_cb_cntl.php
 * @brief      Check if the Commuty Builder 'admin.comprofiler.controller.php' files contains the Multi Sites patches.
 *
 * @version    1.2.39
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
 * - V1.1.6 10-FEB-2009: File creation
 * - V1.2.39 12-JUL-2010: Update the fix to check that "ue_config.php" is present in the file.
 *                        Starting with CB 1.2.3, the patch is moved 
 *                        from admin.comprofiler.controller.php
 *                        to   controller/controller.default.php
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkCB_cntl ---------------
/**
 * check if 'MULTISITES_' is present
 */
function jms2win_checkCB_cntl( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[IGNORE]|File Not Found';
	}
   $str = file_get_contents( $filename);

   // If the "ue_config.php" is moved into another file, then ignore this patch
   $pos = strpos( $str, '/ue_config.php');
   if ( $pos === false) {
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
      $result .= '|Replace 2 statements that define the "ue_config.php" file name by 2*18 lines that use the slave site ID into the name of the configuration "ue_config_{site_id}.php" file';
   }
   
   return $rc .'|'. $result;
}

//------------ jms2win_actionCB_cntl ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionCB_cntl( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'comprofiler' .DS. 'patch_cb_cntl.php');
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

   	$configfile			=	$_CB_adminpath."/ue_config.php";
      
      ===========
      and Replace by:
      ===========

   	if ( defined( 'MULTISITES_ID')) {
      	$configfile          =	$_CB_adminpath."/ue_config_" . MULTISITES_ID . ".php";
      	$configfile_master   =	$_CB_adminpath."/ue_config.php";
   		// If the slave site params_ini file does not exists and a master one exists
         jimport('joomla.filesystem.file');
         if ( !JFile::exists( $configfile) && JFile::exists( $configfile_master)) {
            // Duplicate the master file as slave site
            JFile::copy( $configfile_master, $configfile);
         }
   	}
   	else {
      	$configfile			=	$_CB_adminpath."/ue_config.php";
   	}

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n      ...          '/ue_config.php' ....\n
      p0                   p1               p2

      \n      ...          '/ue_config.php' ....\n
      p3                   p4               p5
      
      Produce
      begin -> p0 + INSERT PATCH + p2 -> p3 + INSERT PATCH + p5 -> end
      
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

   // P4: Search begin statement: "/ue_config.php"
   $p4 = strpos( $content, '/ue_config.php',  $p2);
   if ( $p4 === false) {
      return false;
   }
   // P3: Go to Begin of line
   for ( $p3=$p4; $p3 > 0 && $content[$p3] != "\n"; $p3--);
   $p0++;
   
   // p5: Search for end of line
   for ( $p5=$p4; $content[$p5] != "\n"; $p5++);

   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch

   $result  = substr( $content, 0, $p0)
           . $patchStr
           . substr( $content, $p2+1, $p3-$p2)
           . $patchStr
           . substr( $content, $p5+1)
           ;

   
   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}
   
   return true;
}
