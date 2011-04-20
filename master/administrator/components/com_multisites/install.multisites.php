<?php
/**
 * @file       install.multisites.php
 * @version    1.2.47
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2011 Edwin2Win sprlu - all right reserved.
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
 * - V1.0.4 10-AUG-2008: Add a check to verify that 'multisites' directory is created.
 * - V1.0.5 19-AUG-2008: Collect the extension version because the manifest files is not yet saved in the target directory.
 * - V1.0.7 22-AUG-2008: Replace native mkdir and copy by Joomla JFolder and JFile in aim to reduce permission problems.
 * - V1.1.21 20-APR-2009: Increase the execution time limit in case where the upload took too much time.
 * - V1.2.0 RC5 25-JUL-2009: Add the creation of an index.html file into the /multisites directory to hide the list of slave sites.
 * - V1.2.0 07-AUG-2009: Include also the controler to allow retreive the JMS version number in the patch definition.
 * - V1.2.23 08-MAR-2010: Cleanup (remove) older Joomla patches to reduce the size of package.
 * - V1.2.29 30-MAY-2010: Avoid replacing the "mutisites.cfg.php" when already present.
 * - V1.2.30 02-JUN-2010: Fix for Joomla 1.6 beta1 compatibility.
 * - V1.2.32 02-JUN-2010: Add Joomla 1.5 Language file conversion to be compatible with for Joomla 1.6 beta3.
 *                        Hide a warning on set_time_limit() when the call to this function not allowed by a server
 *                        that have the safe mode enabled.
 * - V1.2.34 17-JUL-2010: Modify the Joomla 1.5 to 1.6 language conversion to use the "_QQ_" special character corresponding to Quote (") in Joomla 1.6.
 * - V1.2.36 03-SEP-2010: Improve langage conversion to avoid convert first and last quote with _QQ_
 * - V1.2.47 08-FEB-2011: Fix language conversion in Joomla 1.6 that didn't processed the administrator files.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );


if ( !defined( 'JPATH_MULTISITES')) {
   define( 'JPATH_MULTISITES', JPATH_ROOT.DS.'multisites');
}

// If a configuration file are exists, do nothing, else create one based on the "distibution" configuration.
jimport('joomla.filesystem.file');
if ( !JFile::exists( dirname( __FILE__).DS.'multisites.cfg.php')
  &&  JFile::exists( dirname( __FILE__).DS.'multisites.cfg-dist.php')
   )
{
   JFile::copy( dirname( __FILE__).DS.'multisites.cfg-dist.php',
                dirname( __FILE__).DS.'multisites.cfg.php'
              );
                
}

include_once( dirname( __FILE__).DS.'multisites.cfg.php' );

// Save the version number of the extension to use it later during the registration
// If Joomla 1.6
if ( version_compare( JVERSION, '1.6') >= 0) { $myManifestVersion =& $this->manifest->version; 
                                               $GLOBALS['installManifest'] = &$this->manifest;
                                             }
// If Joomla 1.5
else                                         { $myManifestVersion =& $this->manifest->getElementByPath('version'); }
$GLOBALS['installManifestVersion'] = JFilterInput::clean($myManifestVersion->data(), 'cmd');

jimport('joomla.filesystem.folder');

if ( version_compare( JVERSION, '1.6') >= 0) {
   //------------ multisites_convert_languagefile ---------------
   function multisites_convert_language_content( $filename) {
      $lines = array();
      $search  = array( '(', 
                        ')',
                        '{',
                        '}',
                        '[',
                        ']',
                        '"'
                        );
      $replace = array( '&#40;',
                        '&#41;',
                        '&#123;',
                        '&#125;',
                        '&#91;',
                        '&#93;',
                        '"_QQ_"'
                        );
      
      $fd = @fopen( $filename, "r");
      if ( !$fd) {
         return;
      }
      
      while( !feof( $fd)) {
         $line = fgets( $fd);
         if ( !empty( $line)) {
            $line = trim( $line);

            // If comment, skip the processing
            if ( substr( $line, 0, 1) == ';') {}
            // If a old comment
            else if ( substr( $line, 0, 1) == '#') {
               // replace by ';'
               $line = ';' . substr( $line, 1);
            }
            else {
               // Extract the value to quote it and replace some special characters
               $pos = strpos( $line, '=');
               if ( $pos === false) {}
               else {
                  // position just after the "="
                  $pos++;
                  $value = trim( substr( $line, $pos));
                  if ( !empty( $value)) {
                     // If quote is already present
                     if ( substr( $value, 0, 1) == '"' && substr( $value, -1) == '"') {
                        // remove them to avoid convert them
                        $value = rtrim( $value, '"');
                        $value = ltrim( $value, '"');
                        $addquote = '"';
                     }
                     else {
                        $addquote = '"';
                     }
                     $str = str_replace( $search, $replace, $value);
                     
                     $line = substr( $line, 0, $pos)
                           . $addquote
                           . $str
                           . $addquote
                           ;
                  }
               }
            }
         }
         $lines[] = $line;
      }
      fclose( $fd);
      
      // Convert all lines into a string
      $result = implode( "\n", $lines);
      
      // Write the new language file
   	jimport('joomla.filesystem.file');
   	JFile::write( $filename, $result);
   }
   
   //------------ multisites_convert_languagefile ---------------
   function multisites_convert_languagefiles() {
      // Search in the manifest for all the languages files
      if ( empty( $GLOBALS['installManifest'])) {
         return;
      }
      
      $manifest = $GLOBALS['installManifest'];
      if ( !empty( $manifest->languages) && !empty( $manifest->languages->language)) {
         foreach( $manifest->languages->language as $language_file) {
            $filename = JPath::clean( JPATH_ROOT.DS.'language'.DS.$language_file);
            multisites_convert_language_content( $filename);
         }
      }

      if ( !empty( $manifest->administration) && !empty( $manifest->administration->languages)) {
         foreach( $manifest->administration->languages->language as $language_file) {
            $filename = JPath::clean( JPATH_ADMINISTRATOR.DS.'language'.DS.$language_file);
            multisites_convert_language_content( $filename);
         }
      }
   }
}
//------------ com_install ---------------
/**
 * @brief Backup the current Joomla core files that could be patched by MultiSites components.
 *
 * Called by the Component Installer, this function is used to backup all files that could be patched
 * by the Joomla! MultiSites component.\n
 * This backup will be used by Uninstall script to restore the Joomla core files.
 */
function com_install()
{
   $backdir = 'backup_on_install';

   // Increase the maximum time limit of 60 second (just in case where the upload took too much time)
   @set_time_limit( 60);
      
   // Retreive the component name
   $dir = dirname( __FILE__);
   $parts = explode( DS, $dir);
   $name = $parts[count($parts)-1];

   $path = JPATH_ADMINISTRATOR.DS.'components'.DS.$name;
   require_once( $path.DS.'controller.php' );
   require_once( $path.DS.'models'.DS.'registration.php' );
   require_once( $path.DS.'views'.DS.'registration'.DS.'view.php' );
   
   // If Joomla 1.6, 
   if ( version_compare( JVERSION, '1.6') >= 0) {
      // convert Joomla 1.5.x language INI file to replace all special character into their html equivalent
      multisites_convert_languagefiles();
   }

   // Load the language file of this component.
	$lang =& JFactory::getLanguage();
	$lang->load( $name);
   
   // Backup the core joomla files.
   require_once( $path.DS.'models'.DS.'patches.php' );
   $patches = new MultisitesModelPatches();
   $backlist = $patches->backup( $backdir);
   if ( $backlist === false) {
      $msg = $patches->getError();
      echo JText::sprintf( 'INSTALL_BACKUP_ERROR', $msg);
      $backup_rc = false;
   }
   else {
      $backup_rc = true;
   }
   
   // If the backup theorically succeed,
   if ( $backup_rc) {
      // Verify the backup to ensure there is no missing files.
      $missingFiles = $patches->checkBackup( $backdir);
      if ( count($missingFiles) > 0) {
         $msg = '';
         foreach($missingFiles as $missingFile) {
            $msg .= "- $missingFile<br/>";
         }
         echo JText::sprintf( 'INSTALL_CHECKBACKUP_ERROR', $msg);
         return false;
      }
   }
   
   // Create the root Multisites directory where all the 'slave' site configuration will be stored.
   JFolder::create( JPATH_MULTISITES);
   if ( !JFolder::exists( JPATH_MULTISITES)) {
   	$msg = JPATH_MULTISITES;
      echo JText::sprintf( 'INSTALL_MULTISITE_DIR_ERROR', $msg);
   }

   
   // Create an index.html file to hide the list of directories present in the /multisites directory
   JFile::copy( $path.DS.'index.html', JPATH_MULTISITES .DS. 'index.html');
   
   // Finally report success installation
   $fullbackdir = $path.DS.$backdir;
   echo JText::sprintf('INSTALL_BACKUP_SUCCESS', $fullbackdir);

   // remove older patches definition when present
   $cleanupPatches = $patches->cleanupPatches();
   if ( !empty( $cleanupPatches)) {
      echo JText::sprintf('INSTALL_CLEANUP_PATCHES', implode( '</li><li>', $cleanupPatches));
   }


   // Check if this component is registered
   $model = new Edwin2WinModelRegistration();
   if ( !$model->isRegistered()) {
      $view = new Edwin2WinViewRegistration( array('base_path' => $path) );
   	$view->setModel( $model, true );
   	$redirect_url = JURI::base()."index.php?option=$name&task=registered";
   	$view->registrationButton( $redirect_url);
   }


   return true;
}
