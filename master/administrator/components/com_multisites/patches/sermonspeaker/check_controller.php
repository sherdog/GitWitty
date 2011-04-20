<?php
/**
 * @file       check_controller.php
 * @brief      Check if the SermonSpeaker controller contain write the JMS wrapper in the configuration.
 * @version    1.2.39
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
 * - V1.2.13 13-OCT-2009: Initial version
 * - V1.2.39 12-JUL-2010: Starting with Sermon 3.4.1, they have used MVC and no more stored the configuration
 *                        in file on the disk.
 *                        So the patch can be ignored.
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkSermonController ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_ID') ...
 *   is present
 */
function jms2win_checkSermonController( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[IGNORE]|File Not Found';
	}
	
   $str = file_get_contents( $filename);

   // If the file does not contain the saveConfig function, assume this is a Sermon 3.4.1 or higher
   $pos = strpos( $str, 'saveConfig()');
   if ( $pos === false) {
      // In this case, discard the patch
	   return '[IGNORE]|File Not Found';
   }
   
   // if 'MULTISITES_ID' is present
   $pos = strpos( $str, 'MULTISITES_ID');
   if ($pos === false) $wrapperIsPresent = false;
   else                $wrapperIsPresent = true;
   
   $result = "";
   $rc = '[OK]';
   if ( !$wrapperIsPresent) {
	   $rc = '[NOK]';
      $result .= JText::_( 'The configuration wrapper is not present in the MASTER SermonSpeaker configuration menu.');
      $result .= '|[ACTION]';
      $result .= '|Add 54 lines containing the two routing wrapper to redirect on the two configuration files specific to each slave site.';
   }
   
   return $rc .'|'. $result;
}



//------------ jms2win_actionSermonController ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionSermonController( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr_1 = jms2win_loadPatch( '..' .DS. 'sermonspeaker' .DS. 'patch_controller_1.php');
   if ( $patchStr_1 === false) {
      return false;
   }

   $patchStr_2 = jms2win_loadPatch( '..' .DS. 'sermonspeaker' .DS. 'patch_controller_2.php');
   if ( $patchStr_2 === false) {
      return false;
   }
   $patchStr_2b = jms2win_loadPatch( '..' .DS. 'sermonspeaker' .DS. 'patch_controller_2b.php');
   if ( $patchStr_2b === false) {
      return false;
   }

   $patchStr_3 = jms2win_loadPatch( '..' .DS. 'sermonspeaker' .DS. 'patch_controller_3.php');
   if ( $patchStr_3 === false) {
      return false;
   }

   $patchStr_4 = jms2win_loadPatch( '..' .DS. 'sermonspeaker' .DS. 'patch_controller_4.php');
   if ( $patchStr_4 === false) {
      return false;
   }
   $patchStr_4b = jms2win_loadPatch( '..' .DS. 'sermonspeaker' .DS. 'patch_controller_4b.php');
   if ( $patchStr_4b === false) {
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
		function saveConfig() {
		........
		........
	   $configfile = "components/com_sermonspeaker/config.sermonspeaker.php";
	   $permission = is_writable($configfile);
	   if (!$permission) {
		   $this->setRedirect('index.php?option='.$option.'&task=config',"Configuration file not writeable!");
		   return;
	   }
	   $sermonresults = JRequest::getVar('sermonresults','post',string);
	   $limit_speaker = JRequest::getVar('limit_speaker','post',string);
		........
		........
	   $config .= "\n";
	   $config .= "class sermonConfig {\n";
		........
		........
		........
	   $config .= "} \n?";
	   $config .= ">";
		........
		........
		........
	   //Sermoncast Config
	   $cf = "components/com_sermonspeaker/sermoncastconfig.sermonspeaker.php";
	   $permission = is_writable($configfile);
	   if (!$permission) {
		   $this->setRedirect('index.php?option='.$option.'&task=config',"SermonCast configuration file not writeable!");
		   return;
	   }
	   $cache = JRequest::getVar('cache','post',string);
	   $cache_time = JRequest::getVar('cache_time','post',string);
		........
		........
		........
		........
		........
	   $config .= "\n";
	   $config .= "class sermonCastConfig {\n";
		........
		........
	   $config .= "} \n?";
	   $config .= ">";
		........
      
      ===========
      and Replace by:
      ===========
		........
		........
		function saveConfig() {
		........
		........
	   $configfile = "components/com_sermonspeaker/config.sermonspeaker.php";
	   $permission = is_writable($configfile);
	   if (!$permission) {
		   $this->setRedirect('index.php?option='.$option.'&task=config',"Configuration file not writeable!");
		   return;
	   }
//_jms2win_begin v1.2.13
if ( defined( 'MULTISITES_ID')) {
	   $configfile = "components/com_sermonspeaker/config.sermonspeaker." .MULTISITES_ID. ".php";
	   if ( file_exists( __FILE__.DS.$configfile)) {
   	   $permission = is_writable($configfile);
	   }
	   if (!$permission) {
		   $this->setRedirect('index.php?option='.$option.'&task=config', MULTISITES_ID . " Configuration file not writeable!");
		   return;
	   }
}
//_jms2win_end
	   $sermonresults = JRequest::getVar('sermonresults','post',string);
	   $limit_speaker = JRequest::getVar('limit_speaker','post',string);
		........
		........
	   $config .= "\n";
//_jms2win_begin v1.2.13
if ( !defined( 'MULTISITES_ID')) {
	   $config .= "//_jms2win_begin v1.2.13\n";
	   $config .= "if ( defined( 'MULTISITES_ID')\n";
	   $config .= "  && file_exists( dirname(__FILE__) .DS. 'config.sermonspeaker.' .MULTISITES_ID. '.php')) {\n";
	   $config .= "   require_once(  dirname(__FILE__) .DS. 'config.sermonspeaker.' .MULTISITES_ID. '.php');\n";
	   $config .= "} else  {\n";
	   $config .= "//_jms2win_end\n";
}
//_jms2win_end
	   $config .= "class sermonConfig {\n";
		........
		........
		........
//_jms2win_begin v1.2.13
if ( !defined( 'MULTISITES_ID')) {
	   $config .= "} \n";
}
//_jms2win_end
	   $config .= "} \n?";
	   $config .= ">";
		........
		........
		........
	   //Sermoncast Config
	   $cf = "components/com_sermonspeaker/sermoncastconfig.sermonspeaker.php";
	   $permission = is_writable($configfile);
	   if (!$permission) {
		   $this->setRedirect('index.php?option='.$option.'&task=config',"SermonCast configuration file not writeable!");
		   return;
	   }
//_jms2win_begin v1.2.13
if ( defined( 'MULTISITES_ID')) {
	   $cf = "components/com_sermonspeaker/sermoncastconfig.sermonspeaker." .MULTISITES_ID. ".php";
	   if ( file_exists( __FILE__.DS.$cf)) {
   	   $permission = is_writable($cf);
	   }
	   if (!$permission) {
		   $this->setRedirect('index.php?option='.$option.'&task=config', MULTISITES_ID . " SermonCast configuration file not writeable!");
		   return;
	   }
}
//_jms2win_end
	   $cache = JRequest::getVar('cache','post',string);
	   $cache_time = JRequest::getVar('cache_time','post',string);
		........
		........
		........
		........
		........
	   $config .= "\n";
//_jms2win_begin v1.2.13
if ( !defined( 'MULTISITES_ID')) {
	   $config .= "//_jms2win_begin v1.2.13\n";
	   $config .= "if ( defined( 'MULTISITES_ID')\n";
	   $config .= "  && file_exists( dirname(__FILE__) .DS. 'sermoncastconfig.sermonspeaker.' .MULTISITES_ID. '.php')) {\n";
	   $config .= "   require_once(  dirname(__FILE__) .DS. 'sermoncastconfig.sermonspeaker.' .MULTISITES_ID. '.php');\n";
	   $config .= "} else  {\n";
	   $config .= "//_jms2win_end\n";
}
//_jms2win_end
	   $config .= "class sermonCastConfig {\n";
		........
		........
//_jms2win_begin v1.2.13
if ( !defined( 'MULTISITES_ID')) {
	   $config .= "} \n";
}
//_jms2win_end
	   $config .= "} \n?";
	   $config .= ">";
		........

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n .... function saveConfig() { ...
                       p0

      \n .... $sermonresults = JRequest::getVar('sermonresults','post',string); ...
      p1      p2              
      
      \n .... class sermonConfig { ...
      p3      p4     

      \n .... $config .= "} \n?";  ...
      p5                  p6
       
      \n .... $cache = JRequest::getVar('cache','post',string); ...
      p7      p8         

      \n .... $config .= "class sermonCastConfig {\n" ...
      p9                  p10

      \n .... $config .= "} \n?";  ...
      p11                 p12

      
      Produce
      begin -> p1 + INSERT PATCH no 1 
       + p1 -> p3 + INSERT PATCH no 2
       + p3 -> p5 + INSERT PATCH no 2b
       + p5 -> p7 + INSERT PATCH no 3
       + p7 -> p9 + INSERT PATCH no 4
       + p9 -> p11 + INSERT PATCH no 4b
       + p11 -> end
      
    */
   
   // p0: Search for "saveConfig()"
   $p0 = strpos( $content, 'saveConfig()');
   if ( $p0 === false) {
      return false;
   }

   // p2: Search for "$sermonresults"
   $p2 = strpos( $content, '$sermonresults', $p0);
   if ( $p2 === false) {
      return false;
   }
   
   // P1: Go to Begin of line
   for ( $p1 = $p2; $p1 > 0 && $content[$p1] != "\n"; $p1--);
   $p1++;


   // p4: Search for "class sermonConfig"
   $p4 = strpos( $content, 'class sermonConfig', $p2);
   if ( $p4 === false) {
      return false;
   }
   
   // P3: Go to Begin of line
   for ( $p3 = $p4; $p3 > 0 && $content[$p3] != "\n"; $p3--);
   $p3++;

   // p6: Search for "} \n?"
   $p6 = strpos( $content, '} \n?', $p4);
   if ( $p6 === false) {
      return false;
   }
   
   // P5: Go to Begin of line
   for ( $p5 = $p6; $p5 > 0 && $content[$p5] != "\n"; $p5--);
   $p5++;


   // p8: Search for "$cache"
   $p8 = strpos( $content, '$cache', $p6);
   if ( $p8 === false) {
      return false;
   }
   
   // P7: Go to Begin of line
   for ( $p7 = $p8; $p7 > 0 && $content[$p7] != "\n"; $p7--);
   $p7++;
 
   // p10: Search for "class sermonCastConfig"
   $p10 = strpos( $content, 'class sermonCastConfig', $p8);
   if ( $p10 === false) {
      return false;
   }
   
   // P9: Go to Begin of line
   for ( $p9 = $p10; $p9 > 0 && $content[$p9] != "\n"; $p9--);
   $p9++;

   // p12: Search for "} \n?"
   $p12 = strpos( $content, '} \n?', $p10);
   if ( $p12 === false) {
      return false;
   }
   
   // P11: Go to Begin of line
   for ( $p11 = $p12; $p11 > 0 && $content[$p11] != "\n"; $p11--);
   $p11++;
 
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p1)
           . $patchStr_1
           . substr( $content, $p1, $p3-$p1)
           . $patchStr_2
           . substr( $content, $p3, $p5-$p3)
           . $patchStr_2b
           . substr( $content, $p5, $p7-$p5)
           . $patchStr_3
           . substr( $content, $p7, $p9-$p7)
           . $patchStr_4
           . substr( $content, $p9, $p11-$p9)
           . $patchStr_4b
           . substr( $content, $p11)
           ;

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

   return true;
}

