<?php
/**
 * @file       patches.php
 * @version    1.2.46
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
 * - V1.0.4 10-AUG-2008: Add reporting on file/directory permission.
 * - V1.0.6 22-AUG-2008: Try avoid deletion, re-create the backup directory.
 * - V1.0.7 22-AUG-2008: Replace native mkdir and copy by Joomla JFolder and JFile in aim to reduce permission problems.
 * - V1.0.8 27-AUG-2008: When removing files, first check that file is not already removed to avoid reporting errors.
 * - V1.2.0 21-JUL-2009: Add a check that the patches are not installed from a slave site.
 *                       This void for example to install the configuration.php wrapper into the configuration file of a slave site
 *                       and result in the impossiblity to access the website due to syntax error.
 * - V1.2.7 26-SEP-2009: Add "is_writable" when checking permissions to better help users when patches can not be installed.
 * - V1.2.14 05-DEC-2009: Add Joomla 1.6 alpha 2 compatibility.
 * - V1.2.23 08-MAR-2010: Add cleanup of older patch (<1.5.10)
 * - V1.2.27 26-APR-2010: Replace the ereg by preg_match for PHP 5.3 compatibility
 * - V1.2.29 30-MAY-2010: Add the call to a Multisites Plugin to allow merge patches definition with contributors patches.
 * - V1.2.30 04-JUN-2010: Fix the detection that patches are already loaded to speedup the processing.
 * - V1.2.45 16-DEC-2010: Add Joomla 1.6 RC1 compatibility.
 * - V1.2.46 30-DEC-2010: Fix the name of the plugin "files2patch" instead of "files2path" (missing C).
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );
jimport('joomla.filesystem.archive');
jimport('joomla.filesystem.path');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

if ( !defined( 'JPATH_MUTLISITES_COMPONENT')) {
   define( 'JPATH_MUTLISITES_COMPONENT', JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites');
}

if ( !defined( 'MULTISITES_DIR_RIGHTS')) {
   define( 'MULTISITES_DIR_RIGHTS', 0755);
}

// ===========================================================
//             MultisitesModelPatches class
// ===========================================================
/**
 * @brief Check, install, uninstall the Joomla core patches.
 */
class MultisitesModelPatches extends JModel
{
   // Private members
	var $_modelName = 'patches';
	/** List of files and folders that must be patched */
	var $_files2patch = array();
	var $_corefunction2backup = array();      /**< Function that identify the joomla core files to backup */
   var $_patchesVersion = '';
   
   	                      
   //------------ _getPatchList ---------------
   /**
    * @brief Return the list of patch available. This is the hardcoded list + external list.
    */
   function &_getPatchList()
	{
	   static $instance;
	   if ( !isset($instance)) {
         $this->_loadExternalPatches();
	      $instance = $this->_files2patch;
	   }
	   return $instance;
	}
	
   //------------ _loadExternalPatches ---------------
	function _loadExternalPatches()
	{
	   static $isAlreadyLoaded;
		$mainframe	= &JFactory::getApplication();
	   
	   // If already loaded,
	   if ( isset( $isAlreadyLoaded)) {
	      // Do nothing
	      return;
	   }
	   
	   $isAlreadyLoaded = true;

	   // Include the public external function and patch definition + version
      include_once( JPATH_MUTLISITES_COMPONENT.DS.'patches' .DS. 'patch_collection.php');
      if ( isset( $patchesVersion)) {
         $this->_patchesVersion = $patchesVersion;
      }

      // List of function that identify the joomla core files that require a backup
      if ( isset( $corefiles2backup) && is_array( $corefiles2backup)) {
         $this->_corefunction2backup = array_merge( $this->_corefunction2backup, $corefiles2backup);
      }

      JPluginHelper::importPlugin('multisites');
      $results = $mainframe->triggerEvent('coreFunctions2Backup', array ( & $this->_patchesVersion, & $this->_corefunction2backup));
      
      // Update the list of files to patch
      if ( isset( $files2patch) && is_array( $files2patch)) {
         $this->_files2patch = array_merge( $this->_files2patch, $files2patch);
      }
      
      $results = $mainframe->triggerEvent('files2Patch', array ( & $this->_patchesVersion, & $this->_files2patch));
	}

   //------------ getCoreFunctionList ---------------
   /**
    * @brief Get the list of the function that identify the joomla core files that require a backup;
    */
	function getCoreFunctionList()
	{
	   $this->_loadExternalPatches();
	   return $this->_corefunction2backup;
	}
   	                      
   //------------ getPatchesVersion ---------------
   /**
    * @brief Get the version number of the list of patches;;
    */
	function getPatchesVersion()
	{
	   $this->_loadExternalPatches();
	   return $this->_patchesVersion;
	}

	//------------ _checkPermissions ---------------
	function _checkPermissions( $file)
	{
		// Compute a file name that will be used as reference for permission expected
		$parts = explode( DIRECTORY_SEPARATOR, dirname( __FILE__));
		array_pop( $parts );
		$jmsFileName = implode( DIRECTORY_SEPARATOR, $parts ) .DIRECTORY_SEPARATOR. 'admin.multisites.php';
		
		$jmsStat 	 = stat( $jmsFileName);
		$filename = JPath::clean( JPATH_ROOT.DS.$file);
		// If file does not exists,
		if ( !JFile::exists( $filename)) {
			// Use the directory path
			$filename = dirname( $filename);
			// If the directory does not exists
			if ( !JFolder::exists( $filename)) {
				// Ignore the permission checking
				return true;
			}
		}
		$myFileStat  = stat( $filename);
		
		// If same Owner
		if ( $jmsStat['uid'] == $myFileStat['uid']) {
			// Check if file owner has write permission
			$myFilePerms = fileperms( $filename);
			if ( ($myFilePerms & 0x0080) == 0x0080) {
			   if ( !is_writable( $filename)) {
         		return 'PATCHES_IS_NOT_WRITABLE';
			   }
				return true;
			}
			return 'PATCHES_SAME_OWNER_CANNOT_WRITE';
		}
		// Check if same Group
		else if ( $jmsStat['gid'] == $myFileStat['gid']) {
			// Check if file group has write permission
			$myFilePerms = fileperms( $filename);
			if ( ($myFilePerms & 0x0010) == 0x0010) {
			   if ( !is_writable( $filename)) {
         		return 'PATCHES_IS_NOT_WRITABLE';
			   }
				return true;
			}
			return 'PATCHES_SAME_GROUP_CANNOT_WRITE';
		}
		
		// Check if file world has write permission
		$myFilePerms = fileperms( $filename);
		if ( ($myFilePerms & 0x0002) == 0x0002) {
		   if ( !is_writable( $filename)) {
      		return 'PATCHES_IS_NOT_WRITABLE';
		   }
			return true;
		}
		return 'PATCHES_WORLD_CANNOT_WRITE';
	}



   //------------ _check ---------------
   function _check( $action, $file)
   {
      $fnCheck = '';
      if ( is_string( $action)) {
         $fnCheck = $action;
      }
      else if ( is_array( $action)) {
         if ( isset( $action['check'])) {
            $fnCheck = $action['check'];
         }
      }
      if ( empty( $fnCheck)) {
         return '[NOK]|*** ERROR ***| unable to find check action for : ' . var_export( $action);
      }
      // If Function is defined locally
      $fn = '_check' . ucfirst(strtolower( $fnCheck));
      if ( method_exists( $this, $fn)) {
         $status = $this->$fn( $file);
         // If not OK, also check file permission
	      if ( strncmp( $status, '[NOK]', 5) == 0) {
	      	$rc = $this->_checkPermissions( $file);
	      	if ( is_string( $rc)) {
	      		$status = '[NOK]|' . JText::_( $rc) . substr( $status, 5);
	      	}
	      }
         return $status;
      }
      
      // Check if the function exists in an external patches definitin
      $this->_loadExternalPatches();
      $fn = 'jms2win_check' . ucfirst(strtolower( $fnCheck));
      if ( function_exists( $fn)) {
         $status = $fn( $this, $file);
         // If not OK, also check file permission
	      if ( strncmp( $status, '[NOK]', 5) == 0) {
	      	$rc = $this->_checkPermissions( $file);
	      	if ( is_string( $rc)) {
	      		$status = '[NOK]|' . JText::_( $rc) . substr( $status, 5);
	      	}
	      }
         return $status;
      }
      
      // If the function is not present
      return '[NOK]|*** ERROR ***| Check function "' . $fnCheck . '" does not exists.';
   }

	
		
   //------------ _action ---------------
   function _action( $action, $file)
   {
      $fnAction = '';
      if ( is_string( $action)) {
         $fnAction = $action;
      }
      else if ( is_array( $action)) {
         if ( isset( $action['action'])) {
            $fnAction = $action['action'];
         }
      }
      if ( empty( $fnAction)) {
         return '[NOK]|*** ERROR ***| unable to find the action for : ' . var_export( $action);
      }
      // If Function is defined locally
      $fn = '_check' . ucfirst(strtolower( $fnAction));
      if ( method_exists( $this, $fn)) {
         $status = $this->$fn( $file);
         return $status;
      }
      
      // Check if the function exists in an external patches definitin
      $this->_loadExternalPatches();
      $fn = 'jms2win_action' . ucfirst(strtolower( $fnAction));
      if ( function_exists( $fn)) {
         $status = $fn( $this, $file);
         return $status;
      }
      
      // If the function is not present
      return '[NOK]|*** ERROR ***| Check function "' . $fnAction . '" does not exists.';
   }

   //------------ _restoreInstallation ---------------
   /**
    * Restore the 'installation' directory.
    */
   function _restoreInstallation()
   {
      if ( isset( $this->_renamed_install_dir)
        && strlen( $this->_renamed_install_dir) > 0)
      {
         $path = JPath::clean( JPATH_ROOT.DS.$this->_renamed_install_dir);
         if ( !JFolder::exists( $path)) {
   		   $this->setError( JText::sprintf( 'PATCHES_RENAME_INSTALLDIR_NOTFOUND', $path));
            return false;
         }
         
		   $installdir = JPath::clean( JPATH_ROOT.'/installation');
         JFolder::move( $path, $installdir);
      }
      else
      {
   		$extractdir = JPATH_ROOT;
         if ( version_compare( JVERSION, '1.6') >= 0) {
      		$archivename = JPath::clean( JPATH_MUTLISITES_COMPONENT.'/patches/installation_j16.zip');
         }
         else {
      		$archivename = JPath::clean( JPATH_MUTLISITES_COMPONENT.'/patches/installation.tar.gz');
         }
   
   		$result = JArchive::extract( $archivename, $extractdir);
   		if ( $result === false ) {
   		   $this->setError( JText::_( 'PATCHES_DEPLOY_ERR'));
   			return false;
   		}
      }
      
      return true;
   }



	
   //------------ _deployPatches ---------------
   /**
    * @brief Deploy the patches file.
    *
    * @remarks
    * In general the extension of a file to deploy is '.tar.gz'.
    * As we have noticed that Joomla untar is not accurate and may be unable to untar files created on unix platform
    * a rescue '.zip' extension is also available.
    */
   function _deployPatches( $patchfile='patches_files.tar.gz')
   {
      // Try to find the file in the "patches/jxxx/" directory where xxx is the Joomla version number
		$version    = new JVersion();
		$shortVers  = $version->getShortVersion();
		if (version_compare( $shortVers, '1.5.10', '>=')) {
   		$jversdir    = DS. 'j' . $version->getShortVersion();
   		$archivename = JPath::clean( JPATH_MUTLISITES_COMPONENT .DS. 'patches' . $jversdir .DS. $patchfile);
   		if ( !JFile::exists( $archivename)) {
   		   // If extension '.tar.gz' then
   		   if ( substr( $patchfile, -7) == '.tar.gz') {
   		      // Check if a similar file exists with extension .zip
         		$archivename = JPath::clean( JPATH_MUTLISITES_COMPONENT .DS. 'patches' . $jversdir .DS. substr( $patchfile, 0, strlen($patchfile)-7) . '.zip');
         		if ( !JFile::exists( $archivename)) {
            		$archivename = '';   // Use default
         		}
   		   }
   		   else {
         		$archivename = '';   // Use default
   		   }
   		}
		}
		
		// If the file is not present in the Jxxx directory, use the default path
		if ( empty( $archivename)) {
	      // Use default path
   		$archivename = JPath::clean( JPATH_MUTLISITES_COMPONENT.'/patches/'.$patchfile);
   		if ( !JFile::exists( $archivename)) {
      		$archivename = JPath::clean( JPATH_MUTLISITES_COMPONENT.'/patches/'.$patchfile);
   		   // If extension '.tar.gz' then
   		   if ( substr( $patchfile, -7) == '.tar.gz') {
   		      // Check if a similar file exists with extension .zip
   		      $archivename = JPath::clean( JPATH_MUTLISITES_COMPONENT.'/patches/'. substr( $patchfile, 0, strlen($patchfile)-7) . '.zip');
   		   }
   		}
		}

		$extractdir  = JPATH_ROOT;
		$result = JArchive::extract( $archivename, $extractdir);
		if ( $result === false ) {
		   $this->setError( JText::_( 'PATCHES_DEPLOYPATCHES_ERR'));
			return false;
		}
		return true;
   }

	
	var $_canInstall = null;

   //------------ isPatchesInstalled ---------------
   /**
    * Check if ALL patches are installed.
    * For each files to patch list, check if the patch is already applied or not.
    * @return
    * - True  when all patches are installed.
    * - False when a patch is not installed.
    */
   function isPatchesInstalled()
	{
	   $patchlist = $this->_getPatchList();
	   foreach( $patchlist as $file => $fnCheck) {
	      $status = $this->_check( $fnCheck, $file);
	      if ( strncmp( $status, '[NOK]', 5) == 0) {
      	   return false;
	      }
	   }
		
		return true;
	}

   //------------ somePatchesInstalled ---------------
   /**
    * Check if at least one patch is installed
    * @return
    * - True  when one patch is installed.
    * - False when there is NO patch installed.
    */
   function somePatchesInstalled()
	{
	   $patchlist = $this->_getPatchList();
	   foreach( $patchlist as $file => $fnCheck) {
	      $status = $this->_check( $fnCheck, $file);
	      if ( strncmp( $status, '[OK]', 4) == 0) {
      	   return true;
	      }
	   }
		
		return false;
	}

	
   //------------ getPatchesStatus ---------------
   /**
    * For each files to patch, check if the patch is already applied or not.
    * @return An array with KEY=File to patch and VALUE=Status.
    */
   function getPatchesStatus()
	{
	   $this->_canInstall = false;
	   $result = array();
	   $patchlist = $this->_getPatchList();
	   foreach( $patchlist as $file => $fnCheck) {
	      $status = $this->_check( $fnCheck, $file);
	      if ( strncmp( $status, '[NOK]', 5) == 0) {
      	   $this->_canInstall = true;
	      }
	      // If the file is not present and can be Ignored, don't report the error
	      if ( strncmp( $status, '[IGNORE]', 8) == 0) {
	      }
	      else {
   	      $result = array_merge( $result, array( $file => $status));
	      }
	   }
		
		return $result;
	}
	
   //------------ canInstall ---------------
   /**
    * Check if some patches can be installed.
    * @return
    * - TRUE when install must be installed.
    * - FALSE when there is no patches to install (because there are already installed).
    */
	function canInstall()
	{
      // If called from a slave site, disable the installation
      if ( defined( 'MULTISITES_ID')) {
         return false;
      }

	   if ( $this->_canInstall == null) {
	      $this->getPatchesStatus();
	   }
	   
	   return $this->_canInstall;
	}

   //------------ isFn2Backup ---------------
   /**
    * @brief Check if a function correspond to a type of file to backup.
    * In fact all files must be backed up except defines_multisites.php and installation directory
    */
	function isFn2Backup( $fn)
	{
	   if ( $fn == 'ifPresent' || $fn == 'ifDirPresent' || $fn == 'JMSVers' ){
	      return false;
	   }
	   return true;
	}
	
   //------------ isCoreJoomla ---------------
	function isCoreJoomla( $fn)
	{
	   if ( in_array( $fn, $this->getCoreFunctionList())) {
	      return true;
	   }
	   return false;
	}
	
   //------------ file_copy ---------------
	function file_copy( $src, $dest)
	{
	   if ( JFile::exists( $src)) {
	      return JFile::copy($src, $dest);
	   }
	   return false;
	}

   //------------ backup ---------------
   /**
    * Backup all 'defines' files that will be patches
    * 
    * By default, it does not remove the previous backup directory content because the backup
    * is only performed on files that must be patched.
    * We need a cumultative backup of files.
    *
    * @return
    * - TRUE when backup is completed.
    *   The list of backuped files are stored in property __backup_files[].
    * - FALSE when backup FAILED
    */
	function backup( $bakdir='backup', $removeBackupDir = false)
	{
	   $this->_backup_files[] = array();

	   $backup_dir = JPath::clean( JPATH_MUTLISITES_COMPONENT .DS. $bakdir);
		
		if ( $removeBackupDir) {
   		// First remove all the content of the backup directory except the backup directory itself
   		if( JFolder::exists( $backup_dir)
   		 && !JFolder::delete( $backup_dir)) {
   			$this->setError( JText::sprintf( 'PATCHES_REMOVE_BAKDIR_ERR', $bakdir));
   			return false;
   		}
		}

	   // If the backup directory is not present
	   if ( !JFolder::exists( $backup_dir)) {
   	   // Re create the 'backup' directory
   	   if ( !JFolder::create( $backup_dir, MULTISITES_DIR_RIGHTS)) {
   	      $this->setError( JText::sprintf( 'PATCHES_CREATE_BAKDIR_ERR', $bakdir));
   	      return false;
   	   }
	   }

      // Copy all files		
	   $patchlist = $this->_getPatchList();
	   foreach( $patchlist as $file => $fnCheck) {
	      if ( $this->isFn2Backup( $fnCheck)) {
	         $src  = JPath::clean( JPATH_ROOT .DS. $file);
	         $dest = JPath::clean( $backup_dir .DS. $file);
	         
	         // If the patch is already installed
	         // AND a backup already exists
   	      $status = $this->_check( $fnCheck, $file);
	         if ( strncmp( $status, '[OK]', 4) == 0
	           && JFile::exists( $dest)
	            )
	         {
	            // Don't backup the file
	            continue;
	         }

      		// Create the destination folder if it does not exists yet
      		$dest_folder = dirname( $dest);
      	   if ( !JFolder::create( $dest_folder, MULTISITES_DIR_RIGHTS)) {
      	      $this->setError( JText::sprintf( 'PATCHES_CREATE_DEST_FOLDER_ERR', $dest_folder));
      	      return false;
      	   }
      		
      		// Copy the file
   			if (!$this->file_copy($src, $dest)) {
   			   // If the error occurs for a file in the 'installation' directory, ignore it
   			   if ( preg_match( '#^installation#', $file) || !$this->isCoreJoomla( $fnCheck)) { }
   			   else if ( preg_match( '#^administrator/defines#', $file)) { }
   			   else if ( preg_match( '#^defines#', $file)) { }
   			   else {
      				$this->setError( JText::_( 'PATCHES_BACKUP_ERR'));
      				return false;
   			   }
   			}
   			else {
   			   $this->_backup_files[] = $dest;
   			}
	      }
	   }
	   return true;
	}

   //------------ checkBackup ---------------
   /**
    * Check the backup directory to verify that all files are stored.
    * It scan the list of files to patch and verify that an entry exists in the backup directory
    *
    * @return
    * Return an array with the list of files that are missing in the backup.
    * Otherwise, this return an empty list.
    */
	function checkBackup( $bakdir='backup')
	{
	   $result = array();
		$backup_dir = JPath::clean( JPATH_MUTLISITES_COMPONENT .DS. $bakdir);
	   $patchlist = $this->_getPatchList();
	   foreach( $patchlist as $file => $fnCheck) {
	      if ( $this->isFn2Backup( $fnCheck)) {
	         $filename  = JPath::clean( $backup_dir .DS. $file);
	         if ( !JFile::exists( $filename)) {
   			   // If the error occurs for a file in the 'installation' directory, ignore it
   			   if ( preg_match( '#^installation#', $file) || !$this->isCoreJoomla( $fnCheck)) { }
   			   else if ( preg_match( '#^administrator/defines#', $file)) { }
   			   else if ( preg_match( '#^defines#', $file)) { }
   			   else {
   	            $result[] = $filename;
   			   }
	         }
	      }
	   }
	   return $result;
	}

   //------------ cleanupPatches ---------------
   /**
    * @brief Remove older patches
    */
	function cleanupPatches()
	{
	   $results = array();
	   $patchList = array( 'j1.5.3', 'j1.5.4', 'j1.5.5', 'j1.5.6', 'j1.5.7', 'j1.5.8', 'j1.5.9');
	   
		foreach( $patchList as $patchdir) {
   		$dir = JPath::clean( JPATH_MUTLISITES_COMPONENT .DS. 'patches' .DS . $patchdir);
   		if ( JFolder::exists( $dir)) {
   		   if ( JFolder::delete( $dir)) {
   		      $results[] = $patchdir;
   		   }
   		}
		}
		
		return $results;
	}


   //------------ restore ---------------
   /**
    * Restore all 'defines' files that have been patched
    *
    * @return
    * - TRUE when backup is completed.
    * - FALSE when backup FAILED
    */
	function restore( $bakdir='backup')
	{
	   $result = array();
	   $rc = true;
		$backup_dir = JPath::clean( JPATH_MUTLISITES_COMPONENT .DS. $bakdir);
	   $patchlist = $this->_getPatchList();
	   foreach( $patchlist as $file => $fnCheck) {
	      if ( $this->isFn2Backup( $fnCheck)) {
	         $src  = JPath::clean( $backup_dir .DS. $file);
	         $dest = JPath::clean( JPATH_ROOT .DS. $file);
	         // If the backup file does not exists
	         if ( !JFile::exists( $src)) {
   				$this->setError( JText::_('PATCHES_BAKFILE_MISSING'));
   				$rc = false;
	         }
	         else {
	            // If the file is not writable,
	            if ( !is_writable( $dest)) {
	               // Try to make the file writable before restore it
	               // Save current file permission
            		$curPermission = @ decoct(@ fileperms( $dest) & 0777);
            		// Try to make the file writeable
            		if ( JPath::isOwner( $dest) && !JPath::setPermissions( $dest, '0644')) {
            			$rc = false;
            			continue;
            		}
	            }
   	         // Try to replace the file with the backup one
   	         if (!$this->file_copy($src, $dest)) {
      				$this->setError( JText::_('PATCHES_RESTORE_ERR'));
      				$rc = false;
      			}
      			// If we tried to change the file permission
      			if ( isset( $curPermission)) {
      			   // Now restore the value
      			   JPath::setPermissions( $dest, $curPermission);
      			   unset( $curPermission);
      			}
	         }
	      }
	   }
	   return $rc;
	}


   //------------ checkRestore ---------------
   /**
    * Check if all files are correctly stored (patches removed).
    * It scan the list of files to patch and verify that patches are not present.
    *
    * @return
    * Return an array with the list of files that still contain patches.
    * Otherwise, this return an empty list.
    */
	function checkRestore()
	{
	   $result = array();
	   
	   $patchlist = $this->_getPatchList();
	   foreach( $patchlist as $file => $fnCheck) {
	      $fnUcFirst = ucfirst(strtolower( $fnCheck));
	      // If this is a functin for which a backup can be performed
	      if ( $this->isFn2Backup( $fnUcFirst))
	      {
   	      $status = $this->_check( $fnCheck, $file);
   	      // If the patch is present, this is an error
   	      if ( strncmp( $status, '[OK]', 4) == 0) {
   	         $result[] = $file;
   	      }
	      }
	   }
		
		return $result;
	}


   //------------ installPatches ---------------
   /**
    * Proceed with the installation of the patches.
    * The patches are contained into 2 files (defines.targ.gs & installation.tar.gz)
    *
    * @return
    * - TRUE when install is completed.
    * - FALSE when some patches can not be installed.
    */
	function installPatches()
	{
	   $this->_patch_file_err = '';
	   $patchlist = $this->_getPatchList();
	   foreach( $patchlist as $file => $fnCheck) {
	      // If the patch is not installed
	      $status = $this->_check( $fnCheck, $file);
	      if ( strncmp( $status, '[NOK]', 5) == 0) {
	         // Perform the action to deploy the patch
   	      if ( !$this->_action( $fnCheck, $file)) {
   	         $this->_patch_file_err = $file;
   	         return false;
   	      }
	      }
	   }
	   
	   return true;
	}

   //------------ install ---------------
   /**
    * Proceed with the installation of the patches.
    * The patches are contained into 2 files (defines.targ.gs & installation.tar.gz)
    *
    * @param $renamed_install_dir When the original joomla 'installation' directory is renamed,
    *                             this parameter allows to provide the rename 'installation' directory
    *                             that must be used to create the 'installation' directory.
    *
    * @return
    * - TRUE when install is completed.
    * - FALSE when some patches can not be installed.
    */
	function install( $renamed_install_dir='')
	{
		$mainframe	= &JFactory::getApplication();
		
	   // Backup all files in case where the installation failed and a restore is required.
	   if ( !$this->backup()) {
	      return false;
	   }
	   
      // Verify the backup
      $missingFiles = $this->checkBackup();
	   if ( count( $missingFiles) > 0) {
	      $msg = JText::_( 'PATCHES_MISSING_FILES');
	      foreach( $missingFiles as $filename) {
	         $msg .= '</li><li>' . $filename;
	      }
   		$mainframe->enqueueMessage($msg, 'error');
	      return false;
	   }

	   $this->_renamed_install_dir = $renamed_install_dir;
	   
	   // First deploy all patches
	   if ( !$this->installPatches()) {
	      if ( !empty( $this->_patch_file_err)) {
	         $msg = JText::sprintf( 'PATCHES_ERROR_FILE', $this->_patch_file_err);
      		$mainframe->enqueueMessage($msg, 'error');
	      }
         $this->restore();
         $this->_removeInstallation();
         return false;
	   }
	   
	   // As the 'installation' directory could be restored from the original joomla (by the user),
	   // Some patches could be missing.
	   // Therefore, retry deployment of patches to update the 'installation' directory
	   if ( !$this->installPatches()) {
         $this->restore();
         $this->_removeInstallation();
         return false;
	   }
	   
	   return true;
	}

   //------------ _undoPatches ---------------
   /**
    * Proceed with the uninstallation of the patches.
    * @return
    * - TRUE when uninstall is completed.
    * - FALSE when some patches can not be removed.
    */
	function _undoPatches()
	{
		$mainframe	= &JFactory::getApplication();
		$result = array();
		
	   // If there is no patches installed
	   if ( !$this->somePatchesInstalled()) {
	      // Do nothing
	      return $result;
	   }


      // Restore files from the backup
      if ( $this->restore()) {
         // Double check that restore is perfectly restored
         // (just in case where some files was in read only or whatever reason that reject the restore)
         $result = $this->checkRestore();
         if ( count( $result) <= 0) {
            return $result;
         }
      }

      // Retry to restore files from the backup reallized during the installation of the multisites component
      if ( $this->restore( 'backup_on_install')) {
         // Double check that restore is perfectly restored
         $result = $this->checkRestore();
         if ( count( $result) <= 0) {
            return $result;
         }
      }
      
      // Retry rescue from a tar.gz
      if ( $this->_deployPatches( 'restore_files.tar.gz')) {
         // Double check that restore is perfectly restored
         $result = $this->checkRestore();
         if ( count( $result) <= 0) {
            return $result;
         }
      }

      return $result;
	}

   //------------ _removeFiles ---------------
   /**
    * Remove the files that was added
    * @return
    * Return the list of files that can not be deleted
    * When the list is empty, this mean that all files was correctly removed (SUCCESS).
    */
	function _removeFiles()
	{
	   $result = array();
	   $patchlist = $this->_getPatchList();
	   foreach( $patchlist as $file => $fnCheck) {
	      if ( $fnCheck == 'ifPresent') {
      		$filename = JPath::clean( JPATH_ROOT.DS.$file);
      		if ( JFile::exists( $filename)) {
         		JFile::delete( $filename);
      		}
      		if ( JFile::exists( $filename)) {
      		   $result[] = $filename;
      		}
	      }
	   }
	   return $result;
	}


   //------------ _removeInstallation ---------------
   /**
    * Rename the 'installation' directory in 'installation_to_delete'
    */
	function _removeInstallation()
	{
		$inst_dir = JPath::clean( JPATH_ROOT . '/installation');
		// If the 'installation' directory does not exist,
		if ( !JFolder::exists($inst_dir)) {
		   // Do nothing
		   return true;
		}
		
		// Rename the 'installation' directory into 'installation_to_delete' directory
		$del_dir  = JPath::clean( JPATH_ROOT . '/installation_to_delete');
		// If directory already exist, try to add a suffix
		if ( JFolder::exists( $del_dir)) {
		   for ( $i=1; ; $i++) {
      		$del_dir  = JPath::clean( JPATH_ROOT . '/installation_to_delete_' . $i);
      		if ( !JFolder::exists( $del_dir)) {
      		   break;
      		}
		   }
		}
		
		return rename( $inst_dir, $del_dir);
	}


   //------------ uninstall ---------------
   /**
    * Proceed with the uninstallation of the patches.
    * @return
    * - TRUE when uninstall is completed.
    * - FALSE when some patches can not be removed.
    */
	function uninstall()
	{
	   $rc = true;
	   $err = '';
	   
	   // Undo the patches
	   $result = $this->_undoPatches();
	   if ( count( $result)>0) {
	      $err .= JText::_('PATCHES_UNDOPATCHES_ERR');
	      foreach( $result as $filename) {
	         $err .= '</li><li>'
	              .  $filename;
	      }
	      $rc = false;
	   }

	   // Remove the added files
	   $result = $this->_removeFiles();
	   if ( count( $result)>0) {
	      $err .= JText::_('PATCHES_REMOVEFILES_ERR');
	      foreach( $result as $filename) {
	         $err .= '</li><li>'
	              .  $filename;
	      }
	      $rc = false;
	   }

	   // Remove the installation directory
	   if ( !$this->_removeInstallation()) {
	      if ($err!=null) {
	         $err .= '</li><li>';
	      }
	      $err .= JText::_('PATCHES_REMOVEINSTALL_ERR');
	      $rc = false;
	   }
	   
	   if ( $err != '') {
	      $this->setError( $err);
	      return false;
	   }
	   
	   return true;
	}


   //------------ searchInstallationDirectories ---------------
   /**
    * @brief Search for all directories containing the file 'localise.xml'.
    *
    * The 'localise.xml' file seems to be only present in the installation directory.
    * Is presence is an indicator that "installation" directory exist (probably with another name).
    *
    * @return
    * Return the list of all 'root' directories containing the file 'localise.xml'.
    */
   function searchInstallationDirectories()
   {
      $result = array();
      $dirs = JFolder::folders( JPATH_ROOT);
      foreach( $dirs as $dir) {
         $filename = JPATH_ROOT .DS. $dir .DS. 'localise.xml';
         if ( JFile::exists( $filename)) {
            $result[] = $dir;
         }
      }
      
      return $result;
   }


   //------------ checkUpdates ---------------
   /**
    * @brief Check for Updates
    */
   function checkUpdates( $url, $reginfo, $ignoreVersion=false)
   {
   	$option = JRequest::getCmd('option');
      jimport('joomla.installer.helper');
      
      if ( !isset( $reginfo['product_key'])) {
         return JText::_( 'PATCHES_UPDATE_NEEDREG_ERR');
      }
      
      if ( $ignoreVersion) {
         $version = '1';
      }
      else {
         // Load the current external patches definition to retreive its version number
   	   $version = $this->getPatchesVersion();
   	}
	   
      $vars = array( 'option'       => 'com_docman',
                     'task'         => 'downloadkey',
                     'keyref'       => $option.'.checkupdates',
                     'version'      => $version,
                     'product_key'  => $reginfo['product_key']);
      
      // Convert the vars into a list of &key=value
   	$urlencoded = "";
   	while (list($key,$value) = each($vars))
   		$urlencoded.= urlencode($key) . "=" . urlencode($value) . "&";
   	$urlencoded = substr($urlencoded,0,-1);	

	   // Append the parameters to the URL
	   if ( !strstr( $url, '?')) {
	      $url .= '?' . $urlencoded;
	   }
	   else {
	      $url .= $urlencoded;
	   }

		// Download the patches from the URL given
		$p_file = JInstallerHelper::downloadPackage($url, 'jms2win_checkupdates.tar.gz');
		if ( $p_file === false) {
		   // Don't redirect to let the error message displayed
		   return false;
		}
		
		$config =& JFactory::getConfig();
		$tmp_dest 	= $config->getValue('config.tmp_path');
		$filename = $tmp_dest.DS.$p_file;

		//Check if the files downloaded is a TAR.GZ.
		// When a tag <html is present, this means this is not a TAR.GZ
		$content = file_get_contents( $filename);
		if ( stristr( $content, '<html')) {
   		return JText::_( 'PATCHES_UPDATE_DOWNLOAD_ERR');
		}
		
		// Deploy the patches
   	$extractdir = JPath::clean( JPATH_MUTLISITES_COMPONENT.'/patches');
		$result = JArchive::extract( $filename, $extractdir);
		if ( $result === false ) {
		   return JText::_( 'PATCHES_UPDATE_ERR');
		}
		return JText::_( 'PATCHES_UPDATE_SUCCESS');
   }

} // End class
