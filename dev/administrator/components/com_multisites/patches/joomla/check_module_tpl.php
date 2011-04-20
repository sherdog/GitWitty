<?php
/**
 * @file       check_module_tpl.php
 * @brief      Check if the Module management contain the patch when the themes folder is specific
 *
 * @version    1.2.36
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
 * - V1.1.4 04-JAN-2009: File creation
 * - V1.2.36 01-JUN-2010: Add letter tree processing.
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkModule_Tpl ---------------
/**
 * check if 'MULTISITES_' is present
 */
function jms2win_checkModule_Tpl( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[NOK]|File Not Found';
	}
   $str = file_get_contents( $filename);
   
   // if 'MULTISITES_' is present
   $pos = strpos( $str, 'MULTISITES_');
   if ($pos === false) $wrapperIsPresent = false;
   else                $wrapperIsPresent = true;;
   
   $result = "";
   $rc = '[OK]';
   if ( !$wrapperIsPresent) {
	   $rc = '[NOK]';
      $result .= JText::_( 'The usage of specific themes folder is not present in the module management');
      $result .= '|[ACTION]';
      $result .= '|Add 17 lines to allow using a specific template directory when specified for a slave site';
   }
   // If a patch is present
   else {
      // Check if it contain the "letter tree" path result
      $pos = strpos( $str, 'MULTISITES_ID_PATH');
      if ($pos === false) {
   	   $rc = '[NOK]';
         $result .= JText::_( 'The usage of specific themes folder is not present in the module management');
         $result .= '|[ACTION]';
         $result .= '|Replace the previous patch <1.2.35 by a new one. This Add 18 lines to allow using a specific template directory when specified for a slave site';
      }
   }
   
   return $rc .'|'. $result;
}

//------------ jms2win_actionModule_Tpl ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionModule_Tpl( $model, $file)
{
   include_once( dirname(__FILE__) .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( 'patch_module_tpl.php');
   if ( $patchStr === false) {
      return false;
   }

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

		$client =& JApplicationHelper::getClientInfo($this->getState('clientId'));
		if ($client === false) {
			return false;
		}

      ===========
      and Replace by:
      ===========

		$client =& JApplicationHelper::getClientInfo($this->getState('clientId'));
//_jms2win_begin v1.2.36
      jimport( 'joomla.filesystem.path');
		// If there is a specific front-end template folder specified for a slave sites with specific folder.
		if ( $this->getState('clientId') == 0
		  && defined( 'MULTISITES_ID')) {
         if ( defined( 'MULTISITES_ID_PATH'))   { $filename = MULTISITES_ID_PATH.DIRECTORY_SEPARATOR.'config_multisites.php'; }
         else                                   { $filename = JPATH_MULTISITES.DS.MULTISITES_ID.DS.'config_multisites.php'; }
         @include($filename);
         if ( isset( $config_dirs) && !empty( $config_dirs) && !empty( $config_dirs['templates_dir'])) {
            $templates_dir = JPath::clean( $config_dirs['templates_dir']);
            $parts = explode( DS, $templates_dir );
            array_pop( $parts );
            $tmp          = $client;
            $client       = clone( $tmp);
            $client->path = implode( DS, $parts );
         }
		}
//_jms2win_end
		if ($client === false) {
			return false;
		}

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n      ...          'JApplicationHelper::getClientInfo' ....\n
      p0                   p1                                      p2

      \n      ...          'params.ini' ....\n
      p3                   p4               p5
      
      Produce
      begin -> p2 + INSERT PATCH + (p2+1) -> end
      
    */


   // P1: Search begin statement: JApplicationHelper::getClientInfo
   $p1 = strpos( $content, 'JApplicationHelper::getClientInfo');
   if ( $p1 === false) {
      return false;
   }
   // p2: Search for end of line
   for ( $p2=$p1; $content[$p2] != "\n"; $p2++);
   $p2++;
   
   
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p2)
           . $patchStr
           . substr( $content, $p2+1);

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}
   
   return true;
}
