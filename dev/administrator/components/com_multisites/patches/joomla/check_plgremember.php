<?php
/**
 * @file       check_plgremember.php
 * @brief      Check if the Joomla Application allow single sign-in for sub-domains 
 * @version    1.2.24
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Jms MultiSite for joomla!
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2010 Edwin2Win sprlu - all right reserved.
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
 * - V1.2.24 17-JAN-2010: remove a PHP warning
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkPlgRemember ---------------
/**
 * check if 'MULTISITES_COOKIE_SUBDOMAIN' is present
 */
function jms2win_checkPlgRemember( $model, $file)
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
      $result .= '|Replace 1 line by 15 lines to accept that sub-domain share the same session information for a single sign-in';
   }
   
   return $rc .'|'. $result;
}

//------------ jms2win_actionPlgRemember ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionPlgRemember( $model, $file)
{
   include_once( dirname(__FILE__) .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( 'patch_plgremember.php');
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
   	function onAfterInitialise()
   	{
   	   . . . 
   			setcookie( JUtility::getHash('JLOGIN_REMEMBER'), false, time() - 86400, '/' );
   	   . . . 
   	}


      
      ===========
      and Replace by:
      ===========

   	function onAfterInitialise()
   	{
   	   . . . 
         if ( defined( 'MULTISITES_COOKIE_DOMAINS')) {
            $cookie_domains = MULTISITES_COOKIE_DOMAINS;
            foreach( $cookie_domains as $cookie_domain) {
               if ( !empty( $cookie_domain)) {
   					setcookie( JUtility::getHash('JLOGIN_REMEMBER'), false, time() - 86400, '/', $cookie_domain);
   				}
   				else {
   					setcookie( JUtility::getHash('JLOGIN_REMEMBER'), false, time() - 86400, '/' );
   				}
            }
         }
         else {
				setcookie( JUtility::getHash('JLOGIN_REMEMBER'), false, time() - 86400, '/' );
			}
   	   . . . 
   	}

   */
   
   // ------------- Patch definition ----------------
   /* ....\n
      \n      setcookie( .... );\n
      p0      p1               p2

      Produce
      begin -> p0 + INSERT PATCH + p2 -> end
      
    */
   // P1: Search begin statement: "session_start"
   $p1 = strpos( $content, 'setcookie');
   if ( $p1 === false) {
      return false;
   }
   // P0: Go to Begin of line
   for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
   $p0++;
   

   // p2: Search for end of line
   for ( $p2=$p1; $content[$p2] != "\n"; $p2++);
   $p2++;

   
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p0)
           . $patchStr
           . substr( $content, $p2);

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}
   
   return true;
}
