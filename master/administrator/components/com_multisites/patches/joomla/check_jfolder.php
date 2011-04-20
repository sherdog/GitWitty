<?php
/**
 * @file       check_jfolder.php
 * @brief      Check if JFolder bug fix on Symbolic Link folder delete is present.
 *             The bug fix consists in delete of the symbolic link on a folder instead of its contain.
 * @version    1.1.0
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
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkJFolder ---------------
/**
 * check if function is_link() is present
 */
function jms2win_checkJFolder( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[NOK]|File Not Found';
	}
   $str = file_get_contents( $filename);
   
   // if 'is_link' is present
   $pos = strpos( $str, 'is_link');
   if ($pos === false) $wrapperIsPresent = false;
   else                $wrapperIsPresent = true;;
   
   $result = "";
   $rc = '[OK]';
   if ( !$wrapperIsPresent) {
	   $rc = '[NOK]';
      $result .= JText::_( 'The bug fix in core Joomla to avoid deleting of the symbolic link folders content is not present.');
      $result .= '|[ACTION]';
      $result .= '|Replace 1 line by 10 lines in aim to check if a folder to delete is in fact a symbolic link or a real folder.';
      $result .= '|In the case of a symbolic link, delete only the link and not the content of the physical folder.';
   }
   
   return $rc .'|'. $result;
}

//------------ jms2win_actionJFolder ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionJFolder( $model, $file)
{
   include_once( dirname(__FILE__) .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( 'patch_jfolder.php');
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
      function delete($path)
      ....
      
		// Remove sub-folders of folder
		$folders = JFolder::folders($path, '.', false, true, array());
		foreach ($folders as $folder) {
			if (JFolder::delete($folder) !== true) {
				// JFolder::delete throws an error
				return false;
			}
		}
      
      ===========
      and Replace by:
      ===========

		// Remove sub-folders of folder
		$folders = JFolder::folders($path, '.', false, true, array());
		foreach ($folders as $folder) {
		   // If in fact the folder is a link to a folder
		   if ( is_link( $folder)) {
		      // Delete the link (not the folder content).
   			jimport('joomla.filesystem.file');
   			if (JFile::delete( $folder) !== true) {
   				// JFile::delete throws an error
   				return false;
   			}
		   }
		   else if (JFolder::delete($folder) !== true) {
				// JFolder::delete throws an error
				return false;
			}
		}

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      function delete ...  foreach ($folders ...\n ...if .. JFolder::delete ... {
      p0                   p1                   p2          p3                  p4
      
      Produce
      begin -> p2 + INSERT PATCH + p4+1 -> end
      
    */
   // P0: Search for : "function delete"
   $p0 = strpos( $content, 'function delete');
   if ( $p0 === false) {
      return false;
   }
   
   // p1: Search for "foreach ($folders"
   $p1 = strpos( $content, 'foreach ($folders', $p0);
   if ( $p1 === false) {
      return false;
   }

   // p3: Search for "JFolder::delete"
   $p3 = strpos( $content, 'JFolder::delete', $p1);
   if ( $p3 === false) {
      return false;
   }


   // P2: Go to Begin of line
   for ( $p2=$p3; $p2 > 0 && $content[$p2] != "\n"; $p2--);
   $p2++;


   // p4: Search for "{"
   $p4 = strpos( $content, '{', $p3);
   if ( $p4 === false) {
      return false;
   }
   
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p2)
           . $patchStr
           . substr( $content, $p4+1);

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}
   
   return true;
}
