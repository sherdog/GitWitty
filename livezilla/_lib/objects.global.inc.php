<?php
/****************************************************************************************
* LiveZilla objects.global.inc.php
* 
* Copyright 2010 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();
	
class BaseObject
{
	var $Id;
	var $Created;
	var $Edited;
	var $Creator;
	var $Editor;
}

class Action
{
	var $Id;
	var $Folder;
	var $ReceiverUserId;
	var $ReceiverBrowserId;
	var $SenderSystemId;
	var $SenderUserId;
	var $SenderGroupId;
	var $Text;
	var $BrowserId;
	var $Status;
	var $TargetFile;
	var $Extension;
	var $Created;
	var $Displayed;
	var $Accepted;
	var $Declined;
	var $EventActionId = "";
	
	function Save()
	{
		$dataProvider = new DataProvider($this->TargetFile);
		$dataProvider->Save($this->GetData());
		return true;
	}
	
	function Destroy()
	{
		unlinkDataSet($this->TargetFile);
	}
}

class Post extends BaseObject
{
	var $Receiver;
	var $ReceiverGroup;
	var $Sender;
	var $Persistent = false;
	var $ChatId;
	
	function Post()
   	{
		if(func_num_args() == 1)
		{
			$row = func_get_arg(0);
			$this->Id = $row["id"];
			$this->Sender = $row["sender"];
			$this->Receiver = $row["receiver"];
			$this->ReceiverGroup = $row["receiver_group"];
			$this->Text = $row["text"];
			$this->Created = $row["time"];
			$this->ChatId = $row["chat_id"];
		}
		else
		{
			$this->Id = func_get_arg(0);
			$this->Sender = func_get_arg(1);
			$this->Receiver = func_get_arg(2);
			$this->Text = func_get_arg(3);
			$this->Created = func_get_arg(4);
			$this->ChatId = func_get_arg(5);
		}
   	}
	
	function GetXml()
	{
		$receiver = (!isnull($this->ReceiverGroup)) ? $this->ReceiverGroup : $this->Receiver;
		return "<val id=\"".base64_encode($this->Id)."\" sen=\"".base64_encode($this->Sender)."\" rec=\"".base64_encode($receiver)."\" date=\"".base64_encode($this->Created)."\">".base64_encode($this->Text)."</val>\r\n";
	}
	
	function GetCommand()
	{
		return "lz_chat_add_internal_text(\"".base64_encode($this->Text)."\" ,\"".base64_encode($this->Id)."\");";
	}
	
	function Save()
	{
		queryDB(false,"INSERT INTO `".DB_PREFIX.DATABASE_POSTS."` (`id`,`chat_id`,`time`,`micro`,`sender`,`receiver`,`receiver_group`,`text`,`received`,`persistent`) VALUES ('".@mysql_real_escape_string($this->Id)."','".@mysql_real_escape_string($this->ChatId)."',".@mysql_real_escape_string($this->Created).",".@mysql_real_escape_string(mTime()).",'".@mysql_real_escape_string($this->Sender)."','".@mysql_real_escape_string($this->Receiver)."','".@mysql_real_escape_string($this->ReceiverGroup)."','".@mysql_real_escape_string($this->Text)."','0','".@mysql_real_escape_string(parseBool($this->Persistent,false))."');");
	}
}

class FilterList
{
	var $Filters;
	var $Message;
	
	function FilterList()
   	{
		$this->Filters = Array();
		$this->Populate();
   	}
	
	function Populate()
	{
		$filters = getDirectory(PATH_FILTER,false,true);
		foreach($filters as $file)
		{
			if(strpos($file,FILE_EXTENSION_FILTER) !== false)
			{
				$filter = new Filter(str_replace(FILE_EXTENSION_FILTER,"",$file));
				$filter->Load();
				
				$this->Filters[$filter->FilterId] = $filter;
			}
		}
	}
	
	function Match($_ip,$_languages,$_userid)
	{
		foreach($this->Filters as $filterid => $filter)
		{
			if($filter->Activestate == FILTER_TYPE_INACTIVE)
				continue;
			
			$this->Message = $filter->Reason;
			$compare["match_ip"] = $this->IpCompare($_ip,$filter->IP);
			$compare["match_lang"] = $this->LangCompare($_languages,$filter->Languages);
			$compare["match_id"] = ($filter->Userid == $_userid);
			if($compare["match_ip"] && $filter->Exertion == FILTER_EXERTION_BLACK && $filter->Activeipaddress == FILTER_TYPE_ACTIVE)
				define("ACTIVE_FILTER_ID",$filter->FilterId);
			else if(!$compare["match_ip"] && $filter->Exertion == FILTER_EXERTION_WHITE && $filter->Activeipaddress == FILTER_TYPE_ACTIVE)
				define("ACTIVE_FILTER_ID",$filter->FilterId);
			else if($compare["match_lang"] && $filter->Exertion == FILTER_EXERTION_BLACK && $filter->Activelanguage == FILTER_TYPE_ACTIVE)
				define("ACTIVE_FILTER_ID",$filter->FilterId);
			else if(!$compare["match_lang"] && $filter->Exertion == FILTER_EXERTION_WHITE && $filter->Activelanguage == FILTER_TYPE_ACTIVE)
				define("ACTIVE_FILTER_ID",$filter->FilterId);
			else if($compare["match_id"] && $filter->Exertion == FILTER_EXERTION_BLACK && $filter->Activeuserid == FILTER_TYPE_ACTIVE)
				define("ACTIVE_FILTER_ID",$filter->FilterId);
			else if(!$compare["match_id"] && $filter->Exertion == FILTER_EXERTION_WHITE && $filter->Activeuserid == FILTER_TYPE_ACTIVE)
				define("ACTIVE_FILTER_ID",$filter->FilterId);
			if(defined("ACTIVE_FILTER_ID"))
				return true;
		}
		return false;
	}
	
	function IpCompare($_ip, $_comparer)
	{
		$array_ip = explode(".",$_ip);
		$array_comparer = explode(".",$_comparer);
		if(count($array_ip) == 4 && count($array_comparer) == 4)
		{
			foreach($array_ip as $key => $octet)
			{
				if($array_ip[$key] != $array_comparer[$key])
				{
					if($array_comparer[$key] == -1)
						return true;
					return false;
				}
			}
			return true;
		}
		else
			return false;
	}
	
	function LangCompare($_lang, $_comparer)
	{
		$array_lang = explode(",",$_lang);
		$array_comparer = explode(",",$_comparer);
		foreach($array_lang as $key => $lang)
			foreach($array_comparer as $keyc => $langc)
				if(strtoupper($array_lang[$key]) == strtoupper($langc))
					return true;
		return false;
	}
}

class EventList
{
	var $Events;
	
	function EventList()
   	{
		$this->Events = Array();
   	}
	
	function GetActionById($_id)
	{
		foreach($this->Events as $event)
			foreach($event->Actions as $action)
				if($action->Id == $_id)
					return $action;
		return null;
	}
}

class Filter extends BaseObject
{
	var $IP;
	var $Expiredate;
	var $Userid;
	var $Reason;
	var $Filtername;
	var $FilterId;
	var $Activestate;
	var $Exertion;
	var $Languages;
	var $Activeipaddress;
	var $Activeuserid;
	var $Activelanguage;
	
	function GetData()
	{
		$data = Array();
		$data["s_creator"] = $this->Creator;
		$data["s_created"] = $this->Created;
		$data["s_editor"] = $this->Editor;
		$data["s_edited"] = $this->Edited;
		$data["s_ip"] = $this->IP;
		$data["s_expiredate"] = $this->Expiredate;
		$data["s_userid"] = $this->Userid;
		$data["s_reason"] = $this->Reason;
		$data["s_filtername"] = $this->Filtername;
		$data["s_filterid"] = $this->FilterId;
		$data["s_activestate"] = $this->Activestate;
		$data["s_exertion"] = $this->Exertion;
		$data["s_languages"] = $this->Languages;
		$data["s_activeipaddress"] = $this->Activeipaddress;
		$data["s_activeuserid"] = $this->Activeuserid;
		$data["s_activelanguage"] = $this->Activelanguage;
		return $data;
	}
	
	function Filter($_id)
   	{
		$this->FilterId = $_id;
		$this->Edited = time();
   	}
	
	function GetXML()
	{
		return "<val active=\"".base64_encode($this->Activestate)."\" edited=\"".base64_encode($this->Edited)."\" editor=\"".base64_encode($this->Editor)."\" activeipaddresses=\"".base64_encode($this->Activeipaddress)."\" activeuserids=\"".base64_encode($this->Activeuserid)."\" activelanguages=\"".base64_encode($this->Activelanguage)."\" expires=\"".base64_encode($this->Expiredate)."\" creator=\"".base64_encode($this->Creator)."\" created=\"".base64_encode($this->Created)."\" userid=\"".base64_encode($this->Userid)."\" ip=\"".base64_encode($this->IP)."\" filtername=\"".base64_encode($this->Filtername)."\" filterid=\"".base64_encode($this->FilterId)."\" reason=\"".base64_encode($this->Reason)."\" exertion=\"".base64_encode($this->Exertion)."\" languages=\"".base64_encode($this->Languages)."\" />\r\n";
	}
	
	function Load()
	{
		$dataProvider = new FileEditor(PATH_FILTER . $this->FilterId . FILE_EXTENSION_FILTER);
		$dataProvider->Load();
		$this->Creator = $dataProvider->Result["s_creator"];
		$this->Created = $dataProvider->Result["s_created"];
		$this->Editor = $dataProvider->Result["s_editor"];
		$this->Edited = $dataProvider->Result["s_edited"];
		$this->IP = $dataProvider->Result["s_ip"];
		$this->Expiredate = $dataProvider->Result["s_expiredate"];
		$this->Userid = $dataProvider->Result["s_userid"];
		$this->Reason = $dataProvider->Result["s_reason"];
		$this->Filtername = $dataProvider->Result["s_filtername"];
		$this->FilterId = $dataProvider->Result["s_filterid"];
		$this->Activestate = $dataProvider->Result["s_activestate"];
		$this->Exertion = $dataProvider->Result["s_exertion"];
		$this->Languages = $dataProvider->Result["s_languages"];
		$this->Activeipaddress = $dataProvider->Result["s_activeipaddress"];
		$this->Activeuserid = $dataProvider->Result["s_activeuserid"];
		$this->Activelanguage = $dataProvider->Result["s_activelanguage"];
	}
	
	function Save()
	{
		if(strpos($this->FilterId,".")===false)
		{
			$dataProvider = new FileEditor(PATH_FILTER . $this->FilterId . FILE_EXTENSION_FILTER);
			$dataProvider->Save($this->GetData());
		}
	}
	
	function Destroy()
	{
		if(file_exists(PATH_FILTER . $this->FilterId . FILE_EXTENSION_FILTER))
			@unlink(PATH_FILTER . $this->FilterId . FILE_EXTENSION_FILTER);
	}
}

class Rating extends Action
{
	var $Fullname = "";
	var $Email="";
	var $Company="";
	var $InternId="";
	var $UserId="";
	var $RateQualification=0;
	var $RatePoliteness=0;
	var $RateComment=0;

	function Rating()
	{
		$this->Id = func_get_arg(0);
		if(func_num_args() == 2)
		{
			$row = func_get_arg(1);
			$this->RateComment = $row["comment"];
			$this->RatePoliteness = $row["politeness"];
			$this->RateQualification = $row["qualification"];
			$this->Fullname = $row["fullname"];
			$this->Email = $row["email"];
			$this->Company = $row["company"];
			$this->InternId = $row["internal_id"];
			$this->UserId = $row["user_id"];
			$this->Created = $row["time"];
		}
	}
	
	function GetData()
	{
		$data = Array();
		$data["s_rate_c"] = $this->RateComment;
		$data["s_rate_p"] = $this->RatePoliteness;
		$data["s_rate_q"] = $this->RateQualification;
		$data["s_fullname"] = $this->Fullname;
		$data["s_email"] = $this->Email;
		$data["s_company"] = $this->Company;
		$data["s_internid"] = $this->InternId;
		$data["s_userid"] = $this->UserId;
		return $data;
	}
	
	function IsFlood()
	{
		return isRatingFlood();
	}
	
	function Load()
	{
		$dataProvider = new FileEditor($this->TargetFile);
		$dataProvider->Load();
		$this->RateComment = $dataProvider->Result["s_rate_c"];
		$this->RatePoliteness = $dataProvider->Result["s_rate_p"];
		$this->RateQualification = $dataProvider->Result["s_rate_q"];
		$this->Fullname = $dataProvider->Result["s_fullname"];
		$this->Email = $dataProvider->Result["s_email"];
		$this->Company = $dataProvider->Result["s_company"];
		$this->InternId = $dataProvider->Result["s_internid"];
		$this->UserId = $dataProvider->Result["s_userid"];
		$this->Created = @filemtime($this->TargetFile);
	}
	
	function GetXML($_internal,$_full)
	{
		if($_full)
		{
			$intern = (isset($_internal[getInternalSystemIdByUserId($this->InternId)])) ? $_internal[getInternalSystemIdByUserId($this->InternId)]->Fullname : $this->InternId;
			return "<val id=\"".base64_encode($this->Id)."\" cr=\"".base64_encode($this->Created)."\" rc=\"".base64_encode($this->RateComment)."\" rp=\"".base64_encode($this->RatePoliteness)."\" rq=\"".base64_encode($this->RateQualification)."\" fn=\"".base64_encode($this->Fullname)."\" em=\"".base64_encode($this->Email)."\" co=\"".base64_encode($this->Company)."\" ii=\"".base64_encode($intern)."\" ui=\"".base64_encode($this->UserId)."\" />\r\n";
		}
		else
			return "<val id=\"".base64_encode($this->Id)."\" cr=\"".base64_encode($this->Created)."\" />\r\n";
	}
}

class ClosedTicket extends Action
{
	function ClosedTicket()
	{
		$this->Id = func_get_arg(0);
		if(func_num_args() == 2)
		{
			$row = func_get_arg(1);
			$this->Sender = $row["internal_fullname"];
		}
	}

	function GetXML($_time,$_status)
	{
		return "<cl id=\"".base64_encode($this->Id)."\" st=\"".base64_encode($_status)."\" ed=\"".base64_encode($this->Sender)."\" ti=\"".base64_encode($_time)."\"/>\r\n";
	}
}

class UserTicket extends Action
{
	var $Fullname = "";
	var $Email="";
	var $Group="";
	var $Company="";
	var $IP="";
	var $UserId="";
	
	function UserTicket()
	{
		if(func_num_args() == 2)
		{
			$this->Id = func_get_arg(0);
		}
		else
		{
			$row = func_get_arg(0);
			$this->Text = $row["text"];
			$this->Fullname = $row["fullname"];
		 	$this->Email = $row["email"];
			$this->Company = $row["company"];
			$this->Group = $row["target_group_id"];
			$this->IP = $row["ip"];
			$this->Id = $row["ticket_id"];
			$this->UserId = $row["user_id"];
			$this->Created = $row["time"];
		}
	}

	function GetXML($_groups,$_full)
	{
		if($_full)
			return "<val id=\"".base64_encode($this->Id)."\" ct=\"".base64_encode($this->Created)."\" gr=\"".base64_encode($this->Group)."\" mt=\"".base64_encode($this->Text)."\" fn=\"".base64_encode($this->Fullname)."\" em=\"".base64_encode($this->Email)."\" co=\"".base64_encode($this->Company)."\" ui=\"".base64_encode($this->UserId)."\" />\r\n";
		else
			return "<val id=\"".base64_encode($this->Id)."\" ct=\"".base64_encode($this->Created)."\" />\r\n";
	}
}

class Response
{
	var $XML = "";
	var $Internals="";
	var $Groups="";
	var $InternalProfilePictures="";
	var $InternalWebcamPictures="";
	var $InternalVcards="";
	var $Typing="";
	var $Exceptions="";
	var $Filter="";
	var $Events="";
	var $EventTriggers="";
	var $Authentications="";
	var $Posts="";
	var $Login;
	var $Ratings="";
	var $Messages="";
	var $Archive="";
	var $Resources="";
	var $GlobalHash;
	var $Actions="";
	
	function SetStandardResponse($_code,$_sub)
	{
		$this->XML = "<response><value id=\"".base64_encode($_code)."\" />" . $_sub . "</response>";
	}
	
	function SetValidationError($_code,$_addition="")
	{
		if(!isnull($_addition))
			$this->XML = "<validation_error value=\"".base64_encode($_code)."\" error=\"".base64_encode($_addition)."\" />";
		else
			$this->XML = "<validation_error value=\"".base64_encode($_code)."\" />";
	}
	
	function GetXML()
	{
		return "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><livezilla_xml><livezilla_version>".base64_encode(VERSION)."</livezilla_version>" . $this->XML . "</livezilla_xml>";
	}
}

class FileEditor
{
	var $Result;
	var $TargetFile;
	
	function FileEditor($_file)
	{
		$this->TargetFile = $_file;
	}
	
	function Load()
	{
		if(file_exists($this->TargetFile))
		{
			$handle = @fopen ($this->TargetFile, "r");
			while (!@feof($handle))
	   			$this->Result .= @fgets($handle, 4096);
			
			$length = strlen($this->Result);
			$this->Result = @unserialize($this->Result);
			@fclose($handle);
		}
	}

	function Save($_data)
	{
		if(strpos($this->TargetFile,"..") === false)
		{
			$handle = @fopen($this->TargetFile, "w");
			if(!isnull($_data))
				$length = @fputs($handle,serialize($_data));
			@fclose($handle);
		}
	}
}

class DataProvider
{
	var $TargetFile;
	var $Result;
	
	function DataProvider($_file)
	{
		$this->TargetFile = $_file;
	}
	
	function Load()
	{
		$file = $this->TargetFile;
		if(LIVEZILLA_PATH != "./")
			$file = str_replace(LIVEZILLA_PATH, "./", $this->TargetFile);
		if($result = queryDB(true,"SELECT `data` FROM `".DB_PREFIX.DATABASE_DATA."` WHERE `file` = '".mysql_real_escape_string($file)."' LIMIT 1"))
		{
			$row = mysql_fetch_array($result, MYSQL_BOTH);
			$this->Result = @unserialize($row[0]);
		}
	}
	
	function Save($_data)
	{
		$serdat = serialize($_data);
		$result = queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_DATA."` SET `data`='".@mysql_real_escape_string($serdat)."',`time`='".@mysql_real_escape_string(time())."',`size`='".@mysql_real_escape_string(strlen($serdat))."' WHERE `file`='".@mysql_real_escape_string($this->TargetFile)."' LIMIT 1;");
		if(mysql_affected_rows() == 0)
			queryDB(false,"INSERT INTO `".DB_PREFIX.DATABASE_DATA."` (`file`,`time`,`data`,`size`) VALUES ('".@mysql_real_escape_string($this->TargetFile)."',".@mysql_real_escape_string(time()).",'".@mysql_real_escape_string($serdat)."','".@mysql_real_escape_string(strlen($serdat))."');");
	}
	
	function Notify()
	{
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_DATA."` SET `size`=`size`+1 WHERE `file`='".@mysql_real_escape_string($this->TargetFile)."' LIMIT 1;");
	}
}

class FileUploadRequest extends Action
{
	var $Error = false;
	var $Download = false;
	var $FileName;
	var $FileMask;
	var $FileId;
	var $Permission = PERMISSION_VOID;
	
	function FileUploadRequest($_fileId,$_receiverId)
	{
		$this->Id = $_fileId;
		$this->ReceiverUserId = $_receiverId;
		$this->Extension = EX_FILE_UPLOAD_REQUEST;
		$this->Folder = PATH_DATA_INTERNAL . $this->ReceiverUserId . "/";
		$this->TargetFile = $this->Folder . $this->Id . "." . $this->Extension;
	}
	
	function GetData()
	{
		$data = Array();
		$data["s_id"] = $this->Id;
		$data["s_filename"] = $this->FileName;
		$data["s_filemask"] = $this->FileMask;
		$data["s_fileid"] = $this->FileId;
		$data["s_senderUserId"] = $this->SenderUserId;
		$data["s_senderBrowserId"] = $this->SenderBrowserId;
		$data["s_error"] = $this->Error;
		$data["s_permission"] = $this->Permission;
		$data["s_download"] = $this->Download;
		return $data;
	}
	
	function Load()
	{
		$dataProvider = new DataProvider($this->TargetFile);
		$dataProvider->Load();
		$this->Id = $dataProvider->Result["s_id"];
		$this->FileName = $dataProvider->Result["s_filename"];
		$this->FileMask = $dataProvider->Result["s_filemask"];
		$this->FileId = $dataProvider->Result["s_fileid"];
		$this->SenderUserId = $dataProvider->Result["s_senderUserId"];
		$this->SenderBrowserId = $dataProvider->Result["s_senderBrowserId"];
		$this->Error = $dataProvider->Result["s_error"];
		$this->Permission = $dataProvider->Result["s_permission"];
		$this->Download = $dataProvider->Result["s_download"];
	}
	
	function GetFile()
	{
		return PATH_UPLOADS . $this->FileMask;
	}
}

class Forward extends Action
{
	var $Conversation;
	var $TargetSessId;
	var $TargetGroupId;
	var $Processed = false;
	
	function Forward($_receiverUserId,$_receiverBrowserId,$_senderSessId)
	{
		$this->Id = getId(5);
		$this->ReceiverUserId = $_receiverUserId;
		$this->BrowserId = $_receiverBrowserId;
		$this->SenderSystemId = $_senderSessId;
		$this->Extension = EX_CHAT_FORWARDING;
		$this->Folder = PATH_DATA_EXTERNAL . $this->ReceiverUserId . "/b/" . $this->BrowserId . "/";
		$this->TargetFile = $this->Folder . $this->SenderSystemId . "." . $this->Extension;
	}
	
	function GetData()
	{
		$data = Array();
		$data["p_id"] = $this->Id;
		$data["p_sendersessid"] = $this->SenderSystemId;
		$data["p_targetsessid"] = $this->TargetSessId;
		$data["p_targetgroupid"] = $this->TargetGroupId;
		$data["p_browserid"] = $this->BrowserId;
		$data["p_receiveruserid"] = $this->ReceiverUserId;
		$data["p_conversation"] = $this->Conversation;
		$data["p_text"] = $this->Text;
		$data["p_processed"] = $this->Processed;
		return $data;
	}
	
	function Load()
	{
		$dataProvider = new DataProvider($this->TargetFile);
		$dataProvider->Load();
		$this->Id = $dataProvider->Result["p_id"];
		$this->SenderSystemId = $dataProvider->Result["p_sendersessid"];
		$this->TargetSessId = $dataProvider->Result["p_targetsessid"];
		$this->TargetGroupId = $dataProvider->Result["p_targetgroupid"];
		$this->BrowserId = $dataProvider->Result["p_browserid"];
		$this->ReceiverUserId = $dataProvider->Result["p_receiveruserid"];
		$this->Conversation = $dataProvider->Result["p_conversation"];
		$this->Text = $dataProvider->Result["p_text"];
		$this->Processed = $dataProvider->Result["p_processed"];
	}
}

class WebsitePush extends Action
{
	var $TargetURL;
	var $Ask;
	var $ActionId;
	var $Senders;
	
	function WebsitePush()
	{
		if(func_num_args() == 7)
		{
			$this->Id = getId(32);
			$this->SenderSystemId = func_get_arg(0);
			$this->SenderGroupId = func_get_arg(1);
			$this->ReceiverUserId = func_get_arg(2);
			$this->BrowserId = func_get_arg(3);
			$this->Text = func_get_arg(4);
			$this->Ask = func_get_arg(5);
			$this->TargetURL = func_get_arg(6);
			$this->Senders = array();
		}
		else if(func_num_args() == 3)
		{
			$this->Id = getId(32);
			$this->ActionId = func_get_arg(0);
			$this->TargetURL = func_get_arg(1);
			$this->Ask = func_get_arg(2);
			$this->Senders = array();
		}
		else if(func_num_args() == 2)
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->Ask = $_row["ask"];
			$this->TargetURL = $_row["target_url"];
			$this->Senders = array();
		}
		else
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->SenderSystemId = $_row["sender_system_id"];
			$this->ReceiverUserId = $_row["receiver_user_id"];
			$this->BrowserId = $_row["receiver_browser_id"];
			$this->Text = $_row["text"];
			$this->Ask = $_row["ask"];
			$this->TargetURL = $_row["target_url"];
			$this->Accepted = $_row["accepted"];
			$this->Declined = $_row["declined"];
			$this->Displayed = $_row["displayed"];
			$this->Senders = array();
		}
	}

	function SaveEventConfiguration()
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_EVENT_ACTION_WEBSITE_PUSHS."` (`id`, `action_id`, `target_url`,`ask`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->ActionId)."','".@mysql_real_escape_string($this->TargetURL)."','".@mysql_real_escape_string($this->Ask)."');");
	}
	
	function SetStatus($_displayed,$_accepted,$_declined)
	{
		if($_displayed)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_WEBSITE_PUSHS."` SET `displayed`='1',`accepted`='0',`declined`='0' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
		else if($_accepted)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_WEBSITE_PUSHS."` SET `displayed`='1',`accepted`='1',`declined`='0' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
		else if($_declined)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_WEBSITE_PUSHS."` SET `displayed`='1',`accepted`='0',`declined`='1' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}
	
	function Save()
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_WEBSITE_PUSHS."` (`id`, `created`, `sender_system_id`, `receiver_user_id`, `receiver_browser_id`, `text`, `ask`, `target_url`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string(time())."','".@mysql_real_escape_string($this->SenderSystemId)."','".@mysql_real_escape_string($this->ReceiverUserId)."', '".@mysql_real_escape_string($this->BrowserId)."','".@mysql_real_escape_string($this->Text)."','".@mysql_real_escape_string($this->Ask)."','".@mysql_real_escape_string($this->TargetURL)."');");
	}

	function GetInitCommand()
	{
		return "lz_tracking_init_website_push('".base64_encode(str_replace("%target_url%",$this->TargetURL,$this->Text))."',".time().");";
	}
	
	function GetExecCommand()
	{
		return "lz_tracking_exec_website_push('".base64_encode($this->TargetURL)."');";
	}
	
	function GetXML()
	{
		$xml = "<evwp id=\"".base64_encode($this->Id)."\" url=\"".base64_encode($this->TargetURL)."\" ask=\"".base64_encode($this->Ask)."\">\r\n";
		
		foreach($this->Senders as $sender)
			$xml .= $sender->GetXML();

		return $xml . "</evwp>\r\n";
	}
}

class EventActionInternal extends Action
{
	var $TriggerId;
	function EventActionInternal()
	{
		if(func_num_args() == 2)
		{
			$this->Id = getId(32);
			$this->ReceiverUserId = func_get_arg(0);
			$this->TriggerId = func_get_arg(1);
		}
		else
		{
			$_row = func_get_arg(0);
			$this->TriggerId = $_row["trigger_id"];
			$this->EventActionId = $_row["action_id"];
		}
	}

	function Save()
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_EVENT_ACTION_INTERNALS."` (`id`, `created`, `trigger_id`, `receiver_user_id`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string(time())."', '".@mysql_real_escape_string($this->TriggerId)."', '".@mysql_real_escape_string($this->ReceiverUserId)."');");
	}

	function GetXml()
	{
		return "<ia time=\"".base64_encode(time())."\" aid=\"".base64_encode($this->EventActionId)."\" />\r\n";
	}
}

class Alert extends Action
{
	function Alert()
	{
		if(func_num_args() == 3)
		{
			$this->Id = getId(32);
			$this->ReceiverUserId = func_get_arg(0);
			$this->BrowserId = func_get_arg(1);
			$this->Text = func_get_arg(2);
		}
		else
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->ReceiverUserId = $_row["receiver_user_id"];
			$this->BrowserId = $_row["receiver_browser_id"];
			$this->Text = $_row["text"];
			$this->EventActionId = $_row["event_action_id"];
			$this->Displayed = !isnull($_row["displayed"]);
			$this->Accepted = !isnull($_row["accepted"]);
		}
	}

	function Save()
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_ALERTS."` (`id`, `created`, `receiver_user_id`, `receiver_browser_id`,`event_action_id`, `text`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string(time())."','".@mysql_real_escape_string($this->ReceiverUserId)."', '".@mysql_real_escape_string($this->BrowserId)."','".@mysql_real_escape_string($this->EventActionId)."','".@mysql_real_escape_string($this->Text)."');");
	}
	
	function SetStatus($_displayed,$_accepted)
	{
		if($_displayed)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_ALERTS."` SET `displayed`='1',`accepted`='0' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
		else if($_accepted)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_ALERTS."` SET `displayed`='1',`accepted`='1' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}

	function GetCommand()
	{
		return "lz_tracking_send_alert('".$this->Id."','".base64_encode($this->Text)."');";
	}
}

class ChatRequest extends Action
{
	var $Invitation;
	function ChatRequest()
   	{
		if(func_num_args() == 5)
		{
			$this->Id = getId(32);
			$this->SenderSystemId = func_get_arg(0);
			$this->SenderGroupId = func_get_arg(1);
			$this->ReceiverUserId = func_get_arg(2);
			$this->BrowserId = func_get_arg(3);
			$this->Text = func_get_arg(4);
		}
		else
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->SenderSystemId = $_row["sender_system_id"];
			$this->SenderUserId = $_row["sender_system_id"];
			$this->SenderGroupId = $_row["sender_group_id"];
			$this->ReceiverUserId = $_row["receiver_user_id"];
			$this->BrowserId = $_row["receiver_browser_id"];
			$this->EventActionId = $_row["event_action_id"];
			$this->Text = $_row["text"];
			$this->Displayed = !isnull($_row["displayed"]);
			$this->Accepted = !isnull($_row["accepted"]);
			$this->Declined = !isnull($_row["declined"]);
		}
   	}
	
	function SetStatus($_displayed,$_accepted,$_declined)
	{
		if($_displayed)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` SET `displayed`='1',`accepted`='0',`declined`='0' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
		if($_accepted)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` SET `displayed`='1',`accepted`='1',`declined`='0' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
		else if($_declined)
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` SET `displayed`='1',`accepted`='0',`declined`='1' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}
	
	function Save()
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` (`id`, `created`, `sender_system_id`, `sender_group_id`,`receiver_user_id`, `receiver_browser_id`,`event_action_id`, `text`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string(time())."','".@mysql_real_escape_string($this->SenderSystemId)."','".@mysql_real_escape_string($this->SenderGroupId)."','".@mysql_real_escape_string($this->ReceiverUserId)."', '".@mysql_real_escape_string($this->BrowserId)."','".@mysql_real_escape_string($this->EventActionId)."','".@mysql_real_escape_string($this->Text)."');");
	}
	
	function Destroy()
	{
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}

	function CreateInvitationTemplate($_style,$_siteName,$_cwWidth,$_cwHeight,$_serverURL,$_sender,$_closeOnClick)
	{
		$template = (@file_exists(FILE_INVITATIONLOGO) && @file_exists(TEMPLATE_SCRIPT_INVITATION . $_style . "/invitation_header.tpl")) ? getFile(TEMPLATE_SCRIPT_INVITATION . $_style . "/invitation_header.tpl") : getFile(TEMPLATE_SCRIPT_INVITATION . $_style . "/invitation.tpl");
		$template = str_replace("<!--site_name-->",$_siteName,$template);
		$template = str_replace("<!--intern_name-->",$_sender->Fullname,$template);
		$template = str_replace("<!--template-->",$_style,$template);
		$template = str_replace("<!--group_id-->",base64UrlEncode($this->SenderGroupId),$template);
		$template = str_replace("<!--user_id-->",base64UrlEncode($_sender->UserId),$template);
		$template = str_replace("<!--width-->",$_cwWidth,$template);
		$template = str_replace("<!--height-->",$_cwHeight,$template);
		$template = str_replace("<!--server-->",$_serverURL,$template);
		$template = str_replace("<!--intern_image-->",$_sender->GetOperatorPictureFile(),$template);
		$template = str_replace("<!--close_on_click-->",$_closeOnClick,$template);
		return $template;
	}
}

class Invitation
{
	var $Id;
	var $ActionId;
	var $Style = "classic";
	var $DisplayPosition = "11";
	var $Speed = 1;
	var $Slide = true;
	var $Margin;
	var $Senders;
	var $Width;
	var $Height;
	var $HTML;
	var $Text;
	var $CloseOnClick;
	
	function Invitation()
	{
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			$this->Style = $_row["style"];
			$this->Id = $_row["id"];
			$this->Position = $_row["position"];
			$this->Margin = Array($_row["margin_left"],$_row["margin_top"],$_row["margin_right"],$_row["margin_bottom"]);
			$this->Speed = $_row["speed"];
			$this->Slide = $_row["slide"];
			$this->CloseOnClick = $_row["close_on_click"];
		}
		else if(func_num_args() == 10)
		{
			$this->Id = getId(32);
			$this->ActionId = func_get_arg(0);
			$this->Position = func_get_arg(1);
			$this->Margin = Array(func_get_arg(2),func_get_arg(3),func_get_arg(4),func_get_arg(5));
			$this->Speed = func_get_arg(6);
			$this->Style = func_get_arg(7);
			$this->Slide = func_get_arg(8);
			$this->CloseOnClick = func_get_arg(9);
		}
		else
		{
			$this->HTML = func_get_arg(0);
			$this->Position = func_get_arg(1);
			$this->Margin = Array(func_get_arg(2),func_get_arg(3),func_get_arg(4),func_get_arg(5));
			$this->Speed = func_get_arg(6);
			$this->Style = func_get_arg(7);
			$this->Slide = func_get_arg(8);
			$this->Text = func_get_arg(9);
			$this->CloseOnClick = func_get_arg(10);
		}
		
		if(!isnull($this->Style))
		{
			$dimensions = (@file_exists(FILE_INVITATIONLOGO) && @file_exists(TEMPLATE_SCRIPT_INVITATION . $this->Style . "/dimensions_header.txt")) ? explode(",",getFile(TEMPLATE_SCRIPT_INVITATION . $this->Style . "/dimensions_header.txt")) : explode(",",getFile(TEMPLATE_SCRIPT_INVITATION . $this->Style . "/dimensions.txt"));
			$this->Width = @$dimensions[0];
			$this->Height = @$dimensions[1];
		}
		$this->Senders = Array();
	}
	
	function GetSQL()
	{
		return "INSERT INTO `".DB_PREFIX.DATABASE_EVENT_ACTION_INVITATIONS."` (`id`, `action_id`, `position`, `speed`, `slide`, `margin_left`, `margin_top`, `margin_right`, `margin_bottom`, `style`, `close_on_click`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->ActionId)."','".@mysql_real_escape_string($this->Position)."', '".@mysql_real_escape_string($this->Speed)."', '".@mysql_real_escape_string($this->Slide)."', '".@mysql_real_escape_string($this->Margin[0])."', '".@mysql_real_escape_string($this->Margin[1])."', '".@mysql_real_escape_string($this->Margin[2])."', '".@mysql_real_escape_string($this->Margin[3])."', '".@mysql_real_escape_string($this->Style)."', '".@mysql_real_escape_string($this->CloseOnClick)."');";
	}

	function GetXML()
	{
		$xml = "<evinv id=\"".base64_encode($this->Id)."\" ml=\"".base64_encode($this->Margin[0])."\" mt=\"".base64_encode($this->Margin[1])."\" mr=\"".base64_encode($this->Margin[2])."\" mb=\"".base64_encode($this->Margin[3])."\" pos=\"".base64_encode($this->Position)."\" speed=\"".base64_encode($this->Speed)."\" slide=\"".base64_encode($this->Slide)."\" style=\"".base64_encode($this->Style)."\" coc=\"".base64_encode($this->CloseOnClick)."\">\r\n";
		
		foreach($this->Senders as $sender)
			$xml .= $sender->GetXML();
			
		return $xml . "</evinv>\r\n";
	}
	
	function GetCommand()
	{
		return "lz_tracking_request_chat('" . base64_encode($this->Id) . "','". base64_encode($this->Text) ."','". base64_encode($this->HTML) ."',".$this->Width.",".$this->Height.",".$this->Margin[0].",".$this->Margin[1].",".$this->Margin[2].",".$this->Margin[3].",'" . $this->Position . "',".$this->Speed."," . parseBool($this->Slide) . ");";
	}
}

class EventTrigger
{
	var $Id;
	var $ActionId;
	var $ReceiverUserId;
	var $ReceiverBrowserId;
	var $Triggered;
	var $TriggerTime;
	var $Exists = false;
	
	function EventTrigger()
	{
		if(func_num_args() == 5)
		{
			$this->Id = getId(32);
			$this->ReceiverUserId = func_get_arg(0);
			$this->ReceiverBrowserId = func_get_arg(1);
			$this->ActionId = func_get_arg(2);
			$this->TriggerTime = func_get_arg(3);
			$this->Triggered = func_get_arg(4);
		}
		else
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->ReceiverUserId = $_row["receiver_user_id"];
			$this->ReceiverBrowserId = $_row["receiver_browser_id"];
			$this->ActionId = $_row["action_id"];
			$this->Triggered = $_row["triggered"];
			$this->TriggerTime = $_row["time"];
		}
	}
	
	function Load()
	{
		$this->Exists = false;
		if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_EVENT_TRIGGERS."` WHERE `receiver_user_id`='".@mysql_real_escape_string($this->ReceiverUserId)."' AND `receiver_browser_id`='".@mysql_real_escape_string($this->ReceiverBrowserId)."' AND `action_id`='".@mysql_real_escape_string($this->ActionId)."' ORDER BY `time` ASC;"))
			if($row = mysql_fetch_array($result, MYSQL_BOTH))
			{
				$this->Id = $row["id"];
				$this->TriggerTime = $row["time"];
				$this->Triggered = $row["triggered"];
				$this->Exists = true;
			}
	}
	
	function Update()
	{
		queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_EVENT_TRIGGERS."` SET `time`='".@mysql_real_escape_string(time())."' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}

	function Save($_eventId)
	{
		if(!$this->Exists)
			queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_EVENT_TRIGGERS."` (`id`, `receiver_user_id`, `receiver_browser_id`, `action_id`, `time`, `triggered`) VALUES ('".@mysql_real_escape_string($this->Id)."','".@mysql_real_escape_string($this->ReceiverUserId)."', '".@mysql_real_escape_string($this->ReceiverBrowserId)."','".@mysql_real_escape_string($this->ActionId)."', '".@mysql_real_escape_string($this->TriggerTime)."','".@mysql_real_escape_string($this->Triggered)."');");
		else
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_EVENT_TRIGGERS."` SET `triggered`=`triggered`+1, `time`='".@mysql_real_escape_string(time())."' WHERE `id`='".@mysql_real_escape_string($this->Id)."' LIMIT 1;");
	}
}

class EventAction
{
	var $Id = "";
	var $EventId = "";
	var $Type = "";
	var $Value = "";
	var $Invitation;
	var $WebsitePush;
	var $Receivers;
	
	function EventAction()
	{
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->EventId = $_row["eid"];
			$this->Type = $_row["type"];
			$this->Value = $_row["value"];
		}
		else
		{
			$this->EventId = func_get_arg(0);
			$this->Id = func_get_arg(1);
			$this->Type = func_get_arg(2);
			$this->Value = func_get_arg(3);
		}
		$this->Receivers = Array();
	}
	
	function GetSQL()
	{
		return "INSERT INTO `".DB_PREFIX.DATABASE_EVENT_ACTIONS."` (`id`, `eid`, `type`, `value`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->EventId)."','".@mysql_real_escape_string($this->Type)."', '".@mysql_real_escape_string($this->Value)."');";
	}

	function GetXML()
	{
		$xml =  "<evac id=\"".base64_encode($this->Id)."\" type=\"".base64_encode($this->Type)."\" val=\"".base64_encode($this->Value)."\">\r\n";
		
		if(!isnull($this->Invitation))
			$xml .= $this->Invitation->GetXML();
			
		if(!isnull($this->WebsitePush))
			$xml .= $this->WebsitePush->GetXML();
			
		foreach($this->Receivers as $receiver)
			$xml .= $receiver->GetXML();
			
		return $xml . "</evac>\r\n";
	}
	
	function Exists($_receiverUserId,$_receiverBrowserId)
	{
		if($this->Type == 2)
		{
			if($result = queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_CHAT_REQUESTS."` WHERE `receiver_user_id`='".@mysql_real_escape_string($_receiverUserId)."' AND `receiver_browser_id`='".@mysql_real_escape_string($_receiverBrowserId)."' AND `event_action_id`='".@mysql_real_escape_string($this->Id)."' AND `accepted`='0' AND `declined`='0' LIMIT 1;"))
				if($row = mysql_fetch_array($result, MYSQL_BOTH))
					return true;
		}
		else if($this->Type == 3)
		{
			if($result = queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_ALERTS."` WHERE `receiver_user_id`='".@mysql_real_escape_string($_receiverUserId)."' AND `receiver_browser_id`='".@mysql_real_escape_string($_receiverBrowserId)."' AND `event_action_id`='".@mysql_real_escape_string($this->Id)."' AND `accepted`='0' LIMIT 1;"))
				if($row = mysql_fetch_array($result, MYSQL_BOTH))
					return true;
		}
		return false;
	}
	
	function GetInternalReceivers()
	{
		$receivers = array();
		if($result = queryDB(true,"SELECT `receiver_id` FROM `".DB_PREFIX.DATABASE_EVENT_ACTION_RECEIVERS."`;"))
			while($row = mysql_fetch_array($result, MYSQL_BOTH))
				$receivers[]=$row["receiver_id"];
		return $receivers;
	}
}

class EventActionSender
{
	var $Id = "";
	var $ParentId = "";
	var $UserSystemId = "";
	var $GroupId = "";
	var $Priority = "";
	
	function EventActionSender()
	{
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->ParentId = $_row["pid"];
			$this->UserSystemId = $_row["user_id"];
			$this->GroupId = $_row["group_id"];
			$this->Priority = $_row["priority"];
		}
		else if(func_num_args() == 4)
		{
			$this->Id = getId(32);
			$this->ParentId = func_get_arg(0);
			$this->UserSystemId = func_get_arg(1);
			$this->GroupId = func_get_arg(2);
			$this->Priority = func_get_arg(3);
		}
	}
	
	function SaveSender()
	{
		return queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_EVENT_ACTION_SENDERS."` (`id`, `pid`, `user_id`, `group_id`, `priority`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->ParentId)."','".@mysql_real_escape_string($this->UserSystemId)."','".@mysql_real_escape_string($this->GroupId)."', '".@mysql_real_escape_string($this->Priority)."');");
	}

	function GetXML()
	{
		return "<evinvs id=\"".base64_encode($this->Id)."\" userid=\"".base64_encode($this->UserSystemId)."\" groupid=\"".base64_encode($this->GroupId)."\" priority=\"".base64_encode($this->Priority)."\" />\r\n";
	}
}

class EventActionReceiver
{
	var $Id = "";
	var $ReceiverId = "";
	
	function EventActionReceiver()
	{
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->ActionId = $_row["action_id"];
			$this->ReceiverId = $_row["receiver_id"];
		}
		else
		{
			$this->Id = getId(32);
			$this->ActionId = func_get_arg(0);
			$this->ReceiverId = func_get_arg(1);
		}
	}
	
	function GetSQL()
	{
		return "INSERT INTO `".DB_PREFIX.DATABASE_EVENT_ACTION_RECEIVERS."` (`id`, `action_id`, `receiver_id`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->ActionId)."', '".@mysql_real_escape_string($this->ReceiverId)."');";
	}

	function GetXML()
	{
		return "<evr id=\"".base64_encode($this->Id)."\" rec=\"".base64_encode($this->ReceiverId)."\" />\r\n";
	}
}

class EventURL
{
	var $Id = "";
	var $EventId = "";
	var $URL = "";
	var $Referrer = "";
	var $TimeOnSite = "";
	var $Blacklist;
	
	function EventURL()
	{
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->URL = $_row["url"];
			$this->Referrer = $_row["referrer"];
			$this->TimeOnSite = $_row["time_on_site"];
			$this->Blacklist = !isnull($_row["blacklist"]);
		}
		else
		{
			$this->Id = getId(32);
			$this->EventId = func_get_arg(0);
			$this->URL = strtolower(func_get_arg(1));
			$this->Referrer = strtolower(func_get_arg(2));
			$this->TimeOnSite = func_get_arg(3);
			$this->Blacklist = func_get_arg(4);
		}
	}
	
	function GetSQL()
	{
		return "INSERT INTO `".DB_PREFIX.DATABASE_EVENT_URLS."` (`id`, `eid`, `url`, `referrer`, `time_on_site`, `blacklist`) VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->EventId)."','".@mysql_real_escape_string($this->URL)."', '".@mysql_real_escape_string($this->Referrer)."', '".@mysql_real_escape_string($this->TimeOnSite)."', '".@mysql_real_escape_string($this->Blacklist)."');";
	}

	function GetXML()
	{
		return "<evur id=\"".base64_encode($this->Id)."\" url=\"".base64_encode($this->URL)."\" ref=\"".base64_encode($this->Referrer)."\" tos=\"".base64_encode($this->TimeOnSite)."\" bl=\"".base64_encode($this->Blacklist)."\" />\r\n";
	}
}

class Event extends BaseObject
{
	var $Name = "";
	var $PagesVisited = "";
	var	$TimeOnSite = "";
	var $Receivers;
	var $URLs;
	var $Actions;
	var $NotAccepted;
	var $NotDeclined;
	var $TriggerTime;
	var $TriggerAmount;
	var $NotInChat;
	var $Priority;
	var $IsActive;
	
	function Event()
	{
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			$this->Id = $_row["id"];
			$this->Name = $_row["name"];
			$this->Edited = $_row["edited"];
			$this->Editor = $_row["editor"];
			$this->Created = $_row["created"];
			$this->Creator = $_row["creator"];
			$this->TimeOnSite = $_row["time_on_site"];
			$this->PagesVisited = $_row["pages_visited"];
			$this->NotAccepted = $_row["not_accepted"];
			$this->NotDeclined = $_row["not_declined"];
			$this->NotInChat = $_row["not_in_chat"];
			$this->TriggerAmount = $_row["max_trigger_amount"];
			$this->TriggerTime = $_row["trigger_again_after"];
			$this->Priority = $_row["priority"];
			$this->IsActive = !isnull($_row["is_active"]);
			$this->URLs = Array();
			$this->Actions = Array();
			$this->Receivers = Array();
		}
		else
		{
			$this->Id = func_get_arg(0);
			$this->Name = func_get_arg(1);
			$this->Edited = func_get_arg(2);
			$this->Created = func_get_arg(3);
			$this->Editor = func_get_arg(4);
			$this->Creator = func_get_arg(5);
			$this->TimeOnSite = func_get_arg(6);
			$this->PagesVisited = func_get_arg(7);
			$this->NotAccepted = func_get_arg(8);
			$this->NotDeclined = func_get_arg(9);
			$this->TriggerTime = func_get_arg(10);
			$this->TriggerAmount = func_get_arg(11);
			$this->NotInChat = func_get_arg(12);
			$this->Priority = func_get_arg(13);
			$this->IsActive = func_get_arg(14);
		}
	}
	
	function MatchesTriggerCriterias($_trigger)
	{
		$match = true;
		if($this->TriggerTime > 0 && $_trigger->TriggerTime >= (time()-$this->TriggerTime))
			$match = false;
		else if($this->TriggerAmount == 0 || ($this->TriggerAmount > 0 && $_trigger->Triggered > $this->TriggerAmount))
			$match = false;
		return $match;
	}
	
	function MatchesGlobalCriterias($_pageCount,$_timeOnSite,$_invAccepted,$_invDeclined,$_inChat)
	{
		$match = true;
		if($this->PagesVisited > 0 && $_pageCount < $this->PagesVisited)
			$match = false;
		else if($this->TimeOnSite > 0 && $_timeOnSite < $this->TimeOnSite)
			$match = false;
		else if(!isnull($this->NotAccepted) && $_invAccepted)
			$match = false;
		else if(!isnull($this->NotDeclined) && $_invDeclined)
			$match = false;
		else if(!isnull($this->NotInChat) && $_inChat)
			$match = false;
		return $match;
	}
	
	function MatchesURLCriterias($_url,$_referrer,$_timeOnUrl)
	{
		if(count($this->URLs) == 0)
			return true;
		
		$_url = strtolower($_url);
		$_referrer = strtolower($_referrer);
		
		$match = false;
		foreach($this->URLs as $url)
		{
			if($url->TimeOnSite > 0 && $url->TimeOnSite > $_timeOnUrl)
				continue;
		
			$valid = true;
			if(!isnull($url->URL))
			{
				if(strpos($url->URL,"*")===false && $url->URL != $_url)
					$valid = false;
				else
				{
					$parts = explode("*",$url->URL);
					$index = 0;
					for($i=0;$i<count($parts);$i++)
					{
						if($parts[$i] == "")
							continue;
						if($i == count($parts)-1 && substr($_url,(strlen($_url)-strlen($parts[$i])),strlen($parts[$i])) != $parts[$i])
						{
							$valid = false;
							break;
						}
						else if(($pos = strpos($_url,$parts[$i])) !== false)
						{
							if($pos < $index)
							{
								$valid = false;
								break;
							}
						}
						else
						{
							$valid = false;
							break;
						}
					}
				}
			}
			if(!isnull($url->Referrer))
			{
				if(strpos($url->Referrer,"*")===false && $url->Referrer != $_referrer)
					$valid = false;
				else
				{
					$parts = explode("*",$url->Referrer);
					$index = 0;
					for($i=0;$i<count($parts);$i++)
					{
						if($parts[$i] == "")
							continue;
						if($i == count($parts)-1 && substr($_referrer,(strlen($_referrer)-strlen($parts[$i])),strlen($parts[$i])) != $parts[$i])
						{
							$valid = false;
							break;
						}
						else if(($pos = strpos($_referrer,$parts[$i])) !== false)
						{
							if($pos < $index)
							{
								$valid = false;
								break;
							}
						}
						else
						{
							$valid = false;
							break;
						}
					}
				}
			}

			if($valid)
			{
				$match = true;
				if($url->Blacklist)
					return false;
				else
					return true;
			}
		}
		
		return $match;
	}

	function GetSQL()
	{
		return "INSERT INTO `".DB_PREFIX.DATABASE_EVENTS."` (`id`, `name`, `created`, `creator`, `edited`, `editor`, `pages_visited`, `time_on_site`, `max_trigger_amount`, `trigger_again_after`, `not_declined`, `not_accepted`, `not_in_chat`, `priority`, `is_active`) VALUES ('".@mysql_real_escape_string($this->Id)."','".@mysql_real_escape_string($this->Name)."','".@mysql_real_escape_string($this->Created)."','".@mysql_real_escape_string($this->Creator)."','".@mysql_real_escape_string($this->Edited)."', '".@mysql_real_escape_string($this->Editor)."', '".@mysql_real_escape_string($this->PagesVisited)."','".@mysql_real_escape_string($this->TimeOnSite)."','".@mysql_real_escape_string($this->TriggerAmount)."','".@mysql_real_escape_string($this->TriggerTime)."', '".@mysql_real_escape_string($this->NotDeclined)."', '".@mysql_real_escape_string($this->NotAccepted)."', '".@mysql_real_escape_string($this->NotInChat)."', '".@mysql_real_escape_string($this->Priority)."', '".@mysql_real_escape_string($this->IsActive)."');";
	}

	function GetXML()
	{
		$xml = "<ev id=\"".base64_encode($this->Id)."\" nacc=\"".base64_encode($this->NotAccepted)."\" ndec=\"".base64_encode($this->NotDeclined)."\" name=\"".base64_encode($this->Name)."\" prio=\"".base64_encode($this->Priority)."\" created=\"".base64_encode($this->Created)."\" nic=\"".base64_encode($this->NotInChat)."\" creator=\"".base64_encode($this->Creator)."\" editor=\"".base64_encode($this->Editor)."\" edited=\"".base64_encode($this->Edited)."\" tos=\"".base64_encode($this->TimeOnSite)."\" ta=\"".base64_encode($this->TriggerAmount)."\" tt=\"".base64_encode($this->TriggerTime)."\" pv=\"".base64_encode($this->PagesVisited)."\" ia=\"".base64_encode($this->IsActive)."\">\r\n";
		
		foreach($this->Actions as $action)
			$xml .= $action->GetXML();
		
		foreach($this->URLs as $url)
			$xml .= $url->GetXML();
			
		return $xml . "</ev>\r\n";
	}
}

class PredefinedMessage
{
	var $Id = 0;
	var $LangISO = "";
	var $InvitationAuto = "";
	var $InvitationManual = "";
	var $Welcome = "";
	var $WebsitePushAuto = "";
	var $WebsitePushManual = "";
	var $BrowserIdentification = "";
	var $IsDefault;
	var $AutoWelcome;
	var	$GroupId = "";
	var	$UserId = "";
	var $Editable;
	
	function PredefinedMessage()
	{
		if(func_num_args() == 1)
		{
			$_row = func_get_arg(0);
			$this->LangISO = $_row["lang_iso"];
			$this->InvitationAuto = @$_row["invitation_auto"];
			$this->InvitationManual = @$_row["invitation_manual"];
			$this->Welcome = $_row["welcome"];
			$this->WebsitePushAuto = @$_row["website_push_auto"];
			$this->WebsitePushManual = @$_row["website_push_manual"];
			$this->BrowserIdentification = !isnull($_row["browser_ident"]);
			$this->IsDefault = !isnull($_row["is_default"]);
			$this->AutoWelcome = !isnull($_row["auto_welcome"]);
			$this->Editable = !isnull(@$_row["editable"]);
		}
	}
	
	function XMLParamAlloc($_param,$_value)
	{
		if($_param =="inva")
			$this->InvitationAuto = $_value;
		if($_param =="invm")
			$this->InvitationManual = $_value;
		if($_param =="wpa")
			$this->WebsitePushAuto = $_value;
		if($_param =="wpm")
			$this->WebsitePushManual = $_value;
		if($_param =="bi")
			$this->BrowserIdentification = $_value;
		if($_param =="wel")
			$this->Welcome = $_value;
		if($_param =="def")
			$this->IsDefault = $_value;
		if($_param =="aw")
			$this->AutoWelcome = $_value;
		if($_param =="edit")
			$this->Editable = $_value;
	}
	
	function Save()
	{
		queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_PREDEFINED."` (`id` ,`internal_id` ,`group_id` ,`lang_iso` ,`invitation_manual`,`invitation_auto` ,`welcome` ,`website_push_manual` ,`website_push_auto` ,`browser_ident` ,`is_default` ,`auto_welcome`,`editable`)VALUES ('".@mysql_real_escape_string($this->Id)."', '".@mysql_real_escape_string($this->UserId)."','".@mysql_real_escape_string($this->GroupId)."', '".@mysql_real_escape_string($this->LangISO)."', '".@mysql_real_escape_string($this->InvitationManual)."', '".@mysql_real_escape_string($this->InvitationAuto)."','".@mysql_real_escape_string($this->Welcome)."', '".@mysql_real_escape_string($this->WebsitePushManual)."', '".@mysql_real_escape_string($this->WebsitePushAuto)."', '".@mysql_real_escape_string($this->BrowserIdentification)."', '".@mysql_real_escape_string($this->IsDefault)."', '".@mysql_real_escape_string($this->AutoWelcome)."', '".@mysql_real_escape_string($this->Editable)."');");
	}

	function GetXML()
	{
		return "<pm lang=\"".base64_encode($this->LangISO)."\" invm=\"".base64_encode($this->InvitationManual)."\" inva=\"".base64_encode($this->InvitationAuto)."\" wel=\"".base64_encode($this->Welcome)."\" wpa=\"".base64_encode($this->WebsitePushAuto)."\" wpm=\"".base64_encode($this->WebsitePushManual)."\" bi=\"".base64_encode($this->BrowserIdentification)."\" def=\"".base64_encode($this->IsDefault)."\" aw=\"".base64_encode($this->AutoWelcome)."\" edit=\"".base64_encode($this->Editable)."\" />\r\n";
	}
}

class Chat
{
	var $Activated;
	var $Closed;
	var $Declined;
	var $MemberCount;
	var $TargetFileExternal;
	var $TargetFileInternal;
	var $TargetFileInternalActivation;
	var $TargetFileExternalActivation;
	var $TargetFileInternalClosed;
	var $TargetFileInternalDeclined;
	var $InternalUser;
	var $ExternalUser;
	var $FileUploadRequest = null;
	
	function Chat()
	{
		if(func_num_args() == 3)
		{
			$this->ExternalUser = func_get_arg(0);
			$this->InternalUser = func_get_arg(1);
			$this->Id = func_get_arg(2);
			$this->SetDirectories();
		}
		else if(func_num_args() == 2)
		{
			$this->ExternalUser = func_get_arg(0);
			$this->InternalUser = func_get_arg(1);
			$this->SetDirectories();
		}
		else
		{
			$this->Load(func_get_arg(0));
		}
	}
	
	function SetDirectories()
	{
		$this->TargetFileExternal = $this->ExternalUser->Folder . $this->InternalUser->SessId . "." . EX_CHAT_OPEN;
		$this->TargetFileInternal = $this->InternalUser->Folder . $this->ExternalUser->BrowserId . "." . EX_CHAT_OPEN;
		$this->TargetFileExternalActivation = $this->InternalUser->Folder . $this->ExternalUser->BrowserId . "." . EX_CHAT_ACTIVE;
		$this->TargetFileInternalClosed = $this->ExternalUser->Folder . $this->InternalUser->SessId . "." . EX_CHAT_INTERN_CLOSED;
		$this->TargetFileInternalDeclined = $this->ExternalUser->Folder . $this->InternalUser->SessId . "." . EX_CHAT_INTERN_DECLINED;
		$this->TargetFileInternalActivation = $this->ExternalUser->Folder . $this->InternalUser->SessId . "." . EX_CHAT_ACTIVE;
		$this->Declined = (dataSetExists($this->TargetFileInternalDeclined));
		$this->Closed = (dataSetExists($this->TargetFileInternalClosed));
	}
	
	function IsActivated($_systemId)
	{
		$activated = false;
		$files = getDirectory($this->ExternalUser->Folder,false);
		foreach($files as $file)
			if(strpos($file,EX_CHAT_ACTIVE) !== false)
				if(isnull($_systemId) || (!isnull($_systemId) && strpos(trim($file),trim($_systemId)) !== false))
					$activated = true;
		
		$existance = array(dataSetExists($this->TargetFileExternalActivation),dataSetExists($this->TargetFileInternalActivation),dataSetExists($this->TargetFileInternal),dataSetExists($this->TargetFileExternal));
		$this->Activated = (($existance[0] && $existance[1]) ? CHAT_STATUS_ACTIVE : (($existance[0] || $existance[1]) ? CHAT_STATUS_WAITING : CHAT_STATUS_OPEN));
		if(!$this->Closed)
			$this->Closed = ($existance[0] && !$existance[1]);
		return $activated;
	}
	
	function GetData()
	{
		$data = Array();
		$data["s_internal_userid"] = $this->InternalUser->UserId;
		$data["s_internal_sessid"] = $this->InternalUser->SessId;
		$data["s_internal_fullname"] = $this->InternalUser->Fullname;
		$data["s_external_userid"] = $this->ExternalUser->UserId;
		$data["s_external_browserid"] = $this->ExternalUser->BrowserId;
		$data["s_id"] = $this->Id;
		return $data;
	}

	function InternalDecline()
	{
		$dataProvider = new DataProvider($this->TargetFileInternalDeclined);
		$dataProvider->Save(Array(),true);
	}
	
	function InternalClose()
	{
		$dataProvider = new DataProvider($this->TargetFileInternalClosed);
		$dataProvider->Save(Array(),true);
	}
	
	function InternalActivate($_internal)
	{
		$this->InternalUser = $_internal;
		if(!$this->IsActivated(null))
		{
			$this->TargetFileInternalActivation = $this->ExternalUser->Folder . $this->InternalUser->SessId . "." . EX_CHAT_ACTIVE;
			$dataProvider = new DataProvider($this->TargetFileInternalActivation);
			$dataProvider->Save(Array(),true);
		}
		else
		{
			unlinkDataSet($this->TargetFileInternal);
			unlinkDataSet($this->TargetFileExternalActivation);
		}
	}
	
	function ExternalActivate()
	{
		$dataProvider = new DataProvider($this->TargetFileExternalActivation);
		$dataProvider->Save(Array(),true);
	}
	
	function ExternalDestroy()
	{
		unlinkDataSet($this->TargetFileExternal);
		unlinkDataSet($this->TargetFileInternalActivation);
		unlinkDataSet($this->TargetFileInternalClosed);
		unlinkDataSet($this->TargetFileInternalDeclined);
	}
	
	function InternalDestroy()
	{
		unlinkDataSet($this->TargetFileExternalActivation);
		unlinkDataSet($this->TargetFileInternal);
	}
	
	function Load($_chatfile)
	{
		if(isnull($_chatfile))
			$dataProvider = new DataProvider($this->TargetFileExternal);
		else
			$dataProvider = new DataProvider($_chatfile);
		$dataProvider->Load();

		$this->Id = $dataProvider->Result["s_id"];
		$this->InternalUser = new Operator($dataProvider->Result["s_internal_sessid"],$dataProvider->Result["s_internal_userid"]);
		$this->ExternalUser = new ExternalChat($dataProvider->Result["s_external_userid"],$dataProvider->Result["s_external_browserid"]);
		
		$this->InternalUser->Fullname = $dataProvider->Result["s_internal_fullname"];
		$this->SetDirectories();
	}
	
	function Save()
	{
		$dataProvider = new DataProvider($this->TargetFileExternal);
		$dataProvider->Save($this->GetData(),true);
		
		$dataProvider = new DataProvider($this->TargetFileInternal);
		$dataProvider->Save($this->GetData(),true);
	}
}

class DataSet
{
	var $LastActive;
	var $Data;
	var $Name;
	var $Size;
}

class Profile
{
	var $LastEdited;
	var $Firstname;
	var $Name;
	var $Email;
	var $Company;
	var $Phone;
	var $Fax;
	var $Department;
	var $Street;
	var $City;
	var $ZIP;
	var $Country;
	var $Languages;
	var $Comments;
	var $Public;
	
	function Profile()
   	{
		if(func_num_args() == 1)
		{
			$row = func_get_arg(0);
            $this->Firstname = $row["first_name"];
            $this->Name = $row["last_name"];
            $this->Email = $row["email"];
            $this->Company = $row["company"];
            $this->Phone = $row["phone"];
            $this->Fax = $row["fax"];
            $this->Department = $row["department"];
            $this->Street = $row["street"];
            $this->City = $row["city"];
            $this->ZIP = $row["zip"];
            $this->Country = $row["country"];
            $this->Languages = $row["languages"];
            $this->Gender = $row["gender"];
            $this->Comments = $row["comments"];
			$this->Public = $row["public"];
			$this->LastEdited = $row["edited"];
		}
		else
		{
            $this->Firstname = func_get_arg(0);
            $this->Name = func_get_arg(1);
            $this->Email = func_get_arg(2);
            $this->Company = func_get_arg(3);
            $this->Phone = func_get_arg(4);
            $this->Fax = func_get_arg(5);
            $this->Department = func_get_arg(6);
            $this->Street = func_get_arg(7);
            $this->City = func_get_arg(8);
            $this->ZIP = func_get_arg(9);
            $this->Country = func_get_arg(10);
            $this->Languages = func_get_arg(11);
            $this->Gender = func_get_arg(12);
            $this->Comments = func_get_arg(13);
			$this->Public = func_get_arg(14);
		}
   	}
	
	function GetXML($_userId)
	{
		return "<p os=\"".base64_encode($_userId)."\" fn=\"".base64_encode($this->Firstname)."\" n=\"".base64_encode($this->Name)."\" e=\"".base64_encode($this->Email)."\" co=\"".base64_encode($this->Company)."\" p=\"".base64_encode($this->Phone)."\" f=\"".base64_encode($this->Fax)."\" d=\"".base64_encode($this->Department)."\" s=\"".base64_encode($this->Street)."\" z=\"".base64_encode($this->ZIP)."\" c=\"".base64_encode($this->Country)."\" l=\"".base64_encode($this->Languages)."\" ci=\"".base64_encode($this->City)."\" g=\"".base64_encode($this->Gender)."\" com=\"".base64_encode($this->Comments)."\" pu=\"".base64_encode($this->Public)."\" />\r\n";
	}

	function Save($_userId)
	{
		queryDB(false,"INSERT INTO `".DB_PREFIX.DATABASE_PROFILES."` (`id` ,`edited` ,`first_name` ,`last_name` ,`email` ,`company` ,`phone`  ,`fax` ,`street` ,`zip` ,`department` ,`city` ,`country` ,`gender` ,`languages` ,`comments` ,`public`) VALUES ('".@mysql_real_escape_string($_userId)."','".@mysql_real_escape_string(time())."','".@mysql_real_escape_string($this->Firstname)."','".@mysql_real_escape_string($this->Name)."','".@mysql_real_escape_string($this->Email)."','".@mysql_real_escape_string($this->Company)."','".@mysql_real_escape_string($this->Phone)."','".@mysql_real_escape_string($this->Fax)."','".@mysql_real_escape_string($this->Street)."','".@mysql_real_escape_string($this->ZIP)."','".@mysql_real_escape_string($this->Department)."','".@mysql_real_escape_string($this->City)."','".@mysql_real_escape_string($this->Country)."','".@mysql_real_escape_string($this->Gender)."','".@mysql_real_escape_string($this->Languages)."','".@mysql_real_escape_string($this->Comments)."','".@mysql_real_escape_string($this->Public)."');");
	}
}
?>