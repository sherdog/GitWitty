<?php
/**
 * @file       check_config.php
 * @brief      Check if the FrontPage SlideShow configuration wrapper is present.
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
 * - V1.2.17 03-NOV-2009: Initial version
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkFPSSCfgWrapper ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_ID') ...
 *   is present
 */
function jms2win_checkFPSSCfgWrapper( $model, $file)
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
      $result .= JText::_( 'The configuration wrapper is not present in the MASTER FrontPage SlideShow configuration.php file.');
      $result .= '|[ACTION]';
      $result .= '|Add 6 lines containing the routing wrapper to the slave site.';
   }
   
   return $rc .'|'. $result;
}



//------------ jms2win_actionFPSSCfgWrapper ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionFPSSCfgWrapper( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'fpslideshow' .DS. 'patch_config.php');
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
      class FPSSConfig {
      {
		........
		........
		........
	   }
		?>
      
      ===========
      and Replace by:
      ===========
		........
		........
//_jms2win_begin v1.2.17
if ( defined( 'MULTISITES_ID')
  && file_exists( dirname(__FILE__) .DS. 'configuration.' .MULTISITES_ID. '.php')) {
   require_once(  dirname(__FILE__) .DS. 'configuration.' .MULTISITES_ID. '.php');
} else if ( !class_exists( 'FPSSConfig')) {
//_jms2win_end
class FPSSConfig {
      {
		........
		........
		........
	   }
//_jms2win_begin
}
//_jms2win_end
		?>

   */
   
   // ------------- Patch definition ----------------
   /* ....\n
      \n .... class FPSSConfig { ...
      p0            p1              
      

      \n .... ?>;
      p6      p7
      
      Produce
      begin -> p0 + INSERT PATCH + p0 -> p6 + "}" + p6 -> end
      
    */
   
   // p1: Search for "acesef_configuration"
   $p1 = strpos( $content, 'FPSSConfig');
   if ( $p1 === false) {
      return false;
   }
   
   // P0: Go to Begin of line
   for ( $p0 = $p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
   $p0++;

 
   // p7: Search for '? >'
   $p7 = strpos( $content, '?' . '>', $p1);
   if ( $p7 === false) {
      return false;
   }

   // P6: Go to Begin of line
   for ( $p6=$p7; $p6 > 0 && $content[$p6] != "\n"; $p6--);
   $p6++;

 
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p0)
           . $patchStr
           . substr( $content, $p0, $p6-$p0)
           . "//_jms2win_begin\n"
           . "}\n"
           . "//_jms2win_end\n"
           . substr( $content, $p6)
           ;

   // ------------- Write the PATCH results ----------------

	// Set FTP credentials, if given
	jimport('joomla.client.helper');
	JClientHelper::setCredentialsFromRequest('ftp');
	$ftp = JClientHelper::getCredentials('ftp');

	JClientHelper::getCredentials('ftp', true);

	// Try to make configuration.php writeable
	jimport('joomla.filesystem.path');
	if (!JPath::setPermissions($filename, '0644')) {
		JError::raiseNotice('SOME_ERROR_CODE', 'Could not make configuration.php writable');
	}

	// Write the new content
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

	// Try to make configuration.php unwriteable
	if (!JPath::setPermissions($filename, '0444')) {
		JError::raiseNotice('SOME_ERROR_CODE', 'Could not make configuration.php unwritable');
	}

   return true;
}

