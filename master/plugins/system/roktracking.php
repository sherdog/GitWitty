<?php
/**
 * @package RokTracking System Plugin - RocketTheme
 * @version 1.5.0 September 1, 2010
 * @author RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2010 RocketTheme, LLC
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted index access' );

jimport('joomla.plugin.plugin');

class plgSystemRokTracking extends JPlugin
{

	function plgSystemRokTracking(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}


	function onAfterRoute()
	{
		global $mainframe;

		// is user in admin area?
		if($mainframe->isAdmin()) {
			// in admin area
			plgSystemRokTracking::_trackAdmin();

		} else {
			// in user area
			plgSystemRokTracking::_trackUser();
		}
		
		
	}
	
	function _trackUser() {
		$user = &JFactory::getUser();
		$session =& JFactory::getSession();
		
		$ipaddress = $_SERVER['REMOTE_ADDR'];
		if (!$ipaddress) $ipaddress = "Unknown";
		$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		$uri = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : '';
		
		$data = new stdClass();
		$data->user_id = $user->id;
		$data->ip = $ipaddress;
		$data->session_id = $session->getId();
		$data->page = $uri;
		$data->referrer = $referrer;
		
		$db = JFactory::getDBO();
		$db->insertObject( '#__rokuserstats', $data, 'id' );
		
		
	
	}
	
	function _trackAdmin() {
		
		$option = JRequest::getString('option','');		
		$task = JRequest::getString('task','');
		$user = &JFactory::getUser();
		$session =& JFactory::getSession();
		
		
		$ipaddress = $_SERVER['REMOTE_ADDR'];
		if (!$ipaddress) $ipaddress = "Unknown";
		if ($option == '') $option = 'com_cpanel';
		$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		$uri = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : '';
//		
		$cid = JRequest::getVar('cid');
		
		if (is_array($cid)) $cid = $cid[0];
		
		$data = new stdClass();
		$data->user_id = $user->id;
		$data->ip = $ipaddress;
		$data->session_id = $session->getId();
		$data->option = $option;
		$data->task = $task;
		$data->page = $uri;
		$data->referrer = $referrer;
		$data->title = JRequest::getVar('title','');
		$data->cid = $cid;
		
		$db = JFactory::getDBO();
		$db->insertObject( '#__rokadminaudit', $data, 'id' );
		
	}
	


	
}
