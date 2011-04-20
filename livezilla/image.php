<?php

/****************************************************************************************
* LiveZilla image.php
* 
* Copyright 2010 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors. 

* 
***************************************************************************************/ 

define("IN_LIVEZILLA",true);

if(!defined("LIVEZILLA_PATH"))
	define("LIVEZILLA_PATH","./");
	
@set_time_limit(30);

require(LIVEZILLA_PATH . "_definitions/definitions.inc.php");
require(LIVEZILLA_PATH . "_definitions/definitions.files.inc.php");
require(LIVEZILLA_PATH . "_lib/objects.global.users.inc.php");
require(LIVEZILLA_PATH . "_lib/functions.global.inc.php");
require(LIVEZILLA_PATH . "_definitions/definitions.dynamic.inc.php");
require(LIVEZILLA_PATH . "_definitions/definitions.protocol.inc.php");

@set_error_handler("handleError");
@error_reporting(E_ALL);

header("Connection: close");
header("Pragma: no-cache");
header("Cache-Control: no-cache, must-revalidate");

setDataProvider();
$parameters = getTargetParameters();

$html = "";
if(isset($_GET["id"]) && strpos($_GET["id"],"..") === false)
{
	$id = $_GET["id"];
	if(operatorsAvailable(0,$parameters["exclude"],$parameters["include_group"],$parameters["include_user"]) > 0)
		exit(readfile("./banner/livezilla_".$id."_1" . getExtensionById($id,true)));
	else
		exit(readfile("./banner/livezilla_".$id."_0" . getExtensionById($id,false)));
}
else if(isset($_GET["v"]))
{
	$parts = explode("<!>",base64UrlDecode(str_replace(" ","+",$_GET["v"])));
	if(count($parts) > 3 && strlen($parts[3]) > 0)
		$parts[0] = str_replace("<!--class-->","class=\\\"".$parts[3]."\\\"",$parts[0]);
	else if(count($parts) > 0)
		$parts[0] = str_replace("<!--class-->","",$parts[0]);
		
	if(count($parts) > 1 && operatorsAvailable(0,$parameters["exclude"],$parameters["include_group"],$parameters["include_user"]) > 0)
		$html = str_replace("<!--text-->",$parts[1],$parts[0]);
	else if(count($parts) > 2)
		$html = str_replace("<!--text-->",$parts[2],$parts[0]);
	exit("document.write(\"".$html."\");");
}

function getExtensionById($_id,$_online)
{
	if(($_online && @file_exists("./banner/livezilla_".$_id."_1.gif")) || (!$_online && @file_exists("./banner/livezilla_".$_id."_0.gif")))
	{
		header("Content-Type: image/gif;");
		return ".gif";
	}
	else if(($_online && @file_exists("./banner/livezilla_".$_id."_1.png")) || (!$_online && @file_exists("./banner/livezilla_".$_id."_0.png")))
	{
		header("Content-Type: image/png;");
		return ".png";
	}
	else
	{
		header("HTTP/1.0 404 Not Found");
		exit();
	}
}
unloadDataProvider();
?>