<?php
/**
 * @file       check_cbinstaller.php
 * @brief      Check if the Commuty Builder core 'cb.installer.php' files contains the Multi Sites patches.
 *
 * @version    1.2.33
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2010 Edwin2Win sprlu - all right reserved.
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
 * - V1.0.0  23-JUL-2008: File creation
 * - V1.2.12 04-OCT-2009: remove a warning
 * - V1.2.33 26-APR-2010: Replace the ereg by preg_match for PHP 5.3 compatibility
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkCBInstaller ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_HOST'))
 *   is present
 */
function jms2win_checkCBInstaller( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[IGNORE]|File Not Found';
	}
	
   $str = file_get_contents( $filename);
   
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
                        
   $result = "";
   $rc = '[OK]';
   $sep = "";
   $updateLine = 0;
   if ( !$ifdef_ms_host) {
	   $rc = '[NOK]';
      $result .= $sep . JText::_( 'The Community Builder Plugin Installer patch is not present');
      $sep = '|';
      $updateLine += 2;
   }
   
   // If there is some action to perform
   if ( $updateLine != 0) {
      $result .= '|[ACTION]';
      $result .= '|Add 5 lines to disable directory checking by Slave site';
      if ( $updateLine>0) {
         $result .= '|Update ' .$updateLine. ' lines to accept installation overwrite';
      }
   }
   
   return $rc .'|'. $result;
}

//------------ jms2win_actionCBInstaller ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionCBInstaller( $model, $file)
{
//	$filename = JPATH_ROOT.DS.$file;
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
   $content = file_get_contents( $filename);
   if ( $content === false) {
      return false;
   }
   
   // Search/Replace the statement
   /*
      Search for:
		if (file_exists($this->elementDir())) {
      		$this->setError( 1, 'Another plugin is already using directory: "' . $this->elementDir() . '"' );
			return false;
    	}
    	
    	....
		if(!$this->copyFiles($this->installDir(), $this->elementDir(), array($installfile_elemet->data())))  			{
    	....
		if(!$this->copyFiles($this->installDir(), $this->elementDir(), array($uninstallfile_elemet->data()))) {

      
      and Replace by:
if ( defined( 'MULTISITES_HOST')) {
   $jmsoverwrite = true;
}
else {
   $jmsoverwrite = false;
		if (file_exists($this->elementDir())) {
      		$this->setError( 1, 'Another plugin is already using directory: "' . $this->elementDir() . '"' );
			return false;
    	}
}

      ...
		if(!$this->copyFiles($this->installDir(), $this->elementDir(), array($installfile_elemet->data(), $jmsoverwrite)))  			{
    	....
		if(!$this->copyFiles($this->installDir(), $this->elementDir(), array($uninstallfile_elemet->data(), $jmsoverwrite))) {


   */
   
   // ------------- Patch no 1 ----------------
   // Search begin statement: "Another plugin is already using directory"
   $pos = strpos( $content, 'Another plugin is already using directory');
   if ( $pos === false) {
      return false;
   }
   // go in reverse order to find the "if (file_exists($this->elementDir()))"
   $beginPos = false;
   $state = 1;
   for ( $i=$pos; $i>0; $i--) {
      if ( $state == 1) {
         if ( substr( $content, $i, 11) == 'file_exists') {
            $state = 2;
         }
      }
      else {
         if ( substr( $content, $i, 2) == 'if') {
            $beginPos = $i;
            break;
         }
      }
   }
   if ( $beginPos === false) {
      return false;
   }
   // Go to Begin of line
   for ( ; $beginPos > 0 && $content[$beginPos] != "\n"; $beginPos--);
   $beginPos++;
   
   // Now search end of IF statement
   $pos = strpos( $content, '}', $pos);
   if ( $pos === false) {
      return false;
   }
   $endPos = strpos( $content, "\n", $pos);
   if ( $endPos === false) {
      return false;
   }
   
   $saveLines = substr( $content, $beginPos, $endPos-$beginPos+1);
   
   // Here, we found the statement and can replace its content
   $result = substr( $content, 0, $beginPos)
           . "if ( defined( 'MULTISITES_HOST')) {\n"
           . "   \$jmsoverwrite = true;\n"
           . "}\n"
           . "else {\n"
           . "   \$jmsoverwrite = false;\n"
           . $saveLines
           . "}\n"
           . substr( $content, $endPos+1);

   // ------------- Patch no 2 ----------------
   $pos = strpos( $result, '$this->copyFiles', $endPos);
   if ( $pos === false) {
      return false;
   }
   // go in reverse order to find the "if( ! $this->copyFiles ..."
   $beginPos = false;
   for ( $i=$pos; $i>0; $i--) {
      if ( substr( $result, $i, 2) == 'if') {
         $beginPos = $i;
         break;
      }
   }
   if ( $beginPos === false) {
      return false;
   }
   
   $endPos = strpos( $result, "\n", $beginPos);
   if ( $endPos === false) {
      return false;
   }
   // Here, we found the statement and can replace its content
   $result = substr( $result, 0, $beginPos)
           . 'if(!$this->copyFiles($this->installDir(), $this->elementDir(), array($installfile_elemet->data(), $jmsoverwrite))) {' . "\n"
           . substr( $result, $endPos+1);
   
   // ------------- Patch no 3 ----------------
   $pos = strpos( $result, '$this->copyFiles', $endPos);
   if ( $pos === false) {
      return false;
   }
   // go in reverse order to find the "if( ! $this->copyFiles ..."
   $beginPos = false;
   for ( $i=$pos; $i>0; $i--) {
      if ( substr( $result, $i, 2) == 'if') {
         $beginPos = $i;
         break;
      }
   }
   if ( $beginPos === false) {
      return false;
   }
   $endPos = strpos( $result, "\n", $beginPos);
   if ( $endPos === false) {
      return false;
   }
   // Here, we found the statement and can replace its content
   $result = substr( $result, 0, $beginPos)
           . 'if(!$this->copyFiles($this->installDir(), $this->elementDir(), array($uninstallfile_elemet->data(), $jmsoverwrite))) {' . "\n"
           . substr( $result, $endPos+1);

   
   // ------------- Write the PATCH results ----------------
   $fp = fopen( $filename, "w");
   if ( $fp === false) {
      return false;
   }
   fputs( $fp, $result);
   fclose( $fp);
   
   return true;
}
