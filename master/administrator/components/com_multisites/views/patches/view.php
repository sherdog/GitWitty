<?php
/**
 * @file       view.php
 * @version    1.2.42
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
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
 * - V1.2.0 RC5 26-JUL-2009: In case where the "Check Patches" is called from a slave sites, 
 *             disable the Install & UnInstall button to avoid install "master" website patches
 *             into a slave sites.
 *             This may cause problem for example if the master websites configuration.php wrapper
 *             is installed in a slave site "configuration.php" files.
 *             This may result in PHP syntax error and impossibility to access the website.
 * - V1.2.14 05-DEC-2009: Add Joomla 1.6 alpha 2 compatibility.
 * - V1.2.42 05-NOV-2010: Add compatibility with Joomla 1.6 beta 13
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.view');



// ===========================================================
//            MultisitesViewPatches class
// ===========================================================
/**
 * @brief Check if Joomla core files contain the Multi sites patches
 */
class MultisitesViewPatches extends JView
{
   var $_formName   = 'Patches';
   var $_lcFormName = 'patches';
   

   //------------ check ---------------
   /**
    * For all files that must be patches, report the list of files that must be patches.
    * If some patches are missing, the user is proposed to install the patches.
    * When some patches are installed, the user is proposed to uninstall the patches.
    */
	function check($tpl=null)
	{
		$mainframe	= &JFactory::getApplication();
   	$option     = JRequest::getCmd('option');

		$this->setLayout( 'check');
		JRequest::setVar( 'hidemainmenu', 1 );

		// Set title document
		$document = & JFactory::getDocument();
		$document->setTitle( JText::_( 'PATCHES_VIEW_DEFAULT_TITLE'));


		// view data
		$model            = &$this->getModel();
		$patches_status   = &$this->get('PatchesStatus');
		$can_install      = $model->canInstall();
		$isPartialInstall = $model->somePatchesInstalled();
		$patchesVersion   = $model->getPatchesVersion();

		// Set toolbar
		JToolBarHelper::title( JText::_( 'PATCHES_VIEW_DEFAULT_TITLE'), 'config.png');

      // In case where JMS is used from a slave site, disable the Install/Uninstall button
      if ( defined( 'MULTISITES_ID')) { }
      else {
   		if ( $can_install) {
      		JToolBarHelper::customX( 'doInstallPatches', 'apply.png', 'apply_f2.png', 'Install', false );
      		// If there is at least one patch installed, give opportunity to roll back
      		if ( $isPartialInstall) {
         		JToolBarHelper::customX( 'doUninstallPatches', 'delete.png', 'delete_f2.png', 'Uninstall', false );
      		}
   		}
   		else {
      		JToolBarHelper::customX( 'doUninstallPatches', 'delete.png', 'delete_f2.png', 'Uninstall', false );
   		}
   	}
		JToolBarHelper::cancel();
		JToolBarHelper::help( 'screen.patches.install', true );


		// Assign the template parameters
		$this->assignAds();
		$this->assign('id',                 '');
		$this->assign('can_install',        $can_install);
		$this->assignRef('patches_status',  $patches_status);
		$this->assignRef('patchesVersion',  $patchesVersion);
		$this->assignRef('option',          $option);
		

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
