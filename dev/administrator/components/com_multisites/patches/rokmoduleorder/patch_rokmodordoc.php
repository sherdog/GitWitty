//_jms2win_begin v1.2.21
		if ( defined( 'MULTISITES_ID')) {
		   $ini = $directory.DS.$template.DS.'params_' . MULTISITES_ID . '.ini';
		   if ( !file_exists(  $ini)) {
   		   $ini = $directory.DS.$template.DS.'params.ini';
		   }
		}
		else {
		   $ini = $directory.DS.$template.DS.'params.ini';
		}
		if (is_readable( $ini) )
		{
			$content = file_get_contents($ini);
//_jms2win_end
/*_jms2win_undo
		if (is_readable( $directory.DS.$template.DS.'params.ini' ) )
		{
			$content = file_get_contents($directory.DS.$template.DS.'params.ini');
  _jms2win_undo */
