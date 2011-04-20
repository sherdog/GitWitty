<?php
/**
 * @package RokAdminAudit - RocketTheme
 * @version 1.5.0 September 1, 2010
 * @author RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2010 RocketTheme, LLC
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die( 'Restricted access' );

class rokAdminAuditHelper
{
	function getRows(&$params) {
	
		$db = JFactory::getDBO();
		$session =& JFactory::getSession();
		
		$limit = intval($params->get('limit',5));
		$detail_filter = $params->get('detail_filter','low');
		$where = '';
		
		if ($detail_filter == 'low') {
			$where = 'and (r.task = "apply" or r.task = "save")'; 	
		} elseif ($detail_filter == 'medium') {
			$where = 'and (r.task != "cancel" and r.task != "preview" and r.option != "com_cpanel")';
		}
		
		// get admin activity
		$query = 'select r.*, u.name, u.username, u.email,c.name as extension from #__rokadminaudit as r, #__users as u, #__components as c where r.user_id = u.id and c.option = r.option '.$where.' order by id desc limit '. intval($limit);
		$db->setQuery($query);
		$rows = $db->loadObjectList();
	
		return $rows;

	}
	
	function getDescription(&$row) {
	
		$title = '';
		$task = 'Undefined Task';
		$tasks = array('' => 'Default View',
					   'cancel' => 'Canceled',
					   'preview' => 'Previewed',
					   'edit' => 'Edited',
					   'save' => 'Saved',
					   'apply' => 'Saved');
					   
		if ($row->task == 'save' or $row->task == 'apply') {
			$link = $row->referrer;
		} else {
			$link = $row->page;
		}
		
		if (isset($row->title) && $row->title != '') $title = ': <em>'.$row->title.'</em>';
		if (isset($tasks[$row->task])) $task = $tasks[$row->task];
		if ($row->option == 'com_cpanel') $row->extension = JText::_('Site Dashboard');
		
		return $row->extension.': <a href="'.$link.'">'.$task.$title.'</a>';
	
	
	}
	
	function purgeData(&$params) {
	
		$purgedays = intval($params->get('purgedays',20));
		
		$db = JFactory::getDBO();
		$query = 'delete from jos_rokadminaudit where timestamp <= date_sub(curdate(),interval '.$purgedays.' day)';
		$db->setQuery($query);
		$db->query();
	
	}
	
	/**
	 * Get either a Gravatar URL or complete image tag for a specified email address.
	 *
	 * @param string $email The email address
	 * @param string $s Size in pixels, defaults to 80px [ 1 - 512 ]
	 * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
	 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
	 * @param boole $img True to return a complete IMG tag False for just the URL
	 * @param array $atts Optional, additional key/value attributes to include in the IMG tag
	 * @return String containing either just a URL or a complete image tag
	 * @source http://gravatar.com/site/implement/images/php/
	 */
	function getGravatar( $email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array() ) {
		$url = 'http://www.gravatar.com/avatar/';
		$url .= md5( strtolower( trim( $email ) ) );
		$url .= "?s=$s&d=$d&r=$r";
		if ( $img ) {
			$url = '<img src="' . $url . '"';
			foreach ( $atts as $key => $val )
				$url .= ' ' . $key . '="' . $val . '"';
			$url .= ' />';
		}
		return $url;
	}
}
