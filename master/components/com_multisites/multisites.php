<?php
/**
 * @file       multisites.php
 * @brief      Front-end that allow to create dynamic slave sites.
 * @version    1.2.20
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
 * - V1.2.20 02-FEB-2010: Add reading the multisites.cfg.php configuration file to allow force
 *                        used hardcoded values when creating a slave site.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');


$isAutorised = false;
// If Joomla 1.6
if ( version_compare( JVERSION, '1.6') >= 0) {
   if ( !defined( 'MULTISITES_MANIFEST_FILENAME')) {
      define( 'MULTISITES_MANIFEST_FILENAME', 'extension.xml');
   }
   $user = & JFactory::getUser();
	if ($user->authorize('com_multisites.manage')) {
      $isAutorised = true;
	}
}
// If Joomla 1.5
else {
   if ( !defined( 'MULTISITES_MANIFEST_FILENAME')) {
      define( 'MULTISITES_MANIFEST_FILENAME', 'install.xml');
   }

   // Define the group of users that in addition of the owner can show and edit the slave sites
   $auth =& JFactory::getACL();
   $auth->addACL('com_multisites', 'edit', 'users', 'super administrator');
   $auth->addACL('com_multisites', 'edit', 'users', 'administrator');

   $isAutorised = true;
}

if ( $isAutorised) {
   @include_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'multisites.cfg.php' );
   require_once( dirname(__FILE__).DS.'controller.php');
   
   // Create the controller
   $controller = new MultisitesController();
   $controller->execute( JRequest::getCmd( 'task' ));
   $controller->redirect();
}
