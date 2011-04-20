<?php
/**
 * @file       patchloader.php
 * @brief      Load an external patch string
 *
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
 * - V1.1.3 18-DEC-2008: Add the removePatch function
 * - V1.2.14 20-OCT-2009: Fix bug in removePatch that miss a character
 *                        Also introduced a new routine to extract a version number
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_loadPatch ---------------
/**
 * @brief This function cleanup a patch definition to remove the possible "<?php" tag present
 */
function jms2win_loadPatch( $fname, $dir=null)
{
	if ( empty( $dir)) {
   	$filename = dirname( __FILE__) .DS. $fname;
	}
	else {
   	$filename = $dir .DS. $fname;
	}
   $content = file_get_contents( $filename);
   return $content;
}

//------------ jms2win_removePatch ---------------
/**
 * @brief Remove all section //_jms2win_begin ... //_jms2win_end
 */
function jms2win_removePatch( $content, $replaces = null)
{
   $result = '';
   $prev_pos = 0;
   $i = 0;
   while( true) {
      // P1: Search begin statement: //_jms2win_begin
      $p1 = strpos( $content, '//_jms2win_begin', $prev_pos);
      if ( $p1 === false) {
         $result .=  substr( $content, $prev_pos);
         break;
      }
      // P0: Go to Begin of line
      for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
      
      // P2: Search end statement: "'_jms2win_end'
      $p2 = strpos( $content, '//_jms2win_end',  $p1);
      if ( $p2 === false) {
         $result .=  substr( $content, $prev_pos);
         break;
      }

      // p3: Search for end of line
      for ( $p3=$p2; $content[$p3] != "\n"; $p3++);
      
      $result .=  substr( $content, $prev_pos, $p0-$prev_pos+1);
      
      
      // If undo code is NOT present, restore it
      $p4 = strpos( $content, '/*_jms2win_undo',  $p3);
      if ( $p4 === false) {
         $undoPresent = false;
         // Or use alternate undo code
         if ( !empty( $replaces) && !empty( $replaces[$i])) {
            $result .= $replaces[$i];
         }
         $prev_pos = $p3+1;
      }
      else {
         // Cross-check that jms2win_undo is not part of another jms2win_begin
         $nextBegin = strpos( $content, '//_jms2win_begin', $p3);
         $undoPresent = true;
         if ( $nextBegin === false) {}
         else if ( $nextBegin<$p4) {
            $undoPresent = false;
         }
      }

      // If undo is not present
      if ( $undoPresent == false) {
         // Or use alternate undo code
         if ( !empty( $replaces) && !empty( $replaces[$i])) {
            $result .= $replaces[$i];
         }
         $prev_pos = $p3+1;
      }
      else {
         $p7 = strpos( $content, '_jms2win_undo */',  $p4);
         if ( $p7 === false) {
            // Or use alternate undo code
            if ( !empty( $replaces) && !empty( $replaces[$i])) {
               $result .= $replaces[$i];
            }
            $prev_pos = $p3+1;
         }
         else {
            // p5: Search for end of line
            for ( $p5=$p4; $content[$p5] != "\n"; $p5++);
            
            // P6: Go to Begin of line
            for ( $p6=$p7; $p6 > 0 && $content[$p6] != "\n"; $p6--);
            
            $result .=  substr( $content, $p5, $p6-$p5+1);

            // p8: Search for end of line
            for ( $p8=$p7; $content[$p8] != "\n"; $p8++);
            $prev_pos = $p8+1;
         }
      }
   }
   
   return $result;
}

//------------ jms2win_getPatchVersion ---------------
/**
 * @brief Search for a marker "//_jms2win_begin" and extract the version number that follow.
 */
function jms2win_getPatchVersion( $content, $occurence = null)
{
   $result = '';
   $prev_pos = 0;
   $i = 0;
   while( true) {
      $p1 = strpos( $content, '//_jms2win_begin', $prev_pos);
      if ( $p1 === false) {
         break;
      }
      if ( is_null( $occurence) || $i==$occurence) {
         // p2: Search for first character that is not a space or a tab
         for ( $p2=$p1+16; $content[$p2] != " " && $content[$p2] != "\t"; $p2++);
         
         // p3: Search for end of line
         for ( $p3=$p2; $content[$p3] != "\n"; $p3++);
         
         // Extract the version info
         $result = trim( substr( $content, $p2, $p3-$p2));
         return $result;
      }
      $prev_pos = $p1+16;
   }
   
   return '';
}
?>