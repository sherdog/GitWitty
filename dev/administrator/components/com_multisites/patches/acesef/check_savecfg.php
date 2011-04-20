<?php
/**
 * @file       check_savecfg.php
 * @brief      Check if the 'models/config.php' files contains the Multi Sites patches 
 *             to allow saving specific configuration file for each websites.
 *
 * @version    1.2.52
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2009-2011 Edwin2Win sprlu - all right reserved.
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
 * - V1.2.14  17-OCT-2009: Initial version
 * - V1.2.43  11-SEP-2010: Add compatibility with AceSEF 1.5.1
 * - V1.2.53  13-JAN-2011: Add compatibility with AceSEF 1.5.13
 *                         In fact, does not require any patch as now the configuation is saved
 *                         in the DB.
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkACESEFSaveCfg ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_HOST'))
 *   is present
 */
function jms2win_checkACESEFSaveCfg( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[IGNORE]|File Not Found';
	}
	
   $str = file_get_contents( $filename);
   
   // if 'MULTISITES_ID' is present
   $pos = strpos( $str, 'MULTISITES_ID');
   if ($pos === false) {
      // If AceSEF >= 1.5.13
      $pos = strpos( $str, 'JFile::write');
      if ($pos === false) {
         // Ignore the patch. Now the config is saved in the DB
   	   return '[IGNORE]|File Not Found';
      }
      // If Ace SEF < 1.5.13 then it write the config on the disk and required a patch
      else {
         $wrapperIsPresent = false;
      }
   }
   else {
      $wrapperIsPresent = true;
   }
   
   $result = "";
   $rc = '[OK]';
   if ( !$wrapperIsPresent) {
	   $rc = '[NOK]';
      $result .= JText::_( 'The Multi Sites specific "configuration.php" saving for each websites is not present.');
      $result .= '|[ACTION]';
      $result .= '|Replace 6 lines by 34 lines to save specific configuration.php file for each slave site.';
   }
   
   return $rc .'|'. $result;
}


//------------ jms2win_actionACESEFSaveCfg_old ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionACESEFSaveCfg_old( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'acesef' .DS. 'patch_savecfg.php');
   if ( $patchStr === false) {
      return false;
   }


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
		function _save_configuration() {
		........
		........
		$config->loadArray($config_array);
		........
		........
		$db->query();
		........
		// Set the configuration filename
   	$filename = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_acesef'.DS.'configuration.php';

		if (JPath::isOwner($filename) && !JPath::setPermissions($filename, '0644')) {
			JError::raiseNotice('2002', 'Could not make the '.$filename.' writable');
		}

		jimport('joomla.filesystem.file');
		if (JFile::write($filename, $config->toString('PHP', 'config', array('class' => 'acesef_configuration')))) {
		........
		........
      
      ===========
      and Replace by:
      ===========
		........
		function _save_configuration() {
		........
		........
		$config->loadArray($config_array);
		........
		........
		$db->query();
//_jms2win_begin v1.2.14
		jimport('joomla.filesystem.file');
		// If this is a Slave Site, let use the standard forma
		if ( defined( 'MULTISITES_ID')) {
   		// Set the configuration filename
   		$filename = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_acesef'.DS.'configuration.' .MULTISITES_ID. '.php';
   		if ( file_exists( $filename) && JPath::isOwner($filename) && !JPath::setPermissions($filename, '0644')) {
   			JError::raiseNotice('2002', 'Could not make the ' . $filename . '  writable');
   		}
   		// This is the slave sites. So keep the normal configuration files layout (no wrapper).
   		$configStr = $config->toString('PHP', 'config', array('class' => 'acesef_configuration'));
		}
		else {
   		// Set the configuration filename
   		$filename = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_acesef'.DS.'configuration.php';
   		if (JPath::isOwner($filename) && !JPath::setPermissions($filename, '0644')) {
   			JError::raiseNotice('2002', 'Could not make the '.$filename.' writable');
   		}
   		
		   // This is a Master website, so add the MULTISITE wrapper
   		$str = $config->toString('PHP', 'config', array('class' => 'acesef_configuration'));
   		$begPos = strpos( $str, 'class');
   		$endPos = strpos( $str, '?>');
         $configStr = substr( $str, 0, $begPos)
                    . "if ( defined( 'MULTISITES_ID')\n"
                    . "  && file_exists( dirname(__FILE__) .DS. 'configuration.' .MULTISITES_ID. '.php')) {\n"
                    . "   require_once(  dirname(__FILE__) .DS. 'configuration.' .MULTISITES_ID. '.php');\n"
                    . "} else if ( !class_exists( 'acesef_configuration')) {\n"
                    . substr( $str, $begPos, $endPos-$begPos)
                    . "}\n"
                    . "?>\n";
		}
		if (JFile::write($filename, $configStr)) {
//_jms2win_end
		........
		........

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n .... _save_configuration .....
      p0      p1              
      
      \n ....->loadArray ....... \n
             p2                 
             
      \n ....$db->query(); ....... \n
              p3                  p4

      \n .... JFile::write ......\n
              p6                 p7
      
      Produce
      begin -> p3 + INSERT PATCH + p7 -> end
      
    */
   
   // p1: Search for "_save_configuration"
   $p1 = strpos( $content, '_save_configuration');
   if ( $p1 === false) {
      return false;
   }

   // p2: Search for "->loadArray"
   $p2 = strpos( $content, '->loadArray', $p1);
   if ( $p2 === false) {
      return false;
   }


   // p3: Search for "db->query"
   $p3 = strpos( $content, 'db->query', $p2);
   if ( $p3 === false) {
      return false;
   }
 
   // p4: Search for "\n"
   $p4 = strpos( $content, "\n", $p3);
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
           . substr( $content, $p7)
           ;

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

   return true;
}


//------------ jms2win_actionACESEFSaveCfg_15 ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionACESEFSaveCfg_15( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'acesef' .DS. 'patch_savecfg15.php');
   if ( $patchStr === false) {
      return false;
   }


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
   	function save($function = "", $action = "") {
		........
		........
		AceDatabase::query($sql);
		........
		// Set the configuration filename
		$filename = JPATH_ACESEF_ADMIN.DS.'configuration.php';

		if (JPath::isOwner($filename) && !JPath::setPermissions($filename, '0644')) {
			JError::raiseNotice('2002', 'Could not make the '.$filename.' writable');
		}

		jimport('joomla.filesystem.file');
		if (JFile::write($filename, $config->toString('PHP', 'config', array('class' => 'AcesefConfig')))) {
		........
		........
      
      ===========
      and Replace by:
      ===========
		........
   	function save($function = "", $action = "") {
		........
		........
		AceDatabase::query($sql);
		........
//_jms2win_begin v1.2.43
		jimport('joomla.filesystem.file');
		// If this is a Slave Site, let use the standard forma
		if ( defined( 'MULTISITES_ID')) {
   		// Set the configuration filename
   		$filename = JPATH_ACESEF_ADMIN.DS.'configuration.' .MULTISITES_ID. '.php';
   		if ( file_exists( $filename) && JPath::isOwner($filename) && !JPath::setPermissions($filename, '0644')) {
   			JError::raiseNotice('2002', 'Could not make the ' . $filename . '  writable');
   		}
   		// This is the slave sites. So keep the normal configuration files layout (no wrapper).
   		$configStr = $config->toString('PHP', 'config', array('class' => 'AcesefConfig'));
		}
		else {
   		// Set the configuration filename
   		$filename = JPATH_ACESEF_ADMIN.DS.'configuration.php';
   		if (JPath::isOwner($filename) && !JPath::setPermissions($filename, '0644')) {
   			JError::raiseNotice('2002', 'Could not make the '.$filename.' writable');
   		}
   		
		   // This is a Master website, so add the MULTISITE wrapper
   		$str = $config->toString('PHP', 'config', array('class' => 'AcesefConfig'));
   		$begPos = strpos( $str, 'class');
   		$endPos = strpos( $str, '?>');
         $configStr = substr( $str, 0, $begPos)
                    . "if ( defined( 'MULTISITES_ID')\n"
                    . "  && file_exists( dirname(__FILE__) .DS. 'configuration.' .MULTISITES_ID. '.php')) {\n"
                    . "   require_once(  dirname(__FILE__) .DS. 'configuration.' .MULTISITES_ID. '.php');\n"
                    . "} else if ( !class_exists( 'AcesefConfig')) {\n"
                    . substr( $str, $begPos, $endPos-$begPos)
                    . "}\n"
                    . "?>\n";
		}
		if (JFile::write($filename, $configStr)) {
//_jms2win_end
		........
		........

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n .... function save .....
      p0      p1              
      
      \n ....AceDatabase::query($sql); ....... \n
              p3                  p4

      \n .... JFile::write ......\n
              p6                 p7
      
      Produce
      begin -> p3 + INSERT PATCH + p7 -> end
      
    */
   
   // p1: Search for "function save"
   $p1 = strpos( $content, 'function save');
   if ( $p1 === false) {
      return false;
   }

   // p3: Search for "AceDatabase::query"
   $p3 = strpos( $content, 'AceDatabase::query', $p1);
   if ( $p3 === false) {
      return false;
   }
 
   // p4: Search for "\n"
   $p4 = strpos( $content, "\n", $p3);
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
           . substr( $content, $p7)
           ;

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

   return true;
}

//------------ jms2win_actionACESEFSaveCfg ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionACESEFSaveCfg( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
   $content = file_get_contents( $filename);
   if ( $content === false) {
      return false;
   }

   // If AceSEF version 1.5
   $pos = strpos( $content, 'AcesefConfig');
   if ( $pos === false) {
	   return jms2win_actionACESEFSaveCfg_old( $model, $file);
   }
   
   return jms2win_actionACESEFSaveCfg_15( $model, $file);
}