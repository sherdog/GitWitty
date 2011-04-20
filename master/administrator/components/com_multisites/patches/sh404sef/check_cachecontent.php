<?php
/**
 * @file       check_cachecontent.php
 * @brief      Check if the 'shCache.php' files contains the Multi Sites patches 
 *             to allow using specific cache for each websites.
 *
 * @version    1.2.26
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2010 Edwin2Win sprlu - all right reserved.
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
 * - V1.2.26  26-JAN-2010: Initial version
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkSH404CacheContent ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_HOST'))
 *   is present
 */
function jms2win_checkSH404CacheContent( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[IGNORE]|File Not Found';
	}
	
   $str = file_get_contents( $filename);
   
   // if 'MULTISITES_ID' is present
   $pos = strpos( $str, 'MULTISITES_ID');
   if ($pos === false) {
      $pos = strpos( $str, 'shCacheContent.php');
      if ($pos === false) {
   	   return '[IGNORE]|File Not Found';
      }
      $wrapperIsPresent = false;
   }
   else {
      $wrapperIsPresent = true;
   }
   
   $result = "";
   $rc = '[OK]';
   if ( !$wrapperIsPresent) {
	   $rc = '[NOK]';
      $result .= JText::_( 'The Multi Sites specific "shCacheContent.php" cache for each websites is not present.');
      $result .= '|[ACTION]';
      $result .= '|Add 2x3 lines to add a Multisites ID suffix when creating the cache for each slave site. ("shCacheContent.&lt;multisites id&gt;.php") ';
   }
   
   return $rc .'|'. $result;
}


//------------ jms2win_actionSH404CacheContent ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionSH404CacheContent( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr_1 = jms2win_loadPatch( '..' .DS. 'sh404sef' .DS. 'patch_cachecontent_1.php');
   if ( $patchStr_1 === false) {
      return false;
   }
   $patchStr_2 = jms2win_loadPatch( '..' .DS. 'sh404sef' .DS. 'patch_cachecontent_2.php');
   if ( $patchStr_2 === false) {
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
      $shURLCacheFileName = sh404SEF_FRONT_ABS_PATH.'cache/shCacheContent.php';
		........
		........
      $cacheFile = sh404SEF_FRONT_ABS_PATH.'cache/shCacheContent.php';
		........
		........
      
      ===========
      and Replace by:
      ===========
		........
		........
      $shURLCacheFileName = sh404SEF_FRONT_ABS_PATH.'cache/shCacheContent.php';
		........
		........
      $cacheFile = sh404SEF_FRONT_ABS_PATH.'cache/shCacheContent.php';
		........
		........
		........
   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n .... $shURLCacheFileName = sh404SEF_FRONT_ABS_PATH.'cache/shCacheContent.php'; ....\n
      p0                                                           p1                       p2
      
      \n ....$cacheFile = sh404SEF_FRONT_ABS_PATH.'cache/shCacheContent.php'; .....\n
      p3                                                 p4                        p5
             
      
      Produce
      begin -> p0 + INSERT PATCH no 1 + p0->p2 + INSERT PATCH no2 + p2 -> end
      
    */
   
   // p1: Search for "shCacheContent.php"
   $p1 = strpos( $content, 'shCacheContent.php');
   if ( $p1 === false) {
      return false;
   }
   // P0: Go to Begin of line
   for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);

   // p2: Search for "\n"
   $p2 = strpos( $content, "\n", $p1);
   if ( $p2 === false) {
      return false;
   }
   $p2++;

   // p4: Search for "shCacheContent.php"
   $p4 = strpos( $content, 'shCacheContent.php', $p2);
   if ( $p4 === false) {
      return false;
   }

   // P3: Go to Begin of line
   for ( $p3=$p4; $p3 > 0 && $content[$p3] != "\n"; $p3--);

   // p5: Search for "\n"
   $p5 = strpos( $content, "\n", $p4);
   if ( $p5 === false) {
      return false;
   }
   $p5++;
 
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p0+1)
           . $patchStr_1
           . substr( $content, $p2, $p3-$p2+1)
           . $patchStr_2
           . substr( $content, $p5)
           ;

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

   return true;
}

