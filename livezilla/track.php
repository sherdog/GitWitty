<?php
/****************************************************************************************
* LiveZilla track.php
* 
* Copyright 2010 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();

require(LIVEZILLA_PATH . "_lib/functions.tracking.inc.php");

if(!getAvailability())
	die();

if(isset($_GET[GET_TRACK_USERID]) && !isnull($_GET[GET_TRACK_USERID]))
{
	define("CALLER_BROWSER_ID",htmlentities(getParam(GET_TRACK_BROWSERID)));
	define("CALLER_USER_ID",htmlentities(getParam(GET_TRACK_USERID)));

	if(isnull(getCookieValue("userid")) || (!isnull(getCookieValue("userid")) && getCookieValue("userid") != CALLER_USER_ID))
		setCookieValue("userid",CALLER_USER_ID);
}
else if(!isnull(getCookieValue("userid")))
{
	define("CALLER_BROWSER_ID",getId(USER_ID_LENGTH));
	define("CALLER_USER_ID",htmlentities(getCookieValue("userid")));
}
if(!defined("CALLER_USER_ID"))
{
	define("CALLER_USER_ID",getId(USER_ID_LENGTH));
	define("CALLER_BROWSER_ID",getId(USER_ID_LENGTH));
	if(isnull(getCookieValue("userid")) || (!isnull(getCookieValue("userid")) && getCookieValue("userid") != CALLER_USER_ID))
		setCookieValue("userid",CALLER_USER_ID);
}

$EXTERNALUSER = new UserExternal(CALLER_USER_ID);
$EXTERNALUSER->LoadStaticInformation();
if(isset($_GET[GET_TRACK_OUTPUT_TYPE]) && ($_GET[GET_TRACK_OUTPUT_TYPE] == "jscript" || $_GET[GET_TRACK_OUTPUT_TYPE] == "jcrpt"))
{
	if(!isset($_GET[GET_TRACK_NO_SEARCH_ENGINE]))
	{
		header("Location: http://www.livezilla.net");
		exit(getFile(TEMPLATE_HTML_SUPPORT));
	}
	$TRACKINGSCRIPT = getFile(TEMPLATE_SCRIPT_GLOBAL) . getFile(TEMPLATE_SCRIPT_TRACK) . getFile(TEMPLATE_SCRIPT_BOX);
	$TRACKINGSCRIPT = str_replace("<!--server_id-->",substr(md5($CONFIG["gl_lzid"]),5,5),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--file_chat-->",FILE_CHAT,$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--server-->",LIVEZILLA_URL,$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--area_code-->",(isset($_GET[GET_TRACK_SPECIAL_AREA_CODE])) ? htmlentities($_GET[GET_TRACK_SPECIAL_AREA_CODE],ENT_QUOTES,"UTF-8") : "",$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--browser_id-->",CALLER_BROWSER_ID,$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--user_id-->",CALLER_USER_ID,$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--connection_error_span-->",CONNECTION_ERROR_SPAN,$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--poll_frequency-->",$CONFIG["poll_frequency_tracking"],$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--timeout-->",$CONFIG["timeout_track"],$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--height-->",$CONFIG["wcl_window_height"],$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--width-->",$CONFIG["wcl_window_width"],$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = geoReplacements($TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--geo_resolute-->",parseBool(!isSSpanFile() && !dataSetExists($EXTERNALUSER->ExternalStatic->SessionFile) && !isnull($CONFIG["wcl_geo_tracking"]) && !(!isnull(getCookieValue("geo_data")) && getCookieValue("geo_data") > time()-2592000)),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--alert_html-->",base64_encode(getAlertTemplate()),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--user_name-->",getParam(GET_EXTERN_USER_NAME),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--user_email-->",getParam(GET_EXTERN_USER_EMAIL),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--user_company-->",getParam(GET_EXTERN_USER_COMPANY),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--user_question-->",getParam(GET_EXTERN_USER_QUESTION),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--user_header-->",getParam(GET_EXTERN_USER_HEADER),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--user_customs-->",getJSCustomArray(),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--custom_params-->",getCustomParams(),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--is_ie-->",parseBool((!isnull(getServerParam('HTTP_USER_AGENT')) && (strpos(getServerParam('HTTP_USER_AGENT'), 'MSIE') !== false))),$TRACKINGSCRIPT);

	if(isset($_GET[GET_EXTERN_USER_NAME]))
		setCookieValue("login_111",secPrev(base64UrlDecode($_GET[GET_EXTERN_USER_NAME])));
	if(isset($_GET[GET_EXTERN_USER_EMAIL]))
		setCookieValue("login_112",secPrev(base64UrlDecode($_GET[GET_EXTERN_USER_EMAIL])));
	if(isset($_GET[GET_EXTERN_USER_COMPANY]))
		setCookieValue("login_113",secPrev(base64UrlDecode($_GET[GET_EXTERN_USER_COMPANY])));
}
else
{
	$TRACKINGSCRIPT = "lz_tracking_set_sessid(\"".CALLER_USER_ID."\",\"".CALLER_BROWSER_ID."\");";
	if(isset($_GET[GET_TRACK_BROWSERID]) && isset($_GET[GET_TRACK_START]) && isset($_GET[GET_TRACK_URL]))
	{
		if(!isnull($_GET[GET_TRACK_URL]) && strpos(base64UrlDecode($_GET[GET_TRACK_URL]),GET_INTERN_COBROWSE) !== false)
			exit("lz_tracking_stop_tracking();");
	
		$BROWSER = new ExternalBrowser(CALLER_BROWSER_ID,CALLER_USER_ID);

		getData(false,false,false,true,true);
		define("IS_FILTERED",$FILTERS->Match(getIP(),formLanguages(((!isnull(getServerParam("HTTP_ACCEPT_LANGUAGE"))) ? getServerParam("HTTP_ACCEPT_LANGUAGE") : "")),CALLER_USER_ID));
		define("IS_FLOOD",(!dataSetExists($BROWSER->SessionFile) && isFlood()));

		if(!getAvailability() || IS_FILTERED || IS_FLOOD)
		{
			$BROWSER->Destroy();
			exit("lz_tracking_stop_tracking();");
		}
		if(dataSetExists($BROWSER->SessionFile))
			$BROWSER->Load();

		$BROWSER->Customs = getCustomArray();
		
		if(isset($_GET[GET_EXTERN_USER_NAME]) && !isnull($_GET[GET_EXTERN_USER_NAME]))
			$BROWSER->Fullname = substr(secPrev(base64UrlDecode($_GET[GET_EXTERN_USER_NAME])),0,32);
		
		if(isset($_GET[GET_EXTERN_USER_EMAIL]) && !isnull($_GET[GET_EXTERN_USER_EMAIL]))
			$BROWSER->Email = substr(secPrev(base64UrlDecode($_GET[GET_EXTERN_USER_EMAIL])),0,50);
		
		if(isset($_GET[GET_EXTERN_USER_COMPANY]) && !isnull($_GET[GET_EXTERN_USER_COMPANY]))
			$BROWSER->Company = substr(secPrev(base64UrlDecode($_GET[GET_EXTERN_USER_COMPANY])),0,50);
			
		if(isset($_GET[GET_EXTERN_USER_QUESTION]) && !isnull($_GET[GET_EXTERN_USER_QUESTION]))
			$BROWSER->Question = secPrev(base64UrlDecode($_GET[GET_EXTERN_USER_QUESTION]));
			
		$count = count($BROWSER->History);
		if(!dataSetExists($EXTERNALUSER->ExternalStatic->SessionFile))
			createStaticFile($EXTERNALUSER,Array($_GET[GET_TRACK_RESOLUTION_WIDTH],$_GET[GET_TRACK_RESOLUTION_HEIGHT]),$_GET[GET_TRACK_COLOR_DEPTH],$_GET[GET_TRACK_TIMEZONE_OFFSET],((isset($_GET[GEO_LATITUDE]))?$_GET[GEO_LATITUDE]:""),((isset($_GET[GEO_LONGITUDE]))?$_GET[GEO_LONGITUDE]:""),((isset($_GET[GEO_COUNTRY_ISO_2]))?$_GET[GEO_COUNTRY_ISO_2]:""),((isset($_GET[GEO_CITY]))?$_GET[GEO_CITY]:""),((isset($_GET[GEO_REGION]))?$_GET[GEO_REGION]:""),((isset($_GET[GEO_TIMEZONE]))?$_GET[GEO_TIMEZONE]:""),((isset($_GET[GEO_ISP]))?$_GET[GEO_ISP]:""),((isset($_GET[GEO_SSPAN]))?$_GET[GEO_SSPAN]:""),((isset($_GET[GEO_RESULT_ID]))?$_GET[GEO_RESULT_ID]:""));

		if(isset($_GET[GET_TRACK_CLOSE_CHAT_WINDOW]))
		{
			$chat = new ExternalChat($EXTERNALUSER->UserId,$_GET[GET_TRACK_CLOSE_CHAT_WINDOW]);
			$chat->Load();
			$chat->Destroy();
		}

		$BROWSER->LastActive = time();
		if(isnull($BROWSER->FirstActive))
			$BROWSER->FirstActive = time();
			
		$BROWSER->Referrer = (!isset($BROWSER->Referrer)) ? isset($_GET[GET_TRACK_REFERRER]) ? trim(slashesStrip(base64UrlDecode($_GET[GET_TRACK_REFERRER]))) : "" : $BROWSER->Referrer;
		$url = (isset($_GET[GET_TRACK_URL])) ? substr(base64UrlDecode($_GET[GET_TRACK_URL]),0,1024) : "";
		
		if(!isset($BROWSER->History))
			$BROWSER->History = array();
		if(count($BROWSER->History) == 0 || (count($BROWSER->History) > 0 && $BROWSER->History[count($BROWSER->History)-1][1] != $url))
			$BROWSER->History[] = array(time() ,$url ,((isset($_GET[GET_TRACK_SPECIAL_AREA_CODE])) ? base64UrlDecode($_GET[GET_TRACK_SPECIAL_AREA_CODE]) : ""),false,base64_encode(base64UrlDecode(@$_GET[GET_EXTERN_DOCUMENT_TITLE])));

		if(count($BROWSER->History) > DATA_URL_STORAGE_AMOUNT)
		{
			array_shift($BROWSER->History);
			define("ARRAY_MAX_SIZE",true);
		}

		$BROWSER->LoadWebsitePush();
		$BROWSER->LoadChatRequest();
		$BROWSER->LoadAlerts();
	
		$TRACKINGSCRIPT .= triggerEvents();
		$TRACKINGSCRIPT .= processActions();
		
		if(is_numeric($_GET[GET_TRACK_START]))
			$TRACKINGSCRIPT .= "lz_tracking_callback(" . $CONFIG["poll_frequency_tracking"] . ",'" . $_GET[GET_TRACK_START] . "');";
		
		if($count != count($BROWSER->History) || defined("ARRAY_MAX_SIZE"))
			$BROWSER->Save();
		else
			$BROWSER->KeepAlive();

		if(isset($CONFIG["gl_hide_inactive"]) && $CONFIG["gl_hide_inactive"] && $BROWSER->History[count($BROWSER->History)-1][0] < (time()-ACTIVE_TIME))
			exit("lz_tracking_stop_tracking();");
	}
}
?>
