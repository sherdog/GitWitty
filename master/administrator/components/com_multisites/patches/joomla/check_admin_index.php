<?php
/**
 * @file       check_admin_index.php
 * @brief      Check if the Administrator Include contain the patch that use the deploy directory instead of the master website directory
 *
 * @version    1.2.38
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
 * - V1.1.7 14-FEB-2009: File creation
 * - V1.2.14 05-DEC-2009: Add Joomla 1.6 alpha 2 compatibility.
 * - V1.2.35 30-MAY-2010: Add letter tree processing.
 * - V1.2.36 01-JUN-2010: Fix letter tree processing.
 * - V1.2.38 09-JUN-2010: Add compatibility with Joomla 1.6.0 beta 2.
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

function jms2win_checkAdminIndex_olderVersion()
{
   // If this is a Joomla 1.6 then this is NOT a old version as this is a JMS 1.2.32 or higher
   if ( version_compare( JVERSION, '1.6') >= 0) { 
      return false;
   }
   // Retreive version number
   $filename = JPath::clean( JPATH_COMPONENT_ADMINISTRATOR.DS.'install.xml');
   jimport( 'joomla.application.helper');
	if ($data = JApplicationHelper::parseXMLInstallFile($filename)) {
	   // If the version is present
	   if (isset($data['version']) && !empty($data['version'])) {
	      $version = explode( '.', $data['version']);
	   }
	}

   // If JMS version < 1.1.17
   if ( empty( $version)
     || ((int)$version[0] <= 1 && (int)$version[1] <= 1 && (int)$version[2] < 17)
      )
   {
      return true;
   }
   
   return false;
}


//------------ jms2win_checkAdminIndex ---------------
/**
 * check if 'MULTISITES_' is present
 */
function jms2win_checkAdminIndex( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[NOK]|File Not Found';
	}
   $str = file_get_contents( $filename);
   
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

      // If JMS version < 1.1.17
      if ( jms2win_checkAdminIndex_olderVersion())
      {
         $result .= JText::_( '<font color="red" size="3"><b>IT IS REQUIRED TO UPDATE WITH A JMS VERSION 1.1.17 or HIGHER.</b></font>');
         $result .= JText::_( '|You can <font color="red">go on wwww.jms2win.com, login with your account and go in the menu "Get Latest Version".</font>');
         $result .= JText::_( '|<font color="red">Select the Joomla Multi Sites and click on the "Get Latest Version" button in the top right, to receive a new download ID.</font>');
         $result .= JText::_( '|This update is required because it also needs an update of the JMS core');
         $result .= '|';
      }
	   
      $result .= JText::_( 'Use the slave site deployed directory as administrator directory when present. Otherwise use the master website directory');
      $result .= '|[ACTION]';
      $result .= '|Replace 2 lines by 28 lines in aim to use the slave site deploy directory instead of the master directory';
      $result .= '|This allow for example to manage the specific media or image directory from the back-end';
   }
   // If a patch is present
   else {
      // Check if it contain the "letter tree" path result
      $pos = strpos( $str, 'MULTISITES_ID_PATH');
      if ($pos === false) {
   	   $rc = '[NOK]';
         $result .= JText::_( 'Use the slave site deployed directory as administrator directory when present. Otherwise use the master website directory');
         $result .= '|[ACTION]';
         $result .= '|Replace the previous patch <1.2.35 by a new one. This replace 26 lines by 28 lines in aim to use the slave site deploy directory instead of the master directory';
         $result .= '|This allow for example to manage the specific media or image directory from the back-end';
      }
   }
   
   return $rc .'|'. $result;
}

//------------ jms2win_actionAdminIndex ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionAdminIndex( $model, $file)
{
   // If older version, don't touch the file and reply OK to avoid roll-back the current patches
   if ( jms2win_checkAdminIndex_olderVersion())
   {
      return true;
   }

   // Check that it contains the 'JMS2WIN_VERSION' parameter that was introduced in version 1.1.17
   // Otherwise, return true to ignore the installation with success and avoid roll-back
	$filename = JPath::clean( JPATH_ROOT.DS.'includes'.DS.'multisites.php');
	if ( !file_exists( $filename)) {
	   return true;
	}

   // If the 'multisites.php' is not yet updated,
   $str = file_get_contents( $filename);
   $pos = strpos( $str, "'JMS2WIN_VERSION'");
   if ($pos === false) {
      // Deploy the patches to update the include/multisites.php file
      $model->_deployPatches();
   }

   // Verify that 'multisites.php' is correctly updated,
   $str = file_get_contents( $filename);
   $pos = strpos( $str, "'JMS2WIN_VERSION'");
   if ($pos === false) {
		$mainframe	= &JFactory::getApplication();
      $msg = JText::_( 'Dependency with "multisites.php" is not present');
		$mainframe->enqueueMessage($msg, 'error');
      return true;
   }
   
   // NOW WE ARE SURE THAT 'multisites.php' contain the code required by this patch 
   // to avoid having white page when trying login in the administration 
   // when 'multisites.php' is not patched in the same time.

   include_once( dirname(__FILE__) .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( 'patch_admin_index.php');
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
      define('JPATH_BASE', dirname(__FILE__) );

      define('DS', DIRECTORY_SEPARATOR);
      
      ===========
      and Replace by:
      ===========

      define('DS', DIRECTORY_SEPARATOR);
      // Try detect if this is a slave site and this should set the define MULTISITES_ID
      if ( !defined( 'MULTISITES_ID')) {
         if ( !defined( 'JPATH_MULTISITES')) define( 'JPATH_MULTISITES', dirname( dirname(__FILE__)) .DIRECTORY_SEPARATOR. 'multisites');
         if ( !defined( '_EDWIN2WIN_')) define( '_EDWIN2WIN_', true);
         @include( dirname(dirname(__FILE__)) .DIRECTORY_SEPARATOR. 'includes' .DIRECTORY_SEPARATOR. 'multisites.php');
         if ( defined( 'JMS2WIN_VERSION')) {
            if ( !defined( 'MULTISITES_ADMIN')) define( 'MULTISITES_ADMIN', true);
            if ( class_exists( 'Jms2Win')) Jms2Win::matchSlaveSite();
         }
      }
      
      // If this is a slave site, check if it has a specific deploy directory (if YES, use its path to compute the JPATH_BASE)
      if ( defined( 'MULTISITES_ID')) {
         if ( defined( 'MULTISITES_ID_PATH'))   { $filename = MULTISITES_ID_PATH.DIRECTORY_SEPARATOR.'config_multisites.php'; }
         else                                   { $filename = JPATH_MULTISITES.DS.MULTISITES_ID.DIRECTORY_SEPARATOR.'config_multisites.php'; }
         @include($filename);
         if ( isset( $config_dirs) && !empty( $config_dirs) && !empty( $config_dirs['deploy_dir'])) {
            define('JPATH_BASE', $config_dirs['deploy_dir'].DIRECTORY_SEPARATOR.'administrator');
         }
      }
      else {
         define('JPATH_BASE', dirname(__FILE__) );
      }
   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n      ...          'JPATH_BASE' ....\n
      p0                   p1              

      \n      ...          'DIRECTORY_SEPARATOR' ....\n
                           p2                         p3

      Produce
      begin -> p0 + INSERT PATCH + p3 -> end
      
    */
   // P1: Search begin statement: "'JPATH_BASE'"
   $p1 = strpos( $content, '\'JPATH_BASE\'');
   if ( $p1 === false) {
      return false;
   }
   // P0: Go to Begin of line
   for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
   $p0++;
   
   // P2: Search begin statement: "DIRECTORY_SEPARATOR"
   $p2 = strpos( $content, 'DIRECTORY_SEPARATOR', $p1);
   if ( $p2 === false) {
      return false;
   }
   
   // p3: Search for end of line
   for ( $p3=$p2; $content[$p3] != "\n"; $p3++);

   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p0)
           . $patchStr
           . substr( $content, $p3+1);


   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}
   
   return true;
}
