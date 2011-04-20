<?php
/**
 * @file       check_libapplication.php
 * @brief      Check if the Joomla Application allow single sign-in for sub-domains 
 * @version    1.2.33
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
 * - V1.2.10 13-SEP-2009: File creation
 * - V1.2.11 24-SEP-2009: Add a rescue session restore in case where the server does not restore
 *                        the session correctly for sub-domain.
 *                        On some server, we have identified that a session ID is correctly restore
 *                        with sub-domain and that the session info are correctly retreived from the DB
 *                        but the data are not correctly processed by the PHP session handler that seems
 *                        can ignore the session data (encrypted).
 *                        So in that case, the session is not correctly restored and this fix consist
 *                        to rebuild the session information based on the infos retreive from the DB.
 * - V1.2.12 04-OCT-2009: remove a warning during the patch installation
 * - V1.2.33 25-APR-2010: Apply the Joomla fix corresponding to the bug introduced in Joomla 1.5.16 
 *                        when they have published a security fix and perform a session fork that 
 *                        prevent any session processing before joomla one 
 *                        and therefore does not allow using the "_host_" feature
 *                        when working on a localhost.
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkLibApplication ---------------
/**
 * check if 'MULTISITES_COOKIE_DOMAIN' and '_jms2win_restore_session_objects_' are present
 */
function jms2win_checkLibApplication( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[NOK]|File Not Found';
	}
   $str = file_get_contents( $filename);
   
   $result = "";
   $rc = '[OK]';

   // if 'MULTISITES_' is present
   $pos = strpos( $str, 'MULTISITES_COOKIE_DOMAINS');
   if ($pos === false) {
	   $rc = '[NOK]';
      $result .= JText::_( 'The single sign-in patch for sub-domain is not present');
      $result .= '|[ACTION]';
      $result .= '|Replace 2 lines by 2x15 lines to accept that sub-domain share the same session information for a single sign-in';
      $result .= '|Replace 4 lines by 35 lines to add the single sign-in rescue session restore when the server does not restore the session data on sub-domain.';
   }
   else {
      // if '_jms2win_restore_session_objects_' is present
      $pos = strpos( $str, '_jms2win_restore_session_objects_');
      if ($pos === false) {
   	   $rc = '[NOK]';
         $result .= JText::_( 'The single sign-in patch to restore sub-domain session for some platform is not present');
         $result .= '|[ACTION]';
         $result .= '|Replace 4 lines by 35 lines to add the single sign-in rescue session restore when the server does not restore the session data on sub-domain.';
      }
      else {
         // If session fork (introduce in Joomla 1.5.16) is present
         $sessions_fork = strpos( $str, 'session->fork');
         if ( $sessions_fork === false) {}
         else {
            // Check if the $this->_createSession($session->getId()); is present just after the fork
            $posCreateSess = strpos( $str, 'this->_createSession', $sessions_fork);
            if ($posCreateSess === false || ($posCreateSess-$sessions_fork) > 100) {
               // if '_jms2win_fix_j1_5_16_' is present
               $pos = strpos( $str, '_jms2win_fix_j1_5_16_');
               if ($pos === false) {
            	   $rc = '[NOK]';
                  $result .= JText::_( 'Apply the fix concerning the bug introduced in Joomla 1.5.16 and that is described in http://joomlacode.org/gf/project/joomla/tracker/?action=TrackerItemEdit&tracker_item_id=20221.');
                  $result .= '|[ACTION]';
                  $result .= '|Add 1 line to properly manage the session and allow login into joomla.';
               }
            }
         }
      }
   }
   
   return $rc .'|'. $result;
}

//------------ jms2win_actionLibApplication ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionLibApplication( $model, $file)
{
   include_once( dirname(__FILE__) .DS. 'patchloader.php');
   $patchStr_1 = jms2win_loadPatch( 'patch_libapplication_1.php');
   if ( $patchStr_1 === false) {
      return false;
   }
   $patchStr_2 = jms2win_loadPatch( 'patch_libapplication_2.php');
   if ( $patchStr_2 === false) {
      return false;
   }
   $patchStr_3 = jms2win_loadPatch( 'patch_libapplication_3.php');
   if ( $patchStr_3 === false) {
      return false;
   }
   
   $patchStr_4 = jms2win_loadPatch( 'patch_libapplication_4.php');
   if ( $patchStr_4 === false) {
      return false;
   }

//	$filename = JPATH_ROOT.DS.$file;
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
   $content = file_get_contents( $filename);
   if ( $content === false) {
      return false;
   }

   // Remove potential exising patches
   $content = jms2win_removePatch( $content);
   
   // Search/Replace the statement
   /*
      ===========
      Search for:
      ===========
   	function login($credentials, $options = array())
   	{
   	   . . . 
			$session->fork();
   	   . . . 
   		setcookie( JUtility::getHash('JLOGIN_REMEMBER'), $rcookie, $lifetime, '/' );
   	   . . . 
   	}

   	function logout($userid = null, $options = array())
   	{
   	   . . . 
   			setcookie( JUtility::getHash('JLOGIN_REMEMBER'), false, time() - 86400, '/' );
   	   . . . 
   	}
   	
   	function &_createSession( $name )
   	{
   	   . . . 
   		if ($storage->load($session->getId())) {
   			$storage->update();
   			return $session;
   		}
   	   . . . 
   	}

      
      ===========
      and Replace by:
      ===========

   	function login($credentials, $options = array())
   	{
   	   . . . 
		   $session->fork();
		   $this->_createSession($session->getId());
   	   . . . 
         if ( defined( 'MULTISITES_COOKIE_DOMAINS')) {
            $cookie_domains = MULTISITES_COOKIE_DOMAINS;
            foreach ( $cookie_domains as $cookie_domain) {
               if ( !empty( $cookie_domain)) {
   					setcookie( JUtility::getHash('JLOGIN_REMEMBER'), $rcookie, $lifetime, '/', $cookie_domain);
               }
               else {
   					setcookie( JUtility::getHash('JLOGIN_REMEMBER'), $rcookie, $lifetime, '/' );
               }
            }
         }
         else {
				setcookie( JUtility::getHash('JLOGIN_REMEMBER'), $rcookie, $lifetime, '/' );
			}
   	   . . . 
   	}

   	function logout($userid = null, $options = array())
   	{
   	   . . . 
         if ( defined( 'MULTISITES_COOKIE_DOMAINS')) {
            $cookie_domains = MULTISITES_COOKIE_DOMAINS;
            foreach ( $cookie_domains as $cookie_domain) {
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


   	function &_createSession( $name )
   	{
   	   . . . 
   		$sess_id = $session->getId();
   		if ($storage->load( $sess_id)) {
   			$storage->update();
   
   			// If the Registry Object is NOT present in the PHP session
   			$registry = $session->get('registry');
   			if ( empty( $registry)) {
   			   // Create a new empty registry
         		$session->set('registry',	new JRegistry('session'));
   			}
   			
   			// If the User Object is NOT present in the PHP session and there is a userid stored in the session table,
   			$user = $session->get('user');
   			if ( empty( $user) && !empty( $storage->userid)) {
   			   // Rebuild the User object that the session has not restored.
   			   $user = & JFactory::getUser( $storage->userid);
   			   $user->set( 'guest',    $storage->guest);
   			   $user->set( 'usertype', $storage->usertype);
   			   $user->set( 'gid',      $storage->gid);
         		if ( $storage->guest == 0) {
            		$user->set( 'aid', 1);
            		$acl =& JFactory::getACL();
            		if ( $acl->is_group_child_of( $storage->usertype, 'Registered')
            		  || $acl->is_group_child_of( $storage->usertype, 'Public Backend'))
            		{
            			$user->set( 'aid', 2);
            		}
         		}
   			   
         		// Save the user in the session.
         		$session->set('user', $user);
   			}
   			return $session;
   		}
   	   . . . 
   	}

   */
   
   // ------------- Patch definition ----------------
   /* ....\n
      \n      session->fork( .... );\n
      pa      pb                    pc
      
      \n      $this->_createSession($session->getId());\n
      pd      pe                    pf
      
      
      \n      setcookie( .... );\n
      p0      p1               p2

      \n      setcookie( .... );\n
      p3      p4                p5
      
      \n      function &_createSession( $name )
                       p6
                       
		\n      if ($storage->load($session->getId())) {
      p7          p8
         	   }
               p9
      \n
      p10
                       
      
      Produce
      begin -> p0 + INSERT PATCH no 1 + p2 -> p3 + INSERT PATCH no 2 + p5 -> p7 + INSERT PATCH no 3 + p10 -> end
      
    */

   // Pb: Search begin statement: "session->fork"
   $isForkPresent = false;
   $pb = strpos( $content, 'session->fork');
   if ( $pb === false) {}
   else {
      $isForkPresent = true;;

      // Pa: Go to Begin of line
      for ( $pa=$pb; $pa > 0 && $content[$pa] != "\n"; $pa--);
      $pa++;
      
      // pc: Search for end of line
      for ( $pc=$pb; $content[$pc] != "\n"; $pc++);
      $pc++;

      $pe = strpos( $content, 'this->_createSession', $pc);
      if ( $pe === false) {}
      // If Joomla has fixed the bug introduced in Joomla 1.5.16
      else if ( ($pe-$pc) < 100) {
         // Then do not install the patch
         $isForkPresent = false;
      }
   }

   // P1: Search begin statement: "setcookie"
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

   // p4: Search for setcookie
   $p4 = strpos( $content, 'setcookie', $p2);
   if ( $p4 === false) {
      return false;
   }

   // P3: Go to Begin of line
   for ( $p3=$p4; $p3 > 0 && $content[$p3] != "\n"; $p3--);
   $p3++;

   // p5: Search for end of line
   for ( $p5=$p4; $content[$p5] != "\n"; $p5++);
   $p5++;


   // p6: Search for &_createSession
   $p6 = strpos( $content, '&_createSession', $p5);
   if ( $p6 === false) {
      return false;
   }

   // p8: Search for $storage->load
   $p8 = strpos( $content, '$storage->load', $p6);
   if ( $p8 === false) {
      return false;
   }

   // P7: Go to Begin of line
   for ( $p7=$p8; $p7 > 0 && $content[$p7] != "\n"; $p7--);
   $p7++;

   // p9: Search for '}'
   $p9 = strpos( $content, '}', $p8);
   if ( $p8 === false) {
      return false;
   }

   // p10: Search for end of line
   for ( $p10=$p9; $content[$p10] != "\n"; $p10++);
   $p10++;
   
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   if ( $isForkPresent) {
      $result = substr( $content, 0, $pa)
              . $patchStr_4
              . substr( $content, $pc, $p0-$pc)
              . $patchStr_1
              . substr( $content, $p2, $p3-$p2)
              . $patchStr_2
              . substr( $content, $p5, $p7-$p5)
              . $patchStr_3
              . substr( $content, $p10)
              ;
   }
   else {
      $result = substr( $content, 0, $p0)
              . $patchStr_1
              . substr( $content, $p2, $p3-$p2)
              . $patchStr_2
              . substr( $content, $p5, $p7-$p5)
              . $patchStr_3
              . substr( $content, $p10)
              ;
   }

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}
   
   return true;
}
