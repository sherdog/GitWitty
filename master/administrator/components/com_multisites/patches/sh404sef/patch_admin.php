//_jms2win_begin v1.2.26
    if ( defined( 'MULTISITES_ID')) {
      if (file_exists(JPATH_ROOT.'/components/com_sh404sef/cache/shCacheContent.' .MULTISITES_ID. '.php')) {
         unlink(JPATH_ROOT.'/components/com_sh404sef/cache/shCacheContent.' .MULTISITES_ID. '.php');
      }
    } else
//_jms2win_end
