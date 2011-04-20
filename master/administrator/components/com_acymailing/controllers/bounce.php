<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class BounceController extends acymailingController{
	function process(){
		acymailing::increasePerf();
		$config = acymailing::config();
		$bounceClass = acymailing::get('helper.bounce');
		$bounceClass->report = true;
		if(!$bounceClass->init()) return;
		if(!$bounceClass->connect()){
			acymailing::display($bounceClass->getErrors(),'error');
			return;
		}
		acymailing::display(JText::sprintf('BOUNCE_CONNECT_SUCC',$config->get('bounce_username')),'success');
		$nbMessages = $bounceClass->getNBMessages();
		acymailing::display(JText::sprintf('NB_MAIL_MAILBOX',$nbMessages),'info');
		if(empty($nbMessages)) return;
		$bounceClass->handleMessages();
		$bounceClass->close();
	}
}