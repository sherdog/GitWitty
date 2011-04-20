//_jms2win_begin v1.2.13
if ( defined( 'MULTISITES_ID')
  && file_exists( dirname(__FILE__) .DS. 'sermoncastconfig.sermonspeaker.' .MULTISITES_ID. '.php')) {
   require_once(  dirname(__FILE__) .DS. 'sermoncastconfig.sermonspeaker.' .MULTISITES_ID. '.php');
} else  {
//_jms2win_end
