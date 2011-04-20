<?php
/****************************************************************************************
* LiveZilla extern.php
* 
* Copyright 2010 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();
	
require(LIVEZILLA_PATH . "_lib/objects.external.inc.php");
require(LIVEZILLA_PATH . "_lib/functions.external.inc.php");

if(isset($_POST[POST_EXTERN_SERVER_ACTION]))
{
	languageSelect();
	getData(false,true,false,true);
	$externalUser = new UserExternal(AJAXDecode($_POST[POST_EXTERN_USER_USERID]));
	$externalUser->ExternalStatic = new ExternalStatic($externalUser->UserId);
	array_push($externalUser->Browsers,new ExternalChat($externalUser->UserId,AJAXDecode($_POST[POST_EXTERN_USER_BROWSERID])));
	
	define("IS_FILTERED",$FILTERS->Match(getIP(),formLanguages(((getServerParam("HTTP_ACCEPT_LANGUAGE") != null) ? getServerParam("HTTP_ACCEPT_LANGUAGE") : "")),AJAXDecode($_POST[POST_EXTERN_USER_USERID])));
	define("IS_FLOOD",(!dataSetExists($externalUser->Browsers[0]->SessionFile) && isFlood()));

	if(dataSetExists($externalUser->Browsers[0]->SessionFile))
		$externalUser->Browsers[0]->Load();
		
	$externalUser->ExternalStatic->Language = (getServerParam("HTTP_ACCEPT_LANGUAGE") != null) ? getServerParam("HTTP_ACCEPT_LANGUAGE") : "";
	$externalUser->Browsers[0]->LoadChat($CONFIG,null);
	
	if($_POST[POST_EXTERN_SERVER_ACTION] == EXTERN_ACTION_LISTEN)
		$externalUser = listen($externalUser);
	else if($_POST[POST_EXTERN_SERVER_ACTION] == EXTERN_ACTION_MAIL)
	{
		getData(false,true,false,false);
		if($externalUser->SaveTicket(AJAXDecode($_POST[POST_EXTERN_USER_GROUP]),$CONFIG) && ($CONFIG["gl_scom"] != null || $CONFIG["gl_sgom"] != null))
			$externalUser->SendCopyOfMail(AJAXDecode($_POST[POST_EXTERN_USER_GROUP]),$CONFIG,$GROUPS);
	}
	else if($_POST[POST_EXTERN_SERVER_ACTION] == EXTERN_ACTION_RATE)
	{
		getData(true,false,false,false);
		$externalUser->SaveRate(AJAXDecode($_POST[POST_EXTERN_REQUESTED_INTERNID]),$CONFIG);
	}
	else
	{
		if($externalUser->Browsers[0]->Chat != null)
		{
			$externalUser->Browsers[0]->DestroyChatFiles();
			$externalUser->Browsers[0]->Chat->ExternalDestroy();
		}
		unregisterChat(((isset($_POST[POST_EXTERN_CHAT_ID]))?AJAXDecode($_POST[POST_EXTERN_CHAT_ID]):""));
		$externalUser->Browsers[0]->Waiting = false;
		$externalUser->Browsers[0]->WaitingMessageDisplayed = null;
		
		if($_POST[POST_EXTERN_SERVER_ACTION] == EXTERN_ACTION_RELOAD_GROUPS)
		{
			if(isset($_GET[GET_EXTERN_USER_NAME]) && !isnull($_GET[GET_EXTERN_USER_NAME]))
				$externalUser->Browsers[0]->Fullname = base64UrlDecode($_GET[GET_EXTERN_USER_NAME]);
		
			if(isset($_GET[GET_EXTERN_USER_EMAIL]) && !isnull($_GET[GET_EXTERN_USER_EMAIL]))
				$externalUser->Browsers[0]->Email = base64UrlDecode($_GET[GET_EXTERN_USER_EMAIL]);
			
			if(isset($_GET[GET_EXTERN_USER_COMPANY]) && !isnull($_GET[GET_EXTERN_USER_COMPANY]))
				$externalUser->Browsers[0]->Company = base64UrlDecode($_GET[GET_EXTERN_USER_COMPANY]);
				
			if(isset($_GET[GET_EXTERN_USER_QUESTION]) && !isnull($_GET[GET_EXTERN_USER_QUESTION]))
				$externalUser->Browsers[0]->Question = base64UrlDecode($_GET[GET_EXTERN_USER_QUESTION]);
				
			$externalUser->Browsers[0]->Customs = getCustomArray();
			$externalUser = reloadGroups($externalUser);
		}
		else
		{
			$externalUser->Browsers[0]->Destroy();
			exit();
		}
	}

	if(!dataSetExists($externalUser->ExternalStatic->SessionFile) && isset($_POST[POST_EXTERN_RESOLUTION_WIDTH]))
		createStaticFile($externalUser,Array(AJAXDecode($_POST[POST_EXTERN_RESOLUTION_WIDTH]),AJAXDecode($_POST[POST_EXTERN_RESOLUTION_HEIGHT])),AJAXDecode($_POST[POST_EXTERN_COLOR_DEPTH]),AJAXDecode($_POST[POST_EXTERN_TIMEZONE_OFFSET]),((isset($_POST[GEO_LATITUDE]))?AJAXDecode($_POST[GEO_LATITUDE]):""),((isset($_POST[GEO_LONGITUDE]))?AJAXDecode($_POST[GEO_LONGITUDE]):""),((isset($_POST[GEO_COUNTRY_ISO_2]))?AJAXDecode($_POST[GEO_COUNTRY_ISO_2]):""),((isset($_POST[GEO_CITY]))?AJAXDecode($_POST[GEO_CITY]):""),((isset($_POST[GEO_REGION]))?AJAXDecode($_POST[GEO_REGION]):""),((isset($_POST[GEO_TIMEZONE]))?AJAXDecode($_POST[GEO_TIMEZONE]):""),((isset($_POST[GEO_ISP]))?AJAXDecode($_POST[GEO_ISP]):""),((isset($_POST[GEO_SSPAN]))?AJAXDecode($_POST[GEO_SSPAN]):""),((isset($_POST[GEO_RESULT_ID]))?AJAXDecode($_POST[GEO_RESULT_ID]):""));
	if(isset($_GET[GET_TRACK_SPECIAL_AREA_CODE]))
		$externalUser->Browsers[0]->Code = base64UrlDecode($_GET[GET_TRACK_SPECIAL_AREA_CODE]);
	
	if(IS_FILTERED)
		$externalUser->Browsers[0]->Destroy();
	else
		$externalUser->Browsers[0]->Save();
	$EXTERNSCRIPT = $externalUser->Response;
}
?>
