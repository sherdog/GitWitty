//_jms2win_begin v1.2.26
if ( defined( 'MULTISITES_ID')) {
   $shURLCacheFileName = sh404SEF_FRONT_ABS_PATH.'cache/shCacheContent.' .MULTISITES_ID. '.php';
}
else {
   $shURLCacheFileName = sh404SEF_FRONT_ABS_PATH.'cache/shCacheContent.php';
}
//_jms2win_end
/*_jms2win_undo
$shURLCacheFileName = sh404SEF_FRONT_ABS_PATH.'cache/shCacheContent.php';
  _jms2win_undo */
