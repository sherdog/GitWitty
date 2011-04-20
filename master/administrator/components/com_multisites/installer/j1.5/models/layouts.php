<?php
/**
 * @file       index.php
 * @brief      Installer for the JMS front-end layouts (templates).
 *
 * @version    1.2.36
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
 * @par History:
 * - V1.2.20 29-JAN-2010: Initial version
 * - V1.2.36 21-AUG-2010: Add Joomla 1.6 beta7 compatibility
 */

/**
 * @version		$Id: templates.php 12389 2009-07-01 00:34:45Z ian $
 * @package		Joomla
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Import library dependencies
require_once(dirname(__FILE__).DS.'extension.php');
jimport( 'joomla.filesystem.folder' );

/**
 * Extension Manager Templates Model
 *
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class MultisitesInstallerModelLayouts extends InstallerModel
{
	/**
	 * Extension Type
	 * @var	string
	 */
	var $_type = 'layout';

	/**
	 * Overridden constructor
	 * @access	protected
	 */
	function __construct()
	{
		$mainframe	= &JFactory::getApplication();

		// Call the parent constructor
		parent::__construct();

		// Set state variables from the request
		$this->setState('filter.string', $mainframe->getUserStateFromRequest( "com_multisites.templates.string", 'filter', '', 'string' ));
		$this->setState('filter.client', $mainframe->getUserStateFromRequest( "com_multisites.templates.client", 'client', -1, 'int' ));
	}

	function _loadItems()
	{
		$mainframe	= &JFactory::getApplication();
   	$option     = JRequest::getCmd('option');

		$db = &JFactory::getDBO();
		$templates = array();

		if ($this->getState('filter.client') < 0) {
			$client = 'all';
			// Get the site layouts
			$baseDir = JPATH_SITE.DS. 'components' .DS. 'com_multisites'.DS.'templates';
			$templateDirs = JFolder::folders( $baseDir);

			for ($i=0; $i < count($templateDirs); $i++) {
				$template = new stdClass();
				$template->folder = $templateDirs[$i];
				$template->client = 0;
				$template->baseDir = $baseDir;

				if ($this->getState('filter.string')) {
					if (strpos($template->folder, $this->getState('filter.string')) !== false) {
						$templates[] = $template;
					}
				} else {
					$templates[] = $template;
				}
			}
			// Get the admin templates
			$adminBaseDir = JPATH_ADMINISTRATOR.DS. 'components' .DS. 'com_multisites'.DS.'templates';
			if ( JFolder::exists( $adminBaseDir)) {
   			$templateDirs = JFolder::folders( $adminBaseDir);
			}
			else {
   			$templateDirs = array();
			}

			for ($i=0; $i < count($templateDirs); $i++) {
				$template = new stdClass();
				$template->folder = $templateDirs[$i];
				$template->client = 1;
				$template->baseDir = JPATH_ADMINISTRATOR.DS. 'components' .DS. 'com_multisites'.DS.'templates';

				if ($this->getState('filter.string')) {
					if (strpos($template->folder, $this->getState('filter.string')) !== false) {
						$templates[] = $template;
					}
				} else {
					$templates[] = $template;
				}
			}
		} else {
			$clientInfo =& JApplicationHelper::getClientInfo($this->getState('filter.client'));
			$client = $clientInfo->name;

			$adminBaseDir = $clientInfo->path.DS. 'components' .DS. 'com_multisites'.DS.'templates';
			if ( JFolder::exists( $adminBaseDir)) {
   			$templateDirs = JFolder::folders( $adminBaseDir);
			}
			else {
   			$templateDirs = array();
			}
			
			for ($i=0; $i < count($templateDirs); $i++) {
				$template = new stdClass();
				$template->folder = $templateDirs[$i];
				$template->client = $clientInfo->id;
				$template->baseDir = $clientInfo->path.DS. 'components' .DS. 'com_multisites'.DS.'templates';

				if ($this->getState('filter.string')) {
					if (strpos($template->folder, $this->getState('filter.string')) !== false) {
						$templates[] = $template;
					}
				} else {
					$templates[] = $template;
				}
			}
		}

		// Get a list of the currently active templates
		$activeList = array();

		$rows = array();
		$rowid = 0;
		// Check that the directory contains an xml file
		foreach($templates as $template)
		{
			$dirName = $template->baseDir .DS. $template->folder;
			$xmlFilesInDir = JFolder::files($dirName,'.xml$');
			
			if ( !is_array( $xmlFilesInDir)) {
			   continue;
			}

			foreach($xmlFilesInDir as $xmlfile)
			{
				$data = JApplicationHelper::parseXMLInstallFile($dirName . DS. $xmlfile);

				$row = new StdClass();
				$row->id 		= $rowid;
				$row->client_id	= $template->client;
				$row->directory = $template->folder;
				$row->baseDir	= $template->baseDir;

				$row->active = false;

				if ($data) {
					foreach($data as $key => $value) {
						$row->$key = $value;
					}
				}

				$row->checked_out = 0;
				$row->jname = JString::strtolower( str_replace( ' ', '_', $row->name ) );

				$rows[] = $row;
				$rowid++;
			}
		}
		$this->setState('pagination.total', count($rows));
		// if the offset is greater than the total, then can the offset
		if($this->getState('pagination.offset') > $this->getState('pagination.total')) {
			$this->setState('pagination.offset',0);
		}

		if($this->getState('pagination.limit') > 0) {
			$this->_items = array_slice( $rows, $this->getState('pagination.offset'), $this->getState('pagination.limit') );
		} else {
			$this->_items = $rows;
		}
	}
}