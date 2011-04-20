<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class generatemodeType{
	function generatemodeType(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 1,JText::_('AUTONEWS_SEND'));
		$this->values[] = JHTML::_('select.option', 0,JText::_('AUTONEWS_WAIT'));
	}
	function display($map,$value){
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" ', 'value', 'text',(int) $value );
	}
}