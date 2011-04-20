<?php
/****************************************************************************************
* LiveZilla functions.data.db.update.inc.php
* 
* Copyright 2010 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

function updateDatabase($_version,$_link,$_prefix)
{
	global $GROUPS;
	$versions = array("3.1.8.1","3.1.8.2","3.1.8.3","3.1.8.4","3.1.8.5","3.1.8.6","3.2.0.0","3.2.0.1","3.2.0.2");
	if(!in_array($_version,$versions))
		return "Invalid version! (".$_version.")";
	
	while($_version != VERSION)
	{
		if($_version == $versions[0])
			$_version = $versions[1];
		if($_version == $versions[1])
			$_version = $versions[2];
		if($_version == $versions[2])
		{
			$result = up_3183_3184($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[3];
			else
				return $result;
		}
		if($_version == $versions[3])
			$_version = $versions[4];
		if($_version == $versions[4])
			$_version = $versions[5];
		if($_version == $versions[5])
		{
			$result = up_3186_3200($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[6];
			else
				return $result;
		}
		if($_version == $versions[6])
		{
			$result = up_3200_3201($_prefix,$_link);
			if($result === TRUE)
				$_version = $versions[7];
			else
				return $result;
		}
		if($_version == "3.2.0.1")
		{
			$result = up_3201_3202();
			if($result === TRUE)
				$_version = "3.2.0.2";
			else
				return $result;
		}
	}
	@mysql_query("UPDATE `".@mysql_real_escape_string($_prefix)."info` SET `version`='" . VERSION . "'",$_link);
	return true;
}

function up_3201_3202()
{
	@rename(PATH_IMAGES . "lz_header.gif",FILE_CARRIERHEADER);
	return true;
}

function up_3200_3201($_prefix,$_link)
{
	$commands = Array();
	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."chats` ADD `question` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';";
	$allowedecs = Array(1060);
	foreach($commands as $key => $command)
	{
		$result = @mysql_query($command,$_link);
		if(!$result && mysql_errno() != $allowedecs[$key])
			return mysql_errno() . ": " . mysql_error() . "\r\n\r\nMySQL Query: " . $commands[$key];
	}
	return true;
}

function up_3186_3200($_prefix,$_link)
{
	$commands = Array();
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."alerts` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `created` int(10) unsigned NOT NULL DEFAULT '0', `receiver_user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_browser_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `event_action_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `text` mediumtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `displayed` tinyint(1) unsigned NOT NULL DEFAULT '0', `accepted` tinyint(1) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
 	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` ADD `invitation_auto` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL AFTER `invitation`";
 	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` CHANGE `invitation` `invitation_manual` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL";
	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` CHANGE `website_push` `website_push_manual` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL";
	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` ADD `website_push_auto` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL AFTER `website_push_manual`";
	$commands[] = "UPDATE `".@mysql_real_escape_string($_prefix)."predefined` SET `invitation_auto`=`invitation_manual`";
	$commands[] = "UPDATE `".@mysql_real_escape_string($_prefix)."predefined` SET `website_push_auto`=`website_push_manual`";
 	$commands[] = "RENAME TABLE `".@mysql_real_escape_string($_prefix)."rooms` TO `".@mysql_real_escape_string($_prefix)."chat_rooms`;";
 	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_rooms` ADD `creator` varchar(32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT ''";
 	$commands[] = "RENAME TABLE `".@mysql_real_escape_string($_prefix)."posts`  TO `".@mysql_real_escape_string($_prefix)."chat_posts`;";
 	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."chat_posts` ADD `chat_id` varchar(32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `id` ";
	$commands[] = "RENAME TABLE `".@mysql_real_escape_string($_prefix)."res`  TO `".@mysql_real_escape_string($_prefix)."resources`;";
	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."chats` ADD `group_id` varchar(32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `internal_id`";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."logins` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`ip` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`time` int(11) unsigned NOT NULL DEFAULT '0', `password` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."chat_requests` ( `id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `created` int(10) unsigned NOT NULL DEFAULT '0', `sender_system_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `sender_group_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_browser_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `event_action_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `text` mediumtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `displayed` tinyint(1) unsigned NOT NULL DEFAULT '0', `accepted` tinyint(1) unsigned NOT NULL DEFAULT '0', `declined` tinyint(1) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."events` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`name` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`created` int(10) unsigned NOT NULL DEFAULT '0', `creator` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `edited` int(10) unsigned NOT NULL DEFAULT '0', `editor` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `pages_visited` int(10) unsigned NOT NULL DEFAULT '0', `time_on_site` int(10) unsigned NOT NULL DEFAULT '0', `max_trigger_amount` int(10) unsigned NOT NULL DEFAULT '0', `trigger_again_after` int(10) unsigned NOT NULL DEFAULT '0', `not_declined` tinyint(1) unsigned NOT NULL DEFAULT '0', `not_accepted` tinyint(1) unsigned NOT NULL DEFAULT '0', `not_in_chat` tinyint(1) unsigned NOT NULL DEFAULT '0', `priority` int(10) unsigned NOT NULL DEFAULT '0', `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."event_actions` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `eid` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `type` tinyint(2) unsigned NOT NULL DEFAULT '0', `value` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."event_action_internals` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`created` int(10) unsigned NOT NULL DEFAULT '0',`trigger_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',`receiver_user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."event_action_invitations` ( `id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `action_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `position` varchar(2) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `speed` tinyint(1) NOT NULL DEFAULT '1', `slide` tinyint(1) NOT NULL DEFAULT '1', `margin_left` int(11) NOT NULL DEFAULT '0', `margin_top` int(11) NOT NULL DEFAULT '0', `margin_right` int(11) NOT NULL DEFAULT '0', `margin_bottom` int(11) NOT NULL DEFAULT '0', `style` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `close_on_click` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."event_action_receivers` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `action_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."event_action_senders` ( `id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `pid` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `group_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `priority` tinyint(2) unsigned NOT NULL DEFAULT '1', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."event_action_website_pushs` ( `id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `action_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `target_url` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `ask` tinyint(1) NOT NULL DEFAULT '1', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."event_triggers` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_browser_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `action_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `time` int(10) unsigned NOT NULL DEFAULT '0', `triggered` int(10) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."event_urls` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `eid` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `url` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `referrer` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `time_on_site` int(10) unsigned NOT NULL DEFAULT '0', `blacklist` tinyint(1) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."website_pushs` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `created` int(10) unsigned NOT NULL DEFAULT '0', `sender_system_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `receiver_browser_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `text` mediumtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `ask` tinyint(1) unsigned NOT NULL DEFAULT '0', `target_url` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `displayed` tinyint(1) unsigned NOT NULL DEFAULT '0', `accepted` tinyint(1) unsigned NOT NULL DEFAULT '0', `declined` tinyint(1) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."predefined` ADD `editable` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';";
	$commands[] = "ALTER TABLE `".@mysql_real_escape_string($_prefix)."chats` ADD `area_code` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `group_id`;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."profiles` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `edited` int(11) NOT NULL DEFAULT '0', `first_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `last_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `company` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `phone` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `fax` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `street` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `zip` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `department` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `city` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `country` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `gender` tinyint(1) NOT NULL DEFAULT '0', `languages` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `comments` longtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `public` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$commands[] = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."profile_pictures` (`id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', `internal_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `time` int(11) NOT NULL DEFAULT '0', `webcam` tinyint(1) NOT NULL DEFAULT '0', `data` mediumtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, PRIMARY KEY (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$allowedecs = Array(1050,1054,1054,1054,1060,1060,1060,1050,1060,1050,1060,1050,1060,1050,1050,1050,1050,1050,1050,1050,1050,1050,1050,1050,1050,1060,1060,1050,1050);
	foreach($commands as $key => $command)
	{
		$result = @mysql_query($command,$_link);
		if(!$result && mysql_errno() != $allowedecs[$key])
			return mysql_errno() . ": " . mysql_error() . "\r\n\r\nMySQL Query: " . $commands[$key];
	}
	return true;
}

function up_3183_3184($_prefix,$_link)
{
	global $INTERNAL,$GROUPS;
	$result = @mysql_query("ALTER TABLE `".@mysql_real_escape_string($_prefix)."info` ADD `chat_id` INT NOT NULL DEFAULT '11700'",$_link);
	if(!$result && mysql_errno() != 1060)
		return mysql_errno() . ": " . mysql_error();
		
	$result = @mysql_query("ALTER TABLE `".@mysql_real_escape_string($_prefix)."info` ADD `ticket_id` INT NOT NULL DEFAULT '11700'",$_link);
	if(!$result && mysql_errno() != 1060)
		return mysql_errno() . ": " . mysql_error();
		
	$result = @mysql_query("ALTER TABLE `".@mysql_real_escape_string($_prefix)."chats` ADD `transcript_sent` tinyint(1) unsigned NOT NULL default '1'",$_link);
	if(!$result && mysql_errno() != 1060)
		return mysql_errno() . ": " . mysql_error();

	$result = @mysql_query("ALTER TABLE `".@mysql_real_escape_string($_prefix)."res` CHANGE `html` `value` longtext character set utf8 collate utf8_bin NOT NULL",$_link);
	if(!$result && mysql_errno() != 1054)
		return mysql_errno() . ": " . mysql_error();

	$result = @mysql_query("ALTER TABLE `".@mysql_real_escape_string($_prefix)."res` ADD `size` bigint(20) unsigned NOT NULL default '0'",$_link);
	if(!$result && mysql_errno() != 1060)
		return mysql_errno() . ": " . mysql_error();

	$dirs = array(PATH_UPLOADS_INTERNAL,PATH_UPLOADS_EXTERNAL);
	$baseFolderInternal = $baseFolderExternal = false;
	foreach($dirs as $tdir)
	{
		$subdirs = getDirectory($tdir,false,true);
		foreach($subdirs as $dir)
		{
			if(@is_dir($tdir.$dir."/"))
			{
				if($tdir == PATH_UPLOADS_INTERNAL)
					$owner = getInternalSystemIdByUserId($dir);
				else
					$owner = CALLER_SYSTEM_ID;
				
				if(!isset($INTERNAL[$owner]))
					continue;

				$files = getDirectory($tdir.$dir."/",false,true);
				foreach($files as $file)
				{
					if($file != FILE_INDEX && $file != FILE_INDEX_OLD)
					{
						if($tdir == PATH_UPLOADS_INTERNAL)
						{
							$parentId = $owner;
							$type = 3;
							if(!$baseFolderInternal)
							{
								createFileBaseFolders($owner,true);
								$baseFolderInternal = true;
							}
							processResource($owner,$owner,$INTERNAL[$owner]->Fullname,0,$INTERNAL[$owner]->Fullname,0,4,3);
						}
						else
						{
							$parentId = 5;
							$owner = CALLER_SYSTEM_ID;
							$type = 4;
							if(!$baseFolderExternal)
							{
								createFileBaseFolders($owner,false);
								$baseFolderExternal = true;
							}
						}
						$cfile = ($tdir != PATH_UPLOADS_INTERNAL) ? base64_decode($file) : $file;
						$size = filesize($tdir.$dir."/".$file);
						$fid = md5($file . $owner . $size);
						$filename = $owner . "_" . $fid;
						copy($tdir.$dir."/".$file,PATH_UPLOADS . $filename);
						processResource($owner,$fid,$filename,$type,$cfile,0,$parentId,4,$size);
					}
				}
			}
		}
	}
	
	$sql = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."predefined` (`id` int(11) unsigned NOT NULL,`internal_id` varchar(32) character set utf8 collate utf8_bin NOT NULL,`group_id` varchar(32) character set utf8 collate utf8_bin NOT NULL,`lang_iso` varchar(2) character set utf8 collate utf8_bin NOT NULL,`invitation` mediumtext character set utf8 collate utf8_bin NOT NULL,`welcome` mediumtext character set utf8 collate utf8_bin NOT NULL,`website_push` mediumtext character set utf8 collate utf8_bin NOT NULL,`browser_ident` tinyint(1) unsigned NOT NULL default '0',`is_default` tinyint(1) unsigned NOT NULL default '0', `auto_welcome` tinyint(1) unsigned NOT NULL default '0',PRIMARY KEY  (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$result = mysql_query($sql,$_link);
	if(!$result && mysql_errno() != 1050)
		return mysql_errno() . ": " . mysql_error();
	else if($result)
	{
		$counter = 0;
		foreach($GROUPS as $gid => $group)
		{
			@mysql_query("INSERT INTO `".@mysql_real_escape_string($_prefix)."predefined` (`id` ,`internal_id`, `group_id` ,`lang_iso` ,`invitation` ,`welcome` ,`website_push` ,`browser_ident` ,`is_default` ,`auto_welcome`) VALUES ('".@mysql_real_escape_string($counter++)."', '', '".@mysql_real_escape_string($gid)."', 'EN', 'Hello, my name is %name%. Do you need help? Start Live-Chat now to get assistance.', 'Hello %external_name%, my name is %name%, how may I help you?', 'Website Operator %name% would like to redirect you to this URL:\r\n\r\n%url%', '1', '1', '1');",$_link);
			@mysql_query("INSERT INTO `".@mysql_real_escape_string($_prefix)."predefined` (`id` ,`internal_id`, `group_id` ,`lang_iso` ,`invitation` ,`welcome` ,`website_push` ,`browser_ident` ,`is_default` ,`auto_welcome`) VALUES ('".@mysql_real_escape_string($counter++)."', '', '".@mysql_real_escape_string($gid)."', 'DE', '".utf8_encode("Guten Tag, meine Name ist %name%. Benötigen Sie Hilfe? Gerne berate ich Sie in einem Live Chat")."', 'Guten Tag %external_name%, mein Name ist %name% wie kann ich Ihnen helfen?', '".utf8_encode("Ein Betreuer dieser Webseite (%name%) möchte Sie auf einen anderen Bereich weiterleiten:\\r\\n\\r\\n%url%")."', '1', '0', '1');",$_link);
		}
	}
	
	$sql = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."rooms` (`id` int(11) NOT NULL,`time` int(11) NOT NULL,`last_active` int(11) NOT NULL,`status` tinyint(1) NOT NULL default '0',`target_group` varchar(64) NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$result = mysql_query($sql,$_link);
	if(!$result && mysql_errno() != 1050)
		return mysql_errno() . ": " . mysql_error();
		
	$sql = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."posts` (`id` varchar(32) character set utf8 collate utf8_bin NOT NULL,`time` int(10) unsigned NOT NULL default '0',`micro` int(10) unsigned NOT NULL default '0',`sender` varchar(32) character set utf8 collate utf8_bin NOT NULL,`receiver` varchar(32) character set utf8 collate utf8_bin NOT NULL,`receiver_group` varchar(32) character set utf8 collate utf8_bin NOT NULL,`text` mediumtext character set utf8 collate utf8_bin NOT NULL,`received` tinyint(1) unsigned NOT NULL default '0',`persistent` tinyint(1) unsigned NOT NULL default '0', PRIMARY KEY  (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$result = mysql_query($sql,$_link);
	if(!$result && mysql_errno() != 1050)
		return mysql_errno() . ": " . mysql_error();
		
	$sql = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."tickets` (`id` varchar(32) character set utf8 collate utf8_bin NOT NULL,`user_id` varchar(32) character set utf8 collate utf8_bin NOT NULL,`target_group_id` varchar(32) character set utf8 collate utf8_bin NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$result = mysql_query($sql,$_link);
	if(!$result && mysql_errno() != 1050)
		return mysql_errno() . ": " . mysql_error();

	$sql = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."ticket_editors` (`ticket_id` int(10) unsigned NOT NULL,`internal_fullname` varchar(32) character set utf8 collate utf8_bin NOT NULL,`status` tinyint(1) unsigned NOT NULL default '1',`time` int(10) unsigned NOT NULL,PRIMARY KEY  (`ticket_id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$result = mysql_query($sql,$_link);
	if(!$result && mysql_errno() != 1050)
		return mysql_errno() . ": " . mysql_error();
			
	$sql = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."ticket_messages` (`id` int(11) unsigned NOT NULL auto_increment,`time` int(11) unsigned NOT NULL,`ticket_id` varchar(32) character set utf8 collate utf8_bin NOT NULL,`text` mediumtext character set utf8 collate utf8_bin NOT NULL,`fullname` varchar(32) character set utf8 collate utf8_bin NOT NULL,`email` varchar(50) character set utf8 collate utf8_bin NOT NULL,`company` varchar(50) character set utf8 collate utf8_bin NOT NULL,`ip` varchar(15) character set utf8 collate utf8_bin NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin AUTO_INCREMENT=1;";
	$result = mysql_query($sql,$_link);
	if(!$result && mysql_errno() != 1050)
		return mysql_errno() . ": " . mysql_error();
	
	$sql = "CREATE TABLE `".@mysql_real_escape_string($_prefix)."ratings` (`id` varchar(32) character set utf8 collate utf8_bin NOT NULL, `time` int(11) unsigned NOT NULL, `user_id` varchar(32) character set utf8 collate utf8_bin NOT NULL, `internal_id` varchar(32) character set utf8 collate utf8_bin NOT NULL, `fullname` varchar(32) character set utf8 collate utf8_bin NOT NULL, `email` varchar(50) character set utf8 collate utf8_bin NOT NULL, `company` varchar(50) character set utf8 collate utf8_bin NOT NULL, `qualification` tinyint(1) unsigned NOT NULL, `politeness` tinyint(1) unsigned NOT NULL, `comment` varchar(400) character set utf8 collate utf8_bin NOT NULL, `ip` varchar(15) character set utf8 collate utf8_bin NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_bin;";
	$result = mysql_query($sql,$_link);
	if(!$result && mysql_errno() != 1050)
		return mysql_errno() . ": " . mysql_error();
	return TRUE;
}
?>