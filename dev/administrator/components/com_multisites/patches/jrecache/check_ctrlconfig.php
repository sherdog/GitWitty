<?php
/**
 * @file       check_ctrlconfig.php
 * @brief      Check if the 'controls/configuration.php' files contains the Multi Sites patches 
 *             to allow saving specific configuration file for each websites.
 *
 * @version    1.2.12
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
 * - V1.2.12  04-OCT-2009: Initial version
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkJRECtrlCfg ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_HOST'))
 *   is present
 */
function jms2win_checkJRECtrlCfg( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[IGNORE]|File Not Found';
	}
	
   $str = file_get_contents( $filename);
   
   // if 'MULTISITES_ID' is present
   $pos = strpos( $str, 'MULTISITES_ID');
   if ($pos === false) $wrapperIsPresent = false;
   else                $wrapperIsPresent = true;
   
   $result = "";
   $rc = '[OK]';
   if ( !$wrapperIsPresent) {
	   $rc = '[NOK]';
      $result .= JText::_( 'The Multi Sites specific "configuration.php" saving for each websites is not present.');
      $result .= '|[ACTION]';
      $result .= '|Replace 1 lines by 16 lines to save specific configuration.php file for each slave site.';
   }
   
   return $rc .'|'. $result;
}


//------------ jms2win_actionJRECtrlCfg ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionJRECtrlCfg( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'jrecache' .DS. 'patch_ctrlconfig.php');
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
      function save_configuration ( ) {
      	include_once( _JRECACHE_DIR . _DS . 'library'. _DS . 'config.php');
      	$jrecache_config =& new jrecache_config("_JRECache_Config", _JRECACHE_DIR .  _DS . "jrecache.config.php");
      
      	foreach($_POST as $key => $value) {
             	$jrecache_config->setCfg($key, $value);
      	}
      	$jrecache_config->saveConfig();
      }
		........
		........
      
      ===========
      and Replace by:
      ===========

		........
		........
      function save_configuration ( ) {
      	include_once( _JRECACHE_DIR . _DS . 'library'. _DS . 'config.php');
      //_jms2win_begin v1.2.12
         $config_master = _JRECACHE_DIR .  _DS . 'jrecache.config.php';
         if ( defined( 'MULTISITES_ID')) {
            $config_slave = _JRECACHE_DIR .  _DS . 'jrecache.config.' . MULTISITES_ID . '.php';
            if ( file_exists( $config_slave)) {
            	$jrecache_config =& new jrecache_config("_JRECache_Config", $config_slave);
            }
            else {
            	$jrecache_config =& new jrecache_config("_JRECache_Config", $config_master);
            	$jrecache_config->_path = $config_slave;
            }
         }
         else {
         	$jrecache_config =& new jrecache_config("_JRECache_Config", $config_master);
         }
      //_jms2win_end
      
      	foreach($_POST as $key => $value) {
             	$jrecache_config->setCfg($key, $value);
      	}
      	$jrecache_config->saveConfig();
      }
		........
		........

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n .... save_configuration .....
              p0              
      
      \n ....	$jrecache_config =& new jrecache_config(.... "jrecache.config.php"); .... \n
      p1                               p2                                                p3
      
      Produce
      begin -> p1 + INSERT PATCH + p3 -> end
      
    */
   
   // p0: Search for "save_configuration"
   $p0 = strpos( $content, 'save_configuration');
   if ( $p0 === false) {
      return false;
   }

   // p2: Search for "jrecache_config"
   $p2 = strpos( $content, 'jrecache_config', $p0);
   if ( $p2 === false) {
      return false;
   }

    // P1: Go to Begin of line
   for ( $p1=$p2; $p1 > 0 && $content[$p1] != "\n"; $p1--);
   $p1++;

   // p3: Search for "\n"
   $p3 = strpos( $content, "\n", $p2);
   if ( $p3 === false) {
      return false;
   }
   $p3++;
 
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p1)
           . $patchStr
           . substr( $content, $p3)
           ;

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

   return true;
}

