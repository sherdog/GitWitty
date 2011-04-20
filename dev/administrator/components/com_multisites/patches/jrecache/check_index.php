<?php
/**
 * @file       check_index.php
 * @brief      Check if the patch installed by JRE Cache into the index.php.
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
 * - V1.2.12 09-OCT-2009: Initial version
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkJREIndex ---------------
/**
 * check if following lines are present:
 * - define('_JRE_FRAMEWORK',1);
 *   is present and placed before the joomla "define.php" file.
 */
function jms2win_checkJREIndex( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[IGNORE]|File Not Found';
	}
	
   $str = file_get_contents( $filename);

   // Check if a JRE patchs is present
   $jrepos = strpos( $str, '_JRE_FRAMEWORK');
   if ($jrepos === false) {
	   return '[IGNORE]|File Not Found';
   }

   $result = "";
   
   // Search the position of 'defines.php' to see if it is place before or after the JRE patch
   $pos = strpos( $str, "'defines.php'");
   if ($pos === false) {
	   $rc = '[NOK]';
      $result .= JText::_( 'Application error. The word \'define.php\' is not found.');
      $result .= '|[ACTION]';
      $result .= '|contact the support.';
   }
   else {
      $rc = '[OK]';
      // If the JRE patch is placed before the
      // require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
      // statment
      if ( $jrepos < $pos) {
   	   $rc = '[NOK]';
         $result .= JText::_( 'The JRE patch must be placed after the joomla initialisation to allow JMS detect the site ID.');
         $result .= '|[ACTION]';
         $result .= '|Move 3 standard joomla initialisation lines before the JRE patches.';
      }
   }
   
   return $rc .'|'. $result;
}



//------------ jms2win_actionJREIndex ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionJREIndex( $model, $file)
{
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
      define('_JRE_FRAMEWORK',1);
		MOVE MOVE MOVE
		MOVE MOVE MOVE
		MOVE MOVE MOVE
		MOVE MOVE MOVE
      define('JPATH_BASE', dirname(__FILE__) );

      define( 'DS', DIRECTORY_SEPARATOR );
      
      require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
		........
		........
		........
	   }
		?>
      
      ===========
      and Replace by:
      ===========
		........
		........
		........
      define('JPATH_BASE', dirname(__FILE__) );

      define( 'DS', DIRECTORY_SEPARATOR );
      
      require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
      define('_JRE_FRAMEWORK',1);
		MOVE MOVE MOVE
		MOVE MOVE MOVE
		MOVE MOVE MOVE
		MOVE MOVE MOVE
		........
		........
		........
	   }
		?>

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n .... define('_JRE_FRAMEWORK',1); ...
      p0              p1              
      

      \n .... define('JPATH_BASE', dirname(__FILE__) );
      p2      p3

      \n .... require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' ); ...\n
              p4                                                              p5
      
      Produce
      begin->p0 + p2->p5 + p0->p2 + p5->end
      
    */
   
   // p1: Search for "_JRE_FRAMEWORK"
   $p1 = strpos( $content, '_JRE_FRAMEWORK');
   if ( $p1 === false) {
      return false;
   }
   
   // P0: Go to Begin of line
   for ( $p0 = $p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
   $p0++;

   // p3: Search for "JPATH_BASE"
   $p3 = strpos( $content, 'JPATH_BASE', $p1);
   if ( $p3 === false) {
      return false;
   }
   
   // P2: Go to Begin of line
   for ( $p2 = $p3; $p2 > 0 && $content[$p2] != "\n"; $p2--);
   $p2++;
 
   // p4: Search for 'require_once'
   $p4 = strpos( $content, 'require_once', $p3);
   if ( $p4 === false) {
      return false;
   }

   // p5: Search for end of line
   for ( $p5=$p4; $content[$p5] != "\n"; $p5++);
   $p5++;

 
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p0)
           . substr( $content, $p2, $p5-$p2)
           . substr( $content, $p0, $p2-$p0)
           . substr( $content, $p5)
           ;

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

   return true;
}

