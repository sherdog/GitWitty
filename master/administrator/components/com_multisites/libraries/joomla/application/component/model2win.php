<?php
/**
 * @file       model2win.php
 * @brief      Wrapper to JModel to add some general functions
 * @version    1.0.1
 * @author     Edwin CHERONT     (cheront@edwin2win.com)
 *             Edwin2Win sprlu   (www.edwin2win.com)
 * @copyright  (C) 2008 Edwin2Win sprlu - all right reserved.
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
 */


// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

jimport( 'joomla.application.component.model' );

// ===========================================================
//             JModel2Win class
// ===========================================================
if ( !class_exists( 'JModel2Win')) {
class JModel2Win extends JModel
{
	var $_countAll = 0;

   //------------ getCountAll ---------------
   /**
    * @return the total number of records.
    */
   function getCountAll()
	{
	   return $this->_countAll;
	}

   //------------ setFilters ---------------
   function setFilters( &$filters)
	{
	   $this->setState( 'filters', $filters);
	}

   //------------ removeFilters ---------------
   function removeFilters()
	{
	   $this->setState( 'filters', null);
	}

}
}
