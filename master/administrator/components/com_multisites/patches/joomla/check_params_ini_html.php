<?php
/**
 * @file       check_params_ini_tpl.php
 * @brief      Check if the Administrator Template contain the patch to customise the params.ini file with the MultiSites_ID
 *
 * @version    1.0.9
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
 * @par History:
 * - V1.0.9 06-OCT-2008: File creation
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkParams_ini_html ---------------
/**
 * check if 'MULTISITES_' is present
 */
function jms2win_checkParams_ini_html( $model, $file)
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
      $result .= JText::_( 'The slave site specific "params_[id].ini" file reading is not present');
      $result .= '|[ACTION]';
      $result .= '|Replace 3 lines by  to allow using a specific "params_[id].ini" when rendering concern a slave site';
   }
   
   return $rc .'|'. $result;
}

//------------ jms2win_actionParams_ini_html ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionParams_ini_html( $model, $file)
{
   include_once( dirname(__FILE__) .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( 'patch_params_ini_html.php');
   if ( $patchStr === false) {
      return false;
   }

//	$filename = JPATH_ROOT.DS.$file;
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
   $content = file_get_contents( $filename);
   if ( $content === false) {
      return false;
   }
   
   // Search/Replace the statement
   /*
      ===========
      Search for:
      ===========
		if (is_readable( $directory.DS.$template.DS.'params.ini' ) )
		{
			$content = file_get_contents($directory.DS.$template.DS.'params.ini');
			$params = new JParameter($content);
		}
      
      ===========
      and Replace by:
      ===========
		if ( defined( 'MULTISITES_ID')) {
		   $ini = $directory.DS.$template.DS.'params_' . MULTISITES_ID . '.ini';
		   if ( !file_exists(  $ini)) {
   		   $ini = $directory.DS.$template.DS.'params.ini';
		   }
		}
		else {
		   $ini = $directory.DS.$template.DS.'params.ini';
		}
		if (is_readable( $ini) )
		{
			$content = file_get_contents($ini);
			$params = new JParameter($content);
		}

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n  ............    'params.ini' .......'params.ini'...\n
      p0                   p1                 p2             p3
      
      Produce
      begin -> p0 + INSERT PATCH + p3 -> end
      
    */
   // P1: Search begin statement: "<?php $templatefile"
   $p1 = strpos( $content, '\'params.ini\'');
   if ( $p1 === false) {
      return false;
   }
   // P0: Go to Begin of line
   for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
   $p0++;

   $p2 = strpos( $content, '\'params.ini\'', $p1+10);
   if ( $p2 === false) {
      return false;
   }
   
   // p2: Search for end of line
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
