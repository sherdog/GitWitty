<?php
/****************************************************************************************
* LiveZilla functions.index.inc.php
* 
* Copyright 2010 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();

function getFolderPermissions()
{
	global $LZLANG,$CONFIG;
	$message = null;
	$directories = Array(PATH_UPLOADS,PATH_IMAGES,PATH_BANNER,PATH_CONFIG,PATH_USERS,PATH_GROUPS,PATH_LOG);
	foreach($directories as $key => $dir)
	{
		$result = testDirectory($dir);
			if(!$result)
				$message .= $LZLANG["index_no_write_access"] . " (" . $dir . ")<br>";
	}
	
	if(!isnull($message))
	{
		$message = "<span class=\"lz_index_error_cat\">" . $LZLANG["index_write_access"] . ":<br></span> <span class=\"lz_index_red\">" . $message . "</span><a href=\"".CONFIG_LIVEZILLA_FAQ."en/#changepermissions\" class=\"lz_index_helplink\" target=\"_blank\">".$LZLANG["index_solve"]."</a>";
	}
	return str_replace($CONFIG["gl_lzid"],"*****",$message);
}

function getMySQL()
{
	if(!function_exists("mysql_real_escape_string"))
		return "<span class=\"lz_index_error_cat\">MySQL:<br></span> <span class=\"lz_index_red\">No MySQL installed on this server!</span>";
	else
		return null;
}

function getPhpVersion()
{
	global $LZLANG;
	$message = null;
	if(!checkPhpVersion(PHP_NEEDED_MAJOR,PHP_NEEDED_MINOR,PHP_NEEDED_BUILD))
		$message = "<span class=\"lz_index_error_cat\">PHP-Version:<br></span> <span class=\"lz_index_red\">" . str_replace("<!--version-->",PHP_NEEDED_MAJOR . "." . PHP_NEEDED_MINOR . "." . PHP_NEEDED_BUILD,$LZLANG["index_phpversion_needed"]) . "</span>";
	return $message;
}
?>
