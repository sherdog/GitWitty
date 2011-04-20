<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class diagramType{
	function diagramType(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'lists', JText::_('NB_SUB_UNSUB') );
		$this->values[] = JHTML::_('select.option', 'subscription', JText::_('SUB_HISTORY') );
	}
	function display($map,$value){
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text',$value);
	}
}