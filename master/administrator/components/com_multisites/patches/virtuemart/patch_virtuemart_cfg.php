//_jms2win_begin v1.1.0
if ( defined( 'MULTISITES_ID') && file_exists( dirname(__FILE__) .DS. 'virtuemart.' .MULTISITES_ID. '.cfg.php')) {
   include_once( dirname(__FILE__) .DS. 'virtuemart.' .MULTISITES_ID. '.cfg.php');
} else {
//_jms2win_end
