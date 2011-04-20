<?php
/**
 * @package RokQuickLinks - RocketTheme
 * @version 1.5.0 September 1, 2010
 * @author RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2010 RocketTheme, LLC
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die( 'Restricted access' );

class rokQuickLinksHelper
{
	function isConfigured(&$params) {
		if ($params->get('icon-1') == '') {
			echo '<p>RokQuickLinks is not configured, please <a href="'.rokQuickLinksHelper::getConfigureLink().'">configure it now...</a></p>';
			return false;
		} else {
			return true;
		}
		
	}
	
	function getConfigureLink() {
		$db =& JFactory::getDBO();
		$db->setQuery('select id from #__modules where module="mod_rokquicklinks" and client_id=1');
		$cid = $db->loadResult();
		return 'index.php?option=com_modules&client=1&task=edit&cid%5B%5D='.$cid;
	
	}
	
	function getLinks(&$params) {
	
		$links = array();
	
		// get data
		for ($x=1;$x<=6;$x++) {
			$icon = $params->get('icon-'.$x);
			$link = $params->get('link-'.$x);
			$title = $params->get('title-'.$x);
			
			if ($icon == '-1')
				$links[] = null;
			else
				$links[] = array($icon,$link,$title);
		}
		return $links;
	
	}
	
	function getImagePathUrl($image) {
		
		return 'modules/mod_rokquicklinks/tmpl/icons/'.$image.'.png';
		
	}
	
}
