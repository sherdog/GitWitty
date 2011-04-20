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

class JAdminDropdownMenu extends JTree
{

	function __construct()
	{
		$this->_root = new JDropdownNode('ROOT');
		$this->_current = & $this->_root;
	}

	function renderMenu($id = 'menu', $class = '')
	{
		global $mainframe;

		$depth = 1;
		$toplevel = 1;
		
		$topitems = $this->_current->_children;
		$items = $topitems[0]->_parent->_children;
		
		$uri = "index.php?".$_SERVER["QUERY_STRING"];
		$uri = str_replace('&','&amp;', $uri);
		
		echo "<form>\n<select id=\"dropdownnav\" onchange=\"nav()\">\n<option value=\"false\">Quick Navigation</option>\n";
		
		foreach($items[0]->_parent->_children as $item)
		{
				echo '<option value="false" class="dropdown_inactive"';
				if ($item->link == $uri) echo ' selected="selected"'; 
				echo '> :: '.$item->title.' :: </option>';
				
				foreach($item->_children as $child) 
				{
					echo '<option value="'.$child->link.'"'; 
					if ($child->link == $uri) echo ' selected="selected"'; 
					echo '>&nbsp;&nbsp;&nbsp;&nbsp;'.$child->title.'</option>';
					
					foreach ($child->_children as $subchild)
					{
						echo '<option value="'.$subchild->link.'"';
						if ($subchild->link == $uri) echo ' selected="selected"'; 
						echo '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$subchild->title.'</option>';
					}
				}
		}
		
		echo "</select>\n</form>";

	}

}

class JDropdownNode extends JNode
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