<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class emailactionType{
	function emailactionType(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'noaction',JText::_('NO_ACTION'));
		$this->values[] = JHTML::_('select.option', 'delete',JText::_('DELETE_EMAIL'));
		$this->values[] = JHTML::_('select.option', 'forward',JText::_('FORWARD_EMAIL'));
		$js = "function updateEmailAction(num){";
			$js .= "forwardarea = window.document.getElementById('bounce_email_'+num).value;";
			$js .= "if(forwardarea == 'forward') {window.document.getElementById('config_bounce_forward_'+num).style.display = 'block';}else{window.document.getElementById('config_bounce_forward_'+num).style.display = 'none';}";
		$js .= '}';
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
		$this->config = acymailing::config();
	}
	function display($num,$value = ''){
		$js ='window.addEvent(\'domready\', function(){ updateEmailAction(\''.$num.'\'); });';
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
		$return = JHTML::_('select.genericlist', $this->values, 'config[bounce_email_'.$num.']' , 'size="1" onchange="updateEmailAction(\''.$num.'\')"', 'value', 'text', $value,'bounce_email_'.$num);
		$return .= '<input type="text" value="'.$this->config->get('bounce_forward_'.$num,$this->config->get('reply_email')).'" name="config[bounce_forward_'.$num.']" id="config_bounce_forward_'.$num.'"/>';
		return $return;
	}
}