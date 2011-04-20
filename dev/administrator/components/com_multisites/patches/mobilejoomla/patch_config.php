//_jms2win_begin v1.2.45
if ( defined( 'MULTISITES_ID')
  && file_exists( dirname(__FILE__) .DS. 'config.' .MULTISITES_ID. '.php')) {
   require_once(  dirname(__FILE__) .DS. 'config.' .MULTISITES_ID. '.php');
} else if ( empty( $MobileJoomla_Settings)) {
//_jms2win_end
