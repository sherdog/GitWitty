<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class SendController extends acymailingController{
	function sendready(){
		JRequest::setVar( 'layout', 'sendconfirm'  );
		return parent::display();
	}
	function send(){
		JRequest::checkToken() or die( 'Invalid Token' );
		$mailid = acymailing::getCID('mailid');
		if(empty($mailid)) exit;
		$user =& JFactory::getUser();
		$time = time();
		$queueClass = acymailing::get('class.queue');
		$onlyNew = JRequest::getInt('onlynew');
		$totalSub = $queueClass->queue($mailid,$time,$onlyNew);
		if(empty($totalSub)){
			acymailing::display(JText::_('NO_RECEIVER'),'warning');
			return;
		}
		$mailObject = null;
		$mailObject->senddate = $time;
		$mailObject->published = 1;
		$mailObject->mailid = $mailid;
		$mailObject->sentby = $user->id;
		$db =& JFactory::getDBO();
		$db->updateObject(acymailing::table('mail'),$mailObject,'mailid');
		$config =& acymailing::config();
		$queueType = $config->get('queue_type');
		if($queueType=='onlyauto'){
			$messages = array();
			$messages[] = JText::sprintf('ADDED_QUEUE',$totalSub);
			$messages[] = JText::_('AUTOSEND_CONFIRMATION');
			acymailing::display($messages,'success');
			return;
		}else{
			JRequest::setVar( 'totalsend', $totalSub );
			$app =& JFactory::getApplication();
			$app->redirect(acymailing::completeLink('send&task=continuesend&mailid='.$mailid.'&totalsend='.$totalSub,true,true));
			exit;
		}
	}
	function continuesend(){
		$config = acymailing::config();
		$newcrontime = time() + 120;
		if($config->get('cron_next') < $newcrontime){
			$newValue = null;
			$newValue->cron_next = $newcrontime;
			$config->save($newValue);
		}
		$mailid = acymailing::getCID('mailid');
		$totalSend = JRequest::getVar( 'totalsend',0,'','int');
		$alreadySent = JRequest::getVar( 'alreadysent',0,'','int');
		$helperQueue = acymailing::get('helper.queue');
		$helperQueue->mailid = $mailid;
		$helperQueue->report = true;
		$helperQueue->total = $totalSend;
		$helperQueue->start = $alreadySent;
		$helperQueue->pause = $config->get('queue_pause');
		//->Process will exit the current page if it needs to be continued
		$helperQueue->process();


		ob_start();
	}
	function scheduleready(){
		$mailid = acymailing::getCID('mailid');
		if(empty($mailid)) return false;
		$queueClass = acymailing::get('class.queue');
		$values = null;
		$values->nbqueue = $queueClass->nbQueue($mailid);
		if(!empty($values->nbqueue)){
			$messages = array();
			$messages[] = JText::sprintf('ALREADY_QUEUED',$values->nbqueue);
			$messages[] = JText::sprintf('DELETE_QUEUE');
			acymailing::display($messages,'warning');
			return;
		}
		JRequest::setVar( 'layout', 'scheduleconfirm'  );
		return parent::display();
	}
	function genschedule(){
		JRequest::checkToken() or die( 'Invalid Token' );
		$schedHelper = acymailing::get('helper.schedule');
		$result = $schedHelper->queueScheduled();
		acymailing::display($schedHelper->messages,$result ? 'success': 'warning');
		return true;
	}
	function schedule(){
		$mailid = acymailing::getCID('mailid');
		(JRequest::checkToken() && !empty($mailid)) or die( 'Invalid Token' );
		$senddate = JRequest::getString( 'senddate','');
		$user =& JFactory::getUser();
		if(empty($senddate)){
			acymailing::display(JText::_('SPECIFY_DATE'),'warning');
			return $this->scheduleready();
		}
		$realSendDate = acymailing::getTime($senddate);
		if($realSendDate<time()){
			acymailing::display(JText::_('DATE_FUTURE'),'warning');
			return $this->scheduleready();
		}
		$mail = null;
		$mail->mailid = $mailid;
		$mail->senddate = $realSendDate;
		$mail->sentby = $user->id;
		$mail->published = 2;
		$mailClass = acymailing::get('class.mail');
		$mailClass->save($mail);
		$myNewsletter = $mailClass->get($mailid);
		acymailing::display(JText::sprintf('AUTOSEND_DATE',$myNewsletter->subject,acymailing::getDate($realSendDate)),'success');
		$js = "window.top.document.getElementById('toolbar-popshed').innerHTML = '<a class=\"toolbar\" onclick=\"javascript: submitbutton(\'unschedule\')\" href=\"#\"><span class=\"icon-32-unschedule\" title=\"".JText::_('UNSCHEDULE',true)."\"> </span>".JText::_('UNSCHEDULE')."</a>'";
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
	}
	function addqueue(){
		JRequest::setVar( 'layout', 'addqueue'  );
		return parent::display();
	}
	function scheduleone(){
		$mailid = JRequest::getInt('mailid');
		$subid = JRequest::getInt('subid');
		$senddate = JRequest::getString( 'senddate','');
		(JRequest::checkToken() && !empty($mailid) && !empty($subid)) or die( 'Invalid Token' );
		$realSendDate = acymailing::getTime($senddate);
		if($realSendDate<time()){
			acymailing::display(JText::_('DATE_FUTURE'),'warning');
			return $this->addqueue();
		}
		$mailClass = acymailing::get('class.mail');
		$myNewsletter = $mailClass->get($mailid);
		$queueEntry = null;
		$queueEntry->mailid = $myNewsletter->mailid;
		$queueEntry->subid = $subid;
		$queueEntry->senddate = $realSendDate;
		$queueEntry->priority = 1;
		$db =& JFactory::getDBO();
		$status = $db->insertObject('#__acymailing_queue',$queueEntry);
		if($status) acymailing::display(JText::sprintf('AUTOSEND_DATE',$myNewsletter->subject,acymailing::getDate($realSendDate)),'success');
		else{ acymailing::display(array(JText::_('ERROR_SAVING'),$db->getErrorMsg()),'error'); return $this->addqueue();}
	}
}