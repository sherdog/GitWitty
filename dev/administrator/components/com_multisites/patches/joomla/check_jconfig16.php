<?php
/**
 * @file       check_jconfig16.php
 * @brief      Check if the Joomla Global Configuration option contain the patch 
 *             to write a wrapper in the master configuration.php file
 * @version    1.2.52
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.6.x installation using multiple configuration (One for each 'slave' sites).
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
 * - V1.0.2 02-AUG-2008: File creation
 * - V1.1.0 07-NOV-2008: Change the master config wrapper to use the new slave site matching.
 * - V1.2.14 20-OCT-2009: Use the JPATH_ROOT in the patch when it is defined.
 * - V1.2.39 13-JUL-2010: Inherit from check_jconfig (joomla 1.5) to create the specific one for Joomla 1.6
 * - V1.2.52 12-JAN-2011: Changed the implementation to be compatible with Joomla 1.6.0 stable.
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkJConfig16 ---------------
/**
 * check if 'MULTISITES_' is present
 */
function jms2win_checkJConfig16( $model, $file)
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
   
   return $rc .'|'. $result;
}

//------------ jms2win_actionJConfig ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionJConfig16( $model, $file)
{
   include_once( dirname(__FILE__) .DS. 'patchloader.php');
   $patchStr_1 = jms2win_loadPatch( 'patch_jconfig16_1.php');
   if ( $patchStr_1 === false) {
      return false;
   }
   $patchStr_2 = jms2win_loadPatch( 'patch_jconfig16.php');
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

		. . . . . . . .
		. . . . . . . .
(RC1) if (!JFile::write($file, $config->toString('PHP', 'config', array('class' => 'JConfig', 'closingtag' => false)))) {
(160) $configString = $config->toString('PHP', array('class' => 'JConfig', 'closingtag' => false));
		if (!JFile::write($file, $configString)) {
   
		. . . . . . . .
		. . . . . . . .
(RC1) if (!JFile::write($file, $config->toString('PHP', 'config', array('class' => 'JConfig', 'closingtag' => false)))) {
(160)	if (!JFile::write($file, $config->toString('PHP', array('class' => 'JConfig', 'closingtag' => false)))) {
		. . . . . . . .
		. . . . . . . .
      
      ===========
      and Replace TWICE by:
      ===========

      //_jms2win_begin v1.2.39
      		// If this is a Slave Site, let use the standard forma
      		if ( defined( 'MULTISITES_ID')) {
         		$configStr = $config->toString('PHP', 'config', array('class' => 'JConfig'));
      		}
      		else {
      		   // This is a Master website, so add the MULTISITE wrapper
         		$str = $config->toString('PHP', 'config', array('class' => 'JConfig', 'closingtag' => false));
         		$begPos = strpos( $str, 'class');
         		$endPos = strpos( $str, '?>');
               $configStr = substr( $str, 0, $begPos)
                          . "//_jms2win_begin v1.2.39\n"
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
                          . "//_jms2win_begin v1.2.39\n"
                          . "}\n"
                          . "//_jms2win_end\n"
                          . "?>\n";
      		}
      		if ( !JFile::write($fname, $configStr)) {
      //_jms2win_end

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n      if ... ( ... JFile::write ... $config->toString ... JConfig ... ) ... ) .....\n
      p0                   p1               p2                    p3          p4    p5     p6
      
OR    \n ...  $configString = $config->toString('PHP', array('class' => 'JConfig', 'closingtag' => false));
      p0                      p2
		\n ...  if (!JFile::write($file, $configString)) { ...\n
                                                       p3   p6
      \n      if ... ( ... JFile::write ... $config->toString ... JConfig ... ) ... ) .....\n
      p10                  p11              p12                   p13         p14   p15    p16
      
      Produce
   v1.1.0   begin -> p0 + INSERT PATCH + p0 -> p2 + $configStr + p5 -> end
   v1.2.24  begin -> p0 + INSERT PATCH + p5 -> end
      
    */
   
   // p2: Search for $config->toString
   $p2 = strpos( $content, '$config->toString');
   if ( $p2 === false) {
      return false;
   }
   // P0: Go to Begin of line
   for ( $p0=$p2; $p0 > 0 && $content[$p0] != "\n"; $p0--);
   $p0++;

   // p3: Search for {
   $p3 = strpos( $content, '{', $p2);
   if ( $p3 === false) {
      return false;
   }
   
   // p6: Search for end of line
   for ( $p6=$p3; $content[$p6] != "\n"; $p6++);

   // ------- Second occurence --------
   // P11: Search begin statement: "JFile::write"
   $p11 = strpos( $content, 'JFile::write', $p6);
   if ( $p11 === false) {
      return false;
   }
   // P10: Go to Begin of line
   for ( $p10=$p11; $p10 > 0 && $content[$p10] != "\n"; $p10--);
   $p10++;
   
   // p12: Search for $config->toString
   $p12 = strpos( $content, '$config->toString', $p11);
   if ( $p12 === false) {
      return false;
   }

   // p13: Search for JConfig
   $p13 = strpos( $content, 'JConfig', $p12);
   if ( $p13 === false) {
      return false;
   }
   
   // p14: Search for Closing Parenthesis
   $p14 = strpos( $content, ')', $p13+1);
   if ( $p14 === false) {
      return false;
   }

   // p15: Search for Closing Parenthesis (End of $config->toString())
   $p15 = strpos( $content, ')', $p14+1);
   if ( $p15 === false) {
      return false;
   }

   // p16: Search for end of line
   for ( $p16=$p15; $content[$p16] != "\n"; $p16++);
   
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p0)
           . $patchStr_1
           . substr( $content, $p6+1, $p10-($p6+1))
           . $patchStr_2
           . substr( $content, $p16+1)
           ;

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}
   
   return true;
}
