<?php
/**
 * @file       check_savecfg.php
 * @brief      Check if the 'models/alphacontent.php' files contains the Multi Sites patches 
 *             to allow saving specific configuration file for each websites.
 *
 * @version    1.1.9
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
 * - V1.1.9  17-APR-2009: Initial version
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkAlphaContentSaveCfg ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_HOST'))
 *   is present
 */
function jms2win_checkAlphaContentSaveCfg( $model, $file)
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
      $result .= '|Replace 6 lines byt 34 lines to save specific configuration.php file for each slave site.';
   }
   
   return $rc .'|'. $result;
}


//------------ jms2win_actionAlphaContentSaveCfg ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionAlphaContentSaveCfg( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'alphacontent' .DS. 'patch_savecfg.php');
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
		function _save_configuration() {
		........
		........
      $configuration->loadArray($config_array);
		// Set the configuration filename
		$filename = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_alphacontent'.DS.'configuration'.DS.'configuration.php';

		if ( JPath::isOwner($filename) && !JPath::setPermissions($filename, '0644')) {
			JError::raiseNotice('2002', 'Could not make the ' . $filename . '  writable');
		}

		jimport('joomla.filesystem.file');
      if (JFile::write($filename, $configuration->toString('PHP', 'configuration', array('class' => 'alphaConfiguration')))) {
		........
		........
      
      ===========
      and Replace by:
      ===========
		........
		function _save_configuration() {
		........
		........
      $configuration->loadArray($config_array);
		jimport('joomla.filesystem.file');
		// If this is a Slave Site, let use the standard forma
		if ( defined( 'MULTISITES_ID')) {
   		// Set the configuration filename
   		$filename = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_alphacontent'.DS.'configuration'.DS.'configuration.' .MULTISITES_ID. '.php';
   		if ( file_exists( $filename) && JPath::isOwner($filename) && !JPath::setPermissions($filename, '0644')) {
   			JError::raiseNotice('2002', 'Could not make the ' . $filename . '  writable');
   		}
   		// This is the slave sites. So keep the normal configuration files layout (no wrapper).
   		$configStr = $configuration->toString('PHP', 'configuration', array('class' => 'alphaConfiguration'));
		}
		else {
   		// Set the configuration filename
   		$filename = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_alphacontent'.DS.'configuration'.DS.'configuration.php';
   		if ( JPath::isOwner($filename) && !JPath::setPermissions($filename, '0644')) {
   			JError::raiseNotice('2002', 'Could not make the ' . $filename . '  writable');
   		}
   		
		   // This is a Master website, so add the MULTISITE wrapper
   		$str = $configuration->toString('PHP', 'configuration', array('class' => 'alphaConfiguration'));
   		$begPos = strpos( $str, 'class');
   		$endPos = strpos( $str, '?>');
         $configStr = substr( $str, 0, $begPos)
                    . "if ( defined( 'MULTISITES_ID')\n"
                    . "  && file_exists( dirname(__FILE__) .DS. 'configuration.' .MULTISITES_ID. '.php')) {\n"
                    . "   require_once(  dirname(__FILE__) .DS. 'configuration.' .MULTISITES_ID. '.php');\n"
                    . "} else if ( !class_exists( 'alphaConfiguration')) {\n"
                    . substr( $str, $begPos, $endPos-$begPos)
                    . "}\n"
                    . "?>\n";
		}
		if (JFile::write($filename, $configStr)) {
		........
		........

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n .... _save_configuration .....
      p0      p1              
      
      \n ....->loadArray ....... \n
             p2                  p3
             

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

 
   // p3: Search for "\n"
   $p3 = strpos( $content, "\n", $p2);
   if ( $p3 === false) {
      return false;
   }
   $p3++;
 
   // p6: Search for "JFile::write"
   $p6 = strpos( $content, 'JFile::write', $p3);
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
   $result = substr( $content, 0, $p3)
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

