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
	public $offline = '0';
	public $offline_message = 'This site is down for maintenance.<br /> Please check back again soon.';
	public $sitename = 'GitWitty';
	public $editor = 'tinymce';
	public $list_limit = '20';
	public $access = '1';
	public $debug = '0';
	public $debug_lang = '0';
	public $dbtype = 'mysql';
	public $host = 'localhost';
	public $user = 'gitwitty_dbadmin';
	public $password = 'db@dm1n';
	public $db = 'gitwitty_dev';
	public $dbprefix = 'jos_';
	public $live_site = '';
	public $secret = 'pY7xkbLsHxrUYEIY';
	public $gzip = '0';
	public $error_reporting = '-1';
	public $helpurl = 'http://help.joomla.org/proxy/index.php?option=com_help&amp;keyref=Help{major}{minor}:{keyref}';
	public $ftp_host = '127.0.0.1';
	public $ftp_port = '21';
	public $ftp_user = '';
	public $ftp_pass = '';
	public $ftp_root = '';
	public $ftp_enable = '1';
	public $offset = 'UTC';
	public $offset_user = 'UTC';
	public $mailer = 'mail';
	public $mailfrom = 'mikes@pipac.com';
	public $fromname = 'GitWitty';
	public $sendmail = '/usr/sbin/sendmail';
	public $smtpauth = '0';
	public $smtpuser = '';
	public $smtppass = '';
	public $smtphost = 'localhost';
	public $smtpsecure = 'none';
	public $smtpport = '25';
	public $caching = '0';
	public $cache_handler = 'file';
	public $cachetime = '15';
	public $MetaDesc = '';
	public $MetaKeys = '';
	public $MetaTitle = '1';
	public $MetaAuthor = '1';
	public $sef = '1';
	public $sef_rewrite = '0';
	public $sef_suffix = '0';
	public $unicodeslugs = '0';
	public $feed_limit = '10';
	public $log_path = '/home/gitwitty/public_html/dev/logs';
	public $tmp_path = '/home/gitwitty/public_html/dev/tmp';
	public $lifetime = '15';
	public $session_handler = 'database';
}//_jms2win_begin
}
//_jms2win_end
