<?php
/**
 * @file       view.php
 * @version    1.2.32
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
 * - 27-JUN-2008 V1.0.11 : Fix error message in saveSite.
 * - 01-OCT-2008 V1.1.00 : Reformat the Domain List to accept Full URL with sub-directories.
 * - 05-DEC-2009 V1.2.14 : Add Joomla 1.6 alpha 2 compatibility.
 * - 21-JUN-2010 V1.2.32 : No more "hide" the mainmenu.
 *                         This may be usefull to continue have access the main menu when there are
 *                         a lot of slave sites defined (several thousand)
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.view');

require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'multisitesdb.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');


// ===========================================================
//            MultisitesViewManage class
// ===========================================================
/**
 * @brief Content the different Views available for the Site Manager.
 *
 * Views available are:
 * - display() is used to display the list of sites. This is the default view;
 * - editForm() is the form used by Edit or Add task.\n
 *   It displays a simple form with the site information;
 * - deleteForm() is the confirmation form when the 'delete' task is triggered;
 * - saveSite() deploy or update site information.
 */
class MultisitesViewSettings extends JView
{
   // Private members
   var $_formName   = 'Settings';
   var $_lcFormName = 'settings';


   //------------ editForm ---------------
   /**
    * @brief Add or Edit a site
    * @param edit True means edit the site.
    *             False means add a new site.
    */
	function showSettings($tpl=null)
	{
		// JRequest::setVar( 'hidemainmenu', 1 );

		$mainframe	= &JFactory::getApplication();
   	$option     = JRequest::getCmd('option');

		$this->_layout = 'show';
		$table = &$this->get('Settings');
		$this->assignRef('row',       $table);
		$msg = &$this->get( 'Error');
		if ( !empty( $msg)) {
		   $mainframe->enqueueMessage( $msg);
		} 

		/*
		 * Set toolbar items for the page
		 */
		$formName   = $this->_formName;
		$lcFormName = $this->_lcFormName;

		JToolBarHelper::title( JText::_( "Settings" ), 'config.png' );
		JToolBarHelper::cancel( 'manage');
		JToolBarHelper::help( 'screen.settings.show', true );

		$this->assignAds();
		$this->assignRef('option',       $option);

		JHTML::_('behavior.tooltip');

		parent::display($tpl);
	}


   //------------ assignAds ---------------
	function assignAds()
	{
      if ( !defined('_EDWIN2WIN_'))    { define('_EDWIN2WIN_', true); }
      require_once( JPATH_COMPONENT.DS.'classes'.DS.'http.php' );
      require_once( JPATH_COMPONENT.DS.'models'.DS.'registration.php' );
      
   	// Compute Ads
   	$isRegistered =& Edwin2WinModelRegistration::isRegistered();
   	if ( !$isRegistered)    { $ads =& Edwin2WinModelRegistration::getAds(); }
   	else                    { $ads = ''; }
		$this->assignRef('ads', $ads);
	}

} // End class
