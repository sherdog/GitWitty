<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class plgAcymailingUrltracker extends JPlugin
{
	function plgAcymailingUrltracker(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin =& JPluginHelper::getPlugin('acymailing', 'urltracker');
			$this->params = new JParameter( $plugin->params );
		}
	}
	function acymailing_replaceusertagspreview(&$email,&$user){
		return $this->acymailing_replaceusertags($email,$user);
	}
	function acymailing_replaceusertags(&$email,&$user){
		if(!$email->sendHTML OR empty($user->subid) OR !acymailing::level(1)) return;
		$urlClass = acymailing::get('class.url');
		if($urlClass === null) return;
		$urls = array();
		if(!preg_match_all('#href[ ]*=[ ]*"(?!mailto:|\#|callto:|file:|ftp:|webcal:|skype:)([^"]+)"#Ui',$email->body,$results)) return;
		foreach($results[1] as $i => $url){
			if(isset($urls[$results[0][$i]]) OR strpos($url,'subid') OR strpos($url,'{')) continue;
			if($this->params->get('trackingsystem','acymailing') == 'googleanalytics'){
				$args = array();
				$args[] = 'utm_source=newsletter_'.@$email->mailid;
				$args[] = 'utm_medium=email';
				$args[] = 'utm_campaign='.@$email->alias;
				if(strpos($url,'?')){ $mytracker = $url.'&'.implode('&',$args); }
				else{ $mytracker = $url.'?'.implode('&',$args); }
				$urls[$results[0][$i]] = str_replace($url,$mytracker,$results[0][$i]);
			}else{
				$mytracker = $urlClass->getUrl($url,$email->mailid,$user->subid);
				if(empty($mytracker)) continue;
				$urls[$results[0][$i]] = str_replace($url,$mytracker,$results[0][$i]);
			}
		}
		$email->body = str_replace(array_keys($urls),$urls,$email->body);
	}//endfct
	function onAcyDisplayTriggers(&$triggers){
		$triggers['clickurl'] = JText::_('ON_USER_CLICK');
	}
}//endclass