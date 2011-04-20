<?php
$domains = array( 'http://tagmyvideo.com' , 'http://www.tagmyvideo.com');
$indexDomains = array( 'http://tagmyvideo.com' , 'http://www.tagmyvideo.com');
$newDBInfo = array( 'status' => 'Confirmed', 'owner_id' => '64', 'fromTemplateID' => 'pro', 'toDBHost' => 'localhost', 'toDBName' => 'gitwitty_tagmyvid', 'toDBUser' => 'gitwitty_dbadmin', 'toDBPsw' => 'db@dm1n', 'toPrefix' => 'tagmyvid_', 'toFTP_enable' => '0', 'toFTP_host' => 'ftp.gitwitty.com', 'toFTP_port' => '21', 'toFTP_user' => 'tagmyvid', 'toFTP_psw' => 'tagmyvid123', 'toFTP_rootpath' => '/home/gitwitty/public_html/multisites/');
$config_dirs = array( 'cache_dir' => '/home/gitwitty/public_html/master/multisites/tagmyvid/cache', 'symboliclinks' => array( 'administrator' => array( 'action' => 'SL'),
          'cache' => array( 'action' => 'mkdir', 'readOnly' => '1'),
          'cgi-bin' => array( 'action' => 'SL'),
          'components' => array( 'action' => 'ignore'),
          'images' => array( 'action' => 'special'),
          'includes' => array( 'action' => 'SL'),
          'installation' => array( 'action' => 'dirlinks'),
          'language' => array( 'action' => 'SL'),
          'libraries' => array( 'action' => 'SL'),
          'logs' => array( 'action' => 'mkdir', 'readOnly' => '1'),
          'media' => array( 'action' => 'SL'),
          'modules' => array( 'action' => 'SL'),
          'multisites' => array( 'action' => 'ignore'),
          'plugins' => array( 'action' => 'ignore'),
          'templates' => array( 'action' => 'special'),
          'tmp' => array( 'action' => 'mkdir', 'readOnly' => '1'),
          'xmlrpc' => array( 'action' => 'SL'),
          '.ftpquota' => array( 'action' => 'SL'),
          '.htaccess' => array( 'action' => 'SL'),
          'htaccess.txt' => array( 'action' => 'SL'),
          'robots.txt' => array( 'action' => 'SL'),
          'index.php' => array( 'action' => 'redirect', 'readOnly' => '1'),
          'index2.php' => array( 'action' => 'redirect', 'readOnly' => '1')), 'dbsharing' => array( 'dbsh_UserSharing' => 'none'));
?>