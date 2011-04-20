<?php
/****************************************************************************************
* LiveZilla functions.internal.inc.php
* 
* Copyright 2010 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();

function validate()
{
	global $INTERNAL,$GROUPS,$RESPONSE,$CONFIG, $LZLANG;
	if(DB_CONNECTION || SERVERSETUP)
	{
		if(isset($_POST[POST_INTERN_AUTHENTICATION_USERID]) && isset($_POST[POST_INTERN_AUTHENTICATION_PASSWORD]))
		{
			foreach($INTERNAL as $sysId => $internuser)
			{
				if(strtolower($internuser->UserId) == strtolower($_POST[POST_INTERN_AUTHENTICATION_USERID]))
				{
					if($internuser->SaveLoginAttempt(md5($_POST[POST_INTERN_AUTHENTICATION_PASSWORD])))
					{
						if(LOGIN || SERVERSETUP)
							$internuser->LoadPassword();
						if($internuser->Password == md5($_POST[POST_INTERN_AUTHENTICATION_PASSWORD]))
						{
							define("CALLER_SYSTEM_ID",$sysId);
							$internuser->DeleteLoginAttempts();
							$internuser->LoadPredefinedMessages($sysId,false);
							
							if(isset($_POST[POST_INTERN_NEW_PASSWORD]))
								$INTERNAL[CALLER_SYSTEM_ID]->ChangePassword($_POST[POST_INTERN_NEW_PASSWORD]);
							
							if(LOGIN && !SERVERSETUP)
							{
								if($internuser->LastActive != null && $internuser->LastActive > (time()-$CONFIG["timeout_clients"]) && isset($internuser->LoginId) && $_POST[POST_INTERN_AUTHENTICATION_LOGINID] != $internuser->LoginId)
								{
									define("AUTH_RESULT",LOGIN_REPLY_ALREADY_ONLINE);
									break;
								}
								else if($internuser->IsPasswordChangeNeeded())
								{
									define("AUTH_RESULT",LOGIN_REPLY_CHANGE_PASS);
									break;
								}
							}
							
							if(SERVERSETUP && $internuser->Level != USER_LEVEL_ADMIN)
							{
								define("AUTH_RESULT",LOGIN_REPLY_NOADMIN);
								break;
							}
							
							define("VALIDATED",true);
							if(!LOGOFF && isset($_POST[POST_INTERN_AUTHENTICATION_LOGINID]))
								$internuser->LoginId = $_POST[POST_INTERN_AUTHENTICATION_LOGINID];
							elseif(LOGOFF)
								$internuser->LoginId = null;
							
							define("AUTH_RESULT",LOGIN_REPLY_SUCCEEDED);
							if(isset($_POST[POST_GLOBAL_TYPING]) && $_POST[POST_INTERN_SERVER_ACTION] == INTERN_ACTION_LISTEN)
								$internuser->Typing = $_POST[POST_GLOBAL_TYPING];
							break;
						}
						else
						{
							if($internuser->LastActive < (time()-$CONFIG["timeout_clients"]))
								$internuser->Destroy();
							break;
						}
					}
				}
			}
		}
	}
	else
		define("AUTH_RESULT",LOGIN_REPLY_DB);
	
	if(defined("VALIDATED") && LOGIN)
	{
		$INTERNAL[CALLER_SYSTEM_ID]->IP = getIP();
		$INTERNAL[CALLER_SYSTEM_ID]->FirstActive = time();
		$INTERNAL[CALLER_SYSTEM_ID]->VisitorFileSizes = array();
		$INTERNAL[CALLER_SYSTEM_ID]->VisitorStaticReload = array();
		$RESPONSE->Login = $INTERNAL[CALLER_SYSTEM_ID]->GetLoginReply($GROUPS[$INTERNAL[CALLER_SYSTEM_ID]->Groups[0]]->IsExternal,getTimeDifference($_POST[POST_INTERN_CLIENT_TIME]));
	}
	if(!defined("AUTH_RESULT"))
		define("AUTH_RESULT",LOGIN_REPLY_BAD_COMBINATION);
}

function receiveFile($id = FILE_ACTION_NONE)
{
	global $RESPONSE,$INTERNAL;
	if(isset($_POST[POST_INTERN_FILE_TYPE]) && $_POST[POST_INTERN_FILE_TYPE] == FILE_TYPE_USERFILE)
	{
		$fid = md5($_FILES["file"]["name"] . CALLER_SYSTEM_ID . time());
		$filemask = CALLER_SYSTEM_ID . "_" . $fid;
		$parentId = getServerParam("HTTP_QRD_PARENT_ID");
		$rank = getServerParam("HTTP_QRD_RANK");
		if(isnull($parentId))
		{
			createFileBaseFolders(CALLER_SYSTEM_ID,true);
			processResource(CALLER_SYSTEM_ID,CALLER_SYSTEM_ID,$INTERNAL[CALLER_SYSTEM_ID]->Fullname,0,$INTERNAL[CALLER_SYSTEM_ID]->Fullname,0,4,3);
			$parentId = CALLER_SYSTEM_ID;
			$rank = 4;
		}
		processResource(CALLER_SYSTEM_ID,$fid,$filemask,3,$_FILES["file"]["name"],0,$parentId,$rank,$_FILES["file"]["size"]);
		
		if(@move_uploaded_file($_FILES["file"]["tmp_name"], PATH_UPLOADS.$filemask))
			$id = FILE_ACTION_SUCCEEDED;
		else
			$id = FILE_ACTION_ERROR;
	}
	$RESPONSE->SetStandardResponse($id,base64_encode($fid));
}

function removeFile()
{
	global $RESPONSE;
	if(SERVERSETUP && isset($_POST[POST_INTERN_FILE_TYPE]) && $_POST[POST_INTERN_FILE_TYPE] == FILE_TYPE_ADMIN_BANNER && isset($_POST[POST_INTERN_UPLOAD_VALUE]) && strpos($_POST[POST_INTERN_UPLOAD_VALUE],"..")===false)
	{
		$files = explode(";",$_POST[POST_INTERN_UPLOAD_VALUE]);
		foreach($files as $file)
			if(strpos($file,"..")===false)
			{
				$files_to_delete = array("livezilla_".base64_decode($file)."_1.gif","livezilla_".base64_decode($file)."_0.gif","livezilla_".base64_decode($file)."_1.png","livezilla_".base64_decode($file)."_0.png");
				foreach($files_to_delete as $ftd)
					@unlink(PATH_BANNER . $ftd);
			}
	}
	else if(isset($_POST[POST_INTERN_FILE_TYPE]) && $_POST[POST_INTERN_FILE_TYPE] == FILE_TYPE_CARRIERLOGO)
		@unlink(FILE_CARRIERLOGO);
	else if(isset($_POST[POST_INTERN_FILE_TYPE]) && $_POST[POST_INTERN_FILE_TYPE] == FILE_TYPE_CARRIERHEADER)
		@unlink(FILE_CARRIERHEADER);
	$RESPONSE->SetStandardResponse(FILE_ACTION_SUCCEEDED,"");
}

function processActions()
{
	global $CONFIG;
	require(LIVEZILLA_PATH . "_lib/functions.internal.process.inc.php");
	processAcceptedConversations();
	processAuthentications();
	processStatus();
	processClosures();
	processRequests();
	processForwards();
	processWebsitePushs();
	processFilters();
	processProfile();
	processProfilePictures();
	processWebcamPictures();
	processAlerts();
	processPermissions();
	processClosedTickets();
	processExternalReloads();
	processReceivedPosts();
	processArchiveChats();
	processEvents();
	if(SERVERSETUP)
		processBannerPictures();
}

function buildSystem()
{
	global $RESPONSE,$INTERNAL,$GROUPS;
	require_once(LIVEZILLA_PATH . "_lib/functions.internal.build.inc.php");
	$INTERNAL[CALLER_SYSTEM_ID]->GetExternalObjects();
	removeChats();
	buildIntern();
	buildExtern();
	buildFilters();
	buildEvents();
	buildActions();
	if(!LOGIN && !SERVERSETUP)
	{
		buildNewPosts();
		if(!isset($_POST[POST_GLOBAL_SHOUT]))
		{
			$external = $INTERNAL[CALLER_SYSTEM_ID]->IsExternal($GROUPS);
			if($external)
			{
				buildRatings();
				buildMessages();
			}
			buildArchive($external);
			buildResources();
		}
	}
}

function listenXML($runs = 1)
{
	global $CONFIG,$RESPONSE,$INTERNAL;
	processActions();
	
	if(!SERVERSETUP && !LOGIN && $INTERNAL[CALLER_SYSTEM_ID]->Status == USER_STATUS_OFFLINE)
		return;
		
	$start = time();

	while(time() < $start + getLongPollRuntime() || $runs == 1)
	{
		if($runs > 1)
		{
			getDataSets();
			getData(true,true,true,true,true);
		}
		$RESPONSE->XML = "<listen disabled=\"".base64_encode(((getAvailability()) ?  "0" : "1" ))."\" h=\"<!--gl_all-->\" ".((isset($_POST[POST_INTERN_XMLCLIP_HASH_EXECUTION_TIME])) ? "ex_time=\"<!--execution_time-->\"" : "").">\r\n";
		$RESPONSE->Typing = "";
		if($RESPONSE->Login != null)
			$RESPONSE->XML .= $RESPONSE->Login;
			
		buildSystem();
		
		if($runs++ == 1)
			processPosts();
		
		if(($hash = substr(md5($RESPONSE->Typing),0,5)) != @$_POST[POST_INTERN_XMLCLIP_HASH_TYPING] && strlen($RESPONSE->Typing) > 0)
			$RESPONSE->XML .= "<gl_typ h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->Typing . "</gl_typ>\r\n";
		if(($hash = substr(md5($RESPONSE->Events),0,5)) != @$_POST[POST_INTERN_XMLCLIP_HASH_EVENTS])
			$RESPONSE->XML .= "<gl_ev h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->Events . "</gl_ev>\r\n";
		if(($hash = substr(md5($RESPONSE->Exceptions),0,5)) != @$_POST[POST_INTERN_XMLCLIP_HASH_ERRORS] && strlen($RESPONSE->Exceptions) > 0)
			$RESPONSE->XML .= "<gl_e h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->Exceptions . "</gl_e>\r\n";
		if(($hash = substr(md5($RESPONSE->Internals),0,5)) != @$_POST[POST_INTERN_XMLCLIP_HASH_INTERN] && strlen($RESPONSE->Internals) > 0)
			$RESPONSE->XML .= "<int_r h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->Internals . "</int_r>\r\n";
		if(($hash = substr(md5($RESPONSE->Groups),0,5)) != @$_POST[POST_INTERN_XMLCLIP_HASH_GROUPS] && strlen($RESPONSE->Groups) > 0)
			$RESPONSE->XML .= "<int_d h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->Groups . "</int_d>\r\n";
		if(($hash = substr(md5($RESPONSE->Actions),0,5)) != @$_POST[POST_INTERN_XMLCLIP_HASH_ACTIONS])
			$RESPONSE->XML .= "<int_ac h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->Actions . "</int_ac>\r\n";
		if(($hash = substr(md5($RESPONSE->InternalVcards),0,5)) != @$_POST[POST_INTERN_XMLCLIP_HASH_PROFILES])
			$RESPONSE->XML .= "<int_v h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->InternalVcards . "</int_v>\r\n";
		if(($hash = substr(md5($RESPONSE->InternalProfilePictures),0,5)) != @$_POST[POST_INTERN_XMLCLIP_HASH_PICTURES_PROFILE])
			$RESPONSE->XML .= "<int_pp h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->InternalProfilePictures . "</int_pp>\r\n";
		if(($hash = substr(md5($RESPONSE->InternalWebcamPictures),0,5)) != @$_POST[POST_INTERN_XMLCLIP_HASH_PICTURES_WEBCAM])
			$RESPONSE->XML .= "<int_wp h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->InternalWebcamPictures . "</int_wp>\r\n";
		if(($hash = substr(md5($RESPONSE->Filter),0,5)) != @$_POST[POST_INTERN_XMLCLIP_HASH_FILTERS])
			$RESPONSE->XML .= "<ext_b h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->Filter . "</ext_b>\r\n";
		if(($hash = substr(md5($RESPONSE->Tracking),0,5)) != @$_POST[POST_INTERN_XMLCLIP_HASH_TRACKING])
			$RESPONSE->XML .= "<ext_u h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->Tracking . "</ext_u>\r\n";
		if($RESPONSE->Archive != null)
			$RESPONSE->XML .= "<ext_c>\r\n" . $RESPONSE->Archive . "</ext_c>\r\n";
		if($RESPONSE->Resources != null)
			$RESPONSE->XML .= "<ext_res>\r\n" . $RESPONSE->Resources . "</ext_res>\r\n";
		if($RESPONSE->Ratings != null)
			$RESPONSE->XML .= "<ext_r>\r\n" . $RESPONSE->Ratings . "</ext_r>\r\n";
		if($RESPONSE->Messages != null)
			$RESPONSE->XML .= "<ext_m>\r\n" . $RESPONSE->Messages . "</ext_m>\r\n";
		if(strlen($RESPONSE->Authentications) > 0)
			$RESPONSE->XML .= "<gl_auths>\r\n" . $RESPONSE->Authentications . "\r\n</gl_auths>\r\n";
		if(strlen($RESPONSE->Posts)>0)
			$RESPONSE->XML .=  "<usr_p>\r\n" . $RESPONSE->Posts . "</usr_p>\r\n";
		if(isset($_POST[POST_INTERN_ACCESSTEST]))
			$RESPONSE->XML .= "<permission>" . base64_encode(getFolderPermissions()) . "</permission>";
	
		if(SERVERSETUP || LOGIN || $INTERNAL[CALLER_SYSTEM_ID]->LastActive <= @filemtime(FILE_CONFIG))
			$RESPONSE->XML .= getConfig();
			
		$RESPONSE->XML .= "</listen>";
		
		break;
		/*
		if(isset($_POST[POST_GLOBAL_SHOUT]) || substr_count($RESPONSE->XML,"<") > 4 || $INTERNAL[CALLER_SYSTEM_ID]->Status == USER_STATUS_OFFLINE || isset($_POST[POST_GLOBAL_NO_LONG_POLL]))
		{
			break;
		}
		else
		{
			if()
				break;
			$wait = max($CONFIG["poll_frequency_clients"]-1,1);
			if(time()+$wait <= $start + getLongPollRuntime())
			{
				sleep($wait);
			}
			else
				break;
		}
		*/
	}
}

function getConfig()
{
	global $CONFIG;
	$skeys = array("gl_db_host","gl_db_user","gl_db_pass","gl_db_name");
	$xml = "<gl_c h=\"".base64_encode(substr(md5file(FILE_CONFIG),0,5))."\">\r\n";
	foreach($CONFIG as $key => $val)
		if(is_array($val))
		{
			$xml .= "<conf key=\"".base64_encode($key)."\">\r\n";
			foreach($val as $skey => $sval)
				$xml .= "<sub key=\"".base64_encode($skey)."\">".base64_encode($sval)."</sub>\r\n";
			$xml .= "</conf>\r\n";
		}
		else if(!in_array($key,$skeys) || SERVERSETUP)
			$xml .= "<conf value=\"".base64_encode($val)."\" key=\"".base64_encode($key)."\" />\r\n";
		else
			$xml .= "<conf value=\"".base64_encode("")."\" key=\"".base64_encode($key)."\" />\r\n";
	
	if(SERVERSETUP)
	{
		$xml .= "<translations>\r\n";
		$files = getDirectory("./_language","index",true);
		foreach($files as $translation)
		{
			$lang = str_replace(".php","",str_replace("lang","",$translation));
			$xml .= "<language key=\"".base64_encode($lang)."\" />\r\n";
		}
		$xml .= "</translations>\r\n";
		if(@file_exists(FILE_CARRIERLOGO))
			$xml .= "<carrier_logo content=\"".fileToBase64(FILE_CARRIERLOGO)."\" />\r\n";
		if(@file_exists(FILE_CARRIERHEADER))
			$xml .= "<carrier_header content=\"".fileToBase64(FILE_CARRIERHEADER)."\" />\r\n";
		if(@file_exists(FILE_INVITATIONLOGO))
			$xml .= "<invitation_logo content=\"".fileToBase64(FILE_INVITATIONLOGO)."\" />\r\n";
	}
	$xml .= "<php_cfg_vars post_max_size=\"".base64_encode(cfgFileSizeToBytes((@get_cfg_var("post_max_size")!=NULL)?get_cfg_var("post_max_size"):MAX_POST_SIZE_SAFE_MODE))."\" upload_max_filesize=\"".base64_encode(cfgFileSizeToBytes((@get_cfg_var("upload_max_filesize")!=NULL)?get_cfg_var("upload_max_filesize"):MAX_UPLOAD_SIZE_SAFE_MODE))."\" />\r\n";
	$xml .= "</gl_c>\r\n";
	return $xml;
}

function removeChats()
{
	global $INTERNAL,$CONFIG;
	foreach($INTERNAL[CALLER_SYSTEM_ID]->ExternalChats as $chat)
	{
		if(!dataSetExists($chat->ExternalUser->SessionFile) || $chat->Closed && getDataSetTime($chat->ExternalUser->SessionFile) < (time()-$CONFIG["timeout_clients"]))
		{
			$chat->InternalDestroy();
			$chat->ExternalUser->Destroy($CONFIG);
		}
	}
}

function getFolderPermissions($message=null)
{
	$directories = Array(PATH_UPLOADS,PATH_BANNER,PATH_CONFIG,PATH_USERS,PATH_GROUPS);
	foreach($directories as $key => $dir)
	{
		$result = testDirectory($dir);
			if(!$result)
				return 0;
	}
	return 1;
}

function clearPredefinedMessages()
{
	queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_PREDEFINED."`");
}
?>
