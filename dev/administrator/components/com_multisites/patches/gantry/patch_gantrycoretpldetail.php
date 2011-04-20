//_jms2win_begin v1.2.54
      if ( defined( 'MULTISITES_ID')) {
		   $params_file = $gantry->templatePath.DS.'params_' . MULTISITES_ID . '.ini';
         if ( !file_exists(  $params_file)) {
   		   $params_file = $gantry->templatePath.DS.'params.ini';
         }
		}
		else {
		   $params_file = $gantry->templatePath.DS.'params.ini';
   	}
//_jms2win_end
/*_jms2win_undo
      $params_file = $gantry->templatePath.DS.'params.ini';
  _jms2win_undo */
