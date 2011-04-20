<?php
/**
 * @file       check_config.php
 * @brief      Check if the ACE SEF configuration wrapper is present.
 * @version    1.2.43
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
 * - V1.2.14 17-OCT-2009: Initial version
 * - V1.2.43 11-SEP-2010: Add compatibility with AceSEF 1.5.1
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkACESEFCfgWrapper ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_ID') ...
 *   is present
 */
function jms2win_checkACESEFCfgWrapper( $model, $file)
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
      $result .= JText::_( 'The configuration wrapper is not present in the MASTER ACE SEF configuration.php file.');
      $result .= '|[ACTION]';
      $result .= '|Add 6 lines containing the routing wrapper to the slave site.';
   }
   
   return $rc .'|'. $result;
}



//------------ jms2win_actionACESEFCfgWrapper ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionACESEFCfgWrapper( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'acesef' .DS. 'patch_config.php');
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
      class acesef_configuration {
or    class AcesefConfig {
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
//_jms2win_begin v1.2.13
if ( defined( 'MULTISITES_ID')
  && file_exists( dirname(__FILE__) .DS. 'configuration.' .MULTISITES_ID. '.php')) {
   require_once(  dirname(__FILE__) .DS. 'configuration.' .MULTISITES_ID. '.php');
} else  {
//_jms2win_end
      class acesef_configuration {
or    class AcesefConfig {
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
      \n .... class acesef_configuration { ...
      p0            p1              
      

      \n .... ?>;
      p6      p7
      
      Produce
      begin -> p0 + INSERT PATCH + p0 -> p6 + "}" + p6 -> end
      
    */
   
   // p1: Search for "acesef_configuration"
   $p1 = strpos( $content, 'acesef_configuration');
   if ( $p1 === false) {
      $p1 = strpos( $content, 'AcesefConfig');
      if ( $p1 === false) {
         return false;
      }
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
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

   return true;
}

