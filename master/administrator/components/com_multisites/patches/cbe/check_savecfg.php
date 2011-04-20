<?php
/**
 * @file       check_savecfg.php
 * @brief      Check if the 'administrator/components/com_cbe/admin.cbe.php' files contains the Multi Sites patches 
 *             to allow saving specific configuration file for each websites.
 *
 * @version    1.2.52
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Jms Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2011 Edwin2Win sprlu - all right reserved.
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
 * - V1.2.52  07-JAN-2011: Initial version
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkCBESaveCfg ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_HOST'))
 *   is present
 */
function jms2win_checkCBESaveCfg( $model, $file)
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
      $result .= JText::_( 'The CBE specific saving of configuration ("ue_config.php" and "enhanced_config.php") for each websites is not present.');
      $result .= '|[ACTION]';
      $result .= '|Replace 41 lines by 86 to save the specific configuration file for each slave site.';
   }
   
   return $rc .'|'. $result;
}


//------------ jms2win_actionCBESaveCfg ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionCBESaveCfg( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr_1 = jms2win_loadPatch( '..' .DS. 'cbe' .DS. 'patch_savecfg_1.php');
   if ( $patchStr_1 === false) {
      return false;
   }

   $patchStr_2 = jms2win_loadPatch( '..' .DS. 'cbe' .DS. 'patch_savecfg_2.php');
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
   	function saveEnhancedConfig() {
      	global $mainframe;
      	//Add code to check if config file is writeable.
      	$configfile = "components/com_cbe/enhanced_admin/enhanced_config.php";
      	@chmod ($configfile, 0766);
      	if (!is_writable($configfile)) {
      		//mosRedirect("index2.php?option=$option", "FATAL ERROR: Config File Not writeable" );
      		$mainframe->redirect( "index.php?option=$option", "FATAL ERROR: Config File not writeable");
      
      	}
      
      	$txt = "<?php\n";
      	foreach ($_POST as $k=>$v) {
      		if (strpos( $k, 'cfg_' ) === 0) {
      			if (!get_magic_quotes_gpc()) {
      				$v = addslashes( $v );
      			}
      			$txt .= "\$enhanced_Config['".substr( $k, 4 )."']='$v';\n";
      		}
      	}
      	$txt .= "?>";
   		........
   		........
   		........
   		........
   	}
		........
		........
      function saveConfig ( $option ) {
      	global $mainframe;
      
      	//Add code to check if config file is writeable.
      	$configfile = "components/com_cbe/ue_config.php";
      	@chmod ($configfile, 0766);
      	if (!is_writable($configfile)) {
      		//mosRedirect("index2.php?option=$option", "FATAL ERROR: Config File Not writeable" );
      		$mainframe->redirect( "index.php?option=$option", "FATAL ERROR: Config File not writeable");
      	}
      
      	$txt = "<?php\n";
      	foreach ($_POST as $k=>$v) {
      		if (strpos( $k, 'cfg_' ) === 0) {
      			if (!get_magic_quotes_gpc()) {
      				$v = addslashes( $v );
      			}
      			$txt .= "\$ueConfig['".substr( $k, 4 )."']='$v';\n";
      		}
      	}
      	$txt .= "?>";
		........
		........
		........
      
      ===========
      and Replace by:
      ===========
		........
		........
		........
      function saveEnhancedConfig ( $option ) {
      
      	global $mainframe;
      //_jms2win_begin v1.2.52
      	//Add code to check if config file is writeable.
      	$patchBegin = '';
      	$patchEnd   = '';
      	if ( defined( 'MULTISITES_ID')) {
         	$configfile          =	"components/com_cbe/enhanced_admin/enhanced_config_" . MULTISITES_ID . ".php";
         	$configfile_master   =	"components/com_cbe/enhanced_admin/enhanced_config.php";
      		// If the slave site params_ini file does not exists and a master one exists
            jimport('joomla.filesystem.file');
            if ( !JFile::exists( $configfile) && JFile::exists( $configfile_master)) {
               // Duplicate the master file as slave site
               JFile::copy( $configfile_master, $configfile);
            }
      	}
      	else {
         	$configfile = "components/com_cbe/enhanced_admin/enhanced_config.php";
         	$patchBegin = "if ( defined( 'MULTISITES_ID') && file_exists( dirname( __FILE__).DS.'enhanced_config_' . MULTISITES_ID . '.php')) {\n"
         	            . "   include( dirname( __FILE__).DS.'enhanced_config_' . MULTISITES_ID . '.php');\n"
         	            . "} else {\n"; 
         	            ;
         	$patchEnd   = "}\n";
      	}
      
      	@chmod ($configfile, 0766);
      	if (!is_writable($configfile)) {
      		//mosRedirect("index2.php?option=$option", "FATAL ERROR: Config File Not writeable" );
      		$mainframe->redirect( "index.php?option=$option", "FATAL ERROR: Config File not writeable");
      
      	}
      
      	$txt = "<?php\n";
      	$txt .= $patchBegin;
      	foreach ($_POST as $k=>$v) {
      		if (strpos( $k, 'cfg_' ) === 0) {
      			if (!get_magic_quotes_gpc()) {
      				$v = addslashes( $v );
      			}
      			$txt .= "\$enhanced_Config['".substr( $k, 4 )."']='$v';\n";
      		}
      	}
      	$txt .= $patchEnd;
      	$txt .= "?>";
		........
		........
		........
		........
      function saveConfig ( $option ) {
      	global $mainframe;
      
      //_jms2win_begin v1.2.52
      	//Add code to check if config file is writeable.
      	$patchBegin = '';
      	$patchEnd   = '';
      	if ( defined( 'MULTISITES_ID')) {
         	$configfile          =	"components/com_cbe/ue_config_" . MULTISITES_ID . ".php";
         	$configfile_master   =	"components/com_cbe/ue_config.php";
      		// If the slave site params_ini file does not exists and a master one exists
            jimport('joomla.filesystem.file');
            if ( !JFile::exists( $configfile) && JFile::exists( $configfile_master)) {
               // Duplicate the master file as slave site
               JFile::copy( $configfile_master, $configfile);
            }
      	}
      	else {
         	$configfile = "components/com_cbe/ue_config.php";
         	$patchBegin = "if ( defined( 'MULTISITES_ID') && file_exists( dirname( __FILE__).DS.'ue_config_' . MULTISITES_ID . '.php')) {\n"
         	            . "   include( dirname( __FILE__).DS.'ue_config_' . MULTISITES_ID . '.php');\n"
         	            . "} else {\n"; 
         	            ;
         	$patchEnd   = "}\n";
      	}
      
      	@chmod ($configfile, 0766);
      	if (!is_writable($configfile)) {
      		//mosRedirect("index2.php?option=$option", "FATAL ERROR: Config File Not writeable" );
      		$mainframe->redirect( "index.php?option=$option", "FATAL ERROR: Config File not writeable");
      	}
      
      	$txt = "<?php\n";
      	$txt .= $patchBegin;
      	foreach ($_POST as $k=>$v) {
      		if (strpos( $k, 'cfg_' ) === 0) {
      			if (!get_magic_quotes_gpc()) {
      				$v = addslashes( $v );
      			}
      			$txt .= "\$ueConfig['".substr( $k, 4 )."']='$v';\n";
      		}
      	}
      	$txt .= $patchEnd;
      	$txt .= "?>";
      //_jms2win_end

		........
		........
		........
		........
   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n .... function saveEnhancedConfig .....\n
              p1                                p2
      
      \n .... if ($fp = fopen( $configfile, "w")) { .....\n
      p3                p4                               
      
      \n .... function saveConfig ( $option ) { .....\n
              p7                                      p8
      
      \n .... if ($fp = fopen( $configfile, "w")) { .....\n
      p9                p10                             
         
            
      Produce
      begin -> p2 + INSERT PATCH 1 + p3 -> p8 + INSERT PATCH 2 + p9 -> end
      
    */
   
   // p1: Search for "function saveEnhancedConfig"
   $p1 = strpos( $content, 'function saveEnhancedConfig');
   if ( $p1 === false) {
      return false;
   }

   // p2: Search for "\n"
   $p2 = strpos( $content, "\n", $p1);
   if ( $p2 === false) {
      return false;
   }
   $p2++;
 
    // p4: Search for "fopen"
   $p4 = strpos( $content, 'fopen', $p2);
   if ( $p4 === false) {
      return false;
   }

   // P3: Go to Begin of line
   for ( $p3=$p4; $p3 > 0 && $content[$p3] != "\n"; $p3--);
   $p3++;


   // p7: Search for "function saveConfig"
   $p7 = strpos( $content, 'function saveConfig', $p4);
   if ( $p7 === false) {
      return false;
   }

   // p8: Search for "\n"
   $p8 = strpos( $content, "\n", $p7);
   if ( $p8 === false) {
      return false;
   }
   $p8++;

    // p10: Search for "fopen"
   $p10 = strpos( $content, 'fopen', $p8);
   if ( $p10 === false) {
      return false;
   }

   // P9: Go to Begin of line
   for ( $p9=$p10; $p9 > 0 && $content[$p9] != "\n"; $p9--);
   $p3++;

   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p2)
           . $patchStr_1
           . substr( $content, $p3, $p8-$p3)
           . $patchStr_2
           . substr( $content, $p9)
           ;

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

   return true;
}

