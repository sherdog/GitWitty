<?php
/**
 * @file       jms2winmodel.php
 * @brief      Wrapper to JModel to force using the JMS2Win master database connection
 *
 * @version    1.0.1
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
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access or JMS2Win patches are not installed');

jimport('joomla.application.component.model');

// ===========================================================
//             Jms2WinModel class
// ===========================================================
class Jms2WinModel extends JModel
{
	/**
	 * @brief Force using the Master DB when not specified
	 */
	function __construct($config = array())
	{
		if ( !array_key_exists('dbo', $config))  {
		   $config['dbo'] = &Jms2WinFactory::getMasterDBO();
		}
		parent::__construct($config);
	}
}