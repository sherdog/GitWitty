<?php
/**
 * @copyright
 * @
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class cronHelper{
	var $report = false;
	var $messages = array();
	var $detailMessages = array();
	function cron(){
		$time = time();
		$config = acymailing::config();
		$firstMessage = JText::sprintf('CRON_TRIGGERED',acymailing::getDate(time()));
		$this->messages[] = $firstMessage;
		if($this->report){
			acymailing::display($firstMessage,'info');
		}
		if($config->get('cron_next') > $time){
			if($config->get('cron_next') > ($time + $config->get('cron_frequency'))){
				$newConfig = null;
				$newConfig->cron_next = $time + $config->get('cron_frequency');
				$config->save($newConfig);
			}
			$nottime = JText::sprintf('CRON_NEXT',acymailing::getDate($config->get('cron_next')));
			$this->messages[] = $nottime;
			if($this->report){
				acymailing::display($nottime,'info');
			}
			$sendreport = $config->get('cron_sendreport');
			if($sendreport == 1){
				$mailer = acymailing::get('helper.mailer');
				$mailer->report = false;
				$mailer->autoAddUser = true;
				$mailer->checkConfirmField = false;
				$mailer->addParam('report',implode('<br/>',$this->messages));
				$mailer->addParam('detailreport','');
				$receiverString = $config->get('cron_sendto');
				$receivers = explode(',',$receiverString);
				if(!empty($receivers)){
					foreach($receivers as $oneReceiver){
						$mailer->sendOne('report',$oneReceiver);
					}
				}
			}
			return false;
		}
		$queueHelper = acymailing::get('helper.queue');
		$newConfig = null;
		$newConfig->cron_next = $config->get('cron_next') + $config->get('cron_frequency');
		if($newConfig->cron_next <= $time OR $newConfig->cron_next> $time + $config->get('cron_frequency')) $newConfig->cron_next = $time + $config->get('cron_frequency');
		$newConfig->cron_last = $time;
		$userHelper = acymailing::get('helper.user');
		$newConfig->cron_fromip = $userHelper->getIP();
		$config->save($newConfig);
		$queueHelper->report = false;
		$queueHelper->process();
		if(!empty($queueHelper->messages)){
			$this->detailMessages = array_merge($this->detailMessages,$queueHelper->messages);
		}
		$this->messages[] = JText::sprintf('CRON_PROCESS',$queueHelper->nbprocess,$queueHelper->successSend,$queueHelper->errorSend);
		if(!empty($queueHelper->stoptime) AND time()>$queueHelper->stoptime) return true;

		if(acymailing::level(2)){
			$autonewsHelper = acymailing::get('helper.autonews');
			$resultAutonews = $autonewsHelper->generate();
			if(!empty($autonewsHelper->messages)){
				$this->messages = array_merge($this->messages,$autonewsHelper->messages);
			}
			if(!empty($queueHelper->stoptime) AND time()>$queueHelper->stoptime) return true;
		}
		if(acymailing::level(1)){
			$schedHelper = acymailing::get('helper.schedule');
			$resultSchedule = $schedHelper->queueScheduled();
			if($resultSchedule){
				if(!empty($schedHelper->nbNewsletterScheduled)) $this->messages[] = JText::sprintf('NB_SCHED_NEWS',$schedHelper->nbNewsletterScheduled);
				$this->detailMessages = array_merge($this->detailMessages,$schedHelper->messages);
			}
			if(!empty($queueHelper->stoptime) AND time()>$queueHelper->stoptime) return true;
		}


		return true;
	}
	function report(){
		$config = acymailing::config();
		$newConfig = null;
		$newConfig->cron_report = implode('<br/>',$this->messages);
		if(strlen($newConfig->cron_report) > 250) $newConfig->cron_report = substr($newConfig->cron_report,0,245).'...';
		$config->save($newConfig);
		$saveReport = $config->get('cron_savereport');
		if(!empty($saveReport)){
			$reportPath = JPath::clean(ACYMAILING_ROOT.trim(html_entity_decode($config->get('cron_savepath'))));
			file_put_contents($reportPath, "\r\n"."\r\n".str_repeat('*',150)."\r\n".str_repeat('*',20).str_repeat(' ',5).acymailing::getDate(time()).str_repeat(' ',5).str_repeat('*',20)."\r\n", FILE_APPEND);
			@file_put_contents($reportPath, implode("\r\n",$this->messages), FILE_APPEND);
			if($saveReport == 2 AND !empty($this->detailMessages)){
				@file_put_contents($reportPath, "\r\n"."---- Details ----"."\r\n", FILE_APPEND);
				@file_put_contents($reportPath, implode("\r\n",$this->detailMessages), FILE_APPEND);
			}
		}
		$sendreport = $config->get('cron_sendreport');
		if(!empty($sendreport)){
			$mailer = acymailing::get('helper.mailer');
			$mailer->autoAddUser = true;
			$mailer->report = false;
			$mailer->checkConfirmField = false;
			$receiverString = $config->get('cron_sendto');
			$receivers = explode(',',$receiverString);
			$mailer->addParam('report',implode('<br/>',$this->messages));
			$mailer->addParam('detailreport',implode('<br/>',$this->detailMessages));
			if($sendreport == 1 OR !empty($this->detailMessages)){
				if(!empty($receivers)){
					foreach($receivers as $oneReceiver){
						$mailer->sendOne('report',$oneReceiver);
					}
				}
			}
		}
	}
}//endclass