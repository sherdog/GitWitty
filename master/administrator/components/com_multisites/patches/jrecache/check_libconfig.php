<?php
/**
 * @file       check_libconfig.php
 * @brief      Check if the 'lib/config.php' files contains the Multi Sites patches 
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

//------------ jms2win_checkJRELibCfg ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_HOST'))
 *   is present
 */
function jms2win_checkJRELibCfg( $model, $file)
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
      $result .= JText::_( 'The wrapper that must be added in the JRE master configuration is not present.');
      $result .= '|[ACTION]';
      $result .= '|Add 19 lines to write the wrapper into the master JRE configuration file.';
   }
   
   return $rc .'|'. $result;
}


//------------ jms2win_actionJRELibCfg ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionJRELibCfg( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr_1 = jms2win_loadPatch( '..' .DS. 'jrecache' .DS. 'patch_libconfig_1.php');
   if ( $patchStr_1 === false) {
      return false;
   }

   $patchStr_2 = jms2win_loadPatch( '..' .DS. 'jrecache' .DS. 'patch_libconfig_2.php');
   if ( $patchStr_2 === false) {
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
       function saveConfig()
       {
           global $my;
   
           $config = "<?php\n\n";
           $config .= "if( defined( '_" . $this->_name . "') ) {\n return true;\n } else { \ndefine('_" . $this->_name . "',1); \n }\n";
           $config .= "class " . $this->_name . "\n{\n";
           $config .= "// Last Edit: " . strftime("%a, %Y-%b-%d %R") . "\n";

      		........
      		........
      		........
   
           $config .= "}\n";
           $config .= "?>";
      		........
      		........
       }
		........
		........
      
      ===========
      and Replace by:
      ===========

		........
		........
       function saveConfig()
       {
           global $my;
   
           $config = "<?php\n\n";
      //_jms2win_begin v1.2.12
      // If master website, add the wrapper to redirect to the appropriate config
      if ( !defined( 'MULTISITES_ID')) {
      $config .= "//_jms2win_begin v1.2.12\n";
      $config .= "if ( defined( 'MULTISITES_ID')\n";
      $config .= "  && file_exists( dirname(__FILE__) .DS. 'jrecache.config.' .MULTISITES_ID. '.php')) {\n";
      $config .= "   require_once(  dirname(__FILE__) .DS. 'jrecache.config.' .MULTISITES_ID. '.php');\n";
      $config .= "} else  {\n";
      $config .= "//_jms2win_end\n";
      }
      //_jms2win_end
           $config .= "if( defined( '_" . $this->_name . "') ) {\n return true;\n } else { \ndefine('_" . $this->_name . "',1); \n }\n";
           $config .= "class " . $this->_name . "\n{\n";
           $config .= "// Last Edit: " . strftime("%a, %Y-%b-%d %R") . "\n";

      		........
      		........
      		........
   
           $config .= "}\n";
      //_jms2win_begin v1.2.12
      // If master website, add the wrapper to redirect to the appropriate config
      if ( !defined( 'MULTISITES_ID')) {
      $config .= "//_jms2win_begin v1.2.12\n";
      $config .= "}\n";
      $config .= "//_jms2win_end\n";
      }
      //_jms2win_end
           $config .= "?>";
      		........
      		........
       }
		........
		........

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n .... function saveConfig() .....
                       p0              
      
      \n ....	$config = "<?php\n\n"; .... \n
                          p1               p2

      \n ....	$config .= "?>"; .... \n
      p3                  p4
      
      Produce
      begin -> p2 + INSERT PATCH no 1 + p2 -> p3 +  INSERT PATCH no 2 + p3 -> end
      
    */
   
   // p0: Search for "saveConfig"
   $p0 = strpos( $content, 'saveConfig');
   if ( $p0 === false) {
      return false;
   }

   // p2: Search for "< ? php"
   $p1 = strpos( $content, '"<'.'?php', $p0);
   if ( $p1 === false) {
      return false;
   }

   // p2: Search for "\n"
   $p2 = strpos( $content, "\n", $p1);
   if ( $p2 === false) {
      return false;
   }
   $p2++;

   // p4: Search for "? >"
   $p4 = strpos( $content, '"?'.'>"', $p2);
   if ( $p4 === false) {
      return false;
   }
    // P3: Go to Begin of line
   for ( $p3=$p4; $p3 > 0 && $content[$p3] != "\n"; $p3--);
   $p3++;
 
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p2)
           . $patchStr_1
           . substr( $content, $p2, $p3-$p2)
           . $patchStr_2
           . substr( $content, $p3)
           ;

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

   return true;
}

