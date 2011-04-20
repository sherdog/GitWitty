<?php
/**
 * @file       index.php
 * @brief      Installer for the JMS front-end layouts (templates).
 *
 * @version    1.2.20
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2010 Edwin2Win sprlu - all right reserved.
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
 * @remark     Is inpired from the joomla "administrator/components/com_installer".
 * @par History:
 * - V1.2.20 29-JAN-2010: Initial version
 */


/**
*
* @version		$Id: admin.installer.php 10381 2008-06-01 03:35:53Z pasamio $
* @package		Joomla
* @subpackage	Installer
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
 * Make sure the user is authorized to view this page
 */
$user = & JFactory::getUser();
if (!$user->authorize('com_installer', 'installer')) {
	$mainframe->redirect('index.php', JText::_('ALERTNOTAUTH'));
}


$ext = JRequest::getWord('type');

$subMenus = array( 'Layouts' => 'layouts');


foreach ($subMenus as $name => $extension) {
	JSubMenuHelper::addEntry(JText::_( $name ), '#" onclick="javascript:document.adminForm.type.value=\''.$extension.'\';submitbutton(\'manage\');', ($extension == $ext));
}

JSubMenuHelper::addEntry(JText::_( 'Install' ), '#" onclick="javascript:document.adminForm.type.value=\'install\';document.adminForm.task.value=\'\';submitbutton(\'installForm\');', !in_array( $ext, $subMenus));

require_once( dirname( __FILE__) .DS. 'controller.php' );
$controller = new MultisitesInstallerController( array( 'default_task' => 'display',
                                                        'base_path' =>  dirname( __FILE__)
                                                      ) 
                                                );
$task = JRequest::getWord('task');
if( $task == 'install' ){
	$task = 'doInstall';
}
$controller->execute( $task );
$controller->redirect();
