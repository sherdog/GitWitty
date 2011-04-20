<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class connectionType{
	function connectionType(){
		$connections = array(
					'imap' => 'IMAP',
					'pop3' => 'POP3',
					'pear' => 'POP3 (without imap extension)',
					'nntp' => 'NNTP'
					);
		$this->values = array();
		foreach($connections as $code => $string){
			$this->values[] = JHTML::_('select.option', $code,$string);
		}
	}
	function display($map,$value){
		return JHTML::_('select.genericlist', $this->values, $map , 'size="1"', 'value', 'text', $value);
	}
}