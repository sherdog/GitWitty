<?php
/**
 * @file       view.php
 * @version    1.2.47
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2011 Edwin2Win sprlu - all right reserved.
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
 * - 29-NOV-2008 V1.1.02 : Add filtering on the owner
 * - 05-DEC-2008 V1.1.03 : Add a default generic error message when a website is deployed 
 *                         and generate and error without a message
 * - 12-MAY-2009 V1.2.00 : Add new DB parameters, deploy directory creation and alias link.
 * - 21-JUN-2009 V1.2.0 RC2 : Review DB User name and password sanitisation.
 *                            Special character are now: '_.,;:=-+/*@#$£!&(){}[]<>§'
 * - 25-JUL-2009 V1.2.0 RC5 : Fix mapping directory path displayed when using the deploy directory.
 *                            Instead of displaying the "master" directory, now display the deploy directory.
 *                            Also display the resolved domain name instead of the expression when this is possible.
 * - 21-NOV-2009 V1.2.14 : Add FTP parameters to allow modifying the "configuration.php" FTP values.
 *                         Add Joomla 1.6 alpha 2 compatibility.
 * - 07-MAR-2010 V1.2.23 : Add the possibility to create a new slave site based on an existing one (new like).
 *                         Add slave site "search" filtering
 * - 05-NOV-2010 V1.2.42 : Add compatibility with Joomla 1.6 beta 13
 * - 03-FEB-2011 V1.2.47 : In case of Joomla 1.6, add the display of the site name in the "manage site"
 *                         as it is no more displayed by Joomla itself.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.view');


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
class MultisitesViewManage extends JView
{
   // Private members
   var $_formName   = 'Site';
   var $_lcFormName = 'site';

   //------------ display ---------------
   /**
    * @brief Display the list of sites
    */
	function display($tpl=null)
	{
		$mainframe	= &JFactory::getApplication();
   	$option     = JRequest::getCmd('option');

		$this->setLayout( 'default');

		// Set toolbar items for the page
		$formName   = $this->_formName;
		$lcFormName = $this->_lcFormName;

      $site_title = '';
      if ( version_compare( JVERSION, '1.6') >= 0) { 
         $sitename = JFactory::getConfig()->getValue( 'sitename');
         if ( !empty( $sitename)) {
            $site_title = '<span style="font-size:12px;padding-left:10px; ">: '.$sitename.'</span>';
         }
      }
		JToolBarHelper::title( JText::_( 'SITE_LIST_TITLE').$site_title, 'config.png' );
		JToolBarHelper::customX( "delete$formName", 'delete.png', 'delete_f2.png', 'Delete', true );
		JToolBarHelper::editListX( "edit$formName");
		JToolBarHelper::addNewX( "add$formName" );
		JToolBarHelper::customX( "addLike$formName", 'copy.png', 'copy.png', 'New Like', true );
		JToolBarHelper::help( 'screen.' .$lcFormName. 'manager', true);

		$document = & JFactory::getDocument();
		$document->setTitle(JText::_('SITE_LIST_TITLE'));

		// retreive the filters and parameters that limit the query
		$filters = &$this->_getFilters();
		
		// Call the model
		$model = &$this->getModel();
		$model->setFilters( $filters);
		$sites		   = &$this->get('Sites');
		$this->assignRef('sites', $sites);

		// If Joomla < 1.5.3, Check if the master website uses the table prefix is jos_ (if yes, warn the user of potential problem)
      $version       = new JVersion();
      $joomlaversion = $version->getShortVersion();
      $jvers = explode( '.', $joomlaversion);
      if ( count( $jvers) >= 3
        && $jvers[0] == '1' && $jvers[1] == '5'
        && (!is_numeric( $jvers[2]) || (int)$jvers[2] < 3)
         )
      {
   		$db = JFactory::getDBO();
   		$dbPrefix = $db->getPrefix();
   		if ( $dbPrefix == 'jos_') {
   		   $msg = JText::_('SITE_VIEW_JOS_PREFIX');
      		$mainframe->enqueueMessage($msg, 'notice');
   		}
      }
		
		// Check if all patches are installed and inform the view on the result
		$modelPatches	=& $this->getModel( 'Patches' );
		$isPatchesInstalled = $modelPatches->isPatchesInstalled();
		if (!$isPatchesInstalled) {
		   $msg = JText::_('SITE_VIEW_INSTALLPATCHES');
   		$mainframe->enqueueMessage($msg, 'notice');
   	}
   	
		$lists		= &$this->_getViewLists( $filters, true, true);
		$pagination	= &$this->_getPagination( $filters, $this->get('CountAll'));

		// Assign view variable with will be used by the template
		$this->assignAds();
		$this->assignRef('pagination',   $pagination);
		$this->assignRef('lists',        $lists);
		$this->assignRef('limitstart',   $limitstart);
		$this->assignRef('option',       $option);

		JHTML::_('behavior.tooltip');

		// Display the template
		parent::display($tpl);
	}


   //------------ _getFilters ---------------
   /**
    * @brief Return all the filter values posted by the "display" form (the list) and also store the values into the registry for later use.
    * The filter values are used by the model to filter, sort and limit the records that must be displayed in the list.
    */
	function &_getFilters()
	{
		$mainframe	= &JFactory::getApplication();
   	$option = JRequest::getCmd('option');
	   $filters = array();

		// $option				   = JRequest::getCmd( 'option' );
		$client                 = JRequest::getWord( 'filter_client', 'site' );
		// Retreive search filter
		$search				      = $mainframe->getUserStateFromRequest( "$option.$client.search",			   'search',			   '',			'string' );
		$filters['search']	   = JString::strtolower( $search );
		// Retreive filter combo values
		$filters['status']	   = $mainframe->getUserStateFromRequest( "$option.$client.filter_status",    'filter_status',	   '[unselected]',			'string' );
		$filters['sitename']	   = $mainframe->getUserStateFromRequest( "$option.$client.filter_sitename",  'filter_sitename',	'[unselected]',			'string' );
		$filters['host']	      = $mainframe->getUserStateFromRequest( "$option.$client.filter_host",      'filter_host',	      '[unselected]',			'string' );
		$filters['db']	         = $mainframe->getUserStateFromRequest( "$option.$client.filter_db",        'filter_db',	      '[unselected]',			'string' );
		$filters['site_ids']	   = $mainframe->getUserStateFromRequest( "$option.$client.filter_site_ids",  'filter_site_ids',	'[unselected]',			'string' );
		$filters['owner_id']	   = $mainframe->getUserStateFromRequest( "$option.$client.filter_owner_id",  'filter_owner_id',	'[unselected]',			'string' );
//		$filters['templates']	= $mainframe->getUserStateFromRequest( "$option.$client.filter_templates", 'filter_templates',	'[unselected]',			'string' );
		// Retreive selected sort column and direction
		$filters['order']		   = $mainframe->getUserStateFromRequest( "$option.$client.filter_order",		'filter_order',		'',	      'cmd' );
		$filters['order_Dir']	= $mainframe->getUserStateFromRequest( "$option.$client.filter_order_Dir",	'filter_order_Dir',	'',			'word' );
		// Retreive the limit for display
		$filters['limit']		   = $mainframe->getUserStateFromRequest( 'global.list.limit',                'limit',             $mainframe->getCfg('list_limit'), 'int' );
		$filters['limitstart']	= $mainframe->getUserStateFromRequest( $option.'.limitstart',              'limitstart',        0, 'int' );

		return $filters;
	}

   //------------ _getViewLists ---------------
	function &_getViewLists( &$filters, $facultative_status=false, $onChangeStatus=false)
	{
	   
		// Filter combo
		$lists['status']	   = MultisitesHelper::getAllStatusList(  'filter_status', $filters['status'], $facultative_status, $onChangeStatus);
		$lists['sitename']   = MultisitesHelper::getSiteNameList(   $this->sites, $filters['sitename']);
		$lists['dbserver']	= MultisitesHelper::getDBServerList(   $this->sites, $filters['host']);
		$lists['dbname']	   = MultisitesHelper::getDBNameList(     $this->sites, $filters['db']);
		$lists['site_ids']	= MultisitesHelper::getSiteIdsList(    $this->sites, $filters['site_ids']);
		$lists['owner_id']	= MultisitesHelper::getSiteOwnerList(  $this->sites, $filters['owner_id']);

	   // For Joomla 1.6 compatibility, test if the template exists before doing the call
		if ( !empty( $this->_models[strtolower('Templates' )])) {
		   $model      =& $this->getModel( 'Templates');
		}
		else {
		   $model = null;
		}
	   
	   if ( is_object( $model)) {
   	   $templates  =& $model->getTemplates();
   		$lists['templates']	= MultisitesHelper::getTemplatesList(  $templates, $this->template->id);
	   }

		// table ordering
		$lists['order_Dir']	= $filters['order_Dir'];
		$lists['order']		= $filters['order'];

		// search filter
		$lists['search']     = $filters['search'];

		return $lists;
	}


   //------------ _getPagination ---------------
	function &_getPagination( &$filters, $total=0)
	{
		jimport('joomla.html.pagination');
		$pagination = new JPagination( $total, $filters['limitstart'], $filters['limit'] );
		return $pagination;
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

   //------------ deleteForm ---------------
   /**
    * @brief Request the user to confirm the deletion.
    * This display the current site information that ask for a confirmation.
    */
	function deleteForm($tpl=null)
	{
		$mainframe	= &JFactory::getApplication();
   	$option     = JRequest::getCmd('option');

		$this->setLayout( 'delete');
		JRequest::setVar( 'hidemainmenu', 1 );

		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::title(  JText::_( 'SITE_DELETE_TITLE' ) . ': <small><small>[ '. JText::_( 'Delete' ) .' ]</small></small>', 'config.png' );
		JToolBarHelper::custom( 'doDeleteSite', 'delete.png', 'delete_f2.png', 'Delete', false );
		JToolBarHelper::cancel();
		JToolBarHelper::help( 'screen.sitemanager.delete', true );

		// view data
		$site       = &$this->get('CurrentRecord');
		$site_dir   = &$this->get('SiteDir');

		$document = & JFactory::getDocument();
		$document->setTitle( JText::sprintf( 'SITE_DELETE_CONFIRM_SITE', $site->sitename) );

		// Assign value to the view
		$this->assignAds();
		$this->assignRef('site',      $site);
		$this->assignRef('site_dir',  $site_dir);
		$this->assignRef('option',    $option);

		parent::display($tpl);
	}


   //------------ editForm ---------------
   /**
    * @brief Add or Edit a site
    * @param edit True means edit the site.
    *             False means add a new site.
    */
	function editForm($edit,$tpl=null)
	{
		JRequest::setVar( 'hidemainmenu', 1 );

		$mainframe	= &JFactory::getApplication();
   	$option     = JRequest::getCmd('option');

		$this->setLayout( 'edit');
		if($edit == 'edit' || $edit == 'newLike') {
			$table = &$this->get('CurrentRecord');
			$template = new Jms2WinTemplate();
			$template->load( $table->fromTemplateID);
		}
		else {
			$table = &$this->get('NewRecord');
			$template = new Jms2WinTemplate();
		}
		$this->assignRef('row',       $table);
		$this->assignRef('template',  $template);

		/*
		 * Set toolbar items for the page
		 */
		$formName   = $this->_formName;
		$lcFormName = $this->_lcFormName;

		$isNew = (($table->id == '') || ($edit == 'newLike'));
		if ( $isNew) {
		   $text = JText::_('SITE_EDIT_TITLE_NEW');
		}
		else {
		   $text = JText::_('SITE_EDIT_TITLE_EDIT');
		}
		JToolBarHelper::title( JText::_( 'SITE_EDIT_TITLE' ).': <small><small>[ '. $text.' ]</small></small>', 'config.png' );
		JToolBarHelper::custom( "save$formName", 'save.png', 'save_f2.png', 'Save', false );
		JToolBarHelper::cancel( 'manage');
		JToolBarHelper::help( 'screen.' .$lcFormName. 'manager.new', true );

		// retreive the filters and parameters that limit the query
		$filters = &$this->_getFilters();
		$model   = &$this->getModel();
		$sites	= &$this->get('Sites');
		$this->assignRef('sites', $sites);
		$lists	= &$this->_getViewLists( $filters);


	   // If there is no website to replicate
	   if ( empty( $table->fromTemplateID) || $table->fromTemplateID == '[unselected]') {
	      // Hide the Check Box 'share DB'
		   $style_shareCheckBox = 'style="display:none;"';
		   $table->shareDB = '';
	   }
	   else {
	      // Show the check box that all to share the same DB connection (db and prefix).
   	   $style_shareCheckBox = '';    // The Check Box itself
	   }
	      
		$style_shareDB       = '';    // All the DB field (host, name, user, psw) that are conditioned by the shareDB checkbox field
		if ( $isNew) {
		   if ( !empty( $template->shareDB)) {
   		   $table->shareDB = $template->shareDB;
		   }
		}
		if ( !empty( $table->shareDB) && $table->shareDB == true) {
		   $style_showDBFields = 'style="display:none;"';
		}
		else {
		   $style_shareDB = '';
   		$style_showDBFields = '';
   		// If there is a "to" prefix defined in the template or a "to" prefix defined in the site
   		if ( !empty( $template->toPrefix) || !empty( $table->toPrefix)) {
   		   // Show
   		   $style_shareDB = '';
   		   $style_showDBFields = '';
   		}
   		else {
   		   // Hide
   		   $style_shareDB = 'style="display:none;"';
   		   $style_showDBFields = 'style="display:none;"';
   		}
		}

		// If FTP parameter defined in the site.
		if ( isset( $table->toFTP_enable) && ($table->toFTP_enable == '0' || $table->toFTP_enable == '1')) {
   	   $style_showFTPFields = '';
		}
		// Else use the FTP parameter defined in the JMS template
		else {
   		// If there is no JMS templates FTP
   		if ( isset( $template->toFTP_enable) && ($template->toFTP_enable == '0' || $template->toFTP_enable == '1')) {
      	   $style_showFTPFields = '';
   		}
   		// if FTP = default
   		else {
      	   $style_showFTPFields = 'style="display:none;"';
   		}
		}

		$this->assignRef('style_shareCheckBox',   $style_shareCheckBox);
		$this->assignRef('style_shareDB',         $style_shareDB);
		$this->assignRef('style_showDBFields',    $style_showDBFields);
		$this->assignRef('style_showFTPFields', $style_showFTPFields);

		$this->assignAds();
		$this->assignRef('lists',     $lists);
		$this->assign('isnew',        $isNew);
		$this->assignRef('option',    $option);

		JHTML::_('behavior.tooltip');

		parent::display($tpl);
	}



   //------------ saveSite ---------------
   /**
    * @brief This create a directory with the site id and deploy the multisites files.
    */
	function saveSite($tpl=null)
	{
		$mainframe	= &JFactory::getApplication();
   	$option = JRequest::getCmd('option');
		
	   // Retreive entered values
	   $id = JRequest::getCmd('id', false);
	   if ( $id === false) {
	      $msg = JText::_( 'Please provide a site id' );
	      $this->setError( $msg);
	      return $msg;
	   }

		$enteredvalues = array();
		$enteredvalues['id']             = $id;
		$enteredvalues['status']         = JRequest::getString('status', null);
		$enteredvalues['payment_ref']    = JRequest::getString('payment_ref', null);
		$enteredvalues['expiration']     = JRequest::getString('expiration', null);
		$enteredvalues['owner_id']       = JRequest::getInt('owner_id');
		$enteredvalues['site_prefix']    = JRequest::getString('site_prefix', null);
		$enteredvalues['site_alias']     = JRequest::getString('site_alias', null);
		$enteredvalues['siteComment']    = isset( $_REQUEST[ 'siteComment']) ? stripslashes( $_REQUEST[ 'siteComment']) : '';
		$enteredvalues['domains']        = MultisitesHelper::getDomainList( 'domains');
		$enteredvalues['fromTemplateID'] = JRequest::getString('fromTemplateID', null);
		if ( !empty( $enteredvalues['fromTemplateID']) && $enteredvalues['fromTemplateID'] == '[unselected]') {
		   $enteredvalues['fromTemplateID'] = '';
		}
		$enteredvalues['toSiteName']     = JRequest::getString('toSiteName', null);
		$enteredvalues['shareDB']        = JRequest::getBool('shareDB');
		$enteredvalues['toDBType']       = JRequest::getCmd('toDBType', null);
		$enteredvalues['toDBHost']       = JRequest::getCmd('toDBHost', null);
	   $enteredvalues['toDBName']       = (string) preg_replace( '/[^A-Z0-9_\.\-{}]/i', '', 
	                                                             JRequest::getString('toDBName', ''));
	   $enteredvalues['toDBUser']       = (string) preg_replace( '/[^A-Za-z0-9_\.\,\;\:\=\-\+\*\/\@\#\$\£!\(\){}\[\]§]/i', '', 
	                                                             JRequest::getVar( 'toDBUser', '', 'default', 'username'));
		$enteredvalues['toDBPsw']        = (string) preg_replace( '/[^A-Za-z0-9_\.\,\;\:\=\-\+\*\/\@\#\$\£!\(\){}\[\]§]/i', '', 
	                                                             JRequest::getVar( 'toDBPsw', '', 'default', 'username'));
		$enteredvalues['toPrefix']       = JRequest::getString('toPrefix', null);
		$enteredvalues['newAdminEmail']  = JRequest::getString('newAdminEmail', null);
		$enteredvalues['newAdminPsw']    = JRequest::getString('newAdminPsw', null);
		$enteredvalues['deploy_dir']     = JRequest::getString('deploy_dir', null);
		$enteredvalues['deploy_create']  = JRequest::getString('deploy_create', null);
		$enteredvalues['alias_link']     = JRequest::getString('alias_link', null);
		$enteredvalues['media_dir']      = JRequest::getString('media_dir', null);
		$enteredvalues['images_dir']     = JRequest::getString('images_dir', null);
		$enteredvalues['templates_dir']  = JRequest::getString('templates_dir', null);
		$enteredvalues['tmp_dir']        = JRequest::getString('tmp_dir', null);

		$enteredvalues['toFTP_enable']   = JRequest::getString('toFTP_enable', null);
		$enteredvalues['toFTP_host']     = JRequest::getString('toFTP_host', null);
		$enteredvalues['toFTP_port']     = JRequest::getInt('toFTP_port', null);
		$enteredvalues['toFTP_user']     = JRequest::getString('toFTP_user', null);
		$enteredvalues['toFTP_psw']      = JRequest::getString('toFTP_psw', null);
		$enteredvalues['toFTP_rootpath'] = JRequest::getString('toFTP_rootpath', null);

		$enteredvalues['isnew']          = (JRequest::getInt('isnew', 0)==1) ? true : false;

	   // Prepare processing
      $site_dir  = JPATH_ROOT;   // Use the Master site directory

		// Assign the values
		$this->assignAds();
		$this->assignRef('id',        $id);
		$this->assignRef('site_dir',  $site_dir);
		$this->assignRef('domains',   $enteredvalues['domains']);
		$this->assign('isnew',        $enteredvalues['isnew']);
		
		// Cleanup enteredValues to remove all 'null' entries
		foreach( $enteredvalues as $key => $value) {
		   if ( empty( $enteredvalues[$key])) {
		      unset( $enteredvalues[$key]);
		   }
		}

	   // deploy the site using the site id
	   $model = $this->getModel();
		if ( !$model->deploySite( $enteredvalues)) {
		   $msg = $model->getError();
		   if ( empty( $msg)) {
		      $msg = JText::_('SITE_SAVE_DEPLOY_ERR');
		   }
			JError::raiseWarning( 500, $msg);
			return '';
		}
		// Re-create the master index containing all the host name and associated directories
		$model->createMasterIndex();
		
		$msgid = ($this->isnew) ? 'SITE_DEPLOYED' : 'SITE_UPDATED';
		if ( !empty( $enteredvalues['indexDomains'])) {
   		$domainStr = implode(",", $enteredvalues['indexDomains']);
		}
		else {
   		$domainStr = implode(",", $this->domains);
		}
		// If there is NO deploy directory 
		// - Case of Windows 
		// - OR Unix with Symbolic Link forbidden
		// - OR authorized but not filled by the user
	   $deploy_dir  = &$this->get('DeployDir');
	   if ( empty( $deploy_dir)) {
	      // Use the Master site directory
	      $deploy_dir  = JPATH_ROOT;   
	   }
		$msg = JText::sprintf( $msgid, $this->id, $domainStr, $deploy_dir);
		return $msg;
	}
	
   //------------ getSiteToolTips ---------------
	function getSiteToolTips( $site)
	{
	   if ( !empty( $site->site_dir)) {
	      $site_dir = $site->site_dir;
	   }
	   else {
	      $site_dir = JPATH_ROOT;
	   }

	   $str = $site->sitename . '<br/><br/>'
	        . '<i><b>'  . JText::_( 'DNS mapping') . ' :</b></i><br/>'
	        . '- ' . $site_dir;

	   if ( !empty( $site->deploy_dir)) {
   	   $str .= '<br/>'
   	        . '- ' . $site->deploy_dir;
	   }
	   
	   if ( !empty( $site->fromTemplateID)) {
   	   $str .= '<br/>'
	           .  '<i><b>'  . JText::_( 'SITE_EDIT_TEMPLATES') . ' :</b></i> '
	           .  $site->fromTemplateID;
	      $template = & $site->getTemplate();
   	   $str .= '&nbsp;(&nbsp;' . $template->fromSiteID . '&nbsp;)';
	      
	   }

	   return JText::_( 'Edit Site' ). '::' . $str ;
	}
	
} // End class
