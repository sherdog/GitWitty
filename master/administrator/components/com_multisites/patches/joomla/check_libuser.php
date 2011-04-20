<?php
/**
 * @file       check_libuser.php
 * @brief      Check if the Library / ... /user is patched by JACLPlus 
 *             and if it disable itself when 'configuration.php' files is not present
 * @version    1.1.8
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

//------------ jms2win_checkLibUser ---------------
/**
 * check if 'JACLPlus' is present
 * and 'configuration.php' is NOT present
 */
function jms2win_checkLibUser( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[NOK]|File Not Found';
	}
   $str = strtolower( file_get_contents( $filename));
   
   // if 'JACLPlus' is NOT present
   $pos = strpos( $str, 'jaclplus');
   if ($pos === false) {
	   return '[IGNORE]|JACPlus is not present';
   }
   
   // if 'configuration.php' is NOT present
   $pos = strpos( $str, 'configuration.php');
   if ($pos === false) $wrapperIsPresent = false;
   else                $wrapperIsPresent = true;;
   
   $result = "";
   $rc = '[OK]';
   if ( !$wrapperIsPresent) {
	   $rc = '[NOK]';
      $result .= JText::_( 'JACLPlus return a fatal error during a fresh Joomla Installation because the DB is not present<br/>'
                         . 'and does not give access to the websites when the JACLPlus table are not present. (Case of a fresh installation)');
      $result .= '|[ACTION]';
      $result .= '|Add 16 lines to disable JACPLus during a fresh Joomla Installation and also when you access a website that has not re-install JACLPlus. Otherwise JACLPlus remain active.';
   }
   
   return $rc .'|'. $result;
}

//------------ jms2win_actionJConfig ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionLibUser( $model, $file)
{
   include_once( dirname(__FILE__) .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( 'patch_libuser.php');
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

      // Start JACLPlus Modification
      if( file_exists( JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jaclplus'.DS.'jaclplus.class.php' ) ) {
      	if(!defined('_JACL')) {
      		ob_start();
      		require_once ( JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jaclplus'.DS.'jaclplus.class.php' );
      		ob_end_clean();
      		if(class_exists('JACLPlus')) define('_JACL', 1);
      	}
      }
      // End JACLPlus Modification


      
      ===========
      and Replace by:
      ===========

      //_jms2win_begin v1.1.8
      if (!file_exists( JPATH_CONFIGURATION . DS . 'configuration.php' ) || (filesize( JPATH_CONFIGURATION . DS . 'configuration.php' ) < 10) || file_exists( JPATH_INSTALLATION . DS . 'index.php' )) {}
      else 
      //_jms2win_end
      // Start JACLPlus Modification
      if( file_exists( JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jaclplus'.DS.'jaclplus.class.php' ) ) {
      	if(!defined('_JACL')) {
      		ob_start();
      		require_once ( JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jaclplus'.DS.'jaclplus.class.php' );
      		ob_end_clean();
      		if(class_exists('JACLPlus')) define('_JACL', 1);
      	}
      }
      // End JACLPlus Modification

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n // Start JACLPlus Modification
      p0          p1
      
      Produce
      begin -> p0 + INSERT PATCH + p0 -> end
      
    */
   // P1: Search begin statement: "JFile::write"
   $p1 = strpos( strtolower( $content), 'jaclplus');
   if ( $p1 === false) {
      return false;
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
