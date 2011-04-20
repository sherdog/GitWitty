<?php
/****************************************************************************************
* LiveZilla functions.internal.man.inc.php
* 
* Copyright 2010 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();

function setAvailability($_available)
{
	global $INTERNAL,$RESPONSE;
	if($INTERNAL[CALLER_SYSTEM_ID]->Level==USER_LEVEL_ADMIN)
	{
		if($_available=="1" && file_exists(FILE_SERVER_DISABLED))
			@unlink(FILE_SERVER_DISABLED);
		else if($_available=="0")
			createFile(FILE_SERVER_DISABLED,time(),true);
		$RESPONSE->SetStandardResponse(1,"");
	}
}

function setIdle($_idle)
{
	global $INTERNAL,$RESPONSE;
	if($INTERNAL[CALLER_SYSTEM_ID]->Level==USER_LEVEL_ADMIN)
	{
		if($_idle=="0" && file_exists(FILE_SERVER_IDLE))
			@unlink(FILE_SERVER_IDLE);
		else if($_idle=="1")
			createFile(FILE_SERVER_IDLE,time(),true);
		$RESPONSE->SetStandardResponse(1,"");
	}
}

function getBannerList($list = "")
{
	global $VISITOR,$CONFIG,$RESPONSE;
	$banners = getDirectory(PATH_BANNER,".php",true);
	sort($banners);
	foreach($banners as $banner)
	{
		if(@is_dir(PATH_BANNER . $banner) || ((strpos($banner,"_0.png") === false && strpos($banner,"_1.png") === false) && (strpos($banner,"_0.gif") === false && strpos($banner,"_1.gif") === false)))
			continue;
		$list .= "<banner name=\"".base64_encode($banner)."\" hash=\"".base64_encode(hashFile(PATH_BANNER . $banner))."\"/>\r\n";
	}
	$RESPONSE->SetStandardResponse(1,"<banner_list>".$list."</banner_list>");
}

function getTranslationData($translation = "")
{
	global $LZLANG,$RESPONSE;
	if(!(isset($_POST[POST_INTERN_DOWNLOAD_TRANSLATION_ISO]) && (strlen($_POST[POST_INTERN_DOWNLOAD_TRANSLATION_ISO])==2||strlen($_POST[POST_INTERN_DOWNLOAD_TRANSLATION_ISO])==5)))
	{
		$RESPONSE->SetStandardResponse(1,"");
		return;
	}
	include("./_language/lang" . strtolower($_POST[POST_INTERN_DOWNLOAD_TRANSLATION_ISO]) . ".php");
	$translation .= "<language key=\"".base64_encode($_POST[POST_INTERN_DOWNLOAD_TRANSLATION_ISO])."\">\r\n";
	foreach($LZLANG as $key => $value)
		$translation .= "<val key=\"".base64_encode($key)."\">".base64_encode($value)."</val>\r\n";
	$translation .= "</language>\r\n";
	$RESPONSE->SetStandardResponse(1,$translation);
}

function updatePredefinedMessages($_counter = 0)
{
	global $GROUPS,$INTERNAL;
	clearPredefinedMessages();

	$tpm_types = array("g"=>$GROUPS,"u"=>$INTERNAL);
	$pms = array();
	foreach($tpm_types as $type => $objectlist)
		foreach($objectlist as $id => $object)
		{
			$pms[$type.$id] = array();
			foreach($_POST as $key => $value)
				if(strpos($key,"p_db_pm_".$type."_" . $id . "_")===0)
				{
					$parts = explode("_",$key);
					if(!isset($pms[$type.$id][$parts[5]]))
					{
						$pms[$type.$id][$parts[5]] = new PredefinedMessage();
						$pms[$type.$id][$parts[5]]->GroupId = ($type=="g") ? $id : "";
						$pms[$type.$id][$parts[5]]->UserId = ($type=="u") ? $id : "";
						$pms[$type.$id][$parts[5]]->LangISO = $parts[5];
					}
					$pms[$type.$id][$parts[5]]->XMLParamAlloc($parts[6],$value);
				}
		}

	foreach($pms as $oid => $messages)
		foreach($messages as $iso => $message)
		{
			$message->Id = $_counter++;
			$message->Save();
		}
}

function setManagement()
{
	global $INTERNAL,$RESPONSE,$GROUPS;
	
	if(!DB_CONNECTION)
	{
		$res = testDataBase($CONFIG["gl_db_host"],$CONFIG["gl_db_user"],$CONFIG["gl_db_pass"],$CONFIG["gl_db_name"],$CONFIG["gl_db_prefix"]);
			if(!isnull($res))
				$RESPONSE->SetValidationError(LOGIN_REPLY_DB,$res);
		return;
	}
	
	if($INTERNAL[CALLER_SYSTEM_ID]->Level == USER_LEVEL_ADMIN)
	{
		createFile(PATH_USERS . "internal.inc.php",base64_decode($_POST[POST_INTERN_FILE_INTERN]),true);
		createFile(PATH_GROUPS . "groups.inc.php",base64_decode($_POST[POST_INTERN_FILE_GROUPS]),true);
		getData(true,true,true,false);
		updatePredefinedMessages();
			
		if(isset($_POST[POST_INTERN_EDIT_USER]))
		{
			$combos = explode(";",$_POST[POST_INTERN_EDIT_USER]);
			for($i=0;$i<count($combos);$i++)
				if(strpos($combos[$i],",") !== false)
				{
					$vals = explode(",",$combos[$i]);
					if(strlen($vals[1])>0)
						$INTERNAL[$vals[0]]->ChangePassword($vals[1]);
					$INTERNAL[$vals[0]]->SetPasswordChangeNeeded(($vals[2] == 1));
				}
		}
		$userdirs = getDirectory(PATH_DATA_INTERNAL,".htm",true);
		foreach($userdirs as $userdir)
			if(!isset($INTERNAL[$userdir]))
				deleteDirectory(PATH_DATA_INTERNAL . $userdir);
				
		$datafiles = getDirectory(PATH_USERS,".htm",true);
		foreach($datafiles as $datafile)
			if(strpos($datafile, FILE_EXTENSION_PASSWORD) !== false || strpos($datafile, FILE_EXTENSION_CHANGE_PASSWORD) !== false)
			{
				$parts = explode(".",$datafile);
				if(!isset($INTERNAL[$parts[0]]))
					@unlink(PATH_USERS . $datafile);
			}
		setIdle(0);
		$RESPONSE->SetStandardResponse(1,"");
	}
}

function setConfig($id = 0)
{
	global $INTERNAL,$RESPONSE;
	if(SERVERSETUP)
	{
		$id = createFile(FILE_CONFIG,base64_decode($_POST[POST_INTERN_UPLOAD_VALUE]),true);
		if(isset($_POST[POST_INTERN_SERVER_AVAILABILITY]))
			setAvailability($_POST[POST_INTERN_SERVER_AVAILABILITY]);
		
		if(isset($_POST[POST_INTERN_FILE_CARRIER_LOGO]) && strlen($_POST[POST_INTERN_FILE_CARRIER_LOGO]) > 0)
			base64ToFile(FILE_CARRIERLOGO,$_POST[POST_INTERN_FILE_CARRIER_LOGO]);
		else if(isset($_POST[POST_INTERN_FILE_CARRIER_LOGO]) && file_exists(FILE_CARRIERLOGO))
			@unlink(FILE_CARRIERLOGO);
			
		if(isset($_POST[POST_INTERN_FILE_CARRIER_HEADER]) && strlen($_POST[POST_INTERN_FILE_CARRIER_HEADER]) > 0)
			base64ToFile(FILE_CARRIERHEADER,$_POST[POST_INTERN_FILE_CARRIER_HEADER]);
		else if(isset($_POST[POST_INTERN_FILE_CARRIER_HEADER]) && file_exists(FILE_CARRIERHEADER))
			@unlink(FILE_CARRIERHEADER);
			
		if(isset($_POST[POST_INTERN_FILE_INVITATION_LOGO]) && strlen($_POST[POST_INTERN_FILE_INVITATION_LOGO]) > 0)
			base64ToFile(FILE_INVITATIONLOGO,$_POST[POST_INTERN_FILE_INVITATION_LOGO]);
		else if(isset($_POST[POST_INTERN_FILE_INVITATION_LOGO]) && file_exists(FILE_INVITATIONLOGO))
			@unlink(FILE_INVITATIONLOGO);
			
		$int = 1;
		while(isset($_POST[POST_INTERN_DOWNLOAD_TRANSLATION_ISO . "_" . $int]) && strpos($_POST[POST_INTERN_DOWNLOAD_TRANSLATION_ISO . "_" . $int],"..") === false)
		{
			if(!isset($_POST[POST_INTERN_DOWNLOAD_TRANSLATION_DELETE . "_" . $int]))
				createFile("./_language/lang" . strtolower($_POST[POST_INTERN_DOWNLOAD_TRANSLATION_ISO . "_" . $int]) . ".php", slashesStrip($_POST[POST_INTERN_DOWNLOAD_TRANSLATION_CONTENT . "_" . $int]), true);
			else
				@unlink("./_language/lang" . strtolower($_POST[POST_INTERN_DOWNLOAD_TRANSLATION_ISO . "_" . $int]) . ".php");
			$int++;
		}
	}
	removeSSpanFile(true);
	setIdle(0);
	$RESPONSE->SetStandardResponse($id,"");
}

function dataBaseTest($id=0)
{
	global $RESPONSE;
	$res = testDataBase($_POST[POST_INTERN_DATABASE_HOST],$_POST[POST_INTERN_DATABASE_USER],$_POST[POST_INTERN_DATABASE_PASS],$_POST[POST_INTERN_DATABASE_NAME],$_POST[POST_INTERN_DATABASE_PREFIX]);
	if(isnull($res))
		$RESPONSE->SetStandardResponse(1,base64_encode(""));
	else
		$RESPONSE->SetStandardResponse(2,base64_encode($res));
}

function sendTestMail()
{
	global $RESPONSE,$CONFIG;
	$return = sendMail($CONFIG["gl_mail_sender"],$CONFIG["gl_mail_sender"],$CONFIG["gl_mail_sender"],"LiveZilla Test Mail","LiveZilla Test Mail");
	if(isnull($return))
		$RESPONSE->SetStandardResponse(1,base64_encode(""));
	else
		$RESPONSE->SetStandardResponse(2,base64_encode($return));
}

function createTables($id=0)
{
	global $RESPONSE,$GROUPS;
	$connection = @mysql_connect($_POST[POST_INTERN_DATABASE_HOST],$_POST[POST_INTERN_DATABASE_USER],$_POST[POST_INTERN_DATABASE_PASS]);
	@mysql_query("SET NAMES 'utf8'", $connection);

	if(!$connection)
	{
		$error = mysql_error();
		$RESPONSE->SetStandardResponse($id,base64_encode("Can't connect to database. Invalid host or login! (" . mysql_errno() . ((!isnull($error)) ? ": " . $error : "") . ")"));
	}
	else
	{
		$db_selected = mysql_select_db(@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_NAME]),$connection);
		if (!$db_selected) 
    		$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error()));
		else
		{
			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_ALERTS."` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `created` int(10) unsigned NOT NULL DEFAULT '0', `receiver_user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_browser_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `event_action_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `text` mediumtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `displayed` tinyint(1) unsigned NOT NULL DEFAULT '0', `accepted` tinyint(1) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}
			
			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_CHATS."` (`id` varchar(32) character set utf8 collate utf8_bin NOT NULL DEFAULT '',`time` int(11) unsigned NOT NULL DEFAULT '0',`endtime` int(11) unsigned NOT NULL DEFAULT '0',`closed` int(11) unsigned NOT NULL DEFAULT '0',`chat_id` varchar(64) character set utf8 collate utf8_bin NOT NULL DEFAULT '',`external_id` varchar(32) character set utf8 collate utf8_bin NOT NULL DEFAULT '',`fullname` varchar(32) character set utf8 collate utf8_bin NOT NULL DEFAULT '',`internal_id` varchar(32) character set utf8 collate utf8_bin NOT NULL DEFAULT '', `group_id` varchar(32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `area_code` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `html` longtext character set utf8 collate utf8_bin NOT NULL,`plain` longtext character set utf8 collate utf8_bin NOT NULL,`email` varchar(50) character set utf8 collate utf8_bin NOT NULL DEFAULT '',`company` varchar(50) character set utf8 collate utf8_bin NOT NULL DEFAULT '',`iso_language` varchar(8) character set utf8 collate utf8_bin NOT NULL DEFAULT '',`host` varchar(64) character set utf8 collate utf8_bin NOT NULL DEFAULT '',`ip` varchar(15) character set utf8 collate utf8_bin NOT NULL DEFAULT '',`gzip` tinyint(1) unsigned NOT NULL DEFAULT '0',`transcript_sent` tinyint(1) unsigned NOT NULL DEFAULT '1',`question` varchar(255) character set utf8 collate utf8_bin NOT NULL DEFAULT '', PRIMARY KEY(`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}
			
			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_POSTS."` (`id` varchar(32) character set utf8 collate utf8_bin NOT NULL DEFAULT '',`chat_id` varchar(32) character set utf8 collate utf8_bin NOT NULL DEFAULT '',`time` int(11) unsigned NOT NULL default '0',`micro` int(11) unsigned NOT NULL default '0',`sender` varchar(32) character set utf8 collate utf8_bin NOT NULL DEFAULT '',`receiver` varchar(32) character set utf8 collate utf8_bin NOT NULL DEFAULT '',`receiver_group` varchar(32) character set utf8 collate utf8_bin NOT NULL DEFAULT '',`text` mediumtext character set utf8 collate utf8_bin NOT NULL,`received` tinyint(1) unsigned NOT NULL default '0',`persistent` tinyint(1) unsigned NOT NULL default '0', PRIMARY KEY  (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}
			
			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_CHAT_REQUESTS."` ( `id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `created` int(10) unsigned NOT NULL DEFAULT '0', `sender_system_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `sender_group_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_browser_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `event_action_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `text` mediumtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `displayed` tinyint(1) unsigned NOT NULL DEFAULT '0', `accepted` tinyint(1) unsigned NOT NULL DEFAULT '0', `declined` tinyint(1) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}
			
			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_ROOMS."` (`id` int(11) unsigned NOT NULL DEFAULT '0',`time` int(11) unsigned NOT NULL DEFAULT '0',`last_active` int(11) unsigned NOT NULL DEFAULT '0',`status` tinyint(1) unsigned NOT NULL DEFAULT '0',`target_group` varchar(64) NOT NULL DEFAULT '',`creator` varchar(32) NOT NULL DEFAULT '', PRIMARY KEY  (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}
			
			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_DATA."` (`file` varchar(254) character set utf8 collate utf8_bin NOT NULL DEFAULT '',`time` int(11) unsigned NOT NULL DEFAULT'0',`data` text character set utf8 collate utf8_bin NOT NULL,`size` mediumint(9) unsigned NOT NULL DEFAULT'0', UNIQUE KEY `file` (`file`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}
			
			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_EVENTS."` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`name` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`created` int(10) unsigned NOT NULL DEFAULT '0', `creator` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `edited` int(10) unsigned NOT NULL DEFAULT '0', `editor` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `pages_visited` int(10) unsigned NOT NULL DEFAULT '0', `time_on_site` int(10) unsigned NOT NULL DEFAULT '0', `max_trigger_amount` int(10) unsigned NOT NULL DEFAULT '0', `trigger_again_after` int(10) unsigned NOT NULL DEFAULT '0', `not_declined` tinyint(1) unsigned NOT NULL DEFAULT '0', `not_accepted` tinyint(1) unsigned NOT NULL DEFAULT '0', `not_in_chat` tinyint(1) unsigned NOT NULL DEFAULT '0', `priority` int(10) unsigned NOT NULL DEFAULT '0', `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}
			
			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_EVENT_ACTIONS."` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `eid` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `type` tinyint(2) unsigned NOT NULL DEFAULT '0', `value` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}
			
			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_EVENT_ACTION_INTERNALS."` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`created` int(10) unsigned NOT NULL DEFAULT '0',`trigger_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`receiver_user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}

			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_EVENT_ACTION_INVITATIONS."` ( `id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `action_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `position` varchar(2) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `speed` tinyint(1) NOT NULL DEFAULT '1', `slide` tinyint(1) NOT NULL DEFAULT '1', `margin_left` int(11) NOT NULL DEFAULT '0', `margin_top` int(11) NOT NULL DEFAULT '0', `margin_right` int(11) NOT NULL DEFAULT '0', `margin_bottom` int(11) NOT NULL DEFAULT '0', `style` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `close_on_click` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}
			
			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_EVENT_ACTION_RECEIVERS."`  (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `action_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}
			
			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_EVENT_ACTION_SENDERS."` ( `id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `pid` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `group_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `priority` tinyint(2) unsigned NOT NULL DEFAULT '1', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}
			
			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_EVENT_ACTION_WEBSITE_PUSHS."` ( `id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `action_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `target_url` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `ask` tinyint(1) NOT NULL DEFAULT '1', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}
			
			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_EVENT_TRIGGERS."` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_browser_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `action_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `time` int(10) unsigned NOT NULL DEFAULT '0', `triggered` int(10) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}
			
			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_EVENT_URLS."` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `eid` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `url` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `referrer` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `time_on_site` int(10) unsigned NOT NULL DEFAULT '0', `blacklist` tinyint(1) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}
			
			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_LOGINS."` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`ip` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`time` int(11) unsigned NOT NULL DEFAULT '0', `password` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}
			
			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_INFO."` (`version` varchar(15) character set utf8 collate utf8_bin NOT NULL DEFAULT '',`chat_id` int(11) unsigned NOT NULL default '11700',`ticket_id` int(11) unsigned NOT NULL default '11700', PRIMARY KEY  (`version`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}
			
			$sql = "INSERT INTO `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_INFO."` (`version`,`chat_id`,`ticket_id`) VALUES ('".VERSION."',11700,11700);";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1062)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}
			
			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_INTERNAL."` (`id` bigint(20) unsigned NOT NULL auto_increment,`time` int(11) unsigned NOT NULL,`time_confirmed` int(11) unsigned NOT NULL,`internal_id` varchar(15) character set utf8 collate utf8_bin NOT NULL, `status` tinyint(1) unsigned NOT NULL,  PRIMARY KEY  (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin AUTO_INCREMENT=1";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}
			
			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_RESOURCES."`  (`id` varchar(32) character set utf8 collate utf8_bin NOT NULL,`owner` varchar(15) character set utf8 collate utf8_bin NOT NULL,`editor` varchar(15) character set utf8 collate utf8_bin NOT NULL,`value` longtext character set utf8 collate utf8_bin NOT NULL,`edited` int(11) unsigned NOT NULL,`title` varchar(255) character set utf8 collate utf8_bin NOT NULL,`created` int(11) unsigned NOT NULL,`type` tinyint(1) unsigned NOT NULL,`discarded` tinyint(1) unsigned NOT NULL,`parentid` varchar(32) character set utf8 collate utf8_bin NOT NULL,`rank` int(11) unsigned NOT NULL,`size` bigint(20) unsigned NOT NULL, UNIQUE KEY `id` (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}
			
			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_PREDEFINED."` (`id` int(11) unsigned NOT NULL,`internal_id` varchar(32) character set utf8 collate utf8_bin NOT NULL,`group_id` varchar(32) character set utf8 collate utf8_bin NOT NULL,`lang_iso` varchar(2) character set utf8 collate utf8_bin NOT NULL,`invitation_manual` mediumtext character set utf8 collate utf8_bin NOT NULL,`invitation_auto` mediumtext character set utf8 collate utf8_bin NOT NULL,`welcome` mediumtext character set utf8 collate utf8_bin NOT NULL,`website_push_manual` mediumtext character set utf8 collate utf8_bin NOT NULL,`website_push_auto` mediumtext character set utf8 collate utf8_bin NOT NULL,`browser_ident` tinyint(1) unsigned NOT NULL default '0',`is_default` tinyint(1) unsigned NOT NULL default '0', `auto_welcome` tinyint(1) unsigned NOT NULL default '0', `editable` tinyint(1) unsigned NOT NULL default '0',PRIMARY KEY  (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}
			else if($result)
			{
				$counter=0;
				foreach($GROUPS as $gid => $group)
				{
					mysql_query("INSERT INTO `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_PREDEFINED."` (`id` ,`internal_id` ,`group_id` ,`lang_iso` ,`invitation_manual`, `invitation_auto` ,`welcome` ,`website_push_manual`, `website_push_auto` ,`browser_ident` ,`is_default` ,`auto_welcome`)VALUES ('".@mysql_real_escape_string($counter++)."', '','".@mysql_real_escape_string($gid)."', 'EN', 'Hello, my name is %name%. Do you need help? Start Live-Chat now to get assistance.', 'Hello, my name is %name%. Do you need help? Start Live-Chat now to get assistance.','Hello %external_name%, my name is %name%, how may I help you?', 'Website Operator %name% would like to redirect you to this URL:\r\n\r\n%url%', 'Website Operator %name% would like to redirect you to this URL:\r\n\r\n%url%', '1', '1', '1');",$connection);
					mysql_query("INSERT INTO `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_PREDEFINED."` (`id` ,`internal_id` ,`group_id` ,`lang_iso` ,`invitation_manual`, `invitation_auto` ,`welcome` ,`website_push_manual`, `website_push_auto` ,`browser_ident` ,`is_default` ,`auto_welcome`)VALUES ('".@mysql_real_escape_string($counter++)."', '','".@mysql_real_escape_string($gid)."', 'DE', '".utf8_encode("Guten Tag, meine Name ist %name%. Benötigen Sie Hilfe? Gerne berate ich Sie in einem Live Chat.")."', '".utf8_encode("Guten Tag, meine Name ist %name%. Benötigen Sie Hilfe? Gerne berate ich Sie in einem Live Chat.")."','Guten Tag %external_name%, mein Name ist %name% wie kann ich Ihnen helfen?', '".utf8_encode("Ein Betreuer dieser Webseite (%name%) möchte Sie auf einen anderen Bereich weiterleiten:\\r\\n\\r\\n%url%")."','".utf8_encode("Ein Betreuer dieser Webseite (%name%) möchte Sie auf einen anderen Bereich weiterleiten:\\r\\n\\r\\n%url%")."', '1', '0', '1');",$connection);
				}
			}
			
			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_RATINGS."` (`id` varchar(32) character set utf8 collate utf8_bin NOT NULL, `time` int(11) unsigned NOT NULL, `user_id` varchar(32) character set utf8 collate utf8_bin NOT NULL, `internal_id` varchar(32) character set utf8 collate utf8_bin NOT NULL, `fullname` varchar(32) character set utf8 collate utf8_bin NOT NULL, `email` varchar(50) character set utf8 collate utf8_bin NOT NULL, `company` varchar(50) character set utf8 collate utf8_bin NOT NULL, `qualification` tinyint(1) unsigned NOT NULL, `politeness` tinyint(1) unsigned NOT NULL, `comment` varchar(400) character set utf8 collate utf8_bin NOT NULL, `ip` varchar(15) character set utf8 collate utf8_bin NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}

			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_TICKETS."` (`id` varchar(32) character set utf8 collate utf8_bin NOT NULL,`user_id` varchar(32) character set utf8 collate utf8_bin NOT NULL,`target_group_id` varchar(32) character set utf8 collate utf8_bin NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}

			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_TICKET_EDITORS."` (`ticket_id` int(10) unsigned NOT NULL,`internal_fullname` varchar(32) character set utf8 collate utf8_bin NOT NULL,`status` tinyint(1) unsigned NOT NULL default '1',`time` int(10) unsigned NOT NULL,PRIMARY KEY  (`ticket_id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}
			
			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_TICKET_MESSAGES."` (`id` int(11) unsigned NOT NULL auto_increment,`time` int(11) unsigned NOT NULL,`ticket_id` varchar(32) character set utf8 collate utf8_bin NOT NULL,`text` mediumtext character set utf8 collate utf8_bin NOT NULL,`fullname` varchar(32) character set utf8 collate utf8_bin NOT NULL,`email` varchar(50) character set utf8 collate utf8_bin NOT NULL,`company` varchar(50) character set utf8 collate utf8_bin NOT NULL,`ip` varchar(15) character set utf8 collate utf8_bin NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin AUTO_INCREMENT=1;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}

			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_WEBSITE_PUSHS."` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `created` int(10) unsigned NOT NULL DEFAULT '0', `sender_system_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_browser_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `text` mediumtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `ask` tinyint(1) unsigned NOT NULL DEFAULT '0', `target_url` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `displayed` tinyint(1) unsigned NOT NULL DEFAULT '0', `accepted` tinyint(1) unsigned NOT NULL DEFAULT '0', `declined` tinyint(1) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}

			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_PROFILES."` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `edited` int(11) NOT NULL DEFAULT '0', `first_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `last_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `company` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `phone` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `fax` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `street` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `zip` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `department` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `city` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `country` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `gender` tinyint(1) NOT NULL DEFAULT '0', `languages` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `comments` longtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `public` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}
			
			$sql = "CREATE TABLE `".@mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_PROFILE_PICTURES."` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `internal_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `time` int(11) NOT NULL DEFAULT '0', `webcam` tinyint(1) NOT NULL DEFAULT '0', `data` mediumtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error() . "\r\n\r\nSQL: " . $sql));
				return;
			}
			
			$RESPONSE->SetStandardResponse(1,base64_encode(""));
		}
	}
}

function testDataBase($_host,$_user,$_pass,$_dbname,$_prefix)
{
	if(!function_exists("mysql_connect"))
		return "PHP/MySQL extension is missing (php_mysql.dll)";
		
	$connection = @mysql_connect($_host,$_user,$_pass);
	@mysql_query("SET NAMES 'utf8'", $connection);
	if(!$connection)
	{
		$error = mysql_error();
		return "Can't connect to database. Invalid host or login! (" . mysql_errno() . ((!isnull($error)) ? ": " . $error : "") . ")";
	}
	else
	{
		$db_selected = @mysql_select_db(@mysql_real_escape_string($_dbname),$connection);
		if (!$db_selected) 
    		return mysql_errno() . ": " . mysql_error();
		else
		{
			$rand = substr(rand(10000,1000000),0,15);
			$tables = 
			array(
				DATABASE_DATA=>array("`file`","`time`","`data`","`size`"),
				DATABASE_CHATS=>array("`id`","`time`","`endtime`","`closed`","`chat_id`","`external_id`","`fullname`","`internal_id`","`group_id`","`area_code`","`html`","`plain`","`email`","`company`","`iso_language`","`host`","`ip`","`gzip`","`transcript_sent`","`question`"),
				DATABASE_INFO=>array("`version`","`chat_id`","`ticket_id`"),
				DATABASE_INTERNAL=>array("`id`","`time`","`time_confirmed`","`internal_id`","`status`"),
				DATABASE_RESOURCES=>array("`id`","`owner`","`editor`","`value`","`edited`","`title`","`created`","`type`","`discarded`","`parentid`","`rank`","`size`"),
				DATABASE_PREDEFINED=>array("`id`","`internal_id`","`group_id`","`lang_iso`","`invitation_manual`","`invitation_auto`","`welcome`","`website_push_manual`","`website_push_auto`","`browser_ident`","`is_default`","`auto_welcome`","`editable`"),
				DATABASE_ROOMS=>array("`id`","`time`","`last_active`","`status`","`target_group`","`creator`"),
				DATABASE_TICKETS=>array("`id`","`user_id`","`target_group_id`"),
				DATABASE_TICKET_MESSAGES=>array("`id`","`time`","`ticket_id`","`text`","`fullname`","`email`","`company`","`ip`"),
				DATABASE_TICKET_EDITORS=>array("`ticket_id`","`internal_fullname`","`status`","`time`"),
				DATABASE_POSTS=>array("`id`","`chat_id`","`time`","`micro`","`sender`","`receiver`","`receiver_group`","`text`","`received`","`persistent`"),
				DATABASE_EVENTS=>array("`id`","`name`","`created`","`creator`","`edited`","`editor`","`pages_visited`","`time_on_site`","`max_trigger_amount`","`trigger_again_after`","`not_declined`","`not_accepted`","`not_in_chat`","`priority`","`is_active`"),
				DATABASE_EVENT_ACTION_INVITATIONS=>array("`id`","`action_id`","`position`","`speed`","`slide`","`margin_left`","`margin_top`","`margin_right`","`margin_bottom`","`style`","`close_on_click`"),
				DATABASE_EVENT_TRIGGERS=>array("`id`","`receiver_user_id`","`receiver_browser_id`","`action_id`","`time`","`triggered`"),
				DATABASE_PROFILES=>array("`id`" ,"`edited`" ,"`first_name`" ,"`last_name`" ,"`email`" ,"`company`" ,"`phone`" ,"`fax`" ,"`street`" ,"`zip`" ,"`department`" ,"`city`" ,"`country`" ,"`gender`" ,"`languages`" ,"`comments`" ,"`public`"),
				DATABASE_PROFILE_PICTURES=>array("`id`","`internal_id`","`time`","`webcam`","`data`")
			);
			
			$result = @mysql_query("SELECT version FROM `".@mysql_real_escape_string($_prefix).DATABASE_INFO."`",$connection);
			$row = @mysql_fetch_array($result, MYSQL_BOTH);
			$version = $row["version"];
			if(!$result || isnull($version))
				return "Cannot read the LiveZilla Database version. Please try to recreate the table structure.";
			
			if($version != VERSION)
			{
				require_once("./_lib/functions.data.db.update.inc.php");
				$upres = updateDatabase($version,$connection,$_prefix);
				if($upres !== true)
					return "Cannot update database structure from [".$version."] to [".VERSION."]. Please make sure that the user " . $_user . " has the MySQL permission to ALTER tables in " . $_dbname .".\r\n\r\nError: " . $upres;
			}
			
			foreach($tables as $tblName => $fieldlist)
			{
				$result = @mysql_query("SHOW COLUMNS FROM `".@mysql_real_escape_string($_prefix.$tblName)."`",$connection);
				if(!$result)
					return mysql_errno() . ": " . mysql_error();
				else if(@mysql_num_rows($result) != count($fieldlist))
					return "Invalid field count for " . $_prefix.$tblName . ". Delete " . $_prefix.$tblName. " manually and try to recreate the tables.";
			}
			return null;
		}
	}
}


?>
