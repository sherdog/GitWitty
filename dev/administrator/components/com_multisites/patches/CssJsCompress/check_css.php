<?php
/**
 * @file       check_css.php
 * @brief      Check if the 'system/CssJsCompress/css.php' files contains the Multi Sites patches 
 *             to compute the JPATH_BASE directory of a slave site.
 *
 * @version    1.2.39
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2010 Edwin2Win sprlu - all right reserved.
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
 * - V1.2.39  07-JUL-2010: Initial version
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkCssJsCompressCSS ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_HOST'))
 *   is present
 */
function jms2win_checkCssJsCompressCSS( $model, $file)
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
      $result .= JText::_( 'The specific root path for each websites is not present.');
      $result .= '|[ACTION]';
      $result .= '|Replace 3 lines by 30 lines to compute a specific root path for each slave site.';
   }
   
   return $rc .'|'. $result;
}


//------------ jms2win_actionCssJsCompressCSS ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionCssJsCompressCSS( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'CssJsCompress' .DS. 'patch_css.php');
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
      define('DS', DIRECTORY_SEPARATOR);
      define('PATH_ROOT', dirname(__FILE__) . DS);
      $file=PATH_ROOT.'..'.DS.'..'.DS.'..'.DS.'cache'.DS.'css'.DS.$cssFileName;
		........
      
      ===========
      and Replace by:
      ===========
		........
      //_jms2win_begin v1.2.39
      define('DS', DIRECTORY_SEPARATOR);
      // Try detect if this is a slave site and this should set the define MULTISITES_ID
      if ( !defined( 'MULTISITES_ID')) {
         if ( !defined( 'JPATH_MULTISITES')) define( 'JPATH_MULTISITES', dirname( dirname( dirname( dirname(__FILE__)))) .DIRECTORY_SEPARATOR. 'multisites');
         if ( !defined( '_EDWIN2WIN_')) define( '_EDWIN2WIN_', true);
         @include( dirname( dirname( dirname(dirname(__FILE__)))) .DIRECTORY_SEPARATOR. 'includes' .DIRECTORY_SEPARATOR. 'multisites.php');
         if ( defined( 'JMS2WIN_VERSION')) {
            if ( !defined( 'MULTISITES_ADMIN')) define( 'MULTISITES_ADMIN', true);
            if ( class_exists( 'Jms2Win')) Jms2Win::matchSlaveSite();
         }
      }
      
      // If this is a slave site, check if it has a specific deploy directory (if YES, use its path to compute the JPATH_BASE)
      if ( defined( 'MULTISITES_ID')) {
         if ( defined( 'MULTISITES_ID_PATH'))   { $filename = MULTISITES_ID_PATH.DIRECTORY_SEPARATOR.'config_multisites.php'; }
         else                                   { $filename = JPATH_MULTISITES.DS.MULTISITES_ID.DIRECTORY_SEPARATOR.'config_multisites.php'; }
         @include($filename);
         if ( isset( $config_dirs) && !empty( $config_dirs) && !empty( $config_dirs['deploy_dir'])) {
            define('JPATH_BASE', $config_dirs['deploy_dir']);
         }
         else {
            define('JPATH_BASE', dirname( dirname( dirname( dirname(__FILE__)))) );
         }
      }
      else {
         define('JPATH_BASE', dirname( dirname( dirname( dirname(__FILE__)))) );
      }
      $file=JPATH_BASE.DS.'cache'.DS.'css'.DS.$cssFileName;
      //_jms2win_end
		........

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n .... define('DS', DIRECTORY_SEPARATOR); .....\n
      p0                   p1
      
      \n .... $file=PATH_ROOT.'..'.DS.'..'.DS.'..'.DS.'cache'.DS.'css'.DS.$cssFileName; .....\n
                                                                          p5                 p6 
      
      Produce
      begin -> p0 + INSERT PATCH + (p6+1)-> end
      
    */
   
   // p1: Search for DIRECTORY_SEPARATOR
   $p1 = strpos( $content, "DIRECTORY_SEPARATOR");
   if ( $p1 === false) {
      return false;
   }

   // P0: Go to Begin of line
   for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
   $p0++;
 
   // p5: Search for $cssFileName
   $p5 = strpos( $content, '$cssFileName', $p1);
   if ( $p5 === false) {
      return false;
   }

   // p6: Search for "\n"
   $p6 = strpos( $content, "\n", $p5);
   if ( $p6 === false) {
      return false;
   }
   $p6++;

 
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p0)
           . $patchStr
           . substr( $content, $p6)
           ;

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

   return true;
}

