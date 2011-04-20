//_jms2win_begin v1.2.54
         if ( defined( 'MULTISITES_ID')) {
      		$file = T3Path::path(T3_TEMPLATE).DS.'params_' . MULTISITES_ID . '.ini';
            if ( !file_exists( $file)) {
         		$file = T3Path::path(T3_TEMPLATE).DS.'params.ini';
            }
   		}
   		else {
   			$file = T3Path::path(T3_TEMPLATE).DS.'params.ini';
         }
//_jms2win_end
/*_jms2win_undo
			$file = T3Path::path(T3_TEMPLATE).DS.'params.ini';
  _jms2win_undo */
