//_jms2win_begin v1.2.26
if ( defined( 'MULTISITES_ID')
  && file_exists( dirname(__FILE__) .DS. 'ccboard-config.' .MULTISITES_ID. '.php')) {
   require_once(  dirname(__FILE__) .DS. 'ccboard-config.' .MULTISITES_ID. '.php');
} else if ( !class_exists( 'ccboardConfig')) {
//_jms2win_end
