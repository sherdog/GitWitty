<?php
/**
 * @file       check_jdatabase.php
 * @brief      Check if the table_prefix is protected or public.
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
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkJDatabase ---------------
/**
 * check if 'public _table_prefix' is present
 */
function jms2win_checkJDatabase( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[NOK]|File Not Found';
	}
   $str = file_get_contents( $filename);
   
   // if 'protected $_table_prefix' is present
   $p1 = strpos( $str, '$_table_prefix');
   if ( $p1 === false) {
      return false;
   }
   // P0: Go to Begin of line
   for ( $p0=$p1; $p0 > 0 && $str[$p0] != "\n"; $p0--);
   $p0++;

   // p2: Search for end of line
   for ( $p2=$p1; $str[$p2] != "\n"; $p2++);
   
   $line = trim( substr( $str, $p0, $p2-$p0));

   $pos = strpos( $line, 'public');
   if ($pos === false) $wrapperIsPresent = false;
   else                $wrapperIsPresent = true;;
   
   $result = "";
   $rc = '[OK]';
   if ( !$wrapperIsPresent) {
	   $rc = '[NOK]';
      $result .= JText::_( 'A protected information must be converted into public');
      $result .= '|[ACTION]';
      $result .= '|Update 1 line to make public some DB information';
   }
   
   return $rc .'|'. $result;
}

//------------ jms2win_actionJDatabase ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionJDatabase( $model, $file)
{
   include_once( dirname(__FILE__) .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( 'patch_jconfig.php');
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
   	protected $_table_prefix	= '';
      
      ===========
      and Replace by:
      ===========

   	public $_table_prefix	= '';

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n      protected   $_table_prefix	= '';
              p0          p1
      
      Produce
      begin -> p0 + INSERT PATCH + p2 -> end
      
    */
   // P1: Search begin statement: "JFile::write"
   $p1 = strpos( $content, '$_table_prefix');
   if ( $p1 === false) {
      return false;
   }
   // P0: Go to Begin of 'P'rotected word
   for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n" && $content[$p0] != 'p' ; $p0--);

   
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p0)
           . 'public '
           . substr( $content, $p1);

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}
   
   return true;
}
