<?php
/**
 * @file       check_tpl_basedir.php
 * @brief      Check if the installer contain the patch to allow the installation of the templates into another "basedir" directory
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
 * - V1.1.3 18-DEC-2008: File creation
 * - V1.2.36 01-JUN-2010: Add letter tree structure processing.
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkTpl_basedir ---------------
/**
 * check if 'MULTISITES_' is present
 */
function jms2win_checkTpl_basedir( $model, $file)
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
      $result .= JText::_( 'The install "template" does not contain the patch that allow the installion of a template into a specific folder');
      $result .= '|[ACTION]';
      $result .= '|Add 35 lines to provide another "basedir" folder when a specific "themes" folder is specified';
   }
   
   return $rc .'|'. $result;
}

//------------ jms2win_actionTpl_basedir ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionTpl_basedir( $model, $file)
{
   include_once( dirname(__FILE__) .DS. 'patchloader.php');
   $patchStr_1 = jms2win_loadPatch( 'patch_tpl_basedir_1.php');
   if ( $patchStr_1 === false) {
      return false;
   }
   $patchStr_2 = jms2win_loadPatch( 'patch_tpl_basedir_2.php');
   if ( $patchStr_2 === false) {
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
	function _loadItems()
	....
			$templateDirs = JFolder::folders(JPATH_SITE.DS.'templates');
			...
			$template->baseDir
			...
		} else {
			$clientInfo =& JApplicationHelper::getClientInfo($this->_state->get('filter.client'));
			$client = $clientInfo->name;
			$templateDirs = JFolder::folders($clientInfo->path.DS.'templates');
			....
				$template->baseDir = $clientInfo->path.DS.'templates';
			
      
      ===========
      and Replace by:
      ===========

	function _loadItems()
	....
//_jms2win_begin v1.2.36
			$baseDir = JPATH_SITE.DS.'templates';
   		// If there is a specific template folder specified, give this one to the "template" installer.
   		if ( defined( 'MULTISITES_ID')) {
            if ( defined( 'MULTISITES_ID_PATH'))   { $filename = MULTISITES_ID_PATH.DIRECTORY_SEPARATOR.'config_multisites.php'; }
            else                                   { $filename = JPATH_MULTISITES.DS.MULTISITES_ID.DS.'config_multisites.php'; }
            @include($filename);
            if ( isset( $config_dirs) && !empty( $config_dirs) && !empty( $config_dirs['templates_dir'])) {
               $baseDir = JPath::clean( $config_dirs['templates_dir']);
            }
   		}
			$templateDirs = JFolder::folders( $baseDir);

			for ($i=0; $i < count($templateDirs); $i++) {
				$template = new stdClass();
				$template->folder = $templateDirs[$i];
				$template->client = 0;
				$template->baseDir = $baseDir;
//_jms2win_end
   ....
		} else {
			$clientInfo =& JApplicationHelper::getClientInfo($this->_state->get('filter.client'));
			$client = $clientInfo->name;
   
//_jms2win_begin v1.2.36
   		// If there is a specific template folder specified, give this one to the "template" installer.
   		if ( defined( 'MULTISITES_ID')) {
            if ( defined( 'MULTISITES_ID_PATH'))   { $filename = MULTISITES_ID_PATH.DIRECTORY_SEPARATOR.'config_multisites.php'; }
            else                                   { $filename = JPATH_MULTISITES.DS.MULTISITES_ID.DS.'config_multisites.php'; }
            @include($filename);
            if ( isset( $config_dirs) && !empty( $config_dirs) && !empty( $config_dirs['templates_dir'])) {
               $baseDir = JPath::clean( $config_dirs['templates_dir']);
            }
   		}
			$templateDirs = JFolder::folders( $baseDir);
//_jms2win_end

	
   */
   
   // ------------- Patch definition ----------------
   /* ....\n
      ----------- Patch no 1 -------
      \n      ...          JFolder::folders 
      p0                   p1                  

              ...          $template->baseDir ....\n
                           p2                     p3

      ----------- Patch no 2 -------
      \n      ...          getClientInfo ....\n
                           p4
      \n      ...          JFolder::folders
      p5                   p6              
              ...          $template->baseDir ....\n
                           p7                    p8
      
      Produce
      begin -> p0 + INSERT PATCH no 1 + p3 -> p5 + INSERT PATCH no 2 + p8 -> end
      
    */
   // P1: Search begin statement: JFolder::folders
   $p1 = strpos( $content, 'JFolder::folders');
   if ( $p1 === false) {
      return false;
   }
   // P0: Go to Begin of line
   for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
   

   // P2: Search begin statement: $template->baseDir
   $p2 = strpos( $content, '$template->baseDir',  $p1);
   if ( $p2 === false) {
      return false;
   }
   
   // p3: Search for end of line
   for ( $p3=$p2; $content[$p3] != "\n"; $p3++);

   // ------ Patch no 2 ---------
   // P4: Search begin statement: getClientInfo
   $p4 = strpos( $content, 'getClientInfo', $p3);

   // P1: Search begin statement: JFolder::folders
   $p6 = strpos( $content, 'JFolder::folders', $p4);
   if ( $p6 === false) {
      return false;
   }
   // P5: Go to Begin of line
   for ( $p5=$p6; $p5 > 0 && $content[$p5] != "\n"; $p5--);
   

   // P2: Search begin statement: $template->baseDir
   $p7 = strpos( $content, '$template->baseDir',  $p6);
   if ( $p7 === false) {
      return false;
   }
   
   // p3: Search for end of line
   for ( $p8=$p7; $content[$p8] != "\n"; $p8++);

   
   // ------------- Compute the results relatif to param.ini ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p0)
           . $patchStr_1
           . substr( $content, $p3+1, $p5-$p3)
           . $patchStr_2
           . substr( $content, $p8+1);

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}
   
   return true;
}
