<?php 
/*
// JoomlaWorks "Simple RSS Feed Reader" Module for Joomla! 1.5.x - Version 2.2
// Copyright (c) 2006 - 2010 JoomlaWorks, a business unit of Nuevvo Webware Ltd.
// Released under the GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
// More info at http://www.joomlaworks.gr
// Designed and developed by the JoomlaWorks team
// ***Last update: September 22nd, 2010***
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class JElementTemplate extends JElement {

	var $_name = 'template';

	function fetchElement($name, $value, & $node, $control_name) {
		
		jimport('joomla.filesystem.folder');
		$moduleTemplatesPath = JPATH_SITE.DS.'modules'.DS.'mod_jw_srfr'.DS.'tmpl';
		$moduleTemplatesFolders = JFolder::folders($moduleTemplatesPath);
		
		$db =& JFactory::getDBO();
		$query = "SELECT template FROM #__templates_menu WHERE client_id = 0 AND menuid = 0";
		$db->setQuery($query);
		$defaultemplate = $db->loadResult();
		$templatePath = JPATH_SITE.DS.'templates'.DS.$defaultemplate.DS.'html'.DS.'mod_jw_srfr';
		
		if (JFolder::exists($templatePath)){
			$templateFolders = JFolder::folders($templatePath);
			$folders = @array_merge($templateFolders, $moduleTemplatesFolders);
			$folders = @array_unique($folders);
		} else {
			$folders = $moduleTemplatesFolders;
		}

		$exclude = 'default';
		
		$options = array ();
		foreach ($folders as $folder) {
			if (preg_match(chr(1).$exclude.chr(1), $folder)) {	
				continue;
			}
			$options[] = JHTML::_('select.option', $folder, $folder);
		}
		
		array_unshift($options, JHTML::_('select.option','default','-- '.JText::_('Default').' --'));
		
		return JHTML::_('select.genericlist', $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'value', 'text', $value, $control_name.$name);
	
	}

}
