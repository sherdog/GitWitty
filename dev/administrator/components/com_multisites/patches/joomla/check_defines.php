<?php
/**
 * @file       check_defines.php
 * @brief      Check if the joomla core 'defines.php' files contains the Multi Sites patches.
 *
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
 * - V1.2.33 26-APR-2010: Replace the ereg by preg_match for PHP 5.3 compatibility
 * - V1.2.45 16-DEC-2010: Add the action performed when the file is not found.
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ _checkDefines ---------------
/**
 * check if following lines are present:
 * - require_once ( JPATH_BASE .DS.'includes'.DS.'defines_md.php' );
 * - if ( !defined( 'JPATH_CONFIGURATION'))  define( 'JPATH_CONFIGURATION', 	JPATH_ROOT );
 * - if ( !defined( 'JPATH_INSTALLATION'))   define( 'JPATH_INSTALLATION',	JPATH_ROOT.DS.'installation' );
 */
function jms2win_checkDefines( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[NOK]|File Not Found'
	         .'|[ACTION]|' . JText::_( 'Add the file');
	}
	
   $str = file_get_contents( $filename);
   
   // Check if multisites definition is present (formerly multi domain alias 'md');
   $multisites_found = false;
   if ( preg_match( '#defines_multisites.php#i', $str)
     || preg_match( '#defines_md.php#i', $str))
   {
      $multisites_found = true;
   }
   
   // if ( !defined( 'JPATH_CONFIGURATION'))  define( 'JPATH_CONFIGURATION', 	JPATH_ROOT );
   $ifdef_config  = preg_match( '#'
                        . 'if'
                        . '([[:space:]])*'
                        . '\('
                        . '([[:space:]])*'
                        . '!defined'
                        . '([[:space:]])*'
                        . '\('
                        . '([[:space:]])*'
                        . '\'JPATH_CONFIGURATION\''
                        . '([[:space:]])*'
                        . '\)'
                        . '([[:space:]])*'
                        . '\)'
                        . '#', 
                        $str);
                        
   // if ( !defined( 'JPATH_INSTALLATION'))   define( 'JPATH_INSTALLATION',	JPATH_ROOT.DS.'installation' );
   $ifdef_install = preg_match( '#'
                        . 'if'
                        . '([[:space:]])*'
                        . '\('
                        . '([[:space:]])*'
                        . '!defined'
                        . '([[:space:]])*'
                        . '\('
                        . '([[:space:]])*'
                        . '\'JPATH_INSTALLATION\''
                        . '([[:space:]])*'
                        . '\)'
                        . '([[:space:]])*'
                        . '\)'
                        . '#',
                        $str);
   
   $result = "";
   $rc = '[OK]';
   $sep = "";
   $addLine    = 0;
   $updateLine = 0;
   if ( !$multisites_found) {
	   $rc = '[NOK]';
      $result .= $sep . JText::_( 'PATCHES_MS_DEF_NOTFOUND');
      $sep = '|';
      $addLine++;
   }
   if ( !$ifdef_config) {
	   $rc = '[NOK]';
      $result .= $sep . JText::_( 'PATCHES_CONF_REDEF_ERR');
      $sep = '|';
      $updateLine++;
   }
   if ( !$ifdef_install) {
	   $rc = '[NOK]';
      $result .= $sep . JText::_( 'PATCHES_INST_REDEF_ERR');
      $sep = '|';
      $updateLine++;
   }
   
   // If there is some action to perform
   if ( $addLine!=0 || $updateLine != 0) {
      $result .= '|[ACTION]';
      if ( $addLine>0) {
         $result .= '|Add 1 line';
      }
      if ( $updateLine>0) {
         $result .= '|Update ' .$updateLine. ' line';
         if ( $updateLine>1) {
            $result .= 's';   // Line(s)
         }
      }
   }
   
   return $rc .'|'. $result;
}

//------------ _actionDefines ---------------
function jms2win_actionDefines( $model, $file)
{
   return $model->_deployPatches();
}
