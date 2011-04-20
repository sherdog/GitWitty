<?php
/**
 * @file       check_virtuemart_cfg.php
 * @brief      Check if the VirtueMart configuration wrapper is present.
 * @version    1.2.14
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
 * - V1.0.11 25-OCT-2008: Initial version
 * - V1.2.14 20-OCT-2009: Add compatibility with VM 1.1.4
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkVMPlgUpdStatus ---------------
/**
 * check if following lines are present:
 * - JPluginHelper::importPlugin('virtuemart');
 *   is present
 */
function jms2win_checkVMPlgUpdStatus( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[IGNORE]|File Not Found';
	}
	
   $str = file_get_contents( $filename);
   
   // if 'JPluginHelper::importPlugin' is present
   $pos = strpos( $str, 'JPluginHelper::importPlugin');
   if ($pos === false) $wrapperIsPresent = false;
   else                $wrapperIsPresent = true;
   
   $result = "";
   $rc = '[OK]';
   if ( !$wrapperIsPresent) {
	   $rc = '[NOK]';
      $result .= JText::_( 'The code to call the Plugin VirtueMart onOrderStatusUpdate is not present.');
      $result .= '|[ACTION]';
      $result .= '|Add 3 lines containing the call to a VirtueMart plugin onOrderStatusUpdate.';
   }
   
   return $rc .'|'. $result;
}



//------------ jms2win_actionVMPlgUpdStatus ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionVMPlgUpdStatus( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'virtuemart' .DS. 'patch_ps_order.php');
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
		........
		if (ENABLE_DOWNLOADS == '1') {
			##################
			## DOWNLOAD MOD
			$this->mail_download_id( $d );
		}
		........
		........
      
      ===========
      and Replace by:
      ===========
		........
		........
		........
JPluginHelper::importPlugin('virtuemart');
$dispatcher	=& JDispatcher::getInstance();
$results = $dispatcher->trigger('onOrderStatusUpdate', array ( & $d));

		if (ENABLE_DOWNLOADS == '1') {
			##################
			## DOWNLOAD MOD
			$this->mail_download_id( $d );
		}
		........
		........

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n .... if (ENABLE_DOWNLOADS == '1') {
      p0          p1                  p2 p3
      
      Produce
      begin -> p0 + INSERT PATCH + p0 -> end
      
    */
   
   // Search begin statement: if (ENABLE_DOWNLOADS == '1') { ...
   // In VM 1.1.4, If there is another if( ENABLE_DOWNLOADS == '1' && ps_product::is_downloadable($db->f("product_id")) && VM_DOWNLOADABLE_PRODUCTS_KEEP_STOCKLEVEL == '1') {
   // that must be skipped. (So check that after ENABLE_DOWNLOADS == '1', there is nothing (empty string).
   $prev_pos = 0;
   while( true) {
      // p1: Search for global ENABLE_DOWNLOADS
      $p1 = strpos( $content, 'ENABLE_DOWNLOADS', $prev_pos);
      if ( $p1 === false) {
         return false;
      }
      // Go to '1'
      $p2 = strpos( $content, "'1'", $p1);
      if ( $p2 === false) {
         return false;
      }
      // Check that after '1' there is nothing until the ')'
      $p3 = strpos( $content, ")", $p2);
      if ( $p3 === false) {
         return false;
      }
      $str = trim( substr( $content, $p2+3, $p3-($p2+3)));
      if ( !empty( $str)) {
         $prev_pos = $p3;
      }
      else {
         // If empty, then we assume we have the if (ENABLE_DOWNLOADS == '1')
         break;
      }
   }

   // P0: Go to Begin of line
   for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
   $p0++;

 
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p0)
           . $patchStr
           . substr( $content, $p0);

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

   return true;
}

