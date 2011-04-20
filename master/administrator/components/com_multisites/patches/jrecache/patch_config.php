//_jms2win_begin v1.2.12
if ( defined( 'MULTISITES_ID')
  && file_exists( dirname(__FILE__) .DS. 'jrecache.config.' .MULTISITES_ID. '.php')) {
   require_once(  dirname(__FILE__) .DS. 'jrecache.config.' .MULTISITES_ID. '.php');
} else  {
//_jms2win_end
