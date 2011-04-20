<?php
/**
 * @file       check_admin.php
 * @brief      Check if the 'admin.sh404sef.php' files contains the Multi Sites patches 
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
 * - V1.2.26  26-JAN-2010: Initial version
 * - V1.2.41  30-JUL-2010: Discard this patch when "shCacheContent.php" is not present.
 *                         With sh404SEF version 2.0, the have moved the code into another source model/urls.php
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkSH404Admin ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_HOST'))
 *   is present
 */
function jms2win_checkSH404Admin( $model, $file)
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


//------------ jms2win_actionSH404Admin ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionSH404Admin( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'sh404sef' .DS. 'patch_admin.php');
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
		function purge($option, $ViewModeId=0  ) {
		........
		........
       if (file_exists(JPATH_ROOT.'/components/com_sh404sef/cache/shCacheContent.php')) {
         unlink(JPATH_ROOT.'/components/com_sh404sef/cache/shCacheContent.php');
       }
		........
		........
       if (file_exists(JPATH_ROOT.'/components/com_sh404sef/cache/shCacheContent.php')) {
         unlink(JPATH_ROOT.'/components/com_sh404sef/cache/shCacheContent.php');
       }
		........
		........
      
      ===========
      and Replace by:
      ===========
		........
		function purge($option, $ViewModeId=0  ) {
		........
		........
//_jms2win_begin v1.2.26
       if ( defined( 'MULTISITES_ID')) {
         if (file_exists(JPATH_ROOT.'/components/com_sh404sef/cache/shCacheContent.' .MULTISITES_ID. '.php')) {
            unlink(JPATH_ROOT.'/components/com_sh404sef/cache/shCacheContent.' .MULTISITES_ID. '.php');
         }
       } else
//_jms2win_end
       if (file_exists(JPATH_ROOT.'/components/com_sh404sef/cache/shCacheContent.php')) {
         unlink(JPATH_ROOT.'/components/com_sh404sef/cache/shCacheContent.php');
       }
		........
		........
//_jms2win_begin v1.2.26
       if ( defined( 'MULTISITES_ID')) {
         if (file_exists(JPATH_ROOT.'/components/com_sh404sef/cache/shCacheContent.' .MULTISITES_ID. '.php')) {
            unlink(JPATH_ROOT.'/components/com_sh404sef/cache/shCacheContent.' .MULTISITES_ID. '.php');
         }
       } else
//_jms2win_end
       if (file_exists(JPATH_ROOT.'/components/com_sh404sef/cache/shCacheContent.php')) {
         unlink(JPATH_ROOT.'/components/com_sh404sef/cache/shCacheContent.php');
       }
		........
		........
   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n .... function purge .....
              p1              
      
      \n ....shCacheContent.php ....... \n
      p2     p3
             
      \n ....file_exists ...\n
      p4    p5

      
      Produce
      begin -> p2 + INSERT PATCH + p2->p4 + INSERT PATCH + p4 -> end
      
    */
   
   // p1: Search for "function purge"
   $p1 = strpos( $content, 'function purge');
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

   // p5: Search for "file_exists"
   $p5 = strpos( $content, 'file_exists', $p3);
   if ( $p5 === false) {
      return false;
   }

   // P4: Go to Begin of line
   for ( $p4=$p5; $p4 > 0 && $content[$p4] != "\n"; $p4--);
 
 
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p2+1)
           . $patchStr
           . substr( $content, $p2, $p4-$p2+1)
           . $patchStr
           . substr( $content, $p4)
           ;

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

   return true;
}

