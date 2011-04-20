<?php
/**
 * @file       multisitesfactory.php
 * @brief      JMS Multi Sites factory to provide Joomla 1.5 and 1.6 compatibility
 *
 * @version    1.2.34
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  JMS Multi Sites
 *             Single Joomla! 1.5.x and 1.6.x installation using multiple configuration (One for each 'slave' sites).
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
 * - V1.2.34 23-JUL-2010: Initial version
 */

defined('_JEXEC') or die( 'Restricted access' );

// ===========================================================
//             Jms2WinFactory class
// ===========================================================
class MultisitesFactory extends JFactory
{
   //------------ setDBO ---------------
   /**
    * @brief Set a new DB and return the older DB.
    * This function is generally used to swap DB connection
    */
   function &setDBO( $new_db)
   {
      if ( version_compare( JVERSION, '1.6') >= 0) {
      	// Save the current database value
      	$sav_db              = JFactory::$database;
      	JFactory::$database  = $new_db;
      }
      else {
         // Save the current Joomla DB instance
   		$jdb     =& JFactory::getDBO();
   		$sav_db  = $jdb;
   		
   		// Replace the Joomla DB instance with the "Site" DB in aim to get the menu associated to this DB
   		$jdb     = $new_db;
      }
      
      return $sav_db;
   }


   //------------ setConfig ---------------
   /**
    * @brief Set a new Config and return the older Config.
    * This function is generally used to swap the Configuration for a specific slave site
    */
   function &setConfig( $new_config)
   {
      if ( version_compare( JVERSION, '1.6') >= 0) {
      	// Save the current database value
      	$sav_config       = JFactory::$config;
      	JFactory::$config = $new_config;
      }
      else {
         // Save the current Joomla DB instance
   		$config        =& JFactory::getConfig();
   		$sav_config    = $config;
   		
   		// Replace the Joomla DB instance with the "Site" DB in aim to get the menu associated to this DB
   		$config        = $new_config;
      }
      
      return $sav_config;
   }
} // End class
