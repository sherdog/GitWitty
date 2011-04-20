<?php
/*
* Module: Version Notifier
* Author: Jeremy Wilken @ Gnome on the run, with inspiration and working from Samuel Moffatt's jupdateman extension
* Author URL: www.gnomeontherun.com
* License: GNU GPL v2
* Module Description: This administrator module puts a little message in the status bar to help remind users if their Joomla version is out of date.
* 
* Built to work with the jupdateman component by Samuel Moffatt (http://joomlacode.org/gf/project/pasamioprojects/frs/), will work independently however.
* I recommend the jupdateman component for easy Joomla updating.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$target = dirname(__FILE__).DS.'joomla_update.xml';

if (file_exists($target)) {
	$time = filemtime($target);
	if ($time < time() * 86400) {
		checkVersion($target);
	}
	else {
		loadFile($target);
	}
}
else {
	loadFile($target);
}

function loadFile($target) {
	$xml = file_get_contents("http://www.joomlabamboo.com/joomla_update.xml");
	if (!file_put_contents($target,$xml)) { echo '<span class="version-update">'.JText::_('UPDATE ERROR LOAD').'</span>'; }
	checkVersion($target);
}

function checkVersion($target) {
	if ($parser = simplexml_load_file($target)) {
	
		$version = explode('.',$parser->attributes('release'));
		$current = explode('.',JVERSION);
		
		$i = 0;
		$update = false;
		foreach ($version as $x) {
			if ($x > $current[$i]) {
				$update = true;
			}
			$i++;
		}
		if ($update == true) {
			jimport('joomla.application.component.helper');
			$component = JComponentHelper::getComponent('com_jupdateman', true);
			if ($component->enabled) { 
				$link = JRoute::_('index.php?option=com_jupdateman');
				$target = "_top";
			}
			else {
				$link = "http://www.joomla.org/download.html";
				$target = "_blank";
			}
			echo '<span class="version-update"><a href="'.$link.'" target="'.$target.'">'.JText::_('UPDATE AVAILABLE').'</a></span>';
		}
		else {
			echo '<span class="version-uptodate">'.JText::_('UPTODATE').'</span>';
		}
	}
	else {
		echo '<span class="version-update">'.JText::_('UPDATE ERROR FILE').'</span>';
	}
}
?>