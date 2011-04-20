<?php
/**
 * @file       check_config.php
 * @brief      Check if the VirtueMart core 'ps_config.php' files contains the Multi Sites patches.
 *
 * @version    1.2.33
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
 * - V1.0.0  15-JUL-2008: Initial version
 * - V1.0.11 25-OCT-2008: Replace the limited patch on URL and SECURE_URL by a complete wrapper
 *                        on virtuemart.cfg.php file that allow a slave site change any configuration parameters.
 * - V1.0.12 31-OCT-2008: Mixt implementation V1.0.0 & V1.0.11 to solve problem when slave configuration does not exists yet.
 *                        In this case the VM configuration admnistrator was routed to the "master" website rather than the slave site.
 * - V1.2.14 20-OCT-2009: Refine the patch to detect the new VM 1.1.4 implementation.
 *                        They have replaced
 *                        if (!$fp = fopen(ADMINPATH ."virtuemart.cfg.php", "w")) {
 *                        by
 *                        if (!is_writable(ADMINPATH ."virtuemart.cfg.php")) {
 * - V1.2.33 26-APR-2010: Replace the ereg by preg_match for PHP 5.3 compatibility
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkVMConfig ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_HOST'))
 *   is present
 */
function jms2win_checkVMConfig( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[IGNORE]|File Not Found';
	}
	
   $str = file_get_contents( $filename);
   
   // Older JMS Patch 1.0.0
   // if ( defined( 'MULTISITES_HOST')) { ...
   $ifdef_ms_host  = preg_match( '#'
                        . 'if'
                        . '([[:space:]])*'
                        . '\('
                        . '([[:space:]])*'
                        . 'defined'
                        . '\('
                        . '([[:space:]])*'
                        . '\'MULTISITES_HOST\''
                        . '([[:space:]])*'
                        . '\)'
                        . '([[:space:]])*'
                        . '\)'
                        . '#', 
                        $str);
   
   // New JMS Patch 1.0.11
   // if ( defined( 'MULTISITES_ID')) { ...
   $ifdef_ms_id   = preg_match( '#'
                        . 'if'
                        . '([[:space:]])*'
                        . '\('
                        . '([[:space:]])*'
                        . 'defined'
                        . '\('
                        . '([[:space:]])*'
                        . '\'MULTISITES_ID\''
                        . '([[:space:]])*'
                        . '\)'
                        . '([[:space:]])*'
                        . '\)'
                        . '#', 
                        $str);
                        
   $result = "";
   $rc = '[OK]';
   $sep = "";
   $updateLine = 0;
   // If new Patch is present
   if ( $ifdef_ms_id) {}
   else {
      // If Older patch is present
      if ( $ifdef_ms_host) {
   	   $rc = '[NOK]';
         $result .= JText::_( 'JMS contain an older VirtueMart patch that must be replaced.');
         $result .= '|The new patch save the VirtueMart configuration file into a specific slave one and a wrapper is added into the master VM configuration file.';

/*
         // Try to restore the original file to remove the patch
         $backup_dir = JPath::clean( JPATH_MUTLISITES_COMPONENT .DS. 'backup');
         $src  = JPath::clean( $backup_dir .DS. $file);
         $dest = JPath::clean( JPATH_ROOT .DS. $file);
         // Try to replace the file with the backup one
         if (!$model->file_copy($src, $dest)) {
            $result .= '|The original VirtueMart ps_config.php files can not be restored.';
			}
			else {
            $result .= '|The original VirtueMart files has been restored.';
			}
*/

         $result .= '|[ACTION]';
         $result .= '|replace 2 lines by 27 lines to write the configuration file into a slave specific file and to compute the master site wrapper code.';
         $result .= '|Add 4 lines to insert the wrapper into the master virtuemart configuration file.';
         
      }
      // If NO patch installed
      else {
   	   $rc = '[NOK]';
         $result .= JText::_( 'The VirtueMart configuration file wrapper is not present.');
         $result .= '|[ACTION]';
         $result .= '|replace 2 lines by 17 lines to write the configuration file into a slave specific file and to compute the master site wrapper code.';
         $result .= '|Add 4 lines to insert the wrapper into the master virtuemart configuration file.';
      }
   }
   
   return $rc .'|'. $result;
}


//------------ jms2win_actionVMConfig_remove_v1_0_0 ---------------
/**
 * This undo the patch on V1.0.0 before installing the V1.0.11
 */
function jms2win_actionVMConfig_remove_v1_0_0( &$content)
{
   
   // Search/Replace the statement
   /*
      ===========
      Search for:
      ===========
      if ( defined( 'MULTISITES_HOST')) {
         define( 'URL', 'http://'.MULTISITES_HOST.'/' );
         define( 'SECUREURL', '". ((strncmp( $d['conf_SECUREURL'], 'https',5) == 0) ? 'https://' : 'http://')."'.MULTISITES_HOST.'/' );
      }
      else {
      define( 'URL', $url );
      define( 'SECUREURL', '".$db->getEscaped($d['conf_SECUREURL'])."' );
      }
      
      ===========
      and Replace by:
      ===========
      define( 'URL', $url );
      define( 'SECUREURL', '".$db->getEscaped($d['conf_SECUREURL'])."' );
   */   

   // ------------- Patch deinition ----------------
   /* ....\n
      \n      ... MULTISITES_HOST ...else ....{  .... }
      p0          p1                 p2       p3      p4
      
      Produce
      begin -> p0 + p3+1 -> p4-1 + p4+1 -> end
      
    */
   
   // Search begin statement: MULTISITES_HOST ...
   // P1: Search begin statement: "JFile::write"
   $p1 = strpos( $content, 'MULTISITES_HOST');
   if ( $p1 === false) {
      return false;
   }

   // P0: Go to Begin of line
   for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
   $p0++;

   // Search begin statement: else ...
   // P2: Search begin statement: "JFile::write"
   $p2 = strpos( $content, 'else', $p1);
   if ( $p2 === false) {
      return false;
   }

   // P3: Go to "{"
   for ( $p3=$p2; $content[$p3] != "{"; $p3++);

   // P4: Go to "}"
   for ( $p4=$p3; $content[$p4] != "}"; $p4++);
   
   $content = substr( $content, 0, $p0)
            . substr( $content, $p3+1, $p4-$p3-1)
            . substr( $content, $p4+1);
}

//------------ jms2win_actionVMConfig_v1_2_14 ---------------
/**
 * V1.2.14 Patch installation
 */
function jms2win_actionVMConfig_v1_2_14( $model, $file, &$content)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   // Patch for VM 1.1.3 and before
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'virtuemart' .DS. 'patch_config.php');
   if ( $patchStr === false) {
      return false;
   }

   // Patch for VM 1.1.4
   $patchStr14_1 = jms2win_loadPatch( '..' .DS. 'virtuemart' .DS. 'patch_config14_1.php');
   if ( $patchStr14_1 === false) {
      return false;
   }
   
   $patchStr14_2 = jms2win_loadPatch( '..' .DS. 'virtuemart' .DS. 'patch_config14_2.php');
   if ( $patchStr14_2 === false) {
      return false;
   }
   
   // Search/Replace the statement
   /*
      ===========
      Search for:
      ===========
VM 1.1.4
		if (!is_writable(ADMINPATH ."virtuemart.cfg.php")) {
			$vmLogger->err( $VM_LANG->_('VM_CONFIGURATION_CHANGE_FAILURE',false).' ('. ADMINPATH ."virtuemart.cfg.php)" );
			return false;
		}
VM < 1.1.4
		if (!$fp = fopen(ADMINPATH ."virtuemart.cfg.php", "w")) {			
			$vmLogger->err( $VM_LANG->_('VM_CONFIGURATION_CHANGE_FAILURE',false).' ('. ADMINPATH ."virtuemart.cfg.php)" );
			return false;
		}
		........
		........
		........
		global \$mosConfig_absolute_path
		........
		........
      define( 'URL', $url );
      define( 'SECUREURL', '".$db->getEscaped($d['conf_SECUREURL'])."' );
		........
		........
		$config .= "?>";
      
      ===========
      and Replace by:
      ===========
		// If slave site
		if ( defined( 'MULTISITES_ID')) {
		   $config_filename     = ADMINPATH .'virtuemart.' .MULTISITES_ID. '.cfg.php';
		   $master_wrapper      = '';
		   $master_wrapper_end  = '';
		}
		else {
		   $config_filename     = ADMINPATH .'virtuemart.cfg.php';
		   $master_wrapper      = "if ( defined( 'MULTISITES_ID') && file_exists( dirname(__FILE__) .DS. 'virtuemart.' .MULTISITES_ID. '.cfg.php')) {\n"
		                        . "   include_once( dirname(__FILE__) .DS. 'virtuemart.' .MULTISITES_ID. '.cfg.php');\n"
		                        . "} else {\n"
		                        ;
		   $master_wrapper_end  = "}\n";
		}
		if (!is_writable( $config_filename)) {
			$vmLogger->err( $VM_LANG->_('VM_CONFIGURATION_CHANGE_FAILURE',false).' ('. $config_filename .')' );
			return false;
		}
		........
		........
		........
		$master_wrapper
		global \$mosConfig_absolute_path
		........
		........
      if ( defined( 'MULTISITES_HOST')) {
         define( 'URL', 'http://'.MULTISITES_HOST.'/' );
         define( 'SECUREURL', '". ((strncmp( $d['conf_SECUREURL'], 'https',5) == 0) ? 'https://' : 'http://')."'.MULTISITES_HOST.'/' );
      }
      else {
         define( 'URL', \$mosConfig_live_site.\$app );
         define( 'SECUREURL', '".$db->getEscaped($d['conf_SECUREURL'])."' );
      }      
		........
		........
		$config .= $master_wrapper_end;
		$config .= "?>";

   */
   
   // ------------- Patch definition ----------------
   /* ....\n
		\n      ....is_writable(ADMINPATH ...\n ....  return false\n
      \n      ... fopen(ADMINPATH .........\n ....  return false\n
      p0          p1                       p2       p3
      
      \n .... global \$mosConfig_absolute_path .....
      p4      p5                                    

      \n define( \'URL\ ..... getEscaped ....; ..... \n
         p6                   p7             p8      p9

      \n.... $config .= "?>";
      p10    p11

      \n.... file_put_contents(ADMINPATH ."virtuemart.cfg.php", $config ); ....\n
      p12    p13                                                               14
             
      Produce
      begin -> p0 + INSERT PATCH + p2 -> p4 + "$master_wrapper" + p4 -> p6 
      + "\$master_url_wrapper\n"
      + $saveLines
      + p9 -> p10
      + "$config .= $master_wrapper_end;" 
      + p10 -> end
      
    */
   
   // Search begin statement VM 1.1.4: is_writable ...
   $p1 = strpos( $content, 'is_writable');
   if ( $p1 === false) {
      // Test for VM < 1.1.4
      // P1: Search begin statement: "fopen"
   
      $p1 = strpos( $content, 'fopen');
      if ( $p1 === false) {
         return false;
      }
   }


   // P0: Go to Begin of line
   for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
   $p0++;

 
   // p3: Search for return false
   $p3 = strpos( $content, 'return false', $p1);
   if ( $p3 === false) {
      return false;
   }
   
   // Cross check that ADMINPATH is present in the section to replace
   $tmp = substr( $content, $p0, $p3-$p0);
   $tpos = strpos( $tmp, 'ADMINPATH');
   if ( $tpos === false) {
      return false;
   }

   // P2: Go to Begin of line
   for ( $p2=$p3; $p2 > 0 && $content[$p2] != "\n"; $p2--);
   $p2++;

   // p5: Search for global \$mosConfig_absolute_path
   $p5 = strpos( $content, 'global \\$mosConfig_absolute_path', $p3);
   if ( $p5 === false) {
      return false;
   }

   // P4: Go to Begin of line
   for ( $p4=$p5; $p4 > 0 && $content[$p4] != "\n"; $p4--);
   $p4++;

   // P6: Search begin statement: define( 'URL', ...
   $p6 = strpos( $content, 'define( \'URL\',', $p5);
   if ( $p6 === false) {
      return false;
   }
   // P7: Skip content until : getEscaped
   $p7 = strpos( $content, 'getEscaped', $p6);
   if ( $p7 === false) {
      return false;
   }
   // P8: Now search end of statement
   $p8 = strpos( $content, ';', $p7);
   if ( $p8 === false) {
      return false;
   }

   // P9: Go to End of line
   $p9 = strpos( $content, "\n", $p8);
   if ( $p9 === false) {
      return false;
   }
   
   $saveLines = substr( $content, $p6, $p9-$p6+1);

   // p11: Search for $config .= "? >";
   $p11 = strpos( $content, '$config .= "?>";', $p9);
   if ( $p11 === false) {
      return false;
   }

   // P10: Go to Begin of line
   for ( $p10=$p11; $p10 > 0 && $content[$p10] != "\n"; $p10--);
   $p10++;

 
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch

   // If not VM 1.1.4 (<= VM 1.1.3)
   // p13: Search for "file_put_contents";
   $p13 = strpos( $content, 'file_put_contents', $p11);
   if ( $p13 === false) {
      // VM 1.1.3 or lower
      $result = substr( $content, 0, $p0)
              . $patchStr
              . substr( $content, $p2, $p4-$p2)
              . "\$master_wrapper\n"
              . substr( $content, $p4, $p6-$p4)
              // Begin Patch no 2
              . "\$master_url_wrapper\n"
              . $saveLines
              . "\$master_url_wrapper_end\n"
              . substr( $content, $p9, $p10-$p9)
              // End Patch no 2
              . "         \$config .= \$master_wrapper_end;\n"
              . substr( $content, $p10);
   }
   // Else VM 1.1.4
   else {
      // P12: Go to Begin of line
      for ( $p12=$p13; $p12 > 0 && $content[$p12] != "\n"; $p12--);
      $p12++;

      // P14: Go to End of line
      $p14 = strpos( $content, "\n", $p13);
      if ( $p14 === false) {
         return false;
      }


      $result = substr( $content, 0, $p0)
              . $patchStr14_1
              . substr( $content, $p2, $p4-$p2)
              . "\$master_wrapper\n"
              . substr( $content, $p4, $p6-$p4)
              // Begin Patch no 2
              . "\$master_url_wrapper\n"
              . $saveLines
              . "\$master_url_wrapper_end\n"
              . substr( $content, $p9, $p10-$p9)
              // End Patch no 2
              . "         \$config .= \$master_wrapper_end;\n"
              . substr( $content, $p10, $p12-$p10)
              . $patchStr14_2
              . substr( $content, $p14);
   }

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

   return true;
}


//------------ jms2win_actionVMConfig ---------------
function jms2win_actionVMConfig( $model, $file)
{
//	$filename = JPATH_ROOT.DS.$file;
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
   $content = file_get_contents( $filename);
   if ( $content === false) {
      return false;
   }
   
   // If Old JMS patch V1.0.0 is installed (MULTISITES_ID is NOT present AND MULTISITES_HOST is present)
   if ( strstr( $content, 'MULTISITES_ID') === false
     && strstr( $content, 'MULTISITES_HOST') !== false) {
      jms2win_actionVMConfig_remove_v1_0_0( $content);
   }
   
   return jms2win_actionVMConfig_v1_2_14( $model, $file, $content);
}