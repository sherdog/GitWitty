<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class campaignHelper{
	function start($subid,$listids){
		$listCampaignClass = acymailing::get('class.listcampaign');
		$campaignids = $listCampaignClass->getAffectedCampaigns($listids);
		if(empty($campaignids)) return true;
		$campaignSubscription = acymailing::get('class.listsub');
		$campaignSubscription->type = 'campaign';
		$subscription = $campaignSubscription->getSubscription($subid);
		$campaignAdded = array();
		$time = time();
		foreach($campaignids as $id => $campaignid){
			if(!empty($subscription[$campaignid]) AND $subscription[$campaignid]->status == 1 AND $subscription[$campaignid]->unsubdate > $time){
				continue;
			}
			$campaignAdded[] = $campaignid;
		}
		if(empty($campaignAdded)) return true;
		$config = acymailing::config();
		$db = JFactory::getDBO();
		$query = 'SELECT a.`listid`, max(b.`senddate`) as maxsenddate FROM '.acymailing::table('listmail').' as a LEFT JOIN '.acymailing::table('mail').' as b on a.`mailid` = b.`mailid`';
		$query .= ' WHERE a.`listid` IN ('.implode(',',$campaignAdded).') AND b.`published` = 1 GROUP BY a.listid';
		$db->setQuery($query);
		$maxunsubdate = $db->loadObjectList();
		if(empty($maxunsubdate)) return true;
		$queryInsert = array();
		foreach($maxunsubdate as $onecampaign){
			$queryInsert[] = $onecampaign->listid.','.$subid.','.$time.','.($time+$onecampaign->maxsenddate).',1';
		}
		$query = 'REPLACE INTO '.acymailing::table('listsub').' (listid,subid,subdate,unsubdate,status) VALUES ('.implode('),(',$queryInsert).')';
		$db->setQuery($query);
		$db->query();
		$querySelect = 'SELECT '.$subid.',a.`mailid`,'.$time.' + b.`senddate`,'.(int) $config->get('priority_followup',2);
		$querySelect .= ' FROM '.acymailing::table('listmail').' as a LEFT JOIN '.acymailing::table('mail').' as b on a.`mailid` = b.`mailid`';
		$querySelect .= ' WHERE a.`listid` IN ('.implode(',',$campaignAdded).') AND b.`published` = 1';
		$query = 'INSERT IGNORE INTO '.acymailing::table('queue').' (`subid`,`mailid`,`senddate`,`priority`) '.$querySelect;
		$db->setQuery($query);
		return $db->query();
	}
	function stop($subid,$listids){
		$listCampaignClass = acymailing::get('class.listcampaign');
		$campaignids = $listCampaignClass->getAffectedCampaigns($listids);
		if(empty($campaignids)) return true;
		$selectquery = 'SELECT `mailid` FROM '.acymailing::table('listmail').' WHERE `listid` IN ('.implode(',',$campaignids).')';
		$query = 'DELETE FROM '.acymailing::table('queue').' WHERE `subid` = '.$subid.' AND `mailid` IN ('.$selectquery.')';
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$db->query();
		$time = time();
		$db->setQuery('UPDATE '.acymailing::table('listsub').' SET `unsubdate` = '.$time.', `status` = -1 WHERE `subid` = '.$subid.'. AND `listid` IN ('.implode(',',$campaignids).')');
		return $db->query();
	}
	function updateUnsubdate($campaignid,$newdelay){
		$campaignid = intval($campaignid);
		$newdelay = intval($newdelay);
		$db =& JFactory::getDBO();
		$query = 'UPDATE `#__acymailing_listsub` SET `unsubdate` = `subdate` + '.$newdelay.' WHERE listid = '.$campaignid.' AND `subdate` + '.$newdelay.' > `unsubdate` AND `status` = 1 AND `subdate` > '.(time() - $newdelay);
		$db->setQuery($query);
		$db->query();
	}
}//endclass