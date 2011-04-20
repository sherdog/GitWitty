<?php
/**
 * @file       check_libsession.php
 * @brief      Check if the Joomla Session allow single sign-in for sub-domains 
 * @version    1.2.48
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Jms MultiSite for joomla!
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2009 Edwin2Win sprlu - all right reserved.
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
 * - V1.2.10 20-SEP-2009: File creation
 * - V1.2.48 05-NOV-2010: Modify the patch to take in account the new Joomla 1.5.22
 *                        fork() session implementation.
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkLibSession ---------------
/**
 * check if 'MULTISITES_COOKIE_SUBDOMAIN' is present
 */
function jms2win_checkLibSession( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[NOK]|File Not Found';
	}
   $str = file_get_contents( $filename);
   
   // if 'MULTISITES_' is present
   $pos = strpos( $str, 'MULTISITES_COOKIE_DOMAINS');
   if ($pos === false) $wrapperIsPresent = false;
   else                $wrapperIsPresent = true;;
   
   $result = "";
   $rc = '[OK]';
   if ( !$wrapperIsPresent) {
	   $rc = '[NOK]';
      $result .= JText::_( 'The single sign-in patch for sub-domain is not present');
      $result .= '|[ACTION]';
      $result .= '|Add 12 lines and replace 1 line by 15 lines to accept that sub-domain share the same session information for a single sign-in';
   }
   
   return $rc .'|'. $result;
}

//------------ jms2win_actionLibSession ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionLibSession( $model, $file)
{
   include_once( dirname(__FILE__) .DS. 'patchloader.php');
   $patchStr_1 = jms2win_loadPatch( 'patch_libsession_1.php');
   if ( $patchStr_1 === false) {
      return false;
   }
   $patchStr_2 = jms2win_loadPatch( 'patch_libsession_2.php');
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
   	function _start()
   	{
   	   . . . 
   		session_start();
   	   . . . 
   	}

   	function destroy()
   	{
   	   . . . 
   		if (isset($_COOKIE[session_name()])) {
   			setcookie(session_name(), '', time()-42000, '/');
   		}
   	   . . . 
   	}
   	
   	function fork()
   	{
   	   . . . 
   		session_start();
   	   . . . 
   	}

--- OR in Joomla 1.5.22 or higher ---
   	function fork()
   	{
   		if( $this->_state !== 'active' ) {
   			// @TODO :: generated error here
   			return false;
   		}
   		session_regenerate_id();
   		return true;
   	}

      
      ===========
      and Replace by:
      ===========

   	function _start()
   	{
   	   . . . 
         if ( defined( 'MULTISITES_COOKIE_DOMAINS')) {
            $cookie_domains = MULTISITES_COOKIE_DOMAINS;
            if ( !empty( $cookie_domains[0])) {
               ini_set('session.cookie_domain', $cookie_domains[0]);
            }
         }
   		session_start();
   	   . . . 
   	}

   	function destroy()
   	{
   	   . . . 
         if ( defined( 'MULTISITES_COOKIE_DOMAINS')) {
            $cookie_domains = MULTISITES_COOKIE_DOMAINS;
            foreach ( $cookie_domains as $cookie_domain) {
               if ( !empty( $cookie_domain)) {
         			setcookie(session_name(), '', time()-42000, '/', $cookie_domain);
         	   }
         	   else {
         			setcookie(session_name(), '', time()-42000, '/');
               }
            }
         }
         else {
   			setcookie(session_name(), '', time()-42000, '/');
         }
   	   . . . 
   	}
   	
   	function fork()
   	{
   	   . . . 
         if ( defined( 'MULTISITES_COOKIE_DOMAINS')) {
            $cookie_domains = MULTISITES_COOKIE_DOMAINS;
            if ( !empty( $cookie_domains[0])) {
               ini_set('session.cookie_domain', $cookie_domains[0]);
            }
         }
   		session_start();
   	   . . . 
   	}
--- OR in Joomla 1.5.22 or higher ---
      do nothing

   */
   
   // ------------- Patch definition ----------------
   /* ....\n
      \n      session_start()
      p0      p1

      \n      setcookie( .... );\n
      p2      p3                p4

      \n      session_start()
      p5      p6

      
      Produce
      begin -> p0 + INSERT PATCH no 1 + p0 -> p2 + INSERT PATCH no 2 + p4 -> p5 + INSERT PATCH no 1 + p5 -> end
      
    */
   // P1: Search begin statement: "session_start"
   $p1 = strpos( $content, 'session_start');
   if ( $p1 === false) {
      return false;
   }
   // P0: Go to Begin of line
   for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
   $p0++;
   
   // p3: Search for setcookie
   $p3 = strpos( $content, 'setcookie', $p1);
   if ( $p3 === false) {
      return false;
   }

   // P2: Go to Begin of line
   for ( $p2=$p3; $p2 > 0 && $content[$p2] != "\n"; $p2--);
   $p2++;

   // p4: Search for end of line
   for ( $p4=$p3; $content[$p4] != "\n"; $p4++);
   $p4++;

   // P6: Search begin statement: "session_start"
   $patchStr_3 = $patchStr_1;
   $p6 = strpos( $content, 'session_start', $p4);
   if ( $p6 === false) {
      // Cross check that we are in Joomla 1.5.22 that now use the session_regenerate_id
      $p6 = strpos( $content, 'session_regenerate_id', $p4);
      if ( $p6 === false) {
         return false;
      }
      $patchStr_3 = '';
   }
   // P5: Go to Begin of line
   for ( $p5=$p6; $p5 > 0 && $content[$p5] != "\n"; $p5--);
   $p5++;
   
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p0)
           . $patchStr_1
           . substr( $content, $p0, $p2-$p0)
           . $patchStr_2
           . substr( $content, $p4, $p5-$p4)
           . $patchStr_3
           . substr( $content, $p5+1);

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}
   
   return true;
}
