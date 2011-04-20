<?php
/**
 * @file       check_jce.php
 * @brief      Check if the Joomla Content Editor extension contains the patch 
 *             to force the ovewrite in JCE Installation when called by a slave site
 * @version    1.0.10
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

//------------ jms2win_checkJCE ---------------
/**
 * check if 'MULTISITES_' is present
 */
function jms2win_checkJCE( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[IGNORE]|File Not Found';
	}
   $str = file_get_contents( $filename);
   
   // if 'MULTISITES_' is present
   $pos = strpos( $str, 'MULTISITES_');
   if ($pos === false) $wrapperIsPresent = false;
   else                $wrapperIsPresent = true;;
   
   $result = "";
   $rc = '[OK]';
   if ( !$wrapperIsPresent) {
	   $rc = '[NOK]';
      $result .= JText::_( 'The "force overwrite" code is not present in JCE Installer.');
      $result .= '|[ACTION]';
      $result .= '|Add 3 lines to allow Slave sites overwrite installations (Called from JCE -> Install)';
   }
   
   return $rc .'|'. $result;
}

//------------ jms2win_actionJConfig ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionJCE( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'jce' .DS. 'patch_jce.php');
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
   	function &getInstance()
   	{
   		static $instance;
   
   		if (!isset ($instance)) {
   			$instance = new JCEInstaller();
   		}
   		return $instance;
   	}
      
      ===========
      and Replace by:
      ===========
   	function &getInstance()
   	{
   		static $instance;
   
   		if (!isset ($instance)) {
   			$instance = new JCEInstaller();
   
            if ( defined( 'MULTISITES_ID')) {
         	   $instance->setOverwrite( true);
            }
   		
   		}
   		return $instance;
   	}


   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n      ... new JCEInstaller() ...\n
      p0                   p1            p2                    p3          p4    p5
      
      Produce
      begin -> p2 + INSERT PATCH + p2 -> end
      
    */
   // P1: Search begin statement: "JFile::write"
   $p1 = strpos( $content, 'new JCEInstaller()');
   if ( $p1 === false) {
      return false;
   }
   // P2: Go to End of line
   for ( $p2=$p1; $content[$p2] != "\n"; $p2++);
   $p2++;
   
   
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
