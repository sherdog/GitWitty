//_jms2win_begin v1.2.41
    if ( defined( 'MULTISITES_ID')) {
       if (JFile::exists( JPATH_ROOT.'/components/com_sh404sef/cache/shCacheContent.' .MULTISITES_ID. '.php')) {
         JFile::delete( JPATH_ROOT.'/components/com_sh404sef/cache/shCacheContent.' .MULTISITES_ID. '.php');
       }
    } else
//_jms2win_end
