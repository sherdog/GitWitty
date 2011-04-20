<?php
/****************************************************************************************
* LiveZilla objects.global.users.inc.php
* 
* Copyright 2010 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();
	
require(LIVEZILLA_PATH . "_lib/objects.global.inc.php");

class BaseUser
{
	var $IP;
	var $SessId;
	var $UserId;
	var $SystemId;
	var $Messages = array();
	var $Status = USER_STATUS_OFFLINE;
	var $Type;
	var $Folder;
	var $SessionFile;
	var $FirstActive;
	var $LastActive;
	var $Fullname;
	var $Company;
	var $Question;
	var $Email;
	var $Typing = false;
	var $Customs;

	function BaseUser($_userid)
   	{
		$this->UserId = $_userid;
   	}
	
	function GetPosts()
	{
		$messageFileCount = 0;
		$rows = getPosts($this->SystemId);
		$posts = array();
		foreach($rows as $row)
		{
			array_push($posts,new Post($row));
			if(++$messageFileCount >= DATA_ITEM_LOADS)
				break;
		}
		return $posts;
	}
	
	function AppendFromCookies()
	{
		if(defined("CALLER_TYPE") && CALLER_TYPE != CALLER_TYPE_INTERNAL)
		{
			if(!isnull(getCookieValue("login_112")))
				$this->Email = (getCookieValue("login_112"));
			if(!isnull(getCookieValue("login_111")))
				$this->Fullname = (getCookieValue("login_111"));
			if(!isnull(getCookieValue("login_113")))
				$this->Company = (getCookieValue("login_113"));
		}
	}
	
	function Save()
	{
		$dataProvider = new DataProvider($this->SessionFile);
		$dataProvider->Save($this->GetData());
	}
	
	function Notify()
	{
		$dataProvider = new DataProvider($this->SessionFile);
		$dataProvider->Notify();
	}
	
	function KeepAlive()
	{
		if(dataSetExists($this->SessionFile))
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_DATA."` SET `time` = '".@mysql_real_escape_string(time())."' WHERE `file` = '".@mysql_real_escape_string($this->SessionFile)."'");
		else
			$this->Save();
	}
	
	function Destroy()
	{
		unlinkDataSet($this->SessionFile);
	}
}

class UserGroup
{
	var $Id;
	var $Descriptions;
	var $DescriptionArray;
	var $Description;
	var $IsExternal;
	var $IsInternal;
	var $IsStandard;
	var $PredefinedMessages;
	var $Created;
	var $Email;
	var $ChatFunctions;
	var $VisitorFilters;
	var $ChatInputsHidden;
	var $ChatInputsMandatory;

	function UserGroup($_id, $_values = null, $_config = null)
	{
		$this->Id = $_id;
		if(!isnull($_values))
		{
			$this->Descriptions = unserialize(base64_decode($_values["gr_desc"]));
			$this->DescriptionArray = $_values["gr_desc"];
			
			if(defined("DEFAULT_BROWSER_LANGUAGE") && isset($this->Descriptions[strtoupper(DEFAULT_BROWSER_LANGUAGE)]))
				$this->Description = base64_decode($this->Descriptions[strtoupper(DEFAULT_BROWSER_LANGUAGE)]);
			else if(isset($this->Descriptions[strtoupper($_config["gl_default_language"])]))
				$this->Description = base64_decode($this->Descriptions[strtoupper($_config["gl_default_language"])]);
			else if(isset($this->Descriptions["EN"]))
				$this->Description = base64_decode($this->Descriptions["EN"]);
			else
				$this->Description =  base64_decode(current($this->Descriptions));
		
			$this->IsInternal = ($_values["gr_internal"] == 1);
			$this->IsExternal = ($_values["gr_external"] == 1);
			$this->IsStandard =  ($_values["gr_standard"] == 1);
			$this->Created = $_values["gr_created"];
			$this->Email = $_values["gr_email"];
			$this->VisitorFilters = $_values["gr_vfilters"];
			$this->ChatFunctions = Array($_values["gr_ex_sm"],$_values["gr_ex_so"],$_values["gr_ex_pr"],$_values["gr_ex_ra"],$_values["gr_ex_fv"],$_values["gr_ex_fu"]);
			$this->ChatInputsHidden = $_values["gr_ci_hidden"];
			$this->ChatInputsMandatory = $_values["gr_ci_mand"];
		}
		$this->LoadPredefinedMessages();
	}
	
	function LoadPredefinedMessages()
	{
		$this->PredefinedMessages = array();
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_PREDEFINED."` WHERE `group_id`='".@mysql_real_escape_string($this->Id)."'");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
				$this->PredefinedMessages[] = new PredefinedMessage($row);
	}
}

class Operator extends BaseUser
{
	var $Level = 0;
	var $Webspace = 0;
	var $LoginId;
	var $Password;
	var $PasswordFile;
	var $PasswordFileTXT;
	var $Description;
	var $LCAFile;
	var $Profile;
	var $ServerSetup = false;
	var $Authenticated = false;
	var $VisitorFileSizes;
	var $VisitorStaticReload;
	var $ExternalChats;
	var $PermissionSet;
	var $Groups;
	var $GroupsArray;
	var $PredefinedMessages;
	var $InExternalGroup;
	var $ProfilePicture;
	var $ProfilePictureTime;
	var $WebcamPicture;
	var $WebcamPictureTime;
	
	function Operator($_sessid,$_userid)
   	{
		$this->SessId = $this->SystemId = $_sessid;
		$this->UserId = $_userid;
		$this->ExternalChats = array();
		$this->Folder = PATH_DATA_INTERNAL . $this->SessId . "/";
		$this->PasswordFile = PATH_USERS . $this->SessId . FILE_EXTENSION_PASSWORD;
		$this->PasswordFileTXT = PATH_USERS . $this->SessId . FILE_EXTENSION_PASSWORD_TXT;
		$this->ChangePasswordFile = PATH_USERS . $this->SessId . FILE_EXTENSION_CHANGE_PASSWORD;
		$this->Type = USER_TYPE_INTERN;
		$this->SessionFile = $this->Folder . $this->SessId . "." . EX_INTERN_SESSION;
		$this->LCAFile = PATH_DATA_INTERNAL . $this->SessId . FILE_EXTENSION_LAST_CHAT_ALLOCATION;
		$this->VisitorFileSizes = array();
		$this->VisitorStaticReload = array();
		$this->LoadPredefinedMessages();
   	}
	
	function Load()
	{
		$dataProvider = new DataProvider($this->SessionFile);
		$dataProvider->Load();
		$this->LoginId = $dataProvider->Result["s_login_id"];
		$this->FirstActive = $dataProvider->Result["s_first_active"];
		$this->Password = $dataProvider->Result["s_password"];
		$this->Status = $dataProvider->Result["s_status"];
		$this->Level = $dataProvider->Result["s_level"];
		$this->IP = $dataProvider->Result["s_ip"];
		$this->Typing = $dataProvider->Result["s_typing"];
		$this->VisitorFileSizes = $dataProvider->Result["s_vi_file_sizes"];
		$this->LastActive = getDataSetTime($this->SessionFile);
	}
	
	function GetData()
	{
		$data = Array();
		$data["s_login_id"] = $this->LoginId;
		$data["s_first_active"] = $this->FirstActive;
		$data["s_password"] = $this->Password;
		$data["s_status"] = $this->Status;
		$data["s_level"] = $this->Level;
		$data["s_ip"] = $this->IP;
		$data["s_typing"] = $this->Typing;
		$data["s_vi_file_sizes"] = $this->VisitorFileSizes;
		return $data;
	}
	
	function GetLastChatAllocation()
	{
		$dataProvider = new DataProvider($this->LCAFile);
		$dataProvider->Load();
		if($dataProvider->Result != null && isset($dataProvider->Result["s_lca"]))
			return $dataProvider->Result["s_lca"];
		else
			return 0;
	}
	
	function SetLastChatAllocation()
	{
		$dataProvider = new DataProvider($this->LCAFile);
		$dataProvider->Save(Array("s_lca"=>time()),true);
	}

	function GetExternalObjects()
	{
		$actionfiles = getDirectory($this->Folder,false);
		sort($actionfiles);
		$chat_hash = "";
		foreach($actionfiles as $index => $file)
		{
			if(strpos($file, "." . EX_CHAT_OPEN) !== false)
			{
				$chat = new Chat($this->Folder . $file);
				$chat->IsActivated(null);
				$this->ExternalChats[$chat->ExternalUser->SystemId] = $chat;
			}
			else if(strpos($file, "." . EX_FILE_UPLOAD_REQUEST) !== false)
			{
				$request = new FileUploadRequest(str_replace(".".EX_FILE_UPLOAD_REQUEST,"",$file),$this->SessId);
				$request->Load();
				
				$rsid = $request->SenderUserId . "~" . $request->SenderBrowserId;
				if(isset($this->ExternalChats[$rsid]))
				{
					if($this->ExternalChats[$rsid]->Activated != CHAT_STATUS_ACTIVE)
					{
						$request->Destroy();
					}
					else
					{
						$this->ExternalChats[$rsid]->FileUploadRequest = $request;
					}
				}
				else
				{
					$request->Destroy();
				}
			}
		}
	}
	
	function IsExternal($_groupList, $_exclude=null, $_include=null)
	{
		foreach($this->Groups as $groupid)
			if($_groupList[$groupid]->IsExternal && !(!isnull($_exclude) && in_array($groupid,$_exclude)) && !(!isnull($_include) && !in_array($groupid,$_include)))
				return $this->InExternalGroup=true;
		return $this->InExternalGroup=false;
	}
	
	function GetExternalChatAmount($amount=0)
	{
		$actionfiles = getDirectory($this->Folder,false);
		foreach($actionfiles as $index => $file)
		{
			if(strpos($file, "." . EX_CHAT_OPEN) !== false)
				$amount++;
		}
		return $amount;
	}
	
	function LoadPredefinedMessages()
	{
		$this->PredefinedMessages = array();
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_PREDEFINED."` WHERE `internal_id`='".@mysql_real_escape_string($this->SystemId)."'");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
				$this->PredefinedMessages[] = new PredefinedMessage($row);
	}
	
	function LoadProfile()
	{
		$this->Profile = null;
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_PROFILES."` WHERE `id`='".@mysql_real_escape_string($this->SystemId)."'");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
				$this->Profile = new Profile($row);
	}
	
	function LoadPictures($_sessiontime=0)
	{
		$found = false;
		$result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_PROFILE_PICTURES."` WHERE `internal_id`='".@mysql_real_escape_string($this->SystemId)."' AND `time` >= ".@mysql_real_escape_string($_sessiontime));
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				$found = true;
				if(isnull($row["webcam"]))
				{
					$this->ProfilePicture = $row["data"];
					$this->ProfilePictureTime = $row["time"];
				}
				else
				{
					$this->WebcamPicture = $row["data"];
					$this->WebcamPictureTime = $row["time"];
				}
			}
		return $found;
	}

	function SaveLoginAttempt($_password)
	{
		$result = queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_LOGINS."` WHERE ip='".@mysql_real_escape_string(@$_SERVER["REMOTE_ADDR"])."' AND `user_id`='".@mysql_real_escape_string($this->UserId)."' AND `time` > '".@mysql_real_escape_string(time()-86400)."';");
		if(@mysql_num_rows($result) >= MAX_LOGIN_ATTEMPTS)
			return false;
		
		$result = queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_LOGINS."` WHERE ip='".@mysql_real_escape_string(@$_SERVER["REMOTE_ADDR"])."' AND `user_id`='".@mysql_real_escape_string($this->UserId)."' AND `time` > '".@mysql_real_escape_string(time()-86400)."' AND `password`='".@mysql_real_escape_string($_password)."';");
		if(@mysql_num_rows($result) == 0)
			queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_LOGINS."` (`id` ,`user_id` ,`ip` ,`time` ,`password`) VALUES ('".@mysql_real_escape_string(getId(32))."', '".@mysql_real_escape_string($this->UserId)."', '".@mysql_real_escape_string(@$_SERVER["REMOTE_ADDR"])."', '".@mysql_real_escape_string(time())."', '".@mysql_real_escape_string($_password)."');");
		return true;
	}
	
	function DeleteLoginAttempts()
	{
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_LOGINS."` WHERE ip='".@mysql_real_escape_string(@$_SERVER["REMOTE_ADDR"])."' AND `user_id`='".@mysql_real_escape_string($this->UserId)."';");
	}
	
	function LoadPassword()
	{
		$this->Password = null;
		if(@file_exists($this->PasswordFile))
		{
			require($this->PasswordFile);
			$this->Password = $passwd;
		}
		else if(@file_exists($this->PasswordFileTXT))
		{
			$data = getFile($this->PasswordFileTXT);
			$this->Password = $data;
		}
		return $this->Password;
	}
	
	function ChangePassword($_password)
	{
		createFile($this->PasswordFile,"<?php \$passwd=\"".md5($_password)."\"; ?>",true);
		if(@file_exists($this->ChangePasswordFile))
			@unlink($this->ChangePasswordFile);
		if(@file_exists($this->PasswordFileTXT))
			@unlink($this->PasswordFileTXT);
	}
	
	function IsPasswordChangeNeeded()
	{
		return @file_exists($this->ChangePasswordFile);
	}
	
	function SetPasswordChangeNeeded($_needed)
	{
		if($_needed)
			createFile($this->ChangePasswordFile,"",false);
		else if(@file_exists($this->ChangePasswordFile))
			@unlink($this->ChangePasswordFile);
	}
	
	function GetPermission($_type)
	{
		return substr($this->PermissionSet,$_type,1);
	}
	
	function GetOperatorPictureFile()
	{
		return "picture.php?intid=".base64UrlEncode($this->UserId)."&acid=".getId(3);
	}

	function GetLoginReply($_extern,$_time)
	{
		return "<login>\r\n<login_return group=\"".base64_encode($this->GroupsArray)."\" name=\"".base64_encode($this->Fullname)."\" loginid=\"".base64_encode($this->LoginId)."\" level=\"".base64_encode($this->Level)."\" sess=\"".base64_encode($this->SystemId)."\" extern=\"".base64_encode($_extern)."\" timediff=\"".base64_encode($_time)."\" perms=\"".base64_encode($this->PermissionSet)."\" sm=\"".base64_encode(SAFE_MODE)."\" phpv=\"".base64_encode(phpversion())."\" /></login>";
	}
}

class UserExternal extends BaseUser
{
	var $Browsers;
	var $ExternalStatic;
	var $Response;
	var $IsChat = false;
	var $ActiveChatRequest;
	
	function UserExternal($_userid)
   	{
		$this->Browsers = Array();
		$this->UserId = $_userid;
		$this->Folder = PATH_DATA_EXTERNAL . $this->UserId . "/";
   	}
	
	function SaveTicket($_group,$_config)
	{
		$ticket = new UserTicket(getTicketId(),true);
		$ticket->IP = getIP();
		
		setCookieValue("login_111",AJAXDecode($_POST[POST_EXTERN_USER_NAME]));
		setCookieValue("login_112",AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]));
		setCookieValue("login_113",AJAXDecode($_POST[POST_EXTERN_USER_COMPANY]));

		if(!isTicketFlood())
		{
			$ticket->Fullname = AJAXDecode($_POST[POST_EXTERN_USER_NAME]);
			$ticket->UserId = AJAXDecode($_POST[POST_EXTERN_USER_USERID]);
			$ticket->Email = AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]);
			$ticket->Group = $_group;
			$ticket->Company = AJAXDecode($_POST[POST_EXTERN_USER_COMPANY]);
			$ticket->Text = AJAXDecode($_POST[POST_EXTERN_USER_MAIL]);
			if(!(!isnull($_config["gl_rm_om"]) && $_config["gl_rm_om_time"] == 0))
				saveTicket($ticket);
			$this->AddFunctionCall("lz_chat_mail_callback(true);",false);
			return true;
		}
		else
			$this->AddFunctionCall("lz_chat_mail_callback(false);",false);
		return false;
	}
	
	function SendCopyOfMail($_group,$_config,$_groups)
	{
		$message = getFile(TEMPLATE_EMAIL_MAIL);
		if(isnull($_config["gl_pr_nbl"]))
			$message .= base64_decode("DQoNCg0KcG93ZXJlZCBieSBMaXZlWmlsbGEgTGl2ZSBTdXBwb3J0IFtodHRwOi8vd3d3LmxpdmV6aWxsYS5uZXRd");
		$message = str_replace("<!--date-->",date("r"),$message);
		$message = str_replace("<!--name-->",AJAXDecode($_POST[POST_EXTERN_USER_NAME]),$message);
		$message = str_replace("<!--email-->",AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]),$message);
		$message = str_replace("<!--company-->",AJAXDecode($_POST[POST_EXTERN_USER_COMPANY]),$message);
		$message = str_replace("<!--mail-->",AJAXDecode($_POST[POST_EXTERN_USER_MAIL]),$message);
		$message = str_replace("<!--group-->",$_groups[$_group]->Description,$message);
		$sender = (!isnull($_config["gl_usmasend"]) && isValidEmail(AJAXDecode($_POST[POST_EXTERN_USER_EMAIL])) && isnull($_config["gl_smtpauth"])) ? AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]) : $_config["gl_mail_sender"];
		if(!isnull($_config["gl_scom"]))
			sendMail($_config["gl_scom"],$sender,AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]),$message,getSubject(false,AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]),AJAXDecode($_POST[POST_EXTERN_USER_NAME]),$_group,""));
		if(!isnull($_config["gl_sgom"]))
			sendMail($_groups[$_group]->Email,$sender,AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]),$message,getSubject(false,AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]),AJAXDecode($_POST[POST_EXTERN_USER_NAME]),$_group,""));
		if(!isnull($_config["gl_ssom"]) && isValidEmail(AJAXDecode($_POST[POST_EXTERN_USER_EMAIL])))
			sendMail(AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]),$sender,AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]),$message,getSubject(false,AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]),AJAXDecode($_POST[POST_EXTERN_USER_NAME]),$_group,""));
	}
	
	function StoreFile($_browserId,$_partner,$_fullname)
	{
		$filename = namebase($_FILES['userfile']['name']);

		if(!isValidUploadFile($filename))
			return false;

		$fileid = md5($filename . $this->UserId . $_browserId);
		$fileurid = EX_FILE_UPLOAD_REQUEST . "_" . $fileid;
		$filemask = $this->UserId . "_" . $fileid;
		$request = new FileUploadRequest($fileurid,$_partner);
		$request->Load();

		if($request->Permission == PERMISSION_FULL)
		{
			if(move_uploaded_file($_FILES["userfile"]["tmp_name"], PATH_UPLOADS . $request->FileMask))
			{
				createFileBaseFolders($_partner,false);
				processResource($_partner,$this->UserId,$_fullname,0,$_fullname,0,5,3);
				processResource($_partner,$fileid,$filemask,4,$_FILES["userfile"]["name"],0,$this->UserId,4,$_FILES["userfile"]["size"]);
				
				$request->Download = true;
				$request->Save();
				return true;
			}
			else
			{
				$request->Error = true;
				$request->Save();
			}
		}
		return false;
	}
	
	function SaveRate($_internalId,$_config)
	{
		$rate = new Rating(time() . "_" . getIP());
		if(!$rate->IsFlood())
		{
			$rate->RateComment = AJAXDecode($_POST[POST_EXTERN_RATE_COMMENT]);
			$rate->RatePoliteness = AJAXDecode($_POST[POST_EXTERN_RATE_POLITENESS]);
			$rate->RateQualification = AJAXDecode($_POST[POST_EXTERN_RATE_QUALIFICATION]);
			$rate->Fullname = AJAXDecode($_POST[POST_EXTERN_USER_NAME]);
			$rate->Email = AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]);
			$rate->Company = AJAXDecode($_POST[POST_EXTERN_USER_COMPANY]);
			$rate->UserId = AJAXDecode($_POST[POST_EXTERN_USER_USERID]);
			$rate->InternId = $_internalId;
			if(!(!isnull($_config["gl_rm_rt"]) && $_config["gl_rm_rt_time"] == 0))
				saveRating($rate);
			$this->AddFunctionCall("lz_chat_send_rate_callback(true);",false);
		}
		else
			$this->AddFunctionCall("lz_chat_send_rate_callback(false);",false);
	}
	
	function AddFunctionCall($_call,$_overwrite)
	{
		if(isnull($this->Response))
			$this->Response = "";
		if($_overwrite)
			$this->Response = $_call;
		else
			$this->Response .= $_call;
	}
	
	function LoadStaticInformation()
	{
		$this->ExternalStatic = new ExternalStatic($this->UserId);
	}
	
	function IsInChat()
	{
		$result = queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_ROOMS."` WHERE `creator`='".@mysql_real_escape_string($this->UserId)."' LIMIT 1");
		return (@mysql_num_rows($result) > 0);
	}

	function WasInChat()
	{
		$result = queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_CHATS."` WHERE `external_id` = '".@mysql_real_escape_string($this->UserId)."' LIMIT 1");
		if(@mysql_num_rows($result) > 0)
			return true;
		else
		{
			$result = queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_ROOMS."` WHERE `creator` = '".@mysql_real_escape_string($this->UserId)."' LIMIT 1");
			return (@mysql_num_rows($result) > 0);
		}
	}
}

class ExternalBrowser extends BaseUser
{
	var $BrowserId;
	var $Referrer;
	var $History;
	var $ChatRequest;
	var $WebsitePush;
	var $Alert;
	var $HasAcceptedChatRequest;
	var $HasDeclinedChatRequest;
	var $Type = BROWSER_TYPE_BROWSER;
	
	function ExternalBrowser($_browserid,$_userid)
   	{
		$this->BrowserId = $_browserid;
		$this->UserId = $_userid;
		$this->SystemId = $this->UserId . "~" . $this->BrowserId;
		$this->Folder = PATH_DATA_EXTERNAL . $this->UserId . "/b/" . $this->BrowserId . "/";
		$this->SessionFile = $this->Folder . $this->BrowserId . "." . EX_BROWSER_SESSION;
   	}
	
	function Load()
	{
		$this->AppendFromCookies();
		$dataProvider = new DataProvider($this->SessionFile);
		$dataProvider->Load();
		$this->FirstActive = $dataProvider->Result["s_first_active"];
		$this->History = $dataProvider->Result["s_history"];
		$this->Referrer = $dataProvider->Result["s_referrer"];
		
		if(isset($dataProvider->Result["s_fullname"]))
			$this->Fullname = $dataProvider->Result["s_fullname"];
			
		if(isset($dataProvider->Result["s_email"]))
			$this->Email = $dataProvider->Result["s_email"];
		
		if(isset($dataProvider->Result["s_company"]))
			$this->Company = $dataProvider->Result["s_company"];
			
		if(isset($dataProvider->Result["s_customs"]))
			$this->Customs = unserialize($dataProvider->Result["s_customs"]);
	}
	
	function LoadChatRequest()
	{
		$count = 0;
		if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` WHERE `receiver_user_id`='".@mysql_real_escape_string($this->UserId)."' AND `receiver_browser_id`='".@mysql_real_escape_string($this->BrowserId)."' ORDER BY `accepted` ASC,`declined` ASC,`created` ASC;"))
		{
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				if(!isnull($row["declined"]))
					$this->HasDeclinedChatRequest = true;
				if(!isnull($row["accepted"]))
					$this->HasAcceptedChatRequest = true;

				$count++;
				if($count == count($result))
					$this->ChatRequest = new ChatRequest($row);
			}
		}
	}
	
	function LoadAlerts()
	{
		if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_ALERTS."` WHERE `receiver_user_id`='".@mysql_real_escape_string($this->UserId)."' AND `receiver_browser_id`='".@mysql_real_escape_string($this->BrowserId)."' ORDER BY `accepted` ASC;"))
			if($row = mysql_fetch_array($result, MYSQL_BOTH))
				$this->Alert = new Alert($row);
	}
	
	function LoadWebsitePush()
	{
		if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_WEBSITE_PUSHS."` WHERE `receiver_user_id`='".@mysql_real_escape_string($this->UserId)."' AND `receiver_browser_id`='".@mysql_real_escape_string($this->BrowserId)."' ORDER BY `displayed` ASC,`accepted` ASC,`declined` ASC,`created` ASC LIMIT 1;"))
			if($row = mysql_fetch_array($result, MYSQL_BOTH))
				$this->WebsitePush = new WebsitePush($row);
	}
	
	function GetData()
	{
		$data = Array();
		$data["s_first_active"] = $this->FirstActive;
		$data["s_history"] = $this->History;
		$data["s_referrer"] = $this->Referrer;
		if(!isnull($this->Fullname))
			$data["s_fullname"] = $this->Fullname;
		if(!isnull($this->Email))
			$data["s_email"] = $this->Email;
		if(!isnull($this->Company))
			$data["s_company"] = $this->Company;
		if(count($this->Customs)>0)
			$data["s_customs"] = serialize($this->Customs);
		return $data;
	}
	
	function Destroy()
	{
		deleteDirectory($this->Folder);
		foreach(array(DATABASE_CHAT_REQUESTS,DATABASE_ALERTS,DATABASE_WEBSITE_PUSHS,DATABASE_EVENT_TRIGGERS) as $table)
			queryDB(true,"DELETE FROM `".DB_PREFIX.$table."` WHERE `receiver_browser_id`= '" . @mysql_real_escape_string($this->BrowserId) . "' AND `receiver_user_id`='" . @mysql_real_escape_string($this->UserId) . "';");
	}
}

class ExternalChat extends ExternalBrowser
{
	var $DesiredChatGroup;
	var $DesiredChatPartner;
	var $DesiredChatPartnerTyping = false;
	var $Forward;
	var $Waiting;
	var $Chat;
	var $Code = "CHAT";
	var $Type = BROWSER_TYPE_CHAT;
	var $ConnectingMessageDisplayed = null;
	var $ChatRequestReceiptants;

	function ExternalChat($_userid,$_browserId)
   	{
		$this->UserId = $_userid;
		$this->BrowserId = $_browserId;
		$this->SystemId = $this->UserId . "~" . $this->BrowserId;
		$this->Folder = PATH_DATA_EXTERNAL . $this->UserId . "/b/" . $this->BrowserId . "/";
		$this->SessionFile = $this->Folder . $this->BrowserId . "." . EX_CHAT_SESSION;
   	}
	
	function GetData()
	{
		$data = Array();
		$data["s_typing"] = $this->Typing;
		
		if(!isnull($this->Fullname))
			$data["s_fullname"] = $this->Fullname;
			
		$data["s_email"] = $this->Email;
		$data["s_company"] = $this->Company;
		$data["s_waiting"] = $this->Waiting;
		$data["s_code"] = $this->Code;
		$data["s_first_active"] = (!isnull($this->FirstActive)) ? $this->FirstActive : time();
		$data["s_internal"] = $this->DesiredChatPartner;
		$data["s_group"] = $this->DesiredChatGroup;
		$data["s_question"] = $this->Question;
		
		if(count($this->Customs)>0)
			$data["s_customs"] = serialize($this->Customs);
		return $data;
	}
	
	function SetCookieGroup()
	{
		setCookieValue("login_group",$this->DesiredChatGroup);
	}
	
	function RequestFileUpload($_user,$_filename)
	{
		$fileid = md5(namebase($_filename) . $this->UserId . $this->BrowserId);
		$filemask = $this->UserId . "_" . $fileid;
		$fileurid = EX_FILE_UPLOAD_REQUEST . "_" . $fileid;
		$request = new FileUploadRequest($fileurid,$this->DesiredChatPartner);
		$request->SenderUserId = $this->UserId;
		$request->FileName = namebase($_filename);
		$request->FileMask = $filemask;
		$request->FileId = $fileid;
		$request->SenderBrowserId = $this->BrowserId;
		if(dataSetExists($request->TargetFile))
		{
			$request->Load();
			if($request->Permission == PERMISSION_FULL)
			{
				$_user->AddFunctionCall("top.lz_chat_file_start_upload('".$_filename."');",false);
			}
			else if($request->Permission == PERMISSION_NONE)
			{
				$_user->AddFunctionCall("top.lz_chat_file_stop();",false);
				$_user->AddFunctionCall("top.lz_chat_file_error(1);",false);
				$request->Destroy();
			}
		}
		else if(!dataSetExists($request->TargetFile))
		{
			if(!isValidUploadFile($_filename))
				$_user->AddFunctionCall("top.lz_chat_file_error(2);",false);
			else
				$request->Save();
		}
		return $_user;
	}
	
	function AbortFileUpload($_user,$_filename,$_error)
	{
		$fileid = substr(md5(namebase($_filename)),0,5);
		$request = new FileUploadRequest($this->BrowserId . "_" . $fileid,$this->DesiredChatPartner);
		if(dataSetExists($request->TargetFile))
		{
			$request->Load();
			$request->Error = $_error;
			$request->Save();
		}
	}
	
	function Load()
	{
		$this->AppendFromCookies();
		$dataProvider = new DataProvider($this->SessionFile);
		$dataProvider->Load();

		if(isset($dataProvider->Result["s_fullname"]))
			$this->Fullname = $dataProvider->Result["s_fullname"];
		
		$this->Email = $dataProvider->Result["s_email"];
		$this->Company = $dataProvider->Result["s_company"];
		$this->Waiting = $dataProvider->Result["s_waiting"];
		$this->FirstActive = $dataProvider->Result["s_first_active"];
		$this->Typing = $dataProvider->Result["s_typing"];
		$this->Code = $dataProvider->Result["s_code"];
		$this->DesiredChatPartner = $dataProvider->Result["s_internal"];
		$this->DesiredChatGroup = $dataProvider->Result["s_group"];
		$this->Question = $dataProvider->Result["s_question"];
		
		if(isset($dataProvider->Result["s_customs"]))
			$this->Customs = unserialize($dataProvider->Result["s_customs"]);
	}
	
	function LoadChat($_config,$_internal)
	{
		$declined = true;
		$this->Chat = null;
		$this->ChatRequestReceiptants = array();
		$chatfiles = getDirectory($this->Folder,false);
		foreach($chatfiles as $chatfile)
			if(strpos($chatfile, "." . EX_CHAT_OPEN) !== false)
			{
				if(strpos($chatfile, "." . EX_CHAT_OPEN) !== false  && ($_config["gl_alloc_mode"] == ALLOCATION_MODE_ALL || isnull($this->Chat)))
				{
					$partnerid = str_replace("." . EX_CHAT_OPEN,"",$chatfile);
					$chat = new Chat($this->Folder . $chatfile);
					$activated = $chat->IsActivated($partnerid);
					
					if(!$chat->Declined)
					{
						$declined = false;
						if(!$activated)
							$this->ChatRequestReceiptants[] = $partnerid;
					}
					if(($activated || isnull($this->Chat)) || (CALLER_TYPE != CALLER_TYPE_EXTERNAL && !isnull($this->Chat) && ($partnerid == $_internal->SystemId && !$chat->IsActivated(null))))
					{
						if(CALLER_TYPE != CALLER_TYPE_EXTERNAL && $chat->Declined)
							continue;
						$_internal = new Operator($partnerid,null);
						$_internal->Load();
						$this->DesiredChatPartnerTyping = ($_internal->Typing == $this->SystemId);
						if(isnull($this->Chat) || !($_internal->Status == CHAT_STATUS_ACTIVE || $_internal->LastActive < (time()-$_config["timeout_clients"])))
							$this->Chat = $chat;
					}
				}
				else
				{
					unlinkDataSet($this->Folder . $chatfile);
				}
			}
			
		if(!isnull($this->Chat))
			$this->Chat->Declined = $declined;
	}
	
	function GetForwards()
	{
		$this->Forward = null;
		$actionfiles = getDirectory($this->Folder,false);
		sort($actionfiles);
		foreach($actionfiles as $index => $file)
		{
			if(strpos($file,EX_CHAT_FORWARDING) !== false)
			{
				if(isnull($this->Forward))
				{
					$this->Forward = new Forward($this->UserId,$this->BrowserId,str_replace("." . EX_CHAT_FORWARDING,"",$file));
					$this->Forward->Load();
				}
			}
		}
	}
	
	function CreateChat($_internalUser,$_chatId)
	{
		$_internalUser->SetLastChatAllocation();
		$this->Chat = new Chat($this,$_internalUser,$_chatId);
		$this->Chat->InternalDestroy(false);
		$this->Chat->ExternalDestroy(false);
		$this->Chat->Save();
		updateRoom($_chatId,CHAT_STATUS_WAITING,$this->UserId);
	}
	
	function GetLastInvitationSender()
	{
		$result = queryDB(true,"SELECT `sender_system_id` FROM `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` WHERE `receiver_user_id`='".@mysql_real_escape_string($this->UserId)."' ORDER BY `created` DESC LIMIT 1");
		if($result)
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
				return $row["sender_system_id"];
		return null;
	}
	
	function DestroyChatFiles()
	{
		if(!isnull($this->Chat))
			unregisterChat($this->Chat->Id);
		$chatfiles = getDirectory($this->Folder,false);
		foreach($chatfiles as $chatfile)
			if(strpos($chatfile, "." . EX_CHAT_OPEN) !== false || strpos($chatfile, "." . EX_CHAT_ACTIVE) !== false || strpos($chatfile, "." . EX_CHAT_INTERN_CLOSED) != false || strpos($chatfile, "." . EX_CHAT_INTERN_DECLINED) != false)
				unlinkDataSet($this->Folder . $chatfile);
	}
	
	function Destroy()
	{
		deleteDirectory($this->Folder);
		if(!isnull($this->Chat))
			unregisterChat($this->Chat->Id);
	}
}

class ExternalStatic extends BaseUser
{
	var $SystemInfo;
	var $Language;
	var $Resolution;
	var $Host;
	var $Email;
	var $Company;
	var $Visits = 1;
	var $GeoCity;
	var $GeoCountryISO2;
	var $GeoRegion;
	var $GeoLongitude= -522;
	var $GeoLatitude= -522;
	var $GeoTimezoneOffset = "+00:00";
	var $GeoISP;
	var $GeoResultId = 0;
	
	function ExternalStatic($_userid)
   	{
		$this->UserId = $_userid;
		$this->Folder = PATH_DATA_EXTERNAL . $this->UserId . "/";
		$this->SessionFile = $this->Folder . $this->UserId . "." . EX_STATIC_INFO;
   	}
	
	function Load()
	{
		$dataProvider = new DataProvider($this->SessionFile);
		$dataProvider->Load();

		$this->IP = $dataProvider->Result["s_ip"];
		$this->SystemInfo = $dataProvider->Result["s_system"];
		$this->Language = $dataProvider->Result["s_language"];
		$this->Resolution = $dataProvider->Result["s_resolution"];
		$this->Host = $dataProvider->Result["s_host"];
		
		if(isset($dataProvider->Result["s_geotz"]))
			$this->GeoTimezoneOffset = $dataProvider->Result["s_geotz"];
		if(isset($dataProvider->Result["s_geolong"]))
			$this->GeoLongitude = $dataProvider->Result["s_geolong"];
		if(isset($dataProvider->Result["s_geolat"]))
			$this->GeoLatitude = $dataProvider->Result["s_geolat"];
		if(isset($dataProvider->Result["s_geocity"]))
			$this->GeoCity = $dataProvider->Result["s_geocity"];
		if(isset($dataProvider->Result["s_geocountry"]))
			$this->GeoCountryISO2 = $dataProvider->Result["s_geocountry"];
		if(isset($dataProvider->Result["s_georegion"]))
			$this->GeoRegion = $dataProvider->Result["s_georegion"];
		if(isset($dataProvider->Result["s_visits"]))
			$this->Visits =	$dataProvider->Result["s_visits"];
		if(isset($dataProvider->Result["s_georid"]))
			$this->GeoResultId = $dataProvider->Result["s_georid"];
		if(isset($dataProvider->Result["s_geoisp"]))
			$this->GeoISP = $dataProvider->Result["s_geoisp"];
		
	}
	
	function GetData()
	{
		$data = Array();
		$data["s_ip"] = $this->IP;
		$data["s_system"] = $this->SystemInfo;
		$data["s_language"] = $this->Language;
		$data["s_resolution"] = $this->Resolution;
		$data["s_geotz"] = $this->GeoTimezoneOffset;
		if(is_numeric($this->GeoLongitude))
			$data["s_geolong"] = $this->GeoLongitude;
		if(is_numeric($this->GeoLatitude))
			$data["s_geolat"] = $this->GeoLatitude;
		$data["s_geocity"] = $this->GeoCity;
		$data["s_geocountry"] = $this->GeoCountryISO2;
		$data["s_georegion"] = $this->GeoRegion;
		$data["s_visits"] = $this->Visits;
		$data["s_host"] = $this->Host;
		$data["s_georid"] = $this->GeoResultId;
		$data["s_geoisp"] = $this->GeoISP;
		return $data;
	}
	
	function GetLanguageISOTwoLetter()
	{
		if(!isnull($this->GeoCountryISO2))
		{
			return strtoupper($this->GeoCountryISO2);
		}
		if(!isnull($this->Language))
		{
			if(strpos($this->Language,"-") !== false && strlen($this->Language) >= 3)
				return strtoupper(substr($this->Language,0,2));
			else
				return strtoupper($this->Language);
		}
		return "";
	}
}
?>