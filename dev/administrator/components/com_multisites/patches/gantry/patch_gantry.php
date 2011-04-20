//_jms2win_begin v1.2.54
      if ( defined( 'MULTISITES_ID')) {
         $ini = JPATH_SITE.DS."templates".DS.$template.DS.'params_' . MULTISITES_ID . '.ini';
         if ( !file_exists(  $ini)) {
      	   $ini = JPATH_SITE.DS."templates".DS.$template.DS.'params.ini';
         }
		}
		else {
		   $ini = JPATH_SITE.DS."templates".DS.$template.DS.'params.ini';
      }
		if (is_readable( $ini) )
//_jms2win_end
/*_jms2win_undo
     if (is_readable( JPATH_SITE.DS."templates".DS.$template.DS.'params.ini' ) )
  _jms2win_undo */
