//_jms2win_begin v1.2.26
    if ( defined( 'MULTISITES_ID')) {
       $cacheFile = sh404SEF_FRONT_ABS_PATH.'cache/shCacheContent.' .MULTISITES_ID. '.php';
    }
    else {
       $cacheFile = sh404SEF_FRONT_ABS_PATH.'cache/shCacheContent.php';
    }
//_jms2win_end
/*_jms2win_undo
    $cacheFile = sh404SEF_FRONT_ABS_PATH.'cache/shCacheContent.php';
  _jms2win_undo */
