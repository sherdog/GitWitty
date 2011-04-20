<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class urlmailType{
	function urlmailType(){
		$query = 'SELECT b.subject,b.mailid,count(distinct a.urlid) as totalmail FROM '.acymailing::table('urlclick').' as a';
		$query .= ' LEFT JOIN '.acymailing::table('mail').' as b ON a.mailid = b.mailid';
		$query .= ' GROUP BY a.mailid ORDER BY a.mailid DESC';
		$db =& JFactory::getDBO();
		$db->setQuery($query);
		$mails = $db->loadObjectList();
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0', JText::_('ALL_EMAILS') );
		foreach($mails as $oneMail){
			$this->values[] = JHTML::_('select.option', $oneMail->mailid, $oneMail->subject.' ( '.$oneMail->totalmail.' )' );
		}
	}
	function display($map,$value){
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', (int) $value );
	}
}