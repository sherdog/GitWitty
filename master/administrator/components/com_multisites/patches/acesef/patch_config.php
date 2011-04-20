//_jms2win_begin v1.2.14
if ( defined( 'MULTISITES_ID')
  && file_exists( dirname(__FILE__) .DS. 'configuration.' .MULTISITES_ID. '.php')) {
   require_once(  dirname(__FILE__) .DS. 'configuration.' .MULTISITES_ID. '.php');
} else if ( !class_exists( 'acesef_configuration')) {
//_jms2win_end
