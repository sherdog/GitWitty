<?php 
/**
 * @file       uninstall.multisites.php
 * @version    1.2.45
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
 * - V1.0.7 22-AUG-2008: Replace file_exists, unlink, rmdir Joomla JFolder and JFile in aim to reduce permission problems.
 *                       Fix also uninstall procedure that can report error when some patches are installed.
 * - V1.1.0 27-OCT-2008: Ensure that '/includes/multisites.php' file is deleted.
 * - V1.2.1 07-AUG-2009: Fix craches cause by the Version number checking and also ignore some errors that may reported
 *                       on install.xml and folder.php
 * - V1.2.45 15-DEC-2010: Add cleanup for Joomla 1.6 to remove to "defines.php" files when they are present.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

if ( !defined( 'JPATH_MUTLISITES_COMPONENT')) {
   define( 'JPATH_MUTLISITES_COMPONENT', JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites');
}
// If Joomla 1.6
if ( version_compare( JVERSION, '1.6') >= 0) {
   if ( !defined( 'MULTISITES_MANIFEST_FILENAME')) {
      define( 'MULTISITES_MANIFEST_FILENAME', 'extension.xml');
   }
}
// If Joomla 1.5
else {
   if ( !defined( 'MULTISITES_MANIFEST_FILENAME')) {
      define( 'MULTISITES_MANIFEST_FILENAME', 'install.xml');
   }
}

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
require_once( JPATH_MUTLISITES_COMPONENT.DS.'controller.php' );
require_once( JPATH_MUTLISITES_COMPONENT.DS.'models' .DS.'patches.php' );


//------------ com_uninstall ---------------
/**
 * @brief Restore the Joomla core files from the backp done on installation.
 *
 * Called by the Component Uninstaller, this function is used to restore all files that could be patched
 * by the Joomla! MultiSites component.\n
 * This restore the Joomla core files and inform the user to 'rename' or remove the 'installation' directory.
 */
function com_uninstall()
{
   $rc = true;
   $backdir = 'backup_on_install';

   // Load the language file of this component.
	$lang =& JFactory::getLanguage();
	$lang->load( 'com_multisites');

   // Restore the core joomla files.
   $patches = new MultisitesModelPatches();
   $patches->uninstall();

   // JPATH_INSTALLATION can not be used because its definition is modified by JMS2Win.
   // Need to redefine the value to ensure it correspond to the original Joomla Definition.
   $Path2Installation = JPATH_ROOT .DS. 'installation';
   $missingFiles = $patches->checkRestore();
   if ( count($missingFiles) > 0) {
      $msg = '';
      foreach($missingFiles as $missingFile) {
         // If it was not possible to restore the installation defines files, 
         // this mean that the directory was not present and can be deleted
         if ( $missingFile == 'installation/includes/defines.php') {
            // Remove the installation directory
            if ( JFolder::exists( $Path2Installation)) {
               JFolder::delete( $Path2Installation);
            }
         }
         // Ignore the install.xml error
         else if ( $missingFile == 'administrator/components/com_multisites/' .MULTISITES_MANIFEST_FILENAME) {}
         else if ( $missingFile == 'libraries/joomla/filesystem/folder.php') {}
         else if ( $missingFile == 'administrator/defines.php') {}
         else if ( $missingFile == 'defines.php') {}
         else {
            $msg .= "- $missingFile<br/>";
            $rc = false;
         }
      }
      // If error
      if ( $rc == false) {
         echo JText::sprintf( 'INSTALL_CHECKRESTORE_ERROR', $msg);
      }     
   }
   
   // Ensure that defines_multisites.php is deleted
   $filename = JPATH_ROOT.DS.'includes'.DS.'defines_multisites.php';
   if ( JFile::exists( $filename)) {
      JFile::delete( $filename);
   }

   // Ensure that multisites.php is deleted
   $filename = JPATH_ROOT.DS.'includes'.DS.'multisites.php';
   if ( JFile::exists( $filename)) {
      JFile::delete( $filename);
   }
   
   // Ensure the Joomla Installation directory is NOT present. Otherwise, inform the user to rename or remove it.
   if ( JFolder::exists( $Path2Installation)) {
      // Propose the user to rename or delete it
      echo JText::sprintf( 'INSTALL_RENAME_INSTALL_DIR', $msg);
   }


   // If Joomla 1.6
   if ( version_compare( JVERSION, '1.6') >= 0) {
      // Ensure that the "site" defines is deleted
      $filename = JPATH_SITE.DS.'defines.php';
      if ( JFile::exists( $filename)) {
         JFile::delete( $filename);
      }
      // Ensure that the "administrator" defines is deleted
      $filename = JPATH_ADMINISTRATOR.DS.'defines.php';
      if ( JFile::exists( $filename)) {
         JFile::delete( $filename);
      }
   }
   
   return $rc;
}

?>
