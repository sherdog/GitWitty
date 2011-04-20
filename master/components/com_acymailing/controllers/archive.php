<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class ArchiveController extends acymailingController{
	function view(){
		JRequest::setVar( 'layout', 'view'  );
		return parent::display();
	}
	function forward(){
		$config = acymailing::config();
		if(!$config->get('forward',true)) return $this->view();
		$key = JRequest::getString('key');
		$mailid = JRequest::getInt('mailid');
		$mailerHelper = acymailing::get('helper.mailer');
		$mailtosend = $mailerHelper->load($mailid);
		if(empty($key) OR $mailtosend->key !== $key){
			return $this->view();
		}
		JRequest::setVar('layout','forward');
		return parent::display();
	}
	function doforward(){
		acymailing::checkRobots();
		$config = acymailing::config();
		if(!$config->get('forward',true)) return $this->view();
		$email = trim(JRequest::getString('email'));
		$userClass = acymailing::get('helper.user');
		if(!$userClass->validEmail($email)){
				echo "<script>alert('".JText::_('VALID_EMAIL',true)."'); window.history.go(-1);</script>";
				exit;
		}
		$mailid = JRequest::getInt('mailid');
		if(empty($mailid)) return $this->view();
		$receiver = null;
		$receiver->email = $email;
		$receiver->subid = 0;
		$receiver->html = 1;
		$receiver->name = trim(strip_tags(JRequest::getString('name','')));
		$mailerHelper = acymailing::get('helper.mailer');
		$mailerHelper->checkConfirmField = false;
		$mailerHelper->checkEnabled = false;
		$mailerHelper->checkAccept = false;
		$mailtosend = $mailerHelper->load($mailid);
		$key = JRequest::getString('key');
		if(empty($key) OR $mailtosend->key !== $key){
			return $this->view();
		}
		if($mailerHelper->sendOne($mailid,$receiver)){
			$db=& JFactory::getDBO();
			$db->setQuery('UPDATE '.acymailing::table('stats').' SET `forward` = `forward` +1 WHERE `mailid` = '.(int)$mailid);
			$db->query();
		}
		return $this->view();
	}
}