<?php
/**
 * @file       check_config.php
 * @brief      Check if the All Video download script compute the "document root" correctly when Symbolic Link is used.
 * @version    1.2.49
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
 * - V1.2.49 12-NOV-2009: Initial version
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkACESEFCfgWrapper ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_ID') ...
 *   is present
 */
function jms2win_checkAllVideoDownload( $model, $file)
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
      $result .= JText::_( 'The computation of the slave site document root directory is not present.');
      $result .= '|[ACTION]';
      $result .= '|Add 1 line that compute the download directory path based on the slave deployed directory.';
   }
   
   return $rc .'|'. $result;
}



//------------ jms2win_actionAllVideoDownload ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionAllVideoDownload( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'jw_allvideos' .DS. 'patch_download.php');
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
      // Assign paths
      $sitePath = str_replace(DS.'plugins'.DS.'content'.DS.'jw_allvideos'.DS.'includes','',dirname(__FILE__));
      $siteUrl  = str_replace('/plugins/content/jw_allvideos/includes/','',JURI::root());
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
      // Assign paths
      $sitePath = str_replace(DS.'plugins'.DS.'content'.DS.'jw_allvideos'.DS.'includes','',dirname(__FILE__));
//_jms2win_begin v1.2.49
      // If this is a slave site and the path where the "configuration.php" is present then use this path as document root of the sitePath
      if ( defined( 'MULTISITES_ID') && defined( 'MULTISITES_CONFIG_PATH')) { $sitePath = MULTISITES_CONFIG_PATH; }
//_jms2win_end
      $siteUrl  = str_replace('/plugins/content/jw_allvideos/includes/','',JURI::root());
		........
		........
		........
		?>

   */
   
   // ------------- Patch definition ----------------
   /* ....\n
      \n .... $siteUrl ...
      p0      p1              
      

      Produce
      begin -> p0 + INSERT PATCH + p0 -> end
      
    */
   
   // p1: Search for "$siteUrl"
   $p1 = strpos( $content, '$siteUrl');
   if ( $p1 === false) {
      return false;
   }
   
   // P0: Go to Begin of line
   for ( $p0 = $p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
   $p0++;

 
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p0)
           . $patchStr
           . substr( $content, $p0)
           ;

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

   return true;
}

