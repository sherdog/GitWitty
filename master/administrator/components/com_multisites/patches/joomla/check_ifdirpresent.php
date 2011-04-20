<?php
/**
 * @file       check_ifdirpresent.php
 * @brief      Checks if a directory is present.
 *             Is used to test if the 'installation' directory is presnet.
 *
 * @version    1.2.7
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
 * - V1.2.7 30-AUH-2009: Add Joomla 1.6 compatibility
 *                       Check the root 'installation' directory path to restore the installation directory
 *                       This avoid to skip the restore in case where only the index.php file is present in the root
 *                       In that case, this give an alternate solution to also check "installation/includes" directory
 *                       for its restore.
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ _checkIfDirPresent ---------------
/**
 * check if a directory is present 
 * and check if an "index.php" file is present 
 * and check if the "includes" directory is present
 */
function jms2win_checkIfDirPresent( $model, $file)
{
	$dir = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !is_dir( $dir)) {
	   return '[NOK]'      . JText::_( 'PATCHES_DIR_NOTFOUND') 
	        . '|[ACTION]|' . JText::_( 'PATCHES_ACT_RENAME');
	}
	
	$filename = JPath::clean( JPATH_ROOT.DS.$file. '/index.php');
	if ( !file_exists( $filename)) {
	   return '[NOK]|'     . JText::_( 'PATCHES_MISSING_INDEX')
	        . '|[ACTION]|' . JText::_( 'PATCHES_RESTORE_DIR');
	}
	$filename = JPath::clean( JPATH_ROOT.DS.$file. '/includes');
	if ( !is_dir( $filename)) {
	   return '[NOK]|'     . JText::_( 'PATCHES_MISSING_INDEX')
	        . '|[ACTION]|' . JText::_( 'PATCHES_RESTORE_DIR');
	}
	return '[OK]|' . JText::_( 'PATCHES_DIR_PRESENT');
}

//------------ _actionIfDirPresent ---------------
/**
 * Recreate the 'installation' directory.
 */
function jms2win_actionIfDirPresent( $model, $dir)
{
   $parts = explode( '/', $dir );
   if ( $parts[0] == 'installation') {
      return $model->_restoreInstallation();
   }
   
	return true;
}
   
