<?php
/****************************************************************************************
* LiveZilla objects.internal.inc.php
* 
* Copyright 2010 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();
	
class InternalXMLBuilder
{
	var $Config;
	var $Caller;
	var $InternalUsers;
	var $InternalGroups;

	var $XMLProfilePictures = "";
	var $XMLWebcamPictures = "";
	var $XMLProfiles = "";
	var $XMLInternal = "";
	var $XMLTyping = "";
	var $XMLGroups = "";
	
	function InternalXMLBuilder($_config,$_caller,$_internalusers,$_internalgroups)
	{
		$this->Config = $_config;
		$this->Caller = $_caller;
		$this->InternalUsers = $_internalusers;
		$this->InternalGroups = $_internalgroups;
	}
	
	function Generate()
	{
		if(SERVERSETUP && defined("HideGroupSelection"))
			$this->XMLGroups .= "<hide_selection>".base64_encode(1)."</hide_selection>\r\n";
	
		foreach($this->InternalGroups as $groupId => $group)
		{
			$this->XMLGroups .= "<v id=\"".base64_encode($group->Id)."\" desc=\"".base64_encode($group->DescriptionArray)."\" created=\"".base64_encode($group->Created)."\"  email=\"".base64_encode($group->Email)."\"  external=\"".base64_encode($group->IsExternal)."\"  internal=\"".base64_encode($group->IsInternal)."\" standard=\"".base64_encode($group->IsStandard)."\" vf=\"".base64_encode($group->VisitorFilters)."\">\r\n";
			foreach($group->PredefinedMessages as $premes)
				$this->XMLGroups .= $premes->GetXML();

			if(SERVERSETUP)
			{
				if(defined("HideGroupSelection"))
					$this->XMLGroups .= "<hide_selection>".base64_encode(1)."</hide_selection>\r\n";

				$this->XMLGroups .= "<f key=\"".base64_encode("gr_ex_sm")."\">".base64_encode($group->ChatFunctions[0])."</f>\r\n";
				$this->XMLGroups .= "<f key=\"".base64_encode("gr_ex_so")."\">".base64_encode($group->ChatFunctions[1])."</f>\r\n";
				$this->XMLGroups .= "<f key=\"".base64_encode("gr_ex_pr")."\">".base64_encode($group->ChatFunctions[2])."</f>\r\n";
				$this->XMLGroups .= "<f key=\"".base64_encode("gr_ex_ra")."\">".base64_encode($group->ChatFunctions[3])."</f>\r\n";
				$this->XMLGroups .= "<f key=\"".base64_encode("gr_ex_fv")."\">".base64_encode($group->ChatFunctions[4])."</f>\r\n";
				$this->XMLGroups .= "<f key=\"".base64_encode("gr_ex_fu")."\">".base64_encode($group->ChatFunctions[5])."</f>\r\n";
				$this->XMLGroups .= "<ci key=\"".base64_encode("ci_hidden")."\">\r\n";
				foreach($group->ChatInputsHidden as $index)
					$this->XMLGroups .= "<value>".base64_encode($index)."</value>\r\n";
				$this->XMLGroups .= "</ci>\r\n";
				
				$this->XMLGroups .= "<ci key=\"".base64_encode("ci_mandatory")."\">\r\n";
				foreach($group->ChatInputsMandatory as $index)
					$this->XMLGroups .= "<value>".base64_encode($index)."</value>\r\n";
				$this->XMLGroups .= "</ci>\r\n";
			}
			$this->XMLGroups .= "</v>\r\n";
		}
		foreach($this->InternalUsers as $sysId => $internaluser)
		{
			$b64sysId = base64_encode($sysId);
			$sessiontime = getDataSetTime($this->Caller->SessionFile);
			$this->InternalUsers[$sysId]->LoadPictures();
			
			if($sysId != CALLER_SYSTEM_ID && !isnull($this->InternalUsers[$sysId]->WebcamPicture))
			{	
				if($_POST[POST_INTERN_XMLCLIP_HASH_PICTURES_PROFILE] == XML_CLIP_NULL || $this->InternalUsers[$sysId]->WebcamPictureTime >= $sessiontime)
					$this->XMLWebcamPictures .= "<v os=\"".$b64sysId."\" content=\"".$this->InternalUsers[$sysId]->WebcamPicture."\" />\r\n";
			}
			else
				$this->XMLWebcamPictures .= "<v os=\"".$b64sysId."\" content=\"".base64_encode("")."\" />\r\n";
				
			if(!isnull($this->InternalUsers[$sysId]->ProfilePicture))
			{	
				if(@$_POST[POST_INTERN_XMLCLIP_HASH_PICTURES_PROFILE] == XML_CLIP_NULL || $this->InternalUsers[$sysId]->ProfilePictureTime >= $sessiontime)
					$this->XMLProfilePictures .= "<v os=\"".$b64sysId."\" content=\"".$this->InternalUsers[$sysId]->ProfilePicture."\" />\r\n";
			}
			else
				$this->XMLProfilePictures .= "<v os=\"".$b64sysId."\" content=\"".base64_encode("")."\" />\r\n";
			
			$CPONL = ($this->InternalUsers[CALLER_SYSTEM_ID]->Level==USER_LEVEL_ADMIN) ? " cponl=\"".base64_encode(($internaluser->IsPasswordChangeNeeded()) ? 1 : 0)."\"" : "";
			$PASSWORD = (SERVERSETUP) ? " pass=\"".base64_encode($this->InternalUsers[$sysId]->LoadPassword())."\"" : "";
			$this->XMLInternal .= "<v status=\"".base64_encode($this->InternalUsers[$sysId]->Status)."\" id=\"".$b64sysId."\" userid=\"".base64_encode($this->InternalUsers[$sysId]->UserId)."\" email=\"".base64_encode($this->InternalUsers[$sysId]->Email)."\" websp=\"".base64_encode($this->InternalUsers[$sysId]->Webspace)."\" name=\"".base64_encode($this->InternalUsers[$sysId]->Fullname)."\" desc=\"".base64_encode($this->InternalUsers[$sysId]->Description)."\" groups=\"".base64_encode($this->InternalUsers[$sysId]->GroupsArray)."\" perms=\"".base64_encode($this->InternalUsers[$sysId]->PermissionSet)."\" ip=\"".base64_encode($this->InternalUsers[$sysId]->IP)."\" level=\"".base64_encode($this->InternalUsers[$sysId]->Level)."\" ".$CPONL." ".$PASSWORD.">\r\n";
			foreach($internaluser->PredefinedMessages as $premes)
				$this->XMLInternal .= $premes->GetXML();
			$this->XMLInternal .= "</v>\r\n";
			
			if($sysId!=$this->Caller->SystemId && $this->InternalUsers[$sysId]->Status != USER_STATUS_OFFLINE)
				$this->XMLTyping .= "<v id=\"".$b64sysId."\" tp=\"".base64_encode((($this->Caller->SystemId===$this->InternalUsers[$sysId]->Typing)?1:0))."\" />\r\n";
			
			$internaluser->LoadProfile();
			if($internaluser->Profile != null)
				if((isset($_POST[POST_INTERN_XMLCLIP_HASH_PROFILES]) && $_POST[POST_INTERN_XMLCLIP_HASH_PROFILES] == XML_CLIP_NULL) || $internaluser->Profile->LastEdited >= $sessiontime)
					$this->XMLProfiles .= $internaluser->Profile->GetXML($internaluser->SystemId);
				else
					$this->XMLProfiles .= "<p os=\"".$b64sysId."\"/>\r\n";
		}
	}
}

class ExternalXMLBuilder
{
	var $CurrentStatics = array();
	var $ActiveBrowsers = array();
	var $AddedVisitors = array();
	var $SessionFileSizes = array();
	var $StaticReload = array();
	var $DiscardedObjects = array();
	var $IsDiscardedObject = false;
	var $ObjectCounter = 0;
	var $CurrentUser;
	var $CurrentFilesize;
	var $CurrentResponseType = DATA_RESPONSE_TYPE_KEEP_ALIVE;
	
	var $XMLVisitorOpen = false;
	var $XMLCurrentChat = "";
	var $XMLCurrentAliveBrowsers = "";
	var $XMLCurrentVisitor = "";
	var $XMLCurrentVisitorTag = "";
	var $XMLCurrent = "";
	var $XMLTyping = "";
	
	var $Config;
	var $Caller;
	var $ExternUsers;
	var $GetAll;
	var $IsExternal;

	function ExternalXMLBuilder($_config,$_caller,$_externusers,$_getall,$_external)
	{
		$this->Config = $_config;
		$this->Caller = $_caller;
		$this->ExternUsers = $_externusers;
		$this->GetAll = $_getall;
		$this->IsExternal = $_external;
	}
	
	function SetDiscardedObject()
	{
		foreach($this->SessionFileSizes as $sfs_userid => $sfs_browsers)
			if(isset($this->ExternUsers[$sfs_userid]))
			{
				foreach($sfs_browsers as $sfs_bid => $sfs_browser)
					if(!isset($this->ExternUsers[$sfs_userid]->Browsers[$sfs_bid]))
					{
						if(!isset($this->DiscardedObjects[$sfs_userid]))
							$this->DiscardedObjects[$sfs_userid] = array($sfs_bid);
						else if($this->DiscardedObjects[$sfs_userid] != null)
							$this->DiscardedObjects[$sfs_userid][$sfs_bid] = null;
					}
			}
			else
				$this->DiscardedObjects[$sfs_userid] = null;
	}
	
	function Generate()
	{
		global $BROWSER,$USER;
		$this->SetDiscardedObject();
		foreach($this->ExternUsers as $userid => $USER)
		{
			$ischat = false;
			$isactivebrowser = false;
			$this->XMLCurrentAliveBrowsers = 
			$this->XMLCurrentVisitor = "";
			if(count($USER->Browsers) > 0)
				$this->GetStaticInfo();
			$this->CurrentResponseType = ($USER->ExternalStatic != null) ? DATA_RESPONSE_TYPE_STATIC : DATA_RESPONSE_TYPE_KEEP_ALIVE;
			
			foreach($USER->Browsers as $browserId => $BROWSER)
			{
				$this->ObjectCounter++;
				array_push($this->ActiveBrowsers,$BROWSER->BrowserId);
				$this->CurrentFilesize = getDataSetSize($BROWSER->SessionFile);
				
				$BROWSER->LoadChatRequest();
				$this->XMLCurrentChat = null;
				
				if($BROWSER->Type == BROWSER_TYPE_CHAT)
				{
					$isactivebrowser = true;
					$this->BuildChatXML();
					$ischat = (!$ischat) ? $USER->IsChat : $ischat;
					$this->SessionFileSizes[$userid][$browserId] = $this->CurrentFilesize;
				}
				else if(!isset($this->SessionFileSizes[$userid]) || !isnull($BROWSER->ChatRequest) || $this->CurrentResponseType == DATA_RESPONSE_TYPE_STATIC || (isset($this->SessionFileSizes[$userid]) && (!isset($this->SessionFileSizes[$userid][$browserId]) || (isset($this->SessionFileSizes[$userid][$browserId]) && $this->SessionFileSizes[$userid][$browserId] != $this->CurrentFilesize))))
				{
					$isactivebrowser = true;
					if($this->CurrentResponseType == DATA_RESPONSE_TYPE_KEEP_ALIVE)
						$this->CurrentResponseType = DATA_RESPONSE_TYPE_BASIC;
					$this->SessionFileSizes[$userid][$browserId] = $this->CurrentFilesize;
					$BROWSER->Load();
				}
				else
					$this->CurrentResponseType = DATA_RESPONSE_TYPE_KEEP_ALIVE;
				
				$this->BuildVisitorXML();
				$USER->Browsers[$browserId] = $BROWSER;
			}
			$this->XMLCurrentVisitor .= $this->XMLCurrentAliveBrowsers;
			if($this->XMLVisitorOpen)
			{
				if($this->IsDiscardedObject || $isactivebrowser)
					$this->XMLCurrent .= $this->XMLCurrentVisitorTag . $this->XMLCurrentVisitor . "</v>\r\n";
				$this->XMLVisitorOpen = false;
			}
		}
		$this->RemoveFileSizes($this->ActiveBrowsers);
	}
	
	function BuildVisitorXML()
	{
		global $USER,$BROWSER;
		$visitorDetails = Array("userid" => " id=\"".base64_encode($USER->UserId)."\"","resolution" => null,"ip" => null,"lat" => null,"long" => null,"city" => null,"ctryi2" => null,"region" => null,"system" => null,"language" => null,"referrer" => null,"requested" => null,"target" => null,"declined" => null,"accepted" => null,"cname" => null,"cemail" => null,"ccompany" => null,"waiting" => null,"timezoneoffset" => null,"visits" => null,"host"=>null,"grid"=>null,"isp"=>null,"cf0"=>null,"cf1"=>null,"cf2"=>null,"cf3"=>null,"cf4"=>null,"cf5"=>null,"cf6"=>null,"cf7"=>null,"cf8"=>null,"cf9"=>null);
		if($this->CurrentResponseType != DATA_RESPONSE_TYPE_KEEP_ALIVE)
		{
			$visitorDetails["referrer"] = " referrer=\"".base64_encode($BROWSER->Referrer)."\"";
			$visitorDetails["requested"] = (!isnull($BROWSER->ChatRequest) && !$BROWSER->ChatRequest->Accepted && !$BROWSER->ChatRequest->Declined) ? " req=\"".base64_encode(getInternalSystemIdByUserId($BROWSER->ChatRequest->SenderUserId))."\"" : "";
			$visitorDetails["declined"] = (!isnull($BROWSER->ChatRequest) && $BROWSER->ChatRequest->Declined) ? " dec=\"".base64_encode("1")."\"" : "";
			$visitorDetails["accepted"] = (!isnull($BROWSER->ChatRequest) && $BROWSER->ChatRequest->Accepted) ? " acc=\"".base64_encode("1")."\"" : "";
			$visitorDetails["target"] = (!isnull($BROWSER->ChatRequest)) ? " tbid=\"".base64_encode($BROWSER->BrowserId)."\"" : "";
			$visitorDetails["cname"] = " cname=\"".base64_encode($BROWSER->Fullname)."\"";
			$visitorDetails["cemail"] = " cemail=\"".base64_encode($BROWSER->Email)."\"";
			$visitorDetails["ccompany"] = " ccompany=\"".base64_encode($BROWSER->Company)."\"";

			for($int=0;$int<=9;$int++)
			{
				if(isset($BROWSER->Customs[$int]) && !isnull($BROWSER->Customs[$int]))
				{
					$visitorDetails["cf".$int] = " cf".$int."=\"".base64_encode($BROWSER->Customs[$int])."\"";
				}
				else
					$visitorDetails["cf".$int] = "";
			}
			
		}
		if($this->CurrentResponseType == DATA_RESPONSE_TYPE_STATIC)
		{
			$visitorDetails["resolution"] = " res=\"".base64_encode($USER->ExternalStatic->Resolution)."\"";
			$visitorDetails["ip"] = " ip=\"".base64_encode($USER->ExternalStatic->IP)."\"";
			$visitorDetails["timezoneoffset"] = " tzo=\"".base64_encode($USER->ExternalStatic->GeoTimezoneOffset)."\"";
			$visitorDetails["lat"] = " lat=\"".base64_encode($USER->ExternalStatic->GeoLatitude)."\"";
			$visitorDetails["long"] = " long=\"".base64_encode($USER->ExternalStatic->GeoLongitude)."\"";
			$visitorDetails["city"] = " city=\"".base64_encode($USER->ExternalStatic->GeoCity)."\"";
			$visitorDetails["ctryi2"] = " ctryi2=\"".base64_encode($USER->ExternalStatic->GeoCountryISO2)."\"";
			$visitorDetails["region"] = " region=\"".base64_encode($USER->ExternalStatic->GeoRegion)."\"";
			$visitorDetails["system"] = " system=\"".base64_encode($USER->ExternalStatic->SystemInfo)."\"";
			$visitorDetails["language"] = " lang=\"".base64_encode($USER->ExternalStatic->Language)."\"";
			$visitorDetails["visits"] = " vts=\"".base64_encode($USER->ExternalStatic->Visits)."\"";
			$visitorDetails["host"] = " ho=\"".base64_encode($USER->ExternalStatic->Host)."\"";
			$visitorDetails["grid"] = " gr=\"".base64_encode($USER->ExternalStatic->GeoResultId)."\"";
			$visitorDetails["isp"] = " isp=\"".base64_encode($USER->ExternalStatic->GeoISP)."\"";
		}
		$visitorDetails["waiting"] = ($BROWSER->Type == BROWSER_TYPE_CHAT && $BROWSER->Waiting && in_array($BROWSER->DesiredChatGroup,$this->Caller->Groups)) ? " w=\"".base64_encode(1)."\"" : "";
		if(!isnull($BROWSER->ChatRequest))
		{
			if(isnull($USER->ActiveChatRequest) || (!isnull($USER->ActiveChatRequest) && $BROWSER->ChatRequest->Created > $USER->ActiveChatRequest->Created))
				$USER->ActiveChatRequest = $BROWSER->ChatRequest;
		}
		
		if(!in_array($USER->UserId,$this->AddedVisitors) || (!isnull($BROWSER->ChatRequest) && $BROWSER->ChatRequest == $USER->ActiveChatRequest))
		{
			array_push($this->AddedVisitors, $USER->UserId);
			$this->XMLVisitorOpen = true;
			$this->XMLCurrentVisitorTag =  "<v".$visitorDetails["userid"].$visitorDetails["resolution"].$visitorDetails["ip"].$visitorDetails["lat"].$visitorDetails["long"].$visitorDetails["region"].$visitorDetails["city"].$visitorDetails["ctryi2"].$visitorDetails["visits"].$visitorDetails["declined"].$visitorDetails["accepted"].$visitorDetails["target"].$visitorDetails["system"].$visitorDetails["language"].$visitorDetails["requested"].$visitorDetails["cname"].$visitorDetails["cemail"].$visitorDetails["ccompany"].$visitorDetails["timezoneoffset"].$visitorDetails["host"].$visitorDetails["grid"].$visitorDetails["isp"].$visitorDetails["cf0"].$visitorDetails["cf1"].$visitorDetails["cf2"].$visitorDetails["cf3"].$visitorDetails["cf4"].$visitorDetails["cf5"].$visitorDetails["cf6"].$visitorDetails["cf7"].$visitorDetails["cf8"].$visitorDetails["cf9"].">\r\n";
		}

		if($this->CurrentResponseType != DATA_RESPONSE_TYPE_KEEP_ALIVE)
		{
			$this->XMLCurrentVisitor .=  " <b id=\"".base64_encode($BROWSER->BrowserId)."\"".$visitorDetails["referrer"].$visitorDetails["waiting"].">\r\n";
				for($i = 0;$i < count($BROWSER->History);$i++)
					$this->XMLCurrentVisitor .=  "  <h time=\"".base64_encode($BROWSER->History[$i][0])."\" url=\"".base64_encode($BROWSER->History[$i][1])."\" title=\"".base64_encode(@$BROWSER->History[$i][4])."\" code=\"".base64_encode($BROWSER->History[$i][2])."\" cp=\"".base64_encode(($BROWSER->History[$i][3])?"1":"0")."\" />\r\n";
				
			if(!isnull($this->XMLCurrentChat))
				$this->XMLCurrentVisitor .= $this->XMLCurrentChat;
			$this->XMLCurrentVisitor .=  " </b>\r\n";
		}
	}
	
	function BuildChatXML()
	{
		global $USER,$BROWSER,$GROUPS;
		$BROWSER->LoadChat($this->Config,$this->Caller);
		if($this->CurrentResponseType == DATA_RESPONSE_TYPE_KEEP_ALIVE)
			$this->CurrentResponseType = DATA_RESPONSE_TYPE_BASIC;
		if($this->GetAll)
			$this->CurrentResponseType = DATA_RESPONSE_TYPE_STATIC;
		if(!isnull($BROWSER->Chat) && !$BROWSER->Chat->Closed)
		{
			if($this->CurrentResponseType != DATA_RESPONSE_TYPE_KEEP_ALIVE && $BROWSER->Fullname != null && $BROWSER->DesiredChatGroup != null)
			{
				$USER->IsChat = true;
				$this->XMLCurrentChat =  "  <chat id=\"".base64_encode($BROWSER->Chat->Id)."\" st=\"".base64_encode($BROWSER->Chat->Activated)."\" fn=\"" . base64_encode($BROWSER->Fullname) . "\" em=\"" . base64_encode($BROWSER->Email) . "\" eq=\"" . base64_encode($BROWSER->Question) . "\" gr=\"".base64_encode($BROWSER->DesiredChatGroup)."\" co=\"" . base64_encode($BROWSER->Company) . "\">\r\n";
				
				foreach($BROWSER->Customs as $index => $value)
					if(!isnull($value))
						$this->XMLCurrentChat .=  "   <cf index=\"" . base64_encode($index) . "\">".base64_encode($value)."</cf>\r\n";
				
				$this->XMLCurrentChat .=  "   <pn id=\"" . base64_encode($BROWSER->Chat->InternalUser->SystemId) . "\">\r\n";
				if(isnull($BROWSER->Chat->Activated) && count($BROWSER->ChatRequestReceiptants) > 0)
					foreach($BROWSER->ChatRequestReceiptants as $crr_systemid)
						$this->XMLCurrentChat .=  "    <crr id=\"" . base64_encode($crr_systemid) . "\" />\r\n";
				$this->XMLCurrentChat .=  "   </pn>\r\n";
				
				if($BROWSER->Chat->Activated == 0)
				{
					$BROWSER->GetForwards();
					if($BROWSER->Forward != null && $BROWSER->Forward->Processed && CALLER_SYSTEM_ID == $BROWSER->Forward->TargetSessId)
					{
						$this->XMLCurrentChat .=  "  <forward sender=\"".base64_encode($BROWSER->Forward->SenderSystemId)."\" text=\"".base64_encode($BROWSER->Forward->Text)."\" conversation=\"".base64_encode($BROWSER->Forward->Conversation)."\" />\r\n";
						if($this->IsExternal)
							$BROWSER->Forward->Destroy();
					}
				}
				else if(isset($this->Caller->ExternalChats[$BROWSER->SystemId]) && !isnull($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest) && $this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->ReceiverUserId == $this->Caller->SessId)
				{
					if($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->Error)
						$this->XMLCurrentChat .=  "   <fupr id=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->Id)."\" fm=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->FileMask)."\" fn=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->FileName)."\" fid=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->FileId)."\" error=\"".base64_encode(true)."\" />\r\n";
					else if($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->Download)
					{
						$this->XMLCurrentChat .=  "   <fupr id=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->Id)."\" fm=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->FileMask)."\" fn=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->FileName)."\" fid=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->FileId)."\" download=\"".base64_encode(true)."\" size=\"".base64_encode(@filesize($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->GetFile()))."\" />\r\n";
						$this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->Destroy();
					}
					else if($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->Permission == PERMISSION_VOID)
						$this->XMLCurrentChat .=  "   <fupr id=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->Id)."\" fm=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->FileMask)."\" fn=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->FileName)."\" fid=\"".base64_encode($this->Caller->ExternalChats[$BROWSER->SystemId]->FileUploadRequest->FileId)."\" />\r\n";
				}
				
				$this->XMLCurrentChat .=  "  </chat>\r\n";
				$this->XMLTyping .= "<v id=\"".base64_encode($BROWSER->UserId . "~" . $BROWSER->BrowserId)."\" tp=\"".base64_encode((($BROWSER->Typing)?1:0))."\" />\r\n";
			}
			else
				$this->XMLCurrentChat = "  <chat />\r\n";
		}
	}
	
	function GetStaticInfo($found = false)
	{
		global $USER;
		$USER->ExternalStatic = new ExternalStatic($USER->UserId);
		
		foreach($USER->Browsers as $browserId => $BROWSER)
			if(isset($this->SessionFileSizes[$USER->UserId][$browserId]))
			{
				$found = true;
				break;
			}
		
		if($this->GetAll || isset($this->StaticReload[$USER->UserId]) || !$found || (getDataSetTime($this->Caller->SessionFile) <= getDataSetTime($USER->ExternalStatic->SessionFile) && !in_array($USER->UserId,$this->CurrentStatics)))
		{
			if(isset($this->StaticReload[$USER->UserId]))
				unset($this->StaticReload[$USER->UserId]);
			
			array_push($this->CurrentStatics,$USER->UserId);
			$USER->ExternalStatic->Load();
		}
		else
			$USER->ExternalStatic = null;
	}

	function RemoveFileSizes($_browsers)
	{
		foreach($this->SessionFileSizes as $userid => $browsers)
			if(is_array($browsers) && count($browsers) > 0)
			{
				foreach($browsers as $BROWSER => $size)
					if(!in_array($BROWSER,$_browsers))
						unset($this->SessionFileSizes[$userid][$BROWSER]);
			}
			else
				unset($this->SessionFileSizes[$userid]);
	}
}


?>
