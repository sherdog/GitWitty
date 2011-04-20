<?php
/**
* @version		$Id: mod_slideshow.php 00002 2009-10-30 00:00:00 umitkenan $
* @package		Joomla
* @subpackage	Horizontal SlideShow Module
* @link 		http://www.jt.gen.tr
* @copyright	Copyright (C) Joomla Türkçe Eðitim ve Destek Sitesi. http://www.jt.gen.tr 
* @license		GNU/GPL
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Include the syndicate functions only once
require_once( dirname(__FILE__).DS.'helper.php' );

// Getting content
$content = modSlideShowHelper::getParameterList($params);

// Selecting layout
// Default layout is horizontal
$layout = $params->get('layout', 'horizontal');

$path = JModuleHelper::getLayoutPath('mod_slideshow', $layout);
if (file_exists($path)) {
	require($path);
}