<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class welcomeType{
	function welcomeType(){
		$query = 'SELECT `subject`, `mailid` FROM '.acymailing::table('mail').' WHERE `type`= \'welcome\'';
		$db =& JFactory::getDBO();
		$db->setQuery($query);
		$messages = $db->loadObjectList();
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0', JText::_('NO_WELCOME_MESSAGE') );
		foreach($messages as $oneMessage){
			$this->values[] = JHTML::_('select.option', $oneMessage->mailid, '['.$oneMessage->mailid.'] '.$oneMessage->subject);
		}
	}
	function display($value){
		JHTML::_('behavior.modal','a.modal');
		$linkEdit = 'index.php?option=com_acymailing&amp;tmpl=component&amp;ctrl=email&amp;task=edit&amp;mailid='.$value;
		$linkAdd = 'index.php?option=com_acymailing&amp;tmpl=component&amp;ctrl=email&amp;task=add&amp;type=welcome';
		$style = empty($value) ? 'style="display:none"' : '';
		$text = ' <a '.$style.' class="modal" id="welcome_edit" title="'.JText::_('EDIT_EMAIL',true).'"  href="'.$linkEdit.'" rel="{handler: \'iframe\', size:{x:800, y:500}}"><img class="icon16" src="'.ACYMAILING_IMAGES.'icons/icon-16-edit.png" alt="'.JText::_('ACY_EDIT',true).'"/></a>';
		$text .= ' <a class="modal" id="welcome_add" title="'.JText::_('CREATE_EMAIL',true).'"  href="'.$linkAdd.'" rel="{handler: \'iframe\', size:{x:800, y:500}}"><img class="icon16" src="'.ACYMAILING_IMAGES.'icons/icon-16-add.png" alt="'.JText::_('CREATE_EMAIL',true).'"/></a>';
		return JHTML::_('select.genericlist',   $this->values, 'data[list][welmailid]', 'class="inputbox" size="1" onchange="changeMessage(\'welcome\',this.value);"', 'value', 'text', (int) $value ).$text;
	}
}