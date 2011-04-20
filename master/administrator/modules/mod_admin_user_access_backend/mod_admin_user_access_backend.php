<?php
/**
* @package Admin-User-Access (mod_admin_user_access_backend)
* @version 2.1.1
* @copyright Copyright (C) 2007-2008 Carsten Engel. All rights reserved.
* @license GPL available versions: free, trial and pro
* @author http://www.pages-and-items.com
* @joomla Joomla is Free Software
*/

//no direct access
if(!defined('_VALID_MOS') && !defined('_JEXEC')){
	die('Restricted access');
}

//silly workaround for developers who install the trail version while totally ignoring 
//all warnings about that you need Ioncube installed or else it will criple the site
$fua_trial_version = 0;

if(!$fua_trial_version || ($fua_trial_version && extension_loaded('ionCube Loader'))){
	include(dirname(__FILE__).'/mod_admin_user_access_backend2.php');
}


?>