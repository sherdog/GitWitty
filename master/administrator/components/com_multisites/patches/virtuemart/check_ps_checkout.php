<?php
/**
 * @file       check_ps_checkout.php
 * @brief      Call multisite plugin after the order is created
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
 * @par History:
 * - V1.1.0 4-NOV-2008: Initial version
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkVMPlgAfterOrder ---------------
/**
 * check if following lines are present:
 * - JPluginHelper::importPlugin('multisites');
 *   is present
 */
function jms2win_checkVMPlgAfterOrder( $model, $file)
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
      $result .= JText::_( 'The code to allow using the Joomla Multi Sites bridge with VirtueMart is not present.');
      $result .= '|[ACTION]';
      $result .= '|Add 5 lines containing the call to a MultiSites / VirtueMart plugin onAfterOrderCreate.';
   }
   
   return $rc .'|'. $result;
}



//------------ jms2win_actionVMPlgAfterOrder ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionVMPlgAfterOrder( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'virtuemart' .DS. 'patch_ps_checkout.php');
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
   	function add( &$d ) {
		........
		........
		if( is_callable( array($this->_SHIPPING, 'save_rate_info') )) {
			$this->_SHIPPING->save_rate_info($d);
		}
		
		// Now as everything else has been done, we can update
		........
		........
      
      ===========
      and Replace by:
      ===========
		........
   	function add( &$d ) {
		........
		........
		if( is_callable( array($this->_SHIPPING, 'save_rate_info') )) {
			$this->_SHIPPING->save_rate_info($d);
		}

//_jms2win_begin v1.1.0
JPluginHelper::importPlugin('multisites');
$dispatcher	=& JDispatcher::getInstance();
$results = $dispatcher->trigger('onAfterOrderCreate', array ( & $d));
//_jms2win_end


		// Now as everything else has been done, we can update
		........
		........

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      $this->_SHIPPING->save_rate_info .... } ...\n
      p0                                   p1    p2
      
      Produce
      begin -> p2 + INSERT PATCH + p2 -> end
      
    */
   
   // Search begin statement: $this->_SHIPPING->save_rate_info($d);
   // p0: Search for $this->_SHIPPING->save_rate_info
   $p0 = strpos( $content, '$this->_SHIPPING->save_rate_info');
   if ( $p0 === false) {
      return false;
   }

   // p1: Search for end of test '}'
   $p1 = strpos( $content, '}', $p0);
   if ( $p1 === false) {
      return false;
   }

   // p2: Go end of line
   $p2 = strpos( $content, "\n", $p1);
   if ( $p2 === false) {
      return false;
   }
 
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p2)
           . $patchStr
           . substr( $content, $p2);

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

   return true;
}

