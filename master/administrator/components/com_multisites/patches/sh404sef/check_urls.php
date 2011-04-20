<?php
/**
 * @file       check_urls.php
 * @brief      Check if the 'models/urls.php' files contains the Multi Sites patches 
 *             to allow using specific cache for each websites.
 *
 * @version    1.2.41
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
 * - V1.2.41  30-JUL-2010: Initial version
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkSH404URLS ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_HOST'))
 *   is present
 */
function jms2win_checkSH404URLS( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[IGNORE]|File Not Found';
	}
	
   $str = file_get_contents( $filename);

   // If the "shCacheContent.php" is moved into another file, then ignore this patch
   $pos = strpos( $str, '/shCacheContent.php');
   if ( $pos === false) {
	   return '[IGNORE]|File Not Found';
   }
   
   // if 'MULTISITES_ID' is present
   $pos = strpos( $str, 'MULTISITES_ID');
   if ($pos === false) $wrapperIsPresent = false;
   else                $wrapperIsPresent = true;
   
   $result = "";
   $rc = '[OK]';
   if ( !$wrapperIsPresent) {
	   $rc = '[NOK]';
      $result .= JText::_( 'The Multi Sites specific "shCacheContent.php" cache for each websites is not present.');
      $result .= '|[ACTION]';
      $result .= '|Add 6 lines to purge the specific "shCacheContent.php" cache for each slave site.';
   }
   
   return $rc .'|'. $result;
}


//------------ jms2win_actionSH404URLS ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionSH404URLS( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'sh404sef' .DS. 'patch_urls.php');
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
		public function deleteCache() {
		........
		........
       if (JFile::exists( JPATH_ROOT.'/components/com_sh404sef/cache/shCacheContent.php')) {
         JFile::delete( JPATH_ROOT.'/components/com_sh404sef/cache/shCacheContent.php');
       }
		........
		........
      
      ===========
      and Replace by:
      ===========
		........
		public function deleteCache() {
		........
		........
//_jms2win_begin v1.2.41
       if ( defined( 'MULTISITES_ID')) {
          if (JFile::exists( JPATH_ROOT.'/components/com_sh404sef/cache/shCacheContent.' .MULTISITES_ID. '.php')) {
            JFile::delete( JPATH_ROOT.'/components/com_sh404sef/cache/shCacheContent.' .MULTISITES_ID. '.php');
          }
       } else
//_jms2win_end
       if (JFile::exists( JPATH_ROOT.'/components/com_sh404sef/cache/shCacheContent.php')) {
         JFile::delete( JPATH_ROOT.'/components/com_sh404sef/cache/shCacheContent.php');
       }
		........
		........
   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n .... function deleteCache .....
              p1              
      
      \n ....shCacheContent.php ....... \n
      p2     p3
             
      
      Produce
      begin -> p2 + INSERT PATCH + p2 -> end
      
    */
   
   // p1: Search for "function deleteCache"
   $p1 = strpos( $content, 'function deleteCache');
   if ( $p1 === false) {
      return false;
   }

   // p3: Search for "shCacheContent.php"
   $p3 = strpos( $content, 'shCacheContent.php', $p1);
   if ( $p3 === false) {
      return false;
   }

   // P2: Go to Begin of line
   for ( $p2=$p3; $p2 > 0 && $content[$p2] != "\n"; $p2--);

   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p2+1)
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

