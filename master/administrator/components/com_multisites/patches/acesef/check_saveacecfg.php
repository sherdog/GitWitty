<?php
/**
 * @file       check_saveacecfg.php
 * @brief      Check if the ACE SEF configuration wrapper is present.
 * @version    1.2.52
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
 * - V1.2.52 13-JAN-2010: Add compatibility with AceSEF 1.5.13
 *                        Now some patches can be ignored as the configuration is saved in the DB.
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkACESEFSaveAceCfg ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_ID') ...
 *   is present
 */
function jms2win_checkACESEFSaveAceCfg( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[IGNORE]|File Not Found';
	}
	
   $str = file_get_contents( $filename);

   // If the "AcesefConfig" is not present then this is not a AceSEF 1.5 verions
   $pos = strpos( $str, 'AcesefConfig');
   if ( $pos === false) {
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
      $result .= JText::_( 'Add the Multisites wrapper for the Global Configuration and Ace Configuration that might be modified by this source');
      $result .= '|[ACTION]';

      // If AceSEF < 1.5.13
      $pos = strpos( $str, 'AcesefUtility::storeConfig');
      if ($pos === false) {
         $result .= '|Replace 3 statements, that may write updates in the configurations, by 31+(2*32) lines containing the routing wrapper to the slave site.';
      }
      // If AceSEF >= 1.5.13
      else {
         $result .= '|Replace 1 statements, that may write updates in the configurations, by 31 lines containing the routing wrapper to the slave site.';
      }
   }
   
   return $rc .'|'. $result;
}



//------------ jms2win_actionACESEFSaveAecCfg ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionACESEFSaveAceCfg( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr_1 = jms2win_loadPatch( '..' .DS. 'acesef' .DS. 'patch_acecfg_1.php');
   if ( $patchStr_1 === false) {
      return false;
   }
   $patchStr_2 = jms2win_loadPatch( '..' .DS. 'acesef' .DS. 'patch_acecfg_2.php');
   if ( $patchStr_2 === false) {
      return false;
   }
   $patchStr_3 = $patchStr_2;

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
      // Store the configuration
      $file = JPATH_CONFIGURATION.DS.'configuration.php';
  		if (!JFile::write($file, $JoomlaConfig->toString('PHP', 'config', array('class' => 'JConfig')))) {
  			$msg = JText::_('Error writing Joomla! configuration');
  		}
		........
		........
		........
<1.5.13
      // Store the configuration
		$config = new JRegistry('config');
		$config->loadObject($AcesefConfig);
  		if (!JFile::write(JPATH_ACESEF_ADMIN.DS.'configuration.php', $config->toString('PHP', 'config', array('class' => 'AcesefConfig')))) {
>=1.5.13  		   
		AcesefUtility::storeConfig($AcesefConfig);
  		   
		........
		........
		........
<1.5.13
		// Store the configuration
		$config = new JRegistry('config');
		$config->loadObject($AcesefConfig);
		if (!JFile::write(JPATH_ACESEF_ADMIN.DS.'configuration.php', $config->toString('PHP', 'config', array('class' => 'AcesefConfig')))) {
>=1.5.13  		   
		AcesefUtility::storeConfig($AcesefConfig);
		........
		........
		........
		?>
      
      ===========
      and Replace by:
      ===========
		........
		........
//_jms2win_begin v1.2.43
      		// If this is a Slave Site, let use the standard forma
      		if ( defined( 'MULTISITES_ID')) {
         		$configStr = $JoomlaConfig->toString('PHP', 'config', array('class' => 'JConfig'))
      		}
      		else {
      		   // This is a Master website, so add the MULTISITE wrapper
         		$str = $JoomlaConfig->toString('PHP', 'config', array('class' => 'JConfig'))
         		$begPos = strpos( $str, 'class');
         		$endPos = strpos( $str, '?>');
               $configStr = substr( $str, 0, $begPos)
                          . "//_jms2win_begin v1.2.14\n"
                          . "if ( !defined( 'MULTISITES_ID')) {\n"
                          . "   if ( !defined( 'JPATH_MULTISITES')) define( 'JPATH_MULTISITES', (defined( 'JPATH_ROOT') ? JPATH_ROOT : dirname(__FILE__)) .DIRECTORY_SEPARATOR. 'multisites');\n"
                          . "   if ( !defined( '_EDWIN2WIN_')) define( '_EDWIN2WIN_', true);\n"
                          . "   @include( (defined( 'JPATH_ROOT') ? JPATH_ROOT : dirname(__FILE__)) .DIRECTORY_SEPARATOR. 'includes' .DIRECTORY_SEPARATOR. 'multisites.php');\n"
                          . "   if ( class_exists( 'Jms2Win')) Jms2Win::matchSlaveSite();\n"
                          . "}\n"
                          . "if ( (!isset( \$MULTISITES_FORCEMASTER) || !\$MULTISITES_FORCEMASTER)\n"
                          . "  && defined( 'MULTISITES_ID')\n"
                          . "  && file_exists(MULTISITES_CONFIG_PATH .DIRECTORY_SEPARATOR. 'configuration.php')) {\n"
                          . "   require_once( MULTISITES_CONFIG_PATH .DIRECTORY_SEPARATOR. 'configuration.php');\n"
                          . "} else if ( !class_exists( 'JConfig')) {\n"
                          . "//_jms2win_end\n"
                          . substr( $str, $begPos, $endPos-$begPos)
                          . "//_jms2win_begin v1.2.14\n"
                          . "}\n"
                          . "//_jms2win_end\n"
                          . "?>\n";
      		}
      		if (JFile::write($file, $configStr)) {
//_jms2win_end
 		........
		........
		........
<1.5.13
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
>=1.5.13  		   
		AcesefUtility::storeConfig($AcesefConfig);
		........
		........
		........
<1.5.13
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
>=1.5.13  		   
		AcesefUtility::storeConfig($AcesefConfig);
		........
		........
		........
	   }
   */
   
   // ------------- Patch definition ----------------
   /* ....\n
      \n .... if (!JFile::write($file, $JoomlaConfig->toString('PHP', 'config', array('class' => 'JConfig')))) { \n
      p0           p1                                                                                            p2
      
      \n .... if (!JFile::write(JPATH_ACESEF_ADMIN.DS.'configuration.php', $config->toString('PHP', 'config', array('class' => 'AcesefConfig')))) { \n
>=1.5.13           AcesefUtility::storeConfig($AcesefConfig);
      p3           p4                                                                                                                               p5

      \n .... if (!JFile::write(JPATH_ACESEF_ADMIN.DS.'configuration.php', $config->toString('PHP', 'config', array('class' => 'AcesefConfig')))) { \n
>=1.5.13           AcesefUtility::storeConfig($AcesefConfig);
      p6           p7                                                                                                                               p8

      Produce
      begin -> p0 + INSERT PATCH 1 
             + p2 -> p3 + INSERT PATCH 2 
             + p5 -> p6 + INSERT PATCH 3 
             + p8 -> end
      
    */
   
   // --- patch 1 ---
   // p1: Search for "JFile::write"
   $p1 = strpos( $content, 'JFile::write');
   if ( $p1 === false) {
      return false;
   }
   
   // P0: Go to Begin of line
   for ( $p0 = $p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
   $p0++;

   // p2: Search for end of line
   for ( $p2=$p1; $content[$p2] != "\n"; $p2++);

   // --- patch 2 ---
   // p4: Search for "JFile::write"
   $p4 = strpos( $content, 'JFile::write', $p2);
   if ( $p4 === false) {
      $p4 = strpos( $content, 'AcesefUtility::storeConfig', $p2);
      if ( $p4 === false) {
         return false;
      }
      $patchStr_2='';
   }
   
   // P3: Go to Begin of line
   for ( $p3 = $p4; $p3 > 0 && $content[$p3] != "\n"; $p3--);
   $p3++;

   // p5: Search for end of line
   for ( $p5=$p4; $content[$p5] != "\n"; $p5++);
 
   if ( empty( $patchStr_2)) {
      $patchStr_2 = substr( $content, $p3, $p5-$p3);
   }
 
   // --- patch 3 ---
   // p7: Search for "JFile::write"
   $p7 = strpos( $content, 'JFile::write', $p5);
   if ( $p7 === false) {
      $p7 = strpos( $content, 'AcesefUtility::storeConfig', $p5);
      if ( $p7 === false) {
         return false;
      }
      $patchStr_3='';
   }
   
   // P6: Go to Begin of line
   for ( $p6 = $p7; $p6 > 0 && $content[$p6] != "\n"; $p6--);
   $p6++;

   // p8: Search for end of line
   for ( $p8=$p7; $content[$p8] != "\n"; $p8++);
 
   if ( empty( $patchStr_3)) {
      $patchStr_3 = substr( $content, $p6, $p8-$p6);
   }

 
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p0)
           . $patchStr_1
           . substr( $content, $p2, $p3-$p2)
           . $patchStr_2
           . substr( $content, $p5, $p6-$p5)
           . $patchStr_3
           . substr( $content, $p8)
           ;

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

   return true;
}

