<?php
/**
 * @file       slaves_helper.php
 * @brief      General function used by "VIEW" interface.
 * @version    1.1.2
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
 * - V1.1.0 11-OCT-2008: File creation
 * - V1.1.2 29-NOV-2008: Redirect to the Back-End equivalent function.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// ===========================================================
//             MultisitesHelperSlaves class
// ===========================================================
class MultisitesHelperSlaves
{
   //------------ getUsersList ---------------
	/**
	* build a combo box with the list of all the users that have contracts.
	*/
	function getUsersList( $sites, $selected_value, $title='Select user')
	{
	   return MultisitesHelper::getSiteOwnerList( $sites, $selected_value, $title);
	}
}
