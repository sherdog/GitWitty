<?php
/**
 * @file       check_virtuemart_cfg.php
 * @brief      Check if the VirtueMart configuration wrapper is present.
 * @version    1.0.11
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
 * - V1.0.11 25-OCT-2008: Initial version
 * - V1.1.00 07-NOV-2008: Update the comment
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkVMCfgWrapper ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_ID') ...
 *   is present
 */
function jms2win_checkVMCfgWrapper( $model, $file)
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
      $result .= JText::_( 'The routing wrapper is not present in the VirtueMart MASTER configuration file.');
      $result .= '|[ACTION]';
      $result .= '|Add 5 lines containing the routing wrapper to the slave site.';
   }
   
   return $rc .'|'. $result;
}



//------------ jms2win_actionVMCfgWrapper ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionVMCfgWrapper( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'virtuemart' .DS. 'patch_virtuemart_cfg.php');
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
		global \$mosConfig_absolute_path
		........
		........
      define( 'URL', '................' );
      define( 'SECUREURL', '................' );
		........
		........
		?>
      
      ===========
      and Replace by:
      ===========
		........
		........
		........
      if ( defined( 'MULTISITES_ID') && file_exists( dirname(__FILE__) .DS. 'virtuemart.' .MULTISITES_ID. '.cfg.php')) {
         include_once( dirname(__FILE__) .DS. 'virtuemart.' .MULTISITES_ID. '.cfg.php');
      } else {
		global \$mosConfig_absolute_path
		........
		........
      if ( defined( 'MULTISITES_HOST')) {
         define( 'URL', 'http://'.MULTISITES_HOST.'/' );
         define( 'SECUREURL', 'http://'.MULTISITES_HOST.'/' );
      }
      else {
         define( 'URL', '................' );
         define( 'SECUREURL', '................' );
      }      
		........
		........
	   }
		?>

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n .... global \$mosConfig_absolute_path ..... 
      p0      p1       
      
      \n .... define( 'URL'    .... SECUREURL ..... \n
      p2      p3                    p4              p5

      \n .... ?>;
      p6      p7
      
      Produce
      begin -> p0 + INSERT PATCH + p0 -> p2 
      + PATCH_no_2 + p2 -> p5 + "}" + p5 -> p6 + "}" + p6 -> end
      
    */
   
   // Search begin statement: fopen ...
   // p1: Search for global $mosConfig_absolute_path
   $p1 = strpos( $content, 'global $mosConfig_absolute_path');
   if ( $p1 === false) {
      return false;
   }

   // P0: Go to Begin of line
   for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
   $p0++;

 
   // p3: Search for "define( 'URL'"
   $p3 = strpos( $content, "define( 'URL'", $p1);
   if ( $p3 === false) {
      return false;
   }
 
   // P2: Go to Begin of line
   for ( $p2=$p3; $p2 > 0 && $content[$p2] != "\n"; $p2--);
   $p2++;
 
   // p4: Search for "SECUREURL"
   $p4 = strpos( $content, "SECUREURL", $p3);
   if ( $p4 === false) {
      return false;
   }

   // p5: Search for "\n"
   $p5 = strpos( $content, "\n", $p4);
   if ( $p5 === false) {
      return false;
   }
 
   // p7: Search for '? >'
   $p7 = strpos( $content, '?>', $p5);
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
           . substr( $content, $p0, $p2-$p0)
           // Patch No 2
           . "if ( defined( 'MULTISITES_HOST')) {\n"
           . "   define( 'URL', 'http://'.MULTISITES_HOST.'/' );\n"
           . "   define( 'SECUREURL', 'http://'.MULTISITES_HOST.'/' );\n"
           . "} else {\n"
           . substr( $content, $p2, $p5-$p2)
           . "\n}\n"
           . substr( $content, $p5, $p6-$p5)
           . "}\n"
           . substr( $content, $p6)
           ;

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

   return true;
}

