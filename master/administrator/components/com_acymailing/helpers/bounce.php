<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class bounceHelper{
  var $report = false;
  var $config;
  var $mailer;
  var $mailbox;
  var $_message;
  var $listsubClass;
  var $subClass;
  var $db;
  var $blockedUsers = array();
  var $deletedUsers = array();
  var $bounceMessages = array();
  var $usepear = false;
  var $detectEmail = '/[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@([a-z0-9\-]+\.)+[a-z0-9]{2,8}/i';
  function bounceHelper(){
    $this->config = acymailing::config();
    $this->mailer = acymailing::get('helper.mailer');
    $this->mailer->report = false;
    $this->subClass = acymailing::get('class.subscriber');
    $this->listsubClass = acymailing::get('class.listsub');
    $this->listsubClass->checkAccess = false;
    $this->listsubClass->sendNotif = false;
    $this->listsubClass->sendConf = false;
    $this->db =& JFactory::getDBO();
  }
  function init(){
	if($this->config->get('bounce_connection') == 'pear'){
   		$this->usepear = true;
    	include_once(ACYMAILING_FRONT.'inc'.DS.'pear'.DS.'pop3.php');
    	return true;
    }
    if(extension_loaded("imap") OR function_exists('imap_open')) return true;
    $prefix = (PHP_SHLIB_SUFFIX == 'dll') ? 'php_' : '';
    $EXTENSION = $prefix . 'imap.' . PHP_SHLIB_SUFFIX;
    if(function_exists('dl')){
      $fatalMessage = 'The system tried to load dynamically the '.$EXTENSION.' extension';
      $fatalMessage .= '<br/>If you see this message, that means the system could not load this PHP extension';
      $fatalMessage .= '<br/>Please enable the PHP Extension '.$EXTENSION;
      ob_start();
      echo $fatalMessage;
      dl($EXTENSION);
      $warnings = str_replace($fatalMessage,'',ob_get_clean());
      if(extension_loaded("imap") OR function_exists('imap_open')) return true;
    }
    if($this->report){
      acymailing::display('The extension "'.$EXTENSION.'" could not be loaded, please change your PHP configuration to enable it or use the pop3 method without imap extension','error');
      if(!empty($warnings)) acymailing::display($warnings,'warning');
    }
    return false;
  }
  	function connect(){
  		if($this->usepear) return $this->_connectpear();
  		return $this->_connectimap();
  	}
	function _connectpear(){
		ob_start();
		$this->mailbox = new Net_POP3();
		$timeout = $this->config->get('bounce_timeout');
    	if(!empty($timeout)) $this->mailbox->setTimeOut($timeout);
		$port = $this->config->get('bounce_port','');
		if(empty($port)) $port = '110/pop3/notls';
		if(!$this->mailbox->connect($this->config->get('bounce_server'),$port)){
			$warnings = ob_get_clean();
			if($this->report) acymailing::display("Error connecting to the server ".$this->config->get('bounce_server')." : ".$port,'error');
			if(!empty($warnings) AND $this->report) acymailing::display($warnings,'warning');
	      	return false;
	    }
	    $login = $this->mailbox->login($this->config->get('bounce_username'),$this->config->get('bounce_password'),'USER' );
	    if(empty($login) OR isset($login->code)){
			$warnings = ob_get_clean();
			if($this->report) acymailing::display('Identication error '.$this->config->get('bounce_username').':'.$this->config->get('bounce_password'),'error');
			if(!empty($warnings) AND $this->report) acymailing::display($warnings,'warning');
	      	return false;
	    }
	    ob_clean();
	    return true;
	}
  function _connectimap(){
    ob_start();
    $buff = imap_alerts();
    $buff = imap_errors();
    $timeout = $this->config->get('bounce_timeout');
    if(!empty($timeout)) imap_timeout(IMAP_OPENTIMEOUT,$timeout);
    $port = $this->config->get('bounce_port','');
    $secure = $this->config->get('bounce_secured','');
    $protocol = $this->config->get('bounce_connection','');
    $serverName = '{'.$this->config->get('bounce_server');
    if(empty($port)){
    	if($secure == 'ssl' && $protocol == 'imap') $port = '993';
    	elseif($protocol == 'imap') $port = '143';
		elseif($protocol == 'pop3') $port = '110';
    }
    if(!empty($port)) $serverName .= ':'.$port;
    if(!empty($secure)) $serverName .= '/'.$secure;
    if($this->config->get('bounce_certif',false)) $serverName .= '/novalidate-cert';
    if(!empty($protocol)) $serverName .='/service='.$protocol;
    $serverName .= '}';
    $this->mailbox = imap_open($serverName,$this->config->get('bounce_username'),$this->config->get('bounce_password'));
    $warnings = ob_get_clean();
    if(!empty($warnings) && $this->report){
      acymailing::display($warnings,'warning');
    }
    return $this->mailbox ? true : false;
  }
  function getNBMessages(){
  	if($this->usepear){
  		$this->nbMessages = $this->mailbox->numMsg();
  	}else{
		$this->nbMessages = imap_num_msg($this->mailbox);
  	}
    return $this->nbMessages;
  }
  function getMessage($msgNB){
  	if($this->usepear){
  		$message = null;
		$message->headerString = $this->mailbox->getRawHeaders($msgNB);
		if(empty($message->headerString)) return false;
  	}else{
		$message = imap_headerinfo($this->mailbox,$msgNB);
  	}
	return $message;
  }
  function deleteMessage($msgNB){
  	if($this->usepear){
  		$this->mailbox->deleteMsg($msgNB);
  	}else{
		imap_delete($this->mailbox,$msgNB);
		imap_expunge($this->mailbox);
  	}
  }
  function close(){
  	if($this->usepear){
  		$this->mailbox->disconnect();
  	}else{
  		imap_close($this->mailbox);
  	}
  }
	function decodeMessage(){
		if($this->usepear){
	  		return $this->_decodeMessagepear();
	  	}else{
	  		return  $this->_decodeMessageimap();
	  	}
	}
	function _decodeMessagepear(){
		$this->_message->headerinfo = $this->mailbox->getParsedHeaders($this->_message->messageNB);
		if(empty($this->_message->headerinfo['subject'])) return false;
		$this->_message->text = '';
		$this->_message->html = $this->mailbox->getBody($this->_message->messageNB);
		$this->_message->subject = $this->_decodeHeader($this->_message->headerinfo['subject']);
		$this->_message->header->sender_email = @$this->_message->headerinfo['return-path'];
		if(preg_match($this->detectEmail,$this->_message->header->sender_email,$results)){
			$this->_message->header->sender_email = $results[0];
		}
		$this->_message->header->sender_name = strip_tags(@$this->_message->headerinfo['from']);
		$this->_message->header->reply_to_email = $this->_message->header->sender_email;
		$this->_message->header->reply_to_name = $this->_message->header->sender_name;
		$this->_message->header->from_email = $this->_message->header->sender_email;
		$this->_message->header->from_name = $this->_message->header->sender_name;
		return true;
	}
	function _decodeMessageimap(){
		$this->_message->structure = imap_fetchstructure($this->mailbox,$this->_message->messageNB);
		if(empty($this->_message->structure)) return false;
	    $this->_message->html = '';
		$this->_message->text = '';
	    if($this->_message->structure->type == 1){
	      $this->_message->contentType = 2;
	      $allParts = $this->_explodeBody($this->_message->structure);
	      $html = '';
	      $plain = '';
	      foreach($allParts as $num => $onePart){
	        $charset = $this->_getMailParam($onePart,'charset');
	              if ($onePart->subtype=='HTML'){
	                $this->_message->html = $this->_decodeContent(imap_fetchbody($this->mailbox,$this->_message->messageNB,$num),$onePart);
	              }elseif ($onePart->subtype=='PLAIN'){
	                $this->_message->text = $this->_decodeContent(imap_fetchbody($this->mailbox,$this->_message->messageNB,$num),$onePart);
	              }
	      }
	    }else{
	      $charset = $this->_getMailParam($this->_message->structure,'charset');
	      if($this->_message->structure->subtype == 'HTML'){
	        $this->_message->contentType = 1;
	        $this->_message->html = $this->_decodeContent(imap_body($this->mailbox,$this->_message->messageNB),$this->_message->structure);
	      }else{
	        $this->_message->contentType = 0;
	        $this->_message->text = $this->_decodeContent(imap_body($this->mailbox,$this->_message->messageNB),$this->_message->structure);
	      }
	    }
	    $this->_message->subject = $this->_decodeHeader($this->_message->subject);
	    $this->_decodeAddressimap('sender');
	    $this->_decodeAddressimap('from');
	    $this->_decodeAddressimap('reply_to');
	    $this->_decodeAddressimap('to');
		return true;
	}
  function handleMessages(){
    $maxMessages = min($this->nbMessages,$this->config->get('bounce_max',0));
    if(empty($maxMessages)) $maxMessages = $this->nbMessages;
    if($this->report){
    	if(!headers_sent() AND ob_get_level() > 0){
			ob_end_flush();
		}
      $disp = "<div style='position:fixed; top:3px;left:3px;background-color : white;border : 1px solid grey; padding : 3px;'>";
      $disp.= JText::_('BOUNCE_PROCESS');
      $disp.= ':  <span id="counter"/>0</span> / '. $maxMessages;
      $disp.= '</div>';
      $disp .= '<br/>';
      $disp.= '<script type="text/javascript" language="javascript">';
      $disp.= 'var mycounter = document.getElementById("counter");';
      $disp.= 'function setCounter(val){ mycounter.innerHTML=val;}';
      $disp.= '</script>';
      echo $disp;
		if(function_exists('ob_flush')) @ob_flush();
		@flush();
    }
    $msgNB = $maxMessages;
    while(($msgNB>0) && ($this->_message = $this->getMessage($msgNB))){
      if($this->report){
        echo '<script type="text/javascript" language="javascript">setCounter('. ($maxMessages-$msgNB+1) .')</script>';
		if(function_exists('ob_flush')) @ob_flush();
		@flush();
      }
      $this->_message->messageNB = $msgNB;
      $this->decodeMessage();
      $msgNB--;
      if(empty($this->_message->subject)) continue;
      $this->_message->analyseHeader = $this->_message->subject.$this->_message->header->sender_name.$this->_message->header->sender_email;
      $this->_message->analyseText = $this->_message->html.$this->_message->text;
      $this->_display('<b>'.JText::_('JOOMEXT_SUBJECT').' : '.strip_tags($this->_message->subject).'</b>',false,$maxMessages-$this->_message->messageNB+1);
      preg_match('#X-Mailid *:? *([0-9]*)#i',$this->_message->analyseText,$resultsMailid);
      preg_match('#X-Subid *:? *([0-9]*)#i',$this->_message->analyseText,$resultsSubid);
      if(empty($resultsSubid[1])){
        preg_match_all($this->detectEmail,$this->_message->analyseText,$results);
        $replyemail = $this->config->get('reply_email');
        $fromemail=$this->config->get('from_email');
        $bouncemail = $this->config->get('bounce_email');
        $removeEmails = '#('.str_replace(array('%'),array('@'),$this->config->get('bounce_username'));
        if(!empty($bouncemail)) $removeEmails .= '|'.$bouncemail;
        if(!empty($fromemail)) $removeEmails .= '|'.$fromemail;
        if(!empty($replyemail)) $removeEmails .= '|'.$replyemail;
        $removeEmails .= ')#i';
        if(!empty($results[0])){
          foreach($results[0] as $oneEmail){
            if(!preg_match($removeEmails,$oneEmail)){
              $this->_message->subemail = strtolower($oneEmail);
              $this->_message->subid = $this->subClass->subid($this->_message->subemail);
              if(!empty($this->_message->subid)) break;
            }
          }
        }
      }else{
        $this->_message->subid = $resultsSubid[1];
      }
      if(!empty($resultsMailid[1])) $this->_message->mailid = $resultsMailid[1];
      elseif(!empty($this->_message->subid)){
		$this->db->setQuery('SELECT `mailid` FROM '.acymailing::table('userstats').' WHERE `subid` = '.(int) $this->_message->subid.' LIMIT 2');
		$this->db->query();
		$mailids = $this->db->loadResultArray();
		if(!empty($mailids) AND count($mailids) == 1){
			$this->_message->mailid = reset($mailids);
		}
	  }
      if(!$this->_handleAction('bounce') && !$this->_handleActions()){
		$this->_handleAction('end');
      }
      if($msgNB%50 == 0) $this->_subActions();
    }
	$this->_subActions();
    ob_start();
  }
  function _subActions(){
  	if(!empty($this->deletedUsers)){
		$this->subClass->delete($this->deletedUsers);
		$this->deletedUsers = array();
    }
    if(!empty($this->blockedUsers)){
    	$allUsersId = implode(',',$this->blockedUsers);
    	$this->db->setQuery('UPDATE `#__acymailing_subscriber` SET `enabled` = 0 WHERE `subid` IN ('.$allUsersId.')');
		$this->db->query();
		$this->db->setQuery('DELETE FROM `#__acymailing_queue` WHERE `subid` IN ('.$allUsersId.')');
		$this->db->query();
		$this->blockedUsers = array();
    }
    if(!empty($this->bounceMessages)){
    	foreach($this->bounceMessages as $mailid => $bouncedata){
    		$this->db->setQuery('UPDATE '.acymailing::table('stats').' SET `bounceunique` = `bounceunique` + '.$bouncedata['nbbounces'].' WHERE `mailid` = '.(int) $mailid.' LIMIT 1');
			$this->db->query();
			if(!empty($bouncedata['subid'])){
				$this->db->setQuery('UPDATE '.acymailing::table('userstats').' SET `bounce` = `bounce` + 1 WHERE `subid` IN ('.implode(',',$bouncedata['subid']).') AND `mailid` = '.(int) $mailid);
				$this->db->query();
			}
    	}
    	$this->bounceMessages = array();
    }
  }
  function _handleActions(){
    $i = 1;
    while($actionName = $this->config->get('bounce_rules_'.$i,false)){
     if($this->_handleAction($i)) return true;
      $i++;
    }
    return false;
  }
  function _handleAction($num){
    $regex = $this->config->get('bounce_regex_'.$num);
    if(empty($regex) AND is_numeric($num)) return false;
    if(!preg_match('#'.$regex.'#i',$this->_message->analyseHeader)) return false;
    $name = $this->config->get('bounce_rules_'.$num);
    $message = JText::_('BOUNCE_RULES').' : '.$name;
    $emailaction = $this->config->get('bounce_email_'.$num);
    $subaction = $this->config->get('bounce_action_'.$num);
    switch($emailaction){
      case 'forward' :
        $forwardto = $this->config->get('bounce_forward_'.$num);
        $this->mailer->clearAll();
        $this->mailer->Subject = 'BOUNCE FORWARD : '.$this->_message->subject;
        $this->mailer->AddAddress($forwardto);
        if(!empty($this->_message->html)){
          $this->mailer->IsHTML(true);
          $this->mailer->Body = $this->_message->html;
          if(!empty($this->_message->text)) $this->mailer->Body .= '<br/><br/>-------<br/>'.nl2br($this->_message->text);
        }else{
          $this->mailer->IsHTML(false);
          $this->mailer->Body = $this->_message->text;
        }
        $this->mailer->AddReplyTo($this->_message->header->reply_to_email,$this->_message->header->reply_to_name);
        $this->mailer->setFrom($this->_message->header->from_email,$this->_message->header->from_name);
        if($this->mailer->send()){
          $message .= ' | forwarded to '.$forwardto;
        }
		case 'delete' :
			$message .= ' | message deleted';
			$this->deleteMessage($this->_message->messageNB);
    }
    if(!empty($this->_message->subid)){
    	if(in_array($subaction,array('sub','remove','unsub'))){
    		$status = $this->subClass->getSubscriptionStatus($this->_message->subid);
    	}
    	$listId = 0;
		switch($subaction){
			case 'sub' :
				$listId = $this->config->get('bounce_action_lists_'.$num);
				if(!empty($listId)){
					$message .= ' | user '.$this->_message->subid.' subscribed to '.$listId;
		            if(empty($status[$listId])){
						$this->listsubClass->addSubscription($this->_message->subid,array('1' => array($listId)));
		            }elseif($status[$listId]->status != 1){
					 	$this->listsubClass->updateSubscription($this->_message->subid,array('1' => array($listId)));
		            }
				}
			case 'remove' :
				$unsubLists = array_diff(array_keys($status),array($listId));
				if(!empty($unsubLists)){
					$message .= ' | user '.$this->_message->subid.' removed from lists '.implode(',',$unsubLists);
					$this->listsubClass->removeSubscription($this->_message->subid,$unsubLists);
				}else{
					$message .= ' | user '.$this->_message->subid.' not subscribed';
				}
				break;
			case 'unsub' :
				$unsubLists = array_diff(array_keys($status),array($listId));
				if(!empty($unsubLists)){
					$message .= ' | user '.$this->_message->subid.' unsubscribed from lists '.implode(',',$unsubLists);
					$this->listsubClass->updateSubscription($this->_message->subid,array('-1' => $unsubLists));
				}else{
					$message .= ' | user '.$this->_message->subid.' not subscribed';
				}
				break;
			case 'delete' :
				$message .= ' | user '.$this->_message->subid.' deleted';
				$this->deletedUsers[] = intval($this->_message->subid);
				break;
			case 'block' :
				$message .= ' | user '.$this->_message->subid.' blocked';
				$this->blockedUsers[] = intval($this->_message->subid);
				break;
	      }
    }else{
    	$message .= ' | user not identified';
    	if(!empty($this->_message->subemail)) $message .= ' : '.$this->_message->subemail;
    }
    if($num == 'bounce'){
    	if(!empty($this->_message->mailid)){
    		if(empty($this->bounceMessages[$this->_message->mailid]['nbbounces'])){
    			$this->bounceMessages[$this->_message->mailid] = array();
    			$this->bounceMessages[$this->_message->mailid]['nbbounces'] = 1;
    		}else{
    			$this->bounceMessages[$this->_message->mailid]['nbbounces']++ ;
    		}
    		if(!empty($this->_message->subid) AND $subaction != 'delete'){
    			$this->bounceMessages[$this->_message->mailid]['subid'][] = intval($this->_message->subid);
    		}
    	}
    }
    $this->_display($message,true);
	return true;
  }
  function _decodeAddressimap($type){
    $address = $type.'address';
    $name = $type.'_name';
    $email = $type.'_email';
    if(empty($this->_message->$type)) return false;
    $var = $this->_message->$type;
    if(!empty($this->_message->$address)){
      $this->_message->header->$name = $this->_message->$address;
    }else{
      $this->_message->header->$name = $var[0]->personal;
    }
    $this->_message->header->$email = $var[0]->mailbox.'@'.@$var[0]->host;
    return true;
  }
  function _display($message,$status = '',$num = ''){
    $this->messages[] = $message;
    if(!$this->report) return;
    $color = $status ? 'green' : 'blue';
    if(!empty($num)) echo '<br/>'.$num.' : ';
    else echo '<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '<font color="'.$color.'">'.$message.'</font>';
	if(function_exists('ob_flush')) @ob_flush();
	@flush();
  }
    function _decodeHeader($input){
        $input = preg_replace('/(=\?[^?]+\?(q|b)\?[^?]*\?=)(\s)+=\?/i', '\1=?', $input);
    	$this->charset = false;
        while (preg_match('/(=\?([^?]+)\?(q|b)\?([^?]*)\?=)/i', $input, $matches)) {
            $encoded  = $matches[1];
            $charset  = $matches[2];
            $encoding = $matches[3];
            $text     = $matches[4];
            switch (strtolower($encoding)) {
                case 'b':
                    $text = base64_decode($text);
                    break;
                case 'q':
                    $text = str_replace('_', ' ', $text);
                    preg_match_all('/=([a-f0-9]{2})/i', $text, $matches);
                    foreach($matches[1] as $value)
                        $text = str_replace('='.$value, chr(hexdec($value)), $text);
                    break;
            }
            $this->charset = $charset;
            $input = str_replace($encoded, $text, $input);
        }
        return $input;
    }
     function _explodeBody($struct, $path="0",$inline=0){
      $allParts = array();
        if(empty($struct->parts)) return $allParts;
    $c=0; //counts real content
        foreach ($struct->parts as $part){
          if ($part->type==1){
              if ($part->subtype=="MIXED"){ //Mixed:
              $path = $this->_incPath($path,1); //refreshing current path
              $newpath = $path.".0"; //create a new path-id (ex.:2.0)
              $allParts = array_merge($this->_explodeBody($part,$newpath),$allParts); //fetch new parts
              }
              else{ //Alternativ / rfc / signed
              $newpath = $this->_incPath($path, 1);
              $path = $this->_incPath($path,1);
              $allParts = array_merge($this->_explodeBody($part,$newpath,1),$allParts);
              }
          }
          else {
              $c++;
              if ($c==1 && $inline){
              $path = $path.".0";
              }
              $path = $this->_incPath($path, 1);
              $allParts[$path] = $part;
          }
        }
        return $allParts;
    }
    function _incPath($path, $inc){
        $newpath="";
        $path_elements = explode(".",$path);
        $limit = count($path_elements);
        for($i=0;$i < $limit;$i++){
          if($i == $limit-1){ //last element
              $newpath .= $path_elements[$i]+$inc; // new Part-Number
          }
          else{
              $newpath .= $path_elements[$i]."."; //rebuild "1.2.2"-Chronology
          }
        }
        return $newpath;
    }
  function _decodeContent($content,$structure){
    $encoding = $structure->encoding;
        if($encoding == 2) $content = imap_binary($content);
        elseif($encoding == 3) $content = imap_base64($content);
        elseif($encoding == 4) $content = imap_qprint($content);
        $charset = $this->_getMailParam($structure,'charset');
        return $content;
  }
    function _getMailParam($params,$name){
      $searchIn = array();
    if ($params->ifparameters)
      $searchIn=array_merge($searchIn,$params->parameters);
    if ($params->ifdparameters)
      $searchIn=array_merge($searchIn,$params->dparameters);
    if(empty($searchIn)) return false;
    foreach($searchIn as $num => $values)
    {
            if (strtolower($values->attribute) == $name)
      {
                return $values->value;
      }
    }
    }
  function getErrors(){
  	$return = array();
  	if($this->usepear){
  	}else{
  		$alerts = imap_alerts();
    	$errors = imap_errors();
    	if(!empty($alerts)) $return = array_merge($return,$alerts);
    	if(!empty($errors)) $return = array_merge($return,$errors);
  	}
    return $return;
  }
}