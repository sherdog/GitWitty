<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class urlclickClass extends acymailingClass{
	var $tables = array('urlclick');
	function addClick($urlid,$mailid,$subid){
		$mailid = intval($mailid);
		$urlid = intval($urlid);
		$subid = intval($subid);
		if(empty($mailid) OR empty($urlid) OR empty($subid)) return false;
		$statsClass = acymailing::get('class.stats');
		$statsClass->countReturn = false;
		if(!$statsClass->saveStats()) return false;
		$date = time();
		$query = 'INSERT IGNORE INTO '.acymailing::table('urlclick').' (urlid,mailid,subid,date,click) VALUES ('.$urlid.','.$mailid.','.$subid.','.$date.',1)';
		$this->database->setQuery($query);
		$this->database->query();
		if(!$this->database->getAffectedRows()){
			$query = 'UPDATE '.acymailing::table('urlclick').' SET click = click +1,`date` = '.$date.' WHERE mailid = '.$mailid.' AND urlid = '.$urlid.' AND subid = '.$subid.' LIMIT 1';
			$this->database->setQuery($query);
			$this->database->query();
		}
		$query = 'SELECT SUM(click) FROM '.acymailing::table('urlclick').' WHERE mailid = '.$mailid.' AND subid = '.$subid;
		$this->database->setQuery($query);
		$totalUserClick = $this->database->loadResult();
		$query = 'UPDATE '.acymailing::table('stats').' SET clicktotal = clicktotal + 1 ';
		if($totalUserClick <= 1){
			$query .= ' , clickunique = clickunique + 1';
		}
		$query .= ' WHERE mailid = '.$mailid.' LIMIT 1';
		$this->database->setQuery($query);
		$this->database->query();
		$filterClass = acymailing::get('class.filter');
		$filterClass->subid = $subid;
		$filterClass->trigger('clickurl');
		return true;
	}
}