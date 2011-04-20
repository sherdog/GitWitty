<?php
/**
 * @file       check_legacy15getinstance.php
 * @brief      Check if the GetInstance function return a reference or not.
 *
 * @version    1.2.53
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
 * - V1.2.53 02-FEB-2011: File creation
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );


//------------ jms2win_checkLegacy15GetInstance ---------------
/**
 * check if is the public static function getInstance()
 * return a reference (&) or a copy
 */
function jms2win_checkLegacy15GetInstance( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[NOK]|File Not Found';
	}
   $str = file_get_contents( $filename);
   
   // if legacy 15 'function &getInstance()' is present
   $pos = strpos( $str, 'function &getInstance()');
   if ($pos === false) $legacyIsPresent = false;
   else {
      $legacyIsPresent = true;
   }
   
   $result = "";
   $rc = '[OK]';
   if ( !$legacyIsPresent) {
	   $rc = '[NOK]';

      $result .= JText::_( 'Install the Joomla 1.5 legacy API for the getinstance() to return a reference.');
      $result .= '|[ACTION]';
      $result .= '|Replace "function getInstance()" by "function &getInstance()"';
      $result .= '|This allow get the reference of the instance created instead of a copy.';
   }
   
   return $rc .'|'. $result;
}

//------------ jms2win_actionAdminIndex ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionLegacy15GetInstance( $model, $file)
{
//	$filename = JPATH_ROOT.DS.$file;
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
   $content = file_get_contents( $filename);
   if ( $content === false) {
      return false;
   }

   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = str_replace( 'function getInstance()', 'function &getInstance()', $content);

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}
   
   return true;
}
