<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class acyhistoryClass extends acymailingClass{
	function insert($subid,$action,$data){
		$history = null;
		$history->subid = intval($subid);
		$history->action = strip_tags($action);
		$history->data = strip_tags($data);
		$history->date = time();
		$userHelper = acymailing::get('helper.user');
		$history->ip = $userHelper->getIP();
		if(!empty($_SERVER)) $history->source = serialize($_SERVER);
		return $this->database->insertObject(acymailing::table('history'),$history);
	}
}