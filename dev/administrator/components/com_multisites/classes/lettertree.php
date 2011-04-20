<?php
/**
 * @file       lettertree.php
 * @version    1.2.33
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2010 Edwin2Win sprlu - all right reserved.
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
 * - V1.2.9  27-MAY-2010: Initial version
 * - V1.2.33 29-JUN-2010: Also autorize call from internal routine when Joomla is not yet initialized
 */

// Check to ensure this file is included in Joomla!
if( !defined( '_JEXEC') && !defined( '_EDWIN2WIN_') ) {
	die( 'Restricted access' );
}

// ===========================================================
//            MultisitesLetterTree class
// ===========================================================
/**
 * @brief Functions to compute the letter tree directory path.
 */
if ( !class_exists( 'MultisitesLetterTree')) {
class MultisitesLetterTree
{
   //------------ getLetterTreeDir ---------------
   /**
    * @return Convert a site ID into a letter tree directory.
    */
   function getLetterTreeDir( $id)
   {
		static $instances;

		if (!isset( $instances )) {
			$instances = array();
		}
		
		if ( empty( $instances[$id]))
		{
         $str = $id;
         $concate_dot = false;
         $letter_tree = array();
         while( strlen( $str)> 0) {
            // extract the first character
            $c = substr( $str, 0, 1);
            if ( $c == '.') {
               // If the '.' is the first character
               if ( empty( $letter_tree)) {
                  // Then add it 
                  $letter_tree[] = $c;
               }
               // Otherwise, concatenate to the previous character
               else {
                  $letter_tree[count( $letter_tree)-1] .= $c;
               }
               $concate_dot = true;
            }
            else {
               if ( $concate_dot) {
                  $letter_tree[count( $letter_tree)-1] .= $c;
               }
               else {
                  $letter_tree[] = $c;
               }
               $concate_dot = false;
            }
            // remove the first character
            $str = substr( $str, 1);
         }
         $instances[$id] = implode( DIRECTORY_SEPARATOR, $letter_tree);
      }

		return $instances[$id];
   }

} // End Class
}
