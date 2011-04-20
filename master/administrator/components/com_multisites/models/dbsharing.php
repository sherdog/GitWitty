<?php
/**
 * @file       templates.php
 * @version    1.2.0 RC3
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
 * - V1.2.0 03-MAY-2009: Initial version
 * - V1.2.0 RC3 05-JUL-2009: Fix several warning relative to deprecated syntax in PHP 5.x
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

if ( !defined( 'JPATH_MULTISITES_COMPONENT_ADMINISTRATOR')) {
   define( 'JPATH_MULTISITES_COMPONENT_ADMINISTRATOR',
            JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites');
}
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'dbsharing.php');

// ===========================================================
//             MultisitesModelDBSharing class
// ===========================================================
/**
 * @brief Is used to access the DBSharing information.
 */
class MultisitesModelDBSharing extends JModel
{

   //------------ getDBSharing ---------------
   /**
    * @brief Return the DBSharing XML file
    */
	
   function &getDBSharing()
	{
      $dbsharing = & Jms2WinDBSharing::getInstance();
      $dbsharing->load();
      $xml = $dbsharing->getXML();
      return $xml;
	}
	
} // End class
