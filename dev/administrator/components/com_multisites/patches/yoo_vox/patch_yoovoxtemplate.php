//_jms2win_begin v1.2.29
		if ( defined( 'MULTISITES_ID')) {
		   $ini = dirname(dirname(dirname(__FILE__))).'/params_' . MULTISITES_ID . '.ini';
		   if ( !file_exists(  $ini)) {
   		   $ini = dirname(dirname(dirname(__FILE__))).'/params.ini';
		   }
		}
		else {
		   $ini = dirname(dirname(dirname(__FILE__))).'/params.ini';
		}
		$this->params->loadFile( $ini);
//_jms2win_end
/*_jms2win_undo
		$this->params->loadFile(dirname(dirname(dirname(__FILE__))).'/params.ini');
  _jms2win_undo */
