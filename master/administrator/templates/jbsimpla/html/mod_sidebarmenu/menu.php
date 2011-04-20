<?php
/**
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 *
 * @derivitive	Joomla Bamboo - customized for jbsimpla admin theme
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.base.tree');

class JAdmimSidebarMenu extends JTree
{

	function __construct()
	{
		$this->_root = new JSidebarNode('ROOT');
		$this->_current = & $this->_root;
	}

	function renderMenu($id = 'menu', $class = '', $hide = false)
	{
		global $mainframe;
		
		// gets menu in HTML
		$menu = $this->parseMenu();
		
		// checks if a menu is current
		$opened = $this->checkOpened($menu);
		
		// if a menu link is current
		if ($opened) {
			echo $menu;
		}
		// otherwise attempt to remove extra URI information and find current
		else {
			if ($_SERVER["QUERY_STRING"] !== '') $menu = $this->setOpened($menu);
			echo $menu;
		}

	}
	
	function checkOpened($menu) {
		return strpos($menu, 'opened');
	}

	function setOpened($menu) {
		$uri = $_SERVER["QUERY_STRING"];
		if (strpos($uri,'&')) {
			$uri = substr($uri,0, strpos($uri,'&'));
		}
		$replace = 'index.php?'.$uri.'" class="current">';
		$search = '/index.php\?'.$uri.'.*?">/';
		$newmenu = preg_replace($search, $replace, $menu, 1);
		return $newmenu;
	}
	// Makes UL list of menu items
	function parseMenu() {
		$hide = JRequest::getInt('hidemainmenu');
		$topitems = $this->_current->_children;
		$items = $topitems[0]->_parent->_children;
		
		$uri = "index.php?".$_SERVER["QUERY_STRING"];
		$uri = str_replace('&','&amp;', $uri);
		
		if ($uri == 'index.php?') $uri = 'index.php';
		if (strpos($_SERVER['PHP_SELF'], "index2.php")) $uri = 'index2.php';
		
		$current = false;
		$subcurrent = false;
	
		
		$menu = '<ul id="menu"';
		if ($hide) $menu .= ' class="hide"'; 
		$menu .= '>';

		foreach($items[0]->_parent->_children as $item)
		{
				$children = '<ul>';
				foreach($item->_children as $child) 
				{
					// 3rd level
					foreach ($child->_children as $subchild)
					{
						if ($subchild->link == $uri) {
							$current = true;
							$subcurrent = true;
						}
					}

					// End 3rd level					
					// 2nd level
					$children .= '<li><a href="'.$child->link.'"'; 
					if ($subcurrent) { $children .= ' class="current"'; $subcurrent = false; }
					if ($child->link == $uri) { 
						$children .= ' class="current"'; 
						$current = true;
					}
					$children .= '>'.$child->title.'</a>';
					$children .= '</li>';

				}
				$children .= '</ul>';
				
				// 1st level
				$menu .= '<li class="node"><a class="top';
				if ($current == true) {
					$current = false;
					$menu .= ' opened';
				}
				$menu .= '">'.$item->title.'</a>'.$children.'</li>';
		}
		
		$menu .= "</ul>";
		return $menu;
	}
}

class JSidebarNode extends JNode
{
	/**
	 * Node Title
	 */
	var $title = null;

	/**
	 * Node Id
	 */
	var $id = null;

	/**
	 * Node Link
	 */
	var $link = null;

	/**
	 * CSS Class for node
	 */
	var $class = null;

	/**
	 * Active Node?
	 */
	var $active = false;

	function __construct($title, $link = null, $class = null, $active = false)
	{
		$this->title	= $title;
		$this->link		= JFilterOutput::ampReplace($link);
		$this->class	= $class;
		$this->active	= $active;
		$this->id		= str_replace(" ","-",$title);

	}
}

?>