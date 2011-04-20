<?php
/**
 * @copyright	Copyright (C) 2009 Joomla Bamboo. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
 
// no direct access
defined('_JEXEC') or die('Restricted access');

function modChrome_contentboxheader($module, &$params, &$attribs)
{
	if($module->content)
	{
		$content = preg_replace('/<.*>/', '', $module->content);
		 echo "<h3>$content</h3>";
	}
}

function modChrome_contentboxsubmenu($module, &$params, &$attribs)
{
	if($module->content)
	{
		$content = $module->content;
		$content = preg_replace('/<div.*>/', '', $content);
		$content = preg_replace('/<\/div.*>/', '', $content);
		echo $content; 
	}
}

?>