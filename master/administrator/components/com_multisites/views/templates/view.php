<?php
/**
 * @file       view.php
 * @version    1.2.42
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
 * - 01-OCT-2008 V1.1.0 : Initial version
 * - 22-NOV-2008 V1.1.0 : Fix language title of the webpage.
 *                        Use new Symbolic Link detection.
 * - 24-DEC-2008 V1.1.8 : Fix update of the template to reset the field before saving.
 *                        This allow to take in account the fields that are deleted.
 * - 11-MAY-2009 V1.2.0 : Add the DBSharing and new DB parameters.
 * - 21-JUN-2009 V1.2.0 RC2 : Review DB User name and password sanitisation.
 *                            Special character are now: '_.,;:=-+/*@#$£!&(){}[]<>§'
 * - 18-NOV-2009 V1.2.13 : Add the FTP parameters.
 * - 05-DEC-2009 V1.2.14 : Add Joomla 1.6 alpha 2 compatibility.
 * - 07-MAR-2010 V1.2.23 : Add the possibility to create a new template like an existing one.
 * - 12-JUN-2010 V1.2.32 : Add Joomla 1.6 beta 2 compatibility.
 * - 05-NOV-2010 V1.2.42 : Add compatibility with Joomla 1.6 beta 13
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.view');

require_once( JPATH_COMPONENT .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');

// ===========================================================
//            MultisitesViewTemplates class
// ===========================================================
/**
 * @brief Content the different Views available for the Template Manager.
 *
 * Views available are:
 * - display() is used to display the list of templates. This is the default view;
 * - editForm() is the form used by Edit or Add task.\n
 *   It displays a simple form with the site information;
 * - deleteForm() is the confirmation form when the 'delete' task is triggered;
 * - saveTemplate() deploy or update site information.
 */
class MultisitesViewTemplates extends JView
{
   // Private members
   var $_formName   = 'Template';
   var $_lcFormName = 'template';

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
		JToolBarHelper::title( JText::_( 'TEMPLATE_LIST_TITLE' ), 'config.png' );
		JToolBarHelper::customX( "delete$formName", 'delete.png', 'delete_f2.png', 'Delete', true );
		JToolBarHelper::editListX( "edit$formName");
		JToolBarHelper::addNewX( "add$formName" );
		JToolBarHelper::customX( "addLike$formName", 'copy.png', 'copy.png', 'New Like', true );
		JToolBarHelper::help( 'screen.' .$lcFormName. 'manager', true);

		$document = & JFactory::getDocument();
		$document->setTitle(JText::_('TEMPLATE_LIST_TITLE'));

		// retreive the filters and parameters that limit the query
		$filters = &$this->_getFilters();
		
		// Call the model
		$model = &$this->getModel();
		$model->setFilters( $filters);
		$templates		   = &$this->get('Templates');
		$this->assignRef('templates', $templates);

		$lists		= &$this->_getViewLists( $filters);
		$pagination	= &$this->_getPagination( $filters, $this->get('CountAll'));

		// Assign view variable with will be used by the template
		$this->assignAds();
		$this->assignRef('pagination', $pagination);
		$this->assignRef('lists', $lists);
		$this->assignRef('limitstart', $limitstart);
		$this->assignRef('option',       $option);

		JHTML::_('behavior.tooltip');

		// Display the template
		parent::display($tpl);
	}


   //------------ getTemplateToolTips ---------------
	function getTemplateToolTips( $id, $template)
	{
	   $groupName = (!empty( $template['groupName']))
	              ? '<tr><td nowrap=\'nowrap\'>Group name:</td<td>'. htmlspecialchars( $template['groupName']). '</td</tr>'
	              : '';
	   $title = (!empty( $template['title']))
	              ? '<tr><td>Title:</td<td>'.htmlspecialchars( $template['title']). '</td</tr>'
	              : '';
	   $description = (!empty( $template['description']))
	              ? '<tr valign=\'top\'><td>Description:</td><td>'. htmlspecialchars($template['description']). '</td</tr>'
	              : '';

	   $deploy_dir = '';
	   if ( $this->canShowDeployDir()) {
	      $deploy_dir = '<li>Deploy dir: '.$template['deploy_dir']. '</li>';
	   }
	   
	   $media_dir = (!empty( $template['media_dir']))
	              ? '<li>Media dir: '.$template['media_dir']. '</li>'
	              : '';
	   $images_dir = (!empty( $template['images_dir']))
	              ? '<li>Images dir: '.$template['images_dir']. '</li>'
	              : '';
	   $templates_dir = (!empty( $template['templates_dir']))
	              ? '<li>Templates dir: '.$template['templates_dir']. '</li>'
	              : '';
	   $tmp_dir = (!empty( $template['tmp_dir']))
	              ? '<li>Temporary dir: '.$template['tmp_dir']. '</li>'
	              : '';
	   
	   $result = JText::_( 'Edit the template' )
	           . '::'
	           . $id
	           . '<table border=\'0\'>'
	           . $groupName
	           . $title
	           . $description
	           . '</table>'
	           . '<ul>'
	           . $deploy_dir
	           . $media_dir
	           . $images_dir
	           . $templates_dir
	           . $tmp_dir
	           . '</ul>'
	           ;
	   return $result;
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
		$client                 = JRequest::getWord( 'filter_client', 'template' );
		// Retreive search filter
		$search				      = $mainframe->getUserStateFromRequest( "$option.$client.search",			   'search',			   '',			'string' );
		$filters['search']	   = JString::strtolower( $search );
		// Retreive filter combo values
		$filters['sitename']	   = $mainframe->getUserStateFromRequest( "$option.$client.filter_sitename",  'filter_sitename',	'[unselected]',			'string' );
		$filters['host']	      = $mainframe->getUserStateFromRequest( "$option.$client.filter_host",      'filter_host',	      '[unselected]',			'string' );
		$filters['db']	         = $mainframe->getUserStateFromRequest( "$option.$client.filter_db",        'filter_db',	      '[unselected]',			'string' );
		$filters['site_ids']	   = $mainframe->getUserStateFromRequest( "$option.$client.filter_site_ids",  'filter_site_ids',	'[unselected]',			'string' );
		$filters['users']	      = $mainframe->getUserStateFromRequest( "$option.$client.filter_users",     'filter_users',	   '[unselected]',			'string' );
		// Retreive selected sort column and direction
		$filters['order']		   = $mainframe->getUserStateFromRequest( "$option.$client.filter_order",		'filter_order',		'',	      'cmd' );
		$filters['order_Dir']	= $mainframe->getUserStateFromRequest( "$option.$client.filter_order_Dir",	'filter_order_Dir',	'',			'word' );
		// Retreive the limit for display
		$filters['limit']		   = $mainframe->getUserStateFromRequest( 'global.list.limit',                'limit',             $mainframe->getCfg('list_limit'), 'int' );
		$filters['limitstart']	= $mainframe->getUserStateFromRequest( $option.'.limitstart',              'limitstart',        0, 'int' );

		return $filters;
	}

   //------------ _getViewLists ---------------
	function &_getViewLists( &$filters)
	{
	   $model =& $this->getModel( 'Manage');
	   if ( is_object( $model)) {
   	   $sites =  $model->getSites();
   	   
   		// Filter combo
   		$lists['sitename']   = MultisitesHelper::getSiteNameList( $sites, $filters['sitename']);
   		$lists['dbserver']	= MultisitesHelper::getDBServerList( $sites, $filters['host']);
   		$lists['dbname']	   = MultisitesHelper::getDBNameList(   $sites, $filters['db']);
   		$lists['site_ids']	= MultisitesHelper::getSiteIdsList(  $sites, $filters['site_ids']);

//   		$lists['users']	   = MultisitesHelper::getSitesUsersList(  $sites, $filters['site_ids']);
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

   //------------ canShowDeployDir ---------------
   /**
    * @brief Check if the deploy directory is available (Only Unix).
    */
	function canShowDeployDir()
	{
	   return MultisitesHelper::isSymbolicLinks();
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
		JToolBarHelper::title(  JText::_( 'Template confirm' ) . ': <small><small>[ '. JText::_( 'Delete' ) .' ]</small></small>', 'config.png' );
		JToolBarHelper::custom( 'doDeleteTemplate', 'delete.png', 'delete_f2.png', 'Delete', false );
		JToolBarHelper::cancel( 'templates');
		JToolBarHelper::help( 'screen.templatemanager.delete', true );

		// view data
		$template   = &$this->get('CurrentRecord');

		$document = & JFactory::getDocument();
		$document->setTitle('Confirm Delete template: ' . $template->id);

		// Assign value to the view
		$this->assignAds();
		$this->assignRef('template', $template);
		$this->assignRef('option',       $option);

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
		if($edit == 'edit' || $edit == 'newLike')
			$table = &$this->get('CurrentRecord');
		else
			$table = &$this->get('NewRecord');
			
		$this->assignRef('row', $table);

		/*
		 * Set toolbar items for the page
		 */
		$formName   = $this->_formName;
		$lcFormName = $this->_lcFormName;

		$isNew = (($table->id == '') || ($edit == 'newLike'));
		if ( $isNew) {
		   $text = JText::_('New');
		}
		else {
		   $text = JText::_('Edit');
		}
		JToolBarHelper::title( JText::_( 'TEMPLATE_VIEW_EDT_TITLE' ).': <small><small>[ '. $text.' ]</small></small>', 'config.png' );
		JToolBarHelper::custom( "save$formName", 'save.png', 'save_f2.png', 'Save', false );
		JToolBarHelper::cancel( 'templates');
		JToolBarHelper::help( 'screen.' .$lcFormName. 'manager.new', true );

		$document = & JFactory::getDocument();
		$document->setTitle(JText::_('TEMPLATE_VIEW_EDT_TITLE'));

		JHTML::_('behavior.mootools');
		$document->addScript('components/com_multisites/assets/dbsharing.js');
		$document->addStyleSheet('components/com_multisites/assets/dbsharing.css');
		
		$document->addScript('components/com_multisites/assets/inputtree.js');
		JHTML::stylesheet('mootree.css');


		// retreive the filters and parameters that limit the query
		$filters = &$this->_getFilters();
		$filters['site_ids'] = $table->fromSiteID;
		if ( !empty( $table->fromSiteID) && $table->fromSiteID != '[unselected]') {
		   if ( !empty( $table->shareDB) && $table->shareDB) {
   		   $style_showDBFields = 'style="display:none;"';
		   }
		   else {
   		   $style_showDBFields = '';
		   }
		}
		else {
		   $style_showDBFields = 'style="display:none;"';
		}
		$this->assignRef('style_showDBFields', $style_showDBFields);

		if ( isset( $table->toFTP_enable) && ($table->toFTP_enable == '0' || $table->toFTP_enable == '1')) {
   	   $style_showFTPFields = '';
		}
		// if FTP = default
		else {
   	   $style_showFTPFields = 'style="display:none;"';
		}
		$this->assignRef('style_showFTPFields', $style_showFTPFields);
		
		$model      = &$this->getModel();
		$templates	= &$this->get('Templates');
		$this->assignRef('templates', $templates);
		$lists	   = &$this->_getViewLists( $filters);
		
		$symbolicLinks = $this->_computeSymbolicLinks( $table);
		$this->assignRef('symbolicLinks', $symbolicLinks);

		$this->assignAds();
		$this->assignRef('lists', $lists);
		$this->assign('isnew', $isNew);
		
		$this->assign('isCreateView', Jms2WinFactory::isCreateView( $table->fromSiteID));

		$modelSharing  = &$this->getModel( 'dbsharing');
      $xml = $modelSharing->getDBSharing();
   	$treeparams =& $xml->getElementByPath('params');
		$this->assignRef('treeparams'	, $treeparams);
		$this->assign('ignoreUL', false);
		$this->assign('tree_id', ' id="dbsharing-tree"');
		$this->assign('node_id'	, 0);
		$this->assignRef('option',       $option);

		JHTML::_('behavior.tooltip');

		parent::display($tpl);
	}


   //------------ _computeSymbolicLinks ---------------
   /**
    * @brief Fill into the computed symbolic links, the value coming from the template
    */
	function _computeSymbolicLinks( $template)
	{
		$modelManage   = &$this->getModel('Manage');
		$symbolicLinks = $modelManage->compute_default_links();
		
		foreach ( $template->symboliclinks as $key => $symbolicLink) {
		   // Only replace values that correspond to a directory or file present in the master website
		   // If a directory is deleted or removed, the corresponding value in the template will be ignored
		   if ( isset( $symbolicLinks[$key])) {
   		   $symbolicLinks[$key] = $symbolicLink;
		   }
		}
		
		return $symbolicLinks;
	}
	
   //------------ isActionEditable ---------------
   /**
    * @return TRUE when the action allow to enter an additional parameter (file name, path, ...)
    *         FALSE when the action is read only. No additional parameters
    */
	function isActionEditable( $action)
	{
	   if ( strstr( '*copy*unzip*', '*'.$action.'*') !== false) {
	      return true;
	   }
	   return false;
	}

   //------------ _getSymbolicLinks ---------------
   /**
    * @brief Return an array with the list of symbolic links
    */
	function _getSymbolicLinks()
	{
		// Retreive individual Symbolic Links values
		$SL_actions = JRequest::getVar( 'SL_actions', '', 'default', 'array');
		$SL_names   = JRequest::getVar( 'SL_names', '', 'default', 'array');
		$SL_files   = JRequest::getVar( 'SL_files', '', 'default', 'array');
		$SL_readOnly= JRequest::getVar( 'SL_readOnly', '', 'default', 'array');
		
		$symbolicLinks = array();
		foreach( $SL_actions as $i => $action) {
		   // If this is an action that can have a file and that the file is defined
		   if ( $this->isActionEditable( $action)
		     && !empty( $SL_files[$i])) {
		      if ( isset( $SL_readOnly[$i]) && $SL_readOnly[$i]=='true') {
      		   $symbolicLinks[$SL_names[$i]] = array( 'action'    => $action,
                     		                           'file'      => $SL_files[$i],
                     		                           'readOnly'  => true
                     		                         );
		      }
		      else {
      		   $symbolicLinks[$SL_names[$i]] = array( 'action'    => $action,
                     		                           'file'      => $SL_files[$i]
                     		                         );
		      }
		   }
		   else {
		      if ( isset( $SL_readOnly[$i]) && $SL_readOnly[$i]=='true') {
      		   $symbolicLinks[$SL_names[$i]] = array( 'action'    => $action,
      		                                          'readOnly'  => true
      		                                        );
		      }
		      else {
      		   $symbolicLinks[$SL_names[$i]] = array( 'action'    => $action);
		      }
		   }
		}
		
		return $symbolicLinks;
	}
	
   //------------ getSharing ---------------
   /**
    * @brief Return an array with the list of Sharing selections
    */
	function _getSharing()
	{
	   $results = array();
	   if ( !empty( $_REQUEST['params'])) {
   	   // Retreive all the parameters starting with dbsh_
   	   foreach( $_REQUEST['params'] as $key => $value) {
   	      if ( substr( $key, 0, 5) == 'dbsh_') {
         		$results[ $key] = JFilterInput::clean( $value, 'cmd');
   	      }
   	   }
	   }
	   
	   return $results;
	}
	

   //------------ saveTemplate ---------------
   /**
    * @brief This write a configuraton template file
    */
	function saveTemplate($tpl=null)
	{
		$mainframe	= &JFactory::getApplication();
   	$option = JRequest::getCmd('option');
		
	   // Retreive entered values
	   $id = JRequest::getString('id', false);
	   if ( $id === false || empty( $id)) {
	      $msg = JText::_( 'Please provide a template identifier' );
	      $this->setError( $msg);
	      return $msg;
	   }



		$enteredvalues = array();
		$enteredvalues['id']             = $id;
		$fromSiteID                      = JRequest::getString('filter_site_ids', null);
		if ( $fromSiteID == '[unselected]') {
		   $fromSiteID = null;
		}
		$enteredvalues['fromSiteID']     = $fromSiteID;

		$enteredvalues['groupName']      = JRequest::getString('groupName', null);
		$enteredvalues['sku']            = JRequest::getString('sku', null);
		$enteredvalues['title']          = JRequest::getString('title', null);
		$enteredvalues['description']    = isset( $_REQUEST[ 'description']) ? stripslashes( $_REQUEST[ 'description']) : '';
		$enteredvalues['validity']       = JRequest::getInt('validity', null);
		$enteredvalues['validity_unit']  = JRequest::getString('validity_unit', null);
		$enteredvalues['maxsite']        = JRequest::getInt('maxsite', null);
		$enteredvalues['expireurl']      = '';
		$urls = MultisitesHelper::getDomainList( 'expireurl');
		if ( !empty( $urls)) {
		   $enteredvalues['expireurl']   = $urls[0];
		}
		
		$enteredvalues['toDomains']      = MultisitesHelper::getDomainList( 'toDomains');

		$str                             = JRequest::getString('toSiteID', null);
		$enteredvalues['toSiteID']       = (string) preg_replace( '/[^A-Z0-9_\.-{}]/i', '', $str);

		$enteredvalues['shareDB']        = JRequest::getBool('shareDB');
		$enteredvalues['adminUserID']    = JRequest::getInt('adminUserID', null);

		$toDBHost                        = JRequest::getCmd('toDBHost', null);
		if ( !empty( $toDBHost)) { 
		   $enteredvalues['toDBHost']    = $toDBHost;
		}
		
		$str                             = JRequest::getString('toDBName', null);
		if ( !empty( $str)) {
		   $enteredvalues['toDBName']    = (string) preg_replace( '/[^A-Z0-9_\.\-{}]/i', '', $str);
		}

		$str                             = JRequest::getVar( 'toDBUser', null, 'default', 'username');
		if ( !empty( $str)) {
		   $enteredvalues['toDBUser']    = (string) preg_replace( '/[^A-Za-z0-9_\.\,\;\:\=\-\+\*\/\@\#\$\£!\(\){}\[\]§]/i',
		                                                          '', 
		                                                          $str);
		}

		$str                             = JRequest::getVar( 'toDBPsw', null, 'default', 'username');
		if ( !empty( $str)) {
		   $enteredvalues['toDBPsw']     = (string) preg_replace( '/[^A-Za-z0-9_\.\,\;\:\=\-\+\*\/\@\#\$\£!\(\){}\[\]§]/i',
		                                                          '',
		                                                          $str);
		}

		$str                             = JRequest::getString('toPrefix', null);
		$enteredvalues['toPrefix']       = (string) preg_replace( '/[^A-Z0-9_{}]/i', '', $str);

		$deploy_dir                      = JRequest::getString('deploy_dir', null);
		$deploy_create                   = JRequest::getString('deploy_create', null);
		$alias_link                      = JRequest::getString('alias_link', null);
		$media_dir                       = JRequest::getString('media_dir', null);
		$images_dir                      = JRequest::getString('images_dir', null);
		$templates_dir                   = JRequest::getString('templates_dir', null);
		$log_dir                         = JRequest::getString('log_dir', null);
		$tmp_dir                         = JRequest::getString('tmp_dir', null);
		$cache_dir                       = JRequest::getString('cache_dir', null);
		
		if ( !empty( $deploy_dir))       { $enteredvalues['deploy_dir']      = $deploy_dir; }
		if ( !empty( $deploy_create))    { $enteredvalues['deploy_create']   = $deploy_create; }
		if ( !empty( $alias_link))       { $enteredvalues['alias_link']      = $alias_link; }
		if ( !empty( $media_dir))        { $enteredvalues['media_dir']       = $media_dir; }
		if ( !empty( $images_dir))       { $enteredvalues['images_dir']      = $images_dir; }
		if ( !empty( $templates_dir))    { $enteredvalues['templates_dir']   = $templates_dir; }
		if ( !empty( $log_dir))          { $enteredvalues['log_dir']         = $log_dir; }
		if ( !empty( $tmp_dir))          { $enteredvalues['tmp_dir']         = $tmp_dir; }
		if ( !empty( $cache_dir))        { $enteredvalues['cache_dir']       = $cache_dir; }

		// When there is no Site ID
		if ( empty( $fromSiteID)) {
		   // Ensure that media and image folder are empty
		   $enteredvalues['media_dir'] = null;
		   $enteredvalues['images_dir'] = null;
		}

		$toFTP_enable                    = JRequest::getString('toFTP_enable', null);
		$toFTP_host                      = JRequest::getString('toFTP_host', null);
		$toFTP_port                      = JRequest::getInt('toFTP_port', null);
		$toFTP_user                      = JRequest::getString('toFTP_user', null);
		$toFTP_psw                       = JRequest::getString('toFTP_psw', null);
		$toFTP_rootpath                  = JRequest::getString('toFTP_rootpath', null);

		if ( !is_null( $toFTP_enable))   { $enteredvalues['toFTP_enable']    = $toFTP_enable; }
		if ( !empty( $toFTP_host))       { $enteredvalues['toFTP_host']      = $toFTP_host; }
		if ( !empty( $toFTP_port))       { $enteredvalues['toFTP_port']      = $toFTP_port; }
		if ( !empty( $toFTP_user))       { $enteredvalues['toFTP_user']      = $toFTP_user; }
		if ( !empty( $toFTP_psw))        { $enteredvalues['toFTP_psw']       = $toFTP_psw; }
		if ( !empty( $toFTP_rootpath))   { $enteredvalues['toFTP_rootpath']  = $toFTP_rootpath; }
		
		$enteredvalues['isnew']          = (JRequest::getInt('isnew', 0)==1) ? true : false;

		$enteredvalues['symboliclinks']  = $this->_getSymbolicLinks();
		// When there is a replication of a website, also read the sharing rules.
		if ( !empty( $fromSiteID)) {
		   $enteredvalues['dbsharing']   = $this->_getSharing();
		}

		// Assign the values
		$this->assignAds();
		$this->assignRef('id',        $id);
		$this->assign('isnew',        $enteredvalues['isnew']);

	   // deploy the site using the site id
	   $model = $this->getModel();
		if ( !$model->save( $enteredvalues, true)) {
		   $msg = $model->getError();
			JError::raiseWarning( 500, $msg);
			return $msg;
		}
		
		$msgid = ($this->isnew) ? 'TEMPLATE_CREATED' : 'TEMPLATE_UPDATED';
		$msg = JText::sprintf( $msgid, $this->id);
		return $msg;
	}


   //------------ getDBSharingLevel ---------------
	function getDBSharingLevel($param, $ignoreUL = false)
	{
		$this->tree_id = null;
		$txt = null;
		if (count($param->children())) {
			$tmp = $this->treeparams;
			$this->treeparams = $param;

			$ignoreUL_save = $this->ignoreUL;
			$this->ignoreUL = $ignoreUL;

			$txt = $this->loadTemplate('sharing');

			$this->treeparams = $tmp;
			$this->ignoreUL = $ignoreUL_save;
		}
		return $txt;
	}

} // End class
