<?php
/**
 * @file       treesearch.php
 * @version    1.2.0
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2009 Edwin2Win sprlu - all right reserved.
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
 * - V1.2.0 30-MAY-2009: Initial version
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.filesystem.path');

// ===========================================================
//            Jms2WinTemplate class
// ===========================================================
/**
 * @brief This is a Template record.
 *
 * Generally used in collection, this class contain all the information of a Template.\n
 * A site is defined by:
 */
class Jms2WinTreeSearch
{
// var $_parent   = null;        // Reference to the parent TreeSearch
   var $_c        = null;        // A character
// var $_valuesType  = array();  // [0] = Array of values with EXACT match of the key
                                 // [1] = Array of values with WILDCARD (%) match of the key
// var $_children = array();     // Children TreeSearch nodes having the key = to '_c' of the children

   //------------------- Constructor ---------------
   function &getInstance()
   {
		static $instance;

		if (!is_object($instance))
		{
		   $instance = new Jms2WinTreeSearch();
		}
		
		return $instance;
   }

   //------------------- Constructor ---------------
   function Jms2WinTreeSearch( $c = null, $_parent = null)
   {
      $this->_c      = $c;
      $this->_parent = $_parent;
   }

   //------------------- add ---------------
   /**
    * @brief Add the key into the letter tree and associate it to the "value".
    * *
    * * When a key is ended with (%) wildcard, save the value in [1]
    * * When a key is an exact match (not ended with %), save the value in [0]
    */
   function add( $key, &$value)
   {
      $c = substr( $key, 0, 1);
      // If wildcard
      if ( $c == '%') {
         if ( empty( $this->_c )) {
            return false;  // ERROR
         }
         if ( !isset( $this->_valuesType)) {
            $this->_valuesType = array();
         }
         if ( !isset( $this->_valuesType[1])) {
            $this->_valuesType[1] = array();
         }
         $this->_valuesType[1][] = $value;   // Save the value in the wildcard category
         return true;
      }
      
      // When not a wildcard (%)
      if ( !isset( $this->_children)) {
         $this->_children = array();
      }
      if ( !isset( $this->_children[$c])) {
         $this->_children[$c] = new Jms2WinTreeSearch( $c, $this);
      }
      $remaining = substr( $key, 1);
      if ( !empty( $remaining)) {
         return $this->_children[$c]->add( $remaining, $value);
      }
      else {
         if ( !isset( $this->_children[$c]->valuesType)) {
            $this->_children[$c]->_valuesType = array();
         }
         if ( !isset( $this->_children[$c]->_valuesType[0])) {
            $this->_children[$c]->_valuesType[0] = array();
         }
         $this->_children[$c]->_valuesType[0][] = $value;   // Save the value in the EXACT match category
         return true;
      }
      
      return false;
   }


   //------------------- _getKey ---------------
   /**
    * @brief Search in the letter tree the path that match the key
    */
   function & _getKey( $key, & $lastValue)
   {
      // If there is a wildcard solution
      if ( !empty( $this->_valuesType[1])) {
         $lastValue = &$this;
      }
      
      $c = substr( $key, 0, 1);
      if ( isset( $this->_children[$c])) {
         $remaining = substr( $key, 1);
         if ( !empty( $remaining)) {
            return $this->_children[$c]->_getKey( $remaining, $lastValue);
         }
         else {
            // If there is an EXACT solution
            if ( !empty( $this->_children[$c]->_valuesType[0])) {
               $lastValue = $this->_children[$c];
            }
         }
      }
      
      return $lastValue;
   }

   //------------------- getKey ---------------
   /**
    * @brief Search in the letter tree the path that match the key
    */
   function & getKey( $key)
   {
      $null = null;
      return $this->_getKey( $key, $null);
   }


   //------------------- getKeyString ---------------
   /**
    * @brief Return an array with the list keys that can provide a solution
    */
   function & getKeyString( $aSolution)
   {
      $results = array();
      $key = '';
      $node = $aSolution;
      do {
         $key  = $node->_c . $key;
         $node = $node->_parent;
      } while ( !empty( $node));
      
      // If there is a wildcard solution
      if ( !empty( $aSolution->_valuesType[1])) {
         $results[] = $key . '%';
      }
      // If exact match
      if ( !empty( $aSolution->_valuesType[0])) {
         $results[] = $key;
      }
      return $results;
   }

} // End Class
