<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class SendViewSend extends JView
{
	function display($tpl = null)
	{
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function sendconfirm(){
		$mailid = acymailing::getCID('mailid');
		$mailClass = acymailing::get('class.mail');
		$listmailClass = acymailing::get('class.listmail');
		$queueClass = acymailing::get('class.queue');
		$mail = $mailClass->get($mailid);
		$values = null;
		$values->nbqueue = $queueClass->nbQueue($mailid);
		if(empty($values->nbqueue)){
			$lists = $listmailClass->getReceivers($mailid);
			$this->assignRef('lists',$lists);
			$db =& JFactory::getDBO();
			$db->setQuery('SELECT count(subid) FROM `#__acymailing_userstats` WHERE `mailid` = '.intval($mailid));
			$values->alreadySent = $db->loadResult();
		}
		$this->assignRef('values',$values);
		$this->assignRef('mail',$mail);
	}
	function scheduleconfirm(){
		$mailid = acymailing::getCID('mailid');
		$listmailClass = acymailing::get('class.listmail');
		$mailClass = acymailing::get('class.mail');
		$this->assignRef('lists',$listmailClass->getReceivers($mailid));
		$this->assignRef('mail',$mailClass->get($mailid));
	}
	function addqueue(){
		$subid = JRequest::getInt('subid');
		if(empty($subid)) exit;
		$subscriberClass = acymailing::get('class.subscriber');
		$subscriber = $subscriberClass->getFull($subid);
		$db =& JFactory::getDBO();
		$db->setQuery("SELECT `mailid`, `subject`,`alias`, `type` FROM `#__acymailing_mail` WHERE `type` NOT IN ('notification','autonews') OR `alias` = 'confirmation' AND `published` = 1 ORDER BY `type`,`subject` ASC ");
		$allEmails = $db->loadObjectList();
		$emailsToDisplay = array();
		$typeNews = '';
		foreach($allEmails as $oneMail){
			if($oneMail->type != $typeNews){
				if(!empty($typeNews)) $emailsToDisplay[] = JHTML::_('select.option',  '</OPTGROUP>');
				$typeNews = $oneMail->type;
				if($oneMail->type == 'notification'){
					$label = JText::_('NOTIFICATIONS');
				}elseif($oneMail->type == 'news'){
					$label = JText::_('NEWSLETTERS');
				}elseif($oneMail->type == 'followup'){
					$label = JText::_('FOLLOWUP');
				}elseif($oneMail->type == 'welcome'){
					$label = JText::_('MSG_WELCOME');
				}elseif($oneMail->type == 'unsub'){
					$label = JText::_('MSG_UNSUB');
				}else{
					$label = $oneMail->type;
				}
				$emailsToDisplay[] = JHTML::_('select.option',  '<OPTGROUP>', $label );
			}
			$emailsToDisplay[] = JHTML::_('select.option', $oneMail->mailid, $oneMail->subject.' ('.$oneMail->mailid.' : '.$oneMail->alias.')' );
		}
		$emailsToDisplay[] = JHTML::_('select.option',  '</OPTGROUP>');
		$emaildrop = JHTML::_('select.genericlist',  $emailsToDisplay, 'mailid', 'class="inputbox" size="1"', 'value', 'text',JRequest::getInt('mailid'));
		$this->assignRef('subscriber',$subscriber);
		$this->assignRef('emaildrop',$emaildrop);
	}
}