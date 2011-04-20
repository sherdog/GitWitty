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

function pagination_list_footer($list)
{
	// Initialize variables
	$lang =& JFactory::getLanguage();
	
	$html = "<div class=\"limit\">".JText::_('Display Num').$list['limitfield']."</div>\n";
	$html .= "<div class=\"pagination\">\n";
	
	$html .= $list['pageslinks'];
	$html .= "</div>\n<div class=\"pagecounter\">".$list['pagescounter']."</div>";

	$html .= "\n<input type=\"hidden\" name=\"limitstart\" value=\"".$list['limitstart']."\" />";

	return $html;
}

function pagination_list_render($list)
{
	// Initialize variables
	$lang =& JFactory::getLanguage();
	$html = null;

		$html .= $list['start']['data'];
		$html .= $list['previous']['data'];

	foreach( $list['pages'] as $page ) {
		$html .= $page['data'];
	}

		$html .= $list['next']['data'];
		$html .= $list['end']['data'];

	return $html;
}

function pagination_item_active(&$item)
{
	if($item->base>0)
		return "<a href=\"#\" class=\"number\" title=\"".$item->text."\" onclick=\"javascript: document.adminForm.limitstart.value=".$item->base."; submitform();return false;\">".$item->text."</a>";
	else
		return "<a href=\"#\" class=\"number\" title=\"".$item->text."\" onclick=\"javascript: document.adminForm.limitstart.value=0; submitform();return false;\">".$item->text."</a>";
}

function pagination_item_inactive(&$item)
{
	return "<a class=\"number current\">".$item->text."</span>";
}
?>
