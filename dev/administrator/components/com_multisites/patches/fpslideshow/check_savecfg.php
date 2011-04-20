<?php
/**
 * @file       check_savecfg.php
 * @brief      Check if the 'admin.fpslideshow.php' files contains the Multi Sites patches 
 *             to allow saving specific configuration file for each websites.
 *
 * @version    1.2.17
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
 * - V1.2.17  03-NOV-2009: Initial version
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkFPSSSaveCfg---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_HOST'))
 *   is present
 */
function jms2win_checkFPSSSaveCfg( $model, $file)
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
      $result .= '|Replace 14 lines by 39 lines to save specific configuration.php file for each slave site.';
   }
   
   return $rc .'|'. $result;
}


//------------ jms2win_actionFPSSSaveCfg ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionFPSSSaveCfg( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'fpslideshow' .DS. 'patch_savecfg.php');
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
		function saveConfig( $option, $mosConfig_absolute_path ) {
		........
		........
		$config->loadArray($config_array);
		........
		........
		........
		// Set the configuration filename
   	$fname =  JPATH_ADMINISTRATOR.DS.'components'.DS.'com_fpslideshow'.DS.'configuration.php';

   	JClientHelper::getCredentials('ftp', true);
   
   	// Try to make configuration.php writeable
   	jimport('joomla.filesystem.path');
   	if (!JPath::setPermissions($fname, '0644')) {
   		JError::raiseNotice('SOME_ERROR_CODE', 'Could not make configuration.php writable');
   	}
   
   	// Get the config registry in PHP class format and write it to configuation.php
   	jimport('joomla.filesystem.file');
   	if (JFile::write($fname, $config->toString('PHP', 'config', array('class' => 'FPSSConfig')))) {
		........
		........
      
      ===========
      and Replace by:
      ===========
		........
		function saveConfig( $option, $mosConfig_absolute_path ) {
		........
		........
		$config->loadArray($config_array);
		........
		........
//_jms2win_begin v1.2.17
	jimport('joomla.filesystem.file');
	// If this is a Slave Site, let use the standard forma
	if ( defined( 'MULTISITES_ID')) {
		// Set the configuration filename
   	$fname =  JPATH_ADMINISTRATOR.DS.'components'.DS.'com_fpslideshow'.DS.'configuration' .MULTISITES_ID. '.php';
		// This is the slave sites. So keep the normal configuration files layout (no wrapper).
		$configStr = $config->toString('PHP', 'config', array('class' => 'FPSSConfig'));
	}
	else {
		// Set the configuration filename
   	$fname =  JPATH_ADMINISTRATOR.DS.'components'.DS.'com_fpslideshow'.DS.'configuration.php';
	   // This is a Master website, so add the MULTISITE wrapper
		$str = $config->toString('PHP', 'config', array('class' => 'FPSSConfig'));
		$begPos = strpos( $str, 'class');
		$endPos = strpos( $str, '?>');
      $configStr = substr( $str, 0, $begPos)
                 . "//_jms2win_begin v1.2.17\n"
                 . "if ( defined( 'MULTISITES_ID')\n"
                 . "  && file_exists( dirname(__FILE__) .DS. 'configuration.' .MULTISITES_ID. '.php')) {\n"
                 . "   require_once(  dirname(__FILE__) .DS. 'configuration.' .MULTISITES_ID. '.php');\n"
                 . "} else if ( !class_exists( 'FPSSConfig')) {\n"
                 . "//_jms2win_end\n"
                 . substr( $str, $begPos, $endPos-$begPos)
                 . "//_jms2win_begin\n"
                 . "}\n"
                 . "//_jms2win_end\n"
                 . "?>\n";
	}
	JClientHelper::getCredentials('ftp', true);

	// Try to make configuration.php writeable
	jimport('joomla.filesystem.path');
	if (!JPath::setPermissions($fname, '0644')) {
		JError::raiseNotice('SOME_ERROR_CODE', 'Could not make configuration.php writable');
	}

	// Get the config registry in PHP class format and write it to configuation.php
	if (JFile::write($fname, $configStr)) {
//_jms2win_end
		........
		........

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n ... function saveConfig( $option, $mosConfig_absolute_path ) {.....
      p0     p1              
      
      \n ....->loadArray ....... \n
             p2                  p4
             

      \n .... JFile::write ......\n
              p6                 p7
      
      Produce
      begin -> p3 + INSERT PATCH + p7 -> end
      
    */
   
   // p1: Search for "function saveConfig"
   $p1 = strpos( $content, 'function saveConfig');
   if ( $p1 === false) {
      return false;
   }

   // p2: Search for "->loadArray"
   $p2 = strpos( $content, '->loadArray', $p1);
   if ( $p2 === false) {
      return false;
   }


   // p4: Search for "\n"
   $p4 = strpos( $content, "\n", $p2);
   if ( $p4 === false) {
      return false;
   }
   $p4++;
 
   // p6: Search for "JFile::write"
   $p6 = strpos( $content, 'JFile::write', $p4);
   if ( $p6 === false) {
      return false;
   }

   // p7: Search for "\n"
   $p7 = strpos( $content, "\n", $p6);
   if ( $p3 === false) {
      return false;
   }

 
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p4)
           . $patchStr
           . substr( $content, $p7+1)
           ;

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

   return true;
}

