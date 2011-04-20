<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class JElementVmfield extends JElement
{
	function fetchElement($name, $value, &$node, $control_name)
	{
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php')){
			echo 'AcyMailing is required for this plugin';
			return;
		}
		$db =& JFactory::getDBO();
		$fields = reset($db->getTableFields(acymailing::table('vm_user_info',false)));
		$dropdown = array();
		foreach($fields as $oneField => $fieldType){
			$dropdown[] = JHTML::_('select.option', $oneField,$oneField);
		}
		return JHTML::_('select.genericlist', $dropdown, $control_name.'['.$name.']' , 'size="1"', 'value', 'text', $value);
	}
}