<?php
/**
 * @file       check_gantry.php
 * @brief      Check if gantry read the correct params.ini file of a slave site.
 * @version    1.2.54
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
 * - V1.2.54 14-FEB-2011: Initial version
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkGantry ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_ID') ...
 *   is present
 */
function jms2win_checkGantry( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[IGNORE]|File Not Found';
	}
	
   $str = file_get_contents( $filename);
   
   // if 'MULTISITES_ID' is present
   $pos = strpos( $str, 'MULTISITES_ID');
   if ($pos === false) $wrapperIsPresent = false;
   else                $wrapperIsPresent = true;
   
   $result = "";
   $rc = '[OK]';
   if ( !$wrapperIsPresent) {
	   $rc = '[NOK]';
      $result .= JText::_( 'The patch to read the slave site params.ini file is not present.');
      $result .= '|[ACTION]';
      $result .= '|Replace 1 line by 10 lines to compute the appropriate slave site params.ini.';
   }
   
   return $rc .'|'. $result;
}



//------------ jms2win_actionGantry ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionGantry( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'gantry' .DS. 'patch_gantry.php');
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
		........
		........
      if (is_readable( JPATH_SITE.DS."templates".DS.$template.DS.'params.ini' ) )
      {
		........
		........
		........
	   }
		?>
      
      ===========
      and Replace by:
      ===========
		........
		........
//_jms2win_begin v1.2.54
      if ( defined( 'MULTISITES_ID')) {
         $ini = JPATH_SITE.DS."templates".DS.$template.DS.'params_' . MULTISITES_ID . '.ini';
         if ( !file_exists(  $ini)) {
      	   $ini = JPATH_SITE.DS."templates".DS.$template.DS.'params.ini';
         }
		}
		else {
		   $ini = JPATH_SITE.DS."templates".DS.$template.DS.'params.ini';
      }
		if (is_readable( $ini) )
//_jms2win_end
      {
		........
		........
		........
	   }
		?>

   */
   
   // ------------- Patch definition ----------------
   /* ....\n
      \n .... if (is_readable( JPATH_SITE.DS."templates".DS.$template.DS.'params.ini' ) ) ...\n
      p0                                                                  p1                 p2
      

      Produce
      begin -> p0 + INSERT PATCH + + p2 -> end
      
    */
   
   // p1: Search for "params.ini"
   $p1 = strpos( $content, "'params.ini'");
   if ( $p1 === false) {
      return false;
   }
   
   // P0: Go to Begin of line
   for ( $p0 = $p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
   $p0++;

   // p2: Search for end of line
   for ( $p2=$p1; $content[$p2] != "\n"; $p2++);
 
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p0)
           . $patchStr
           . substr( $content, $p2)
           ;

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

   return true;
}

