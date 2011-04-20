<?php
/**
 * Installer File
 * Performs an install / update of NoNumber! extensions
 *
 * @package     NoNumber!-installer
 * @version     2.2.2
 *
 * @author      Peter van Westen <peter@nonumber.nl>
 * @link        http://www.nonumber.nl
 * @copyright   Copyright © 2011 NoNumber! All Rights Reserved
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$mainframe =& JFactory::getApplication();
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.file' );

// Load language for messaging
$lang =& JFactory::getLanguage();
$lang->load( 'com_nonumber-installer-uninstallme' );
// Load english language for backup
$lang->_load( JPATH_ADMINISTRATOR.DS.'language'.DS.'en-GB'.DS.'en-GB.com_nonumber-installer-uninstallme.ini', 'com_nonumber-installer-uninstallme', 0 );

$install_file = dirname( __FILE__ ).DS.'extensions.php';
if ( !is_readable ( $install_file ) ) {
	$mainframe->enqueueMessage( sprintf( JText::_( 'NNI_CANNOT_READ_THE_REQUIRED_INSTALLATION_FILE' ), $install_file ), 'error' );
} else {
	// Create database object
	$db =& JFactory::getDBO();

	$ext = 'NNI_THE_EXTENSION';
	$states = array();
	$has_installed = 0;
	$has_updated = 0;

	$noforce_files = array();

	require_once $install_file;

	if ( is_array( $states ) ) {
		foreach ( $states as $state ) {
			if ( !$state ) {
				$has_installed = $has_updated = 0;
				break;
			} else if ( $state == 2 ) {
				$has_updated = 1;
			} else {
				$has_installed = 1;
			}
		}
	}
	if ( !$has_installed && !$has_updated ) {
		$mainframe->enqueueMessage( JText::_( 'NNI_SOMETHING_HAS_GONE_WRONG_DURING_INSTALLATION_OF_THE_DATABASE_RECORDS' ), 'error' );
	} else {
		$comp_folder = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_nonumber-installer-uninstallme';

		if ( !JFolder::exists( $comp_folder.DS.'files' ) ) {
			$mainframe->enqueueMessage( sprintf( JText::_( 'NNI_CANNOT_FIND_THE_REQUIRED_FILES_FOLDER' ), 'files' ), 'message' );
		} else if ( !JFolder::exists( $comp_folder.DS.'files'.DS.'forced' ) ) {
			$mainframe->enqueueMessage( sprintf( JText::_( 'NNI_CANNOT_FIND_THE_REQUIRED_FILES_FOLDER' ), 'files/forced' ), 'message' );
		} else {
			$succes = 1;
			if ( JFolder::exists( $comp_folder.DS.'files'.DS.'not_forced' ) && !copy_from_folder( $comp_folder.DS.'files'.DS.'not_forced', 0 ) ) {
				$succes = 0;
			}
			if ( !copy_from_folder( $comp_folder.DS.'files'.DS.'forced', 1 ) ) {
				$succes = 0;
			}

			if ( $succes ) {
				$txt_installed = ( $has_installed ) ? JText::_( 'NNI_INSTALLED' ) : '';
				$txt_installed .= ( $has_installed && $has_updated ) ? ' / ' : '';
				$txt_installed .= ( $has_updated ) ? JText::_( 'NNI_UPDATED' ) : '';
				$mainframe->enqueueMessage( sprintf( JText::_( 'NNI_THE_EXTENSION_HAS_BEEN_INSTALLED_SUCCESSFULLY' ), JText::_( $ext ), $txt_installed ), 'message' );
				$mainframe->enqueueMessage( JText::_( 'NNI_PLEASE_CLEAR_YOUR_BROWSERS_CACHE' ), 'notice' );
				installElements();
			} else {
				$mainframe->enqueueMessage( JText::_( 'NNI_COULD_NOT_COPY_ALL_FILES' ), 'error error_nonumber' );
			}
		}
	}
}

// uninstall the installer
uninstallInstaller();

// Redirect with message
$mainframe->redirect( 'index.php?option=com_installer' );

/**
 * Copies all files from install folder
 */
function copy_from_folder( $folder, $force = 0 )
{
	if ( is_dir ( $folder ) ) {
		// Copy files
		$folders = JFolder::folders( $folder );

		$succes = 1;

		foreach ( $folders as $subfolder ) {
			if ( !folder_copy( $folder.DS.$subfolder, JPATH_SITE.DS.$subfolder, $force ) ) {
				$succes = 0;
			}
		}

		return $succes;
	}
}

/**
 * Copy a folder
 */
function folder_copy( $src, $dest, $force = 0 )
{
	$mainframe =& JFactory::getApplication();

	// Initialize variables
	jimport( 'joomla.client.helper' );
	$ftpOptions = JClientHelper::getCredentials( 'ftp' );

	// Eliminate trailing directory separators, if any
	$src = rtrim( $src, DS );
	$dest = rtrim( $dest, DS );

	if ( !JFolder::exists( $src ) ) {
		return 0;
	}

	$succes = 1;

	// Make sure the destination exists
	if ( !JFolder::exists( $dest ) && !folder_create( $dest ) ) {
		$folder = str_replace( JPATH_ROOT, '', $dest );
		$mainframe->enqueueMessage( JText::_( 'NNI_FAILED_TO_CREATE_DIRECTORY' ).': '.$folder, 'error error_folders' );
		$succes = 0;
	}

	if ( !( $dh = @opendir( $src ) ) ) {
		return 0;
	}

	$folders = array();
	$files = array();
	while ( ( $file = readdir( $dh ) ) !== false ) {
		if ( $file != '.' && $file != '..' ) {
			$file_src = $src.DS.$file;
			switch ( filetype( $file_src ) ) {
				case 'dir':
					$folders[] = $file;
					break;
				case 'file':
					$files[] = $file;
					break;
			}
		}
	}
	sort( $folders );
	sort( $files );

	$curr_folder = array_pop( explode( DS, $src ) );
	// Walk through the directory recursing into folders
	foreach ( $folders as $folder ) {
		$folder_src = $src.DS.$folder;
		$folder_dest = $dest.DS.$folder;
		if ( !( $curr_folder == 'language' && !JFolder::exists( $folder_dest ) ) ) {
			if ( !folder_copy( $folder_src, $folder_dest, $force ) ) {
				$succes = 0;
			}
		}
	}

	if ( $ftpOptions['enabled'] == 1 ) {
		// Connect the FTP client
		jimport( 'joomla.client.ftp' );
		$ftp =& JFTP::getInstance(
			$ftpOptions['host'], $ftpOptions['port'], null,
			$ftpOptions['user'], $ftpOptions['pass']
		);

		// Walk through the directory copying files
		foreach ( $files as $file ) {
			$file_src = $src.DS.$file;
			$file_dest = $dest.DS.$file;
			// Translate path for the FTP account
			$file_dest = JPath::clean( str_replace( JPATH_ROOT, $ftpOptions['root'], $file_dest ), '/' );
			if ( $force || !JFile::exists( $file_dest ) ) {
				if ( ! $ftp->store( $file_src, $file_dest ) ) {
					$file_path = str_replace( $ftpOptions['root'], '', $file_dest );
					$mainframe->enqueueMessage( JText::_( 'NNI_ERROR_SAVING_FILE' ).': '.$file_path, 'error error_files' );
					$succes = 0;
				}
			}
		}
	} else {
		foreach ( $files as $file ) {
			$file_src = $src.DS.$file;
			$file_dest = $dest.DS.$file;
			if ( $force || !JFile::exists( $file_dest ) ) {
				if ( !@copy( $file_src, $file_dest ) ) {
					$file_path = str_replace( JPATH_ROOT, '', $file_dest );
					$mainframe->enqueueMessage( JText::_( 'NNI_ERROR_SAVING_FILE' ).': '.$file_path, 'error error_files' );
					$succes = 0;
				}
			}
		}
	}

	return $succes;
}

/**
 * Create a folder
 */
function folder_create( $path = '', $mode = 0755 )
{
	// Initialize variables
	jimport( 'joomla.client.helper' );
	$ftpOptions = JClientHelper::getCredentials( 'ftp' );

	// Check to make sure the path valid and clean
	$path = JPath::clean( $path );

	// Check if dir already exists
	if ( JFolder::exists( $path ) ) {
		return true;
	}

	// Check for safe mode
	if ( $ftpOptions['enabled'] == 1 ) {
		// Connect the FTP client
		jimport( 'joomla.client.ftp' );
		$ftp =& JFTP::getInstance(
			$ftpOptions['host'], $ftpOptions['port'], null,
			$ftpOptions['user'], $ftpOptions['pass']
		);

		// Translate path to FTP path
		$path = JPath::clean( str_replace( JPATH_ROOT, $ftpOptions['root'], $path ), '/' );
		$ret = $ftp->mkdir( $path );
		$ftp->chmod( $path, $mode );
	} else {
		// We need to get and explode the open_basedir paths
		$obd = ini_get( 'open_basedir' );

		// If open_basedir is set we need to get the open_basedir that the path is in
		if ( $obd != null )
		{
			if ( JPATH_ISWIN ) {
				$obdSeparator = ";";
			} else {
				$obdSeparator = ":";
			}
			// Create the array of open_basedir paths
			$obdArray = explode( $obdSeparator, $obd );
			$inBaseDir = false;
			// Iterate through open_basedir paths looking for a match
			foreach ( $obdArray as $test ) {
				$test = JPath::clean( $test );
				if ( strpos( $path, $test ) === 0 ) {
					$inBaseDir = true;
					break;
				}
			}
			if ( $inBaseDir == false ) {
				// Return false for JFolder::create because the path to be created is not in open_basedir
				JError::raiseWarning(
					'SOME_ERROR_CODE',
					'JFolder::create: '.JText::_( 'NNI_PATH_NOT_IN_OPEN_BASEDIR_PATHS' )
				);
				return false;
			}
		}

		// First set umask
		$origmask = @umask(0);

		// Create the path
		if ( !$ret = @mkdir( $path, $mode ) ) {
			@umask( $origmask );
			return false;
		}

		// Reset umask
		@umask( $origmask );
	}

	return $ret;
}

function uninstallInstaller( $name = 'nonumber-installer-uninstallme' )
{
	// Create database object
	$db =& JFactory::getDBO();

	$installer =& JInstaller::getInstance();
	$query = 'SELECT id FROM `#__components`'
		.' WHERE `option` = '.$db->Quote( 'com_'.$name )
		.' AND parent = 0'
		.' LIMIT 1'
		;
	$db->setQuery( $query );
	$id = $db->loadResult();
	$installer->uninstall( 'component', $id );
	$query = 'ALTER TABLE `#__components`'
		.' AUTO_INCREMENT = 1'
		;
	$db->setQuery( $query );
	$db->query();

	// Delete old language files
	$files = JFolder::files( JPATH_SITE.DS.'language', 'com_nonumber-installer-uninstallme.ini' );
	foreach ( $files as $file ) {
		JFile::delete( JPATH_SITE.DS.'language'.DS.$file );
	}
}

function installExtension( $name, $title, $type = 'component', $extra = array() )
{
	$mainframe =& JFactory::getApplication();

	// Create database object
	$db =& JFactory::getDBO();

	$installed = 0;

	if ( function_exists( 'beforeInstall' ) ) {
		beforeInstall( $db );
	}

	switch ( $type )
	{
		case 'component':
			$query = 'SELECT id FROM `#__components`'
				.' WHERE `option` = '.$db->Quote( 'com_'.$name )
				.' LIMIT 1'
				;
			$db->setQuery( $query );
			$installed = $db->loadResult();

			if ( !$installed ) {
				$query = 'ALTER TABLE `#__components`'
					.' AUTO_INCREMENT = 1'
					;
				$db->setQuery( $query );
				$db->query();

				$row =& JTable::getInstance( 'component' );
				$row->name = $title;
				$row->admin_menu_alt = $title;
				$row->option = 'com_'.$name;
				$row->link = 'option=com_'.$name;
				$row->admin_menu_link = 'option=com_'.$name;
				foreach ( $extra as $key => $val ) {
					$row->$key = $val;
				}

				if ( !$row->store() ) {
					$mainframe->enqueueMessage( $row->getError(), 'error' );
					return;
				}
			}

			break;

		case 'plugin':
			$folder = $extra['folder'];
			$query = 'SELECT id FROM `#__plugins`'
				.' WHERE `element` = '.$db->Quote( $name )
				.' AND `folder` = '.$db->Quote( $folder )
				.' LIMIT 1'
				;
			$db->setQuery( $query );
			$installed = $db->loadResult();

			if ( !$installed ) {
				$query = 'ALTER TABLE `#__plugins`'
					.' AUTO_INCREMENT = 1'
					;
				$db->setQuery( $query );
				$db->query();

				$row =& JTable::getInstance( 'plugin' );
				$row->name = $title;
				$row->element = $name;
				$row->published = 1;
				foreach ( $extra as $key => $val ) {
					$row->$key = $val;
				}

				if ( !$row->store() ) {
					$mainframe->enqueueMessage( $row->getError(), 'error' );
					return;
				}
			}

			break;

		case 'module':
			$query = 'SELECT id FROM `#__modules`'
				.' WHERE `module` = '.$db->Quote( 'mod_'.$name )
				.' LIMIT 1'
				;
			$db->setQuery( $query );
			$installed = $db->loadResult();

			if ( !$installed ) {
				$query = 'ALTER TABLE `#__modules`'
					.' AUTO_INCREMENT = 1'
					;
				$db->setQuery( $query );
				$db->query();

				$row =& JTable::getInstance( 'module' );
				$row->title = $title;
				$row->module = 'mod_'.$name;
				$row->ordering = $row->getNextOrder( "position='left'" );
				$row->position = 'left';
				$row->showtitle = 1;
				foreach ( $extra as $key => $val ) {
					$row->$key = $val;
				}

				if ( !$row->store() ) {
					$mainframe->enqueueMessage( $row->getError(), 'error' );
					return;
				}

				// Clean up possible garbage first
				$query = 'DELETE FROM #__modules_menu WHERE moduleid = '.( int ) $row->id;
				$db->setQuery( $query );
				$db->query();

				// Time to create a menu entry for the module
				$query = 'INSERT INTO `#__modules_menu` VALUES ( '.( int ) $row->id.', 0 )';
				$db->setQuery( $query );
				$db->query();
			}

			break;
	}

	if ( function_exists( 'afterInstall' ) ) {
		afterInstall( $db );
	}

	$cookieName = JUtility::getHash( 'version_'.$name.'_version' );
	setcookie( $cookieName, '', 0 );

	return ( $installed ) ? 2 : 1;
}

function installElements()
{
	$install_folder = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_nonumber-installer-uninstallme'.DS.'files'.DS.'elements';
	$xml_file = $install_folder.DS.'plugins'.DS.'system'.DS.'nonumberelements.xml';
	if ( !JFile::exists( $xml_file) ) {
		return;
	}
	$xml_new = JApplicationHelper::parseXMLInstallFile( $install_folder.DS.'plugins'.DS.'system'.DS.'nonumberelements.xml' );

	$do_install = 1;
	if ( $xml_new && isset( $xml_new['version'] ) ) {
		$do_install = 1;
		$xml_file = JPATH_SITE.DS.'plugins'.DS.'system'.DS.'nonumberelements.xml';
		if ( JFile::exists( $xml_file) ) {
			$xml_current = JApplicationHelper::parseXMLInstallFile( $xml_file );
			$installed = ( $xml_current && isset( $xml_current['version'] ) );
			if ( $installed ) {
				$current_version = $xml_current['version'];
				$new_version = $xml_new['version'];
				$do_install = version_compare( $current_version, $new_version, '<=' );
			}
		}
	}

	$succes = 1;
	if ( $do_install ) {
		if ( !copy_from_folder( $install_folder, 1 ) ) {
			$mainframe =& JFactory::getApplication();
			$mainframe->enqueueMessage( 'Could not install the NoNumber Elements extension', 'error' );
			$mainframe->enqueueMessage( 'Could not copy all files', 'error' );
			$succes = 0;
		}
	}

	if ( $succes ) {
		installExtension( 'nonumberelements', 'System - NoNumber! Elements', 'plugin', array( 'folder'=>'system' ) );
	}
}