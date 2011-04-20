<?php
//_jms2win_begin v1.2.14
if ( !defined( 'MULTISITES_ID')) {
   if ( !defined( 'JPATH_MULTISITES')) define( 'JPATH_MULTISITES', (defined( 'JPATH_ROOT') ? JPATH_ROOT : dirname(__FILE__)) .DIRECTORY_SEPARATOR. 'multisites');
   if ( !defined( '_EDWIN2WIN_')) define( '_EDWIN2WIN_', true);
   @include( (defined( 'JPATH_ROOT') ? JPATH_ROOT : dirname(__FILE__)) .DIRECTORY_SEPARATOR. 'includes' .DIRECTORY_SEPARATOR. 'multisites.php');
   if ( class_exists( 'Jms2Win')) Jms2Win::matchSlaveSite();
}
if ( (!isset( $MULTISITES_FORCEMASTER) || !$MULTISITES_FORCEMASTER)
  && defined( 'MULTISITES_ID')
  && file_exists(MULTISITES_CONFIG_PATH .DIRECTORY_SEPARATOR. 'configuration.php')) {
   require_once( MULTISITES_CONFIG_PATH .DIRECTORY_SEPARATOR. 'configuration.php');
} else if ( !class_exists( 'JConfig')) {
//_jms2win_end
class JConfig {
	var $offline = '0';
	var $editor = 'idoeditor';
	var $list_limit = '20';
	var $helpurl = 'http://help.joomla.org';
	var $debug = '0';
	var $debug_lang = '0';
	var $sef = '1';
	var $sef_rewrite = '1';
	var $sef_suffix = '1';
	var $feed_limit = '10';
	var $feed_email = 'author';
	var $secret = 'CFcNZgCQ6OZqP9Zn';
	var $gzip = '0';
	var $error_reporting = '-1';
	var $xmlrpc_server = '0';
	var $log_path = '/home/gitwitty/public_html/master/logs';
	var $tmp_path = '/home/gitwitty/public_html/master/tmp';
	var $live_site = '';
	var $force_ssl = '0';
	var $offset = '0';
	var $caching = '0';
	var $cachetime = '15';
	var $cache_handler = 'file';
	var $memcache_settings = array();
	var $ftp_enable = '0';
	var $ftp_host = 'ftp.gitwitty.com';
	var $ftp_port = '21';
	var $ftp_user = 'gitwitty';
	var $ftp_pass = 'p!p@cw!tty';
	var $ftp_root = '/public_html/master';
	var $dbtype = 'mysql';
	var $host = 'localhost';
	var $user = 'gitwitty_dbadmin';
	var $db = 'gitwitty_master';
	var $dbprefix = 'jos_';
	var $mailer = 'mail';
	var $mailfrom = 'noone@website.com';
	var $fromname = 'Name';
	var $sendmail = '/usr/sbin/sendmail';
	var $smtpauth = '0';
	var $smtpsecure = 'none';
	var $smtpport = '25';
	var $smtpuser = '';
	var $smtppass = '';
	var $smtphost = 'localhost';
	var $MetaAuthor = '1';
	var $MetaTitle = '1';
	var $lifetime = '15';
	var $session_handler = 'database';
	var $password = 'db@dm1n';
	var $sitename = '';
	var $MetaDesc = 'Joomla! - the dynamic portal engine and content management system';
	var $MetaKeys = 'joomla, Joomla';
	var $offline_message = 'This site is down for maintenance. Please check back again soon.';
}
//_jms2win_begin v1.2.14
}
//_jms2win_end
?>
