<?php
/**
 * @file       check_jconfig.php
 * @brief      Check if the Joomla Global Configuration option contain the patch 
 *             to write a wrapper in the master configuration.php file
 * @version    1.2.14
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
 * - V1.0.2 02-AUG-2008: File creation
 * - V1.1.0 07-NOV-2008: Change the master config wrapper to use the new slave site matching.
 * - V1.2.14 20-OCT-2009: Use the JPATH_ROOT in the patch when it is defined.
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkJConfig ---------------
/**
 * check if 'MULTISITES_' is present
 */
function jms2win_checkJConfig( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[NOK]|File Not Found';
	}
   $str = file_get_contents( $filename);
   
   // if 'MULTISITES_' is present
   $pos = strpos( $str, 'MULTISITES_');
   if ($pos === false) $wrapperIsPresent = false;
   else                $wrapperIsPresent = true;;
   
   $result = "";
   $rc = '[OK]';
   if ( !$wrapperIsPresent) {
	   $rc = '[NOK]';
      $result .= JText::_( 'The wrapper for the Master configuration is not present in Joomla administration');
      $result .= '|[ACTION]';
      $result .= '|Add 28 lines containing the wrapper to insert into the master configuration.php file';
      $result .= '|Update 1 line to save the appropriate configuration content';
   }
   else {
      // If patch 1.2.24 is not present
      $p1 = strpos( $str, 'JPATH_ROOT', $pos);
      if ($p1 === false) {
   	   $rc = '[NOK]';
         $result .= JText::_( 'The wrapper for the Master configuration is not present in Joomla administration');
         $result .= '|[ACTION]';
         $result .= '|replace 2 lines in master configuration.php file to use JPATH_ROOT when present';
      }
   }
   
   return $rc .'|'. $result;
}

//------------ jms2win_actionJConfig ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionJConfig( $model, $file)
{
   include_once( dirname(__FILE__) .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( 'patch_jconfig.php');
   if ( $patchStr === false) {
      return false;
   }

//	$filename = JPATH_ROOT.DS.$file;
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
   $content = file_get_contents( $filename);
   if ( $content === false) {
      return false;
   }
   
   $p1 = strpos( $content, 'MULTISITES_');
   if ( $p1 === false) {}
   else {
      // Remove potential exising patches
      $content = jms2win_removePatch( $content);
      
      // restore the original "JFile::write" statement
      // P1: Search begin statement: "JFile::write"
      $p1 = strpos( $content, 'JFile::write');
      if ( $p1 === false) {
         return false;
      }
      // P0: Go to Begin of line
      for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
      $p0++;

      // p2: Search for end of line
      for ( $p2=$p1; $content[$p2] != "\n"; $p2++);
      
      $content = substr( $content, 0, $p0)
               . "if (JFile::write(\$fname, \$config->toString('PHP', 'config', array('class' => 'JConfig')))) {\n"
               . substr( $content, $p2+1);
      
   }
   
   // Search/Replace the statement
   /*
      ===========
      Search for:
      ===========
		if (JFile::write($fname, $config->toString('PHP', 'config', array('class' => 'JConfig')))) {
      
      ===========
      and Replace by:
      ===========

		// If this is a Slave Site, let use the standard forma
		if ( defined( 'MULTISITES_ID')) {
   		$configStr = $config->toString('PHP', 'config', array('class' => 'JConfig'));
		}
		else {
		   // This is a Master website, so add the MULTISITE wrapper
   		$str = $config->toString('PHP', 'config', array('class' => 'JConfig'));
   		$begPos = strpos( $str, 'class');
   		$endPos = strpos( $str, '?>');
         $configStr = substr( $str, 0, $begPos)
                    . "if ( !defined( 'MULTISITES_ID')) {\n"
                    . "   if ( !defined( 'JPATH_MULTISITES')) define( 'JPATH_MULTISITES', dirname(__FILE__) .DIRECTORY_SEPARATOR. 'multisites');\n"
                    . "   define( '_JMS2WIN_', true);\n"
                    . "   @include( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'multisites.php');\n"
                    . "   Jms2Win::matchSlaveSite();\n"
                    . "}\n"
                    . "if ( !defined('MULTISITES_FORCEMASTER') && defined( 'MULTISITES_ID')\n"
                    . "  && file_exists(MULTISITES_CONFIG_PATH .DIRECTORY_SEPARATOR. 'configuration.php')) {\n"
                    . "   require_once( MULTISITES_CONFIG_PATH .DIRECTORY_SEPARATOR. 'configuration.php');\n"
                    . "} else {\n"
                    . substr( $str, $begPos, $endPos-$begPos)
                    . "}\n"
                    . "?>\n";
		}
		if (JFile::write($fname, $configStr)) {

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n      if ... ( ... JFile::write ... $config->toString ... JConfig ... ) ... ) .....\n
      p0                   p1               p2                    p3          p4    p5     p6
      
      Produce
   v1.1.0   begin -> p0 + INSERT PATCH + p0 -> p2 + $configStr + p5 -> end
   v1.2.24  begin -> p0 + INSERT PATCH + p5 -> end
      
    */
   // P1: Search begin statement: "JFile::write"
   $p1 = strpos( $content, 'JFile::write');
   if ( $p1 === false) {
      return false;
   }
   // P0: Go to Begin of line
   for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
   $p0++;
   
   // p2: Search for $config->toString
   $p2 = strpos( $content, '$config->toString', $p1);
   if ( $p2 === false) {
      return false;
   }

   // p3: Search for JConfig
   $p3 = strpos( $content, 'JConfig', $p2);
   if ( $p3 === false) {
      return false;
   }
   
   // p4: Search for Closing Parenthesis
   $p4 = strpos( $content, ')', $p3+1);
   if ( $p4 === false) {
      return false;
   }

   // p5: Search for Closing Parenthesis (End of $config->toString())
   $p5 = strpos( $content, ')', $p4+1);
   if ( $p5 === false) {
      return false;
   }

   // p6: Search for end of line
   for ( $p6=$p5; $content[$p6] != "\n"; $p6++);
   
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p0)
           . $patchStr
           . substr( $content, $p6+1);

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}
   
   return true;
}
