<?php
/**
 * @file       admin.multisites.php
 * @version    1.2.32
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             With a single Joomla! 1.5.x, create as many joomla configuration as you have sites.
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
 * - V1.1.7 23-DEC-2008: Security issue to check that Multi Site is only call from a website that have installed the component.
 *                       This avoid that a slave site with Super Admin password forge the URL to add com_multisites to get access to JMS administration
 * - V1.2.4 22-AUG-2009: Ignore security access when working with Joomla 1.6 due to their ACL changes.
 *                       Should be re-implemented. Temporary ignored to grant the access to JMS.
 * - V1.2.14 05-DEC-2009: Add Joomla 1.6 alpha 2 compatibility.
 * - V1.2.20 29-JAN-2010: Add possibility to execute different types of controllers (autorun or specific controller).
 * - V1.2.32 09-JUN-2010: Add compatibility with Joomla 1.6.0 Beta 2.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.filesystem.file');

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
   // Define the group of users that can access the back-end
   $auth =& JFactory::getACL();
   $auth->addACL('com_multisites', 'manage', 'users', 'super administrator');
   // $auth->addACL('com_multisites', 'manage', 'users', 'administrator');
   // $auth->addACL('com_multisites', 'manage', 'users', 'manager');
   
   // If not Super Administrator or not on the master website (the component is not registered).
   $user = & JFactory::getUser();
	$option = JRequest::getCmd('option');
   $result = &JComponentHelper::getComponent( $option,  true);
   if ( !$user->authorize( 'com_multisites', 'manage')
     || !$result->enabled) {
   	$mainframe->redirect('index.php', JText::_('ALERTNOTAUTH'));
   }
   else {
      $isAutorised = true;
   }
}

if ( $isAutorised) {
   include_once( JPATH_COMPONENT.DS.'multisites.cfg.php' );
   
   require_once( JPATH_COMPONENT.DS.'controller.php' );
   require_once( JPATH_COMPONENT.DS.'helpers'.DS.'helper.php' );

   $controller 	= JRequest::getCmd('controller');
   // If "autorun" directory
   if ( JFile::exists( dirname( __FILE__) .DS. $controller .DS. 'index.php')) {
   	require_once(dirname(__FILE__) .DS. $controller .DS. 'index.php');
   }
   // If there is a specific controller
   else if ( JFile::exists( dirname( __FILE__) .DS. 'controllers' .DS. $controller. '.php')) {
      require_once (JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php');
      $classname  = $controller.'Controller';
      $controller = new $classname( array('default_task' => 'display') );
      $controller->execute( JRequest::getCmd('task' ));
      $controller->redirect(); 
   }
   // Else default
   else {
      $controller = new MultisitesController( array('default_task' => 'manage') );
      $controller->registerTask('apply', 'save');
      $controller->execute( JRequest::getCmd( 'task' ) );
      $controller->redirect();
   }
}