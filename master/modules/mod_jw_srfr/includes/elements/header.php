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

class JElementHeader extends JElement {
	var	$_name = 'header';
	
	function fetchElement($name, $value, &$node, $control_name){
		// Output
		return '
		<div style="font-weight:normal;font-size:12px;color:#fff;padding:4px;margin:0;background:#0B55C4;">
			'.JText::_($value).'
		</div>
		';
	}
	
}
