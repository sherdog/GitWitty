//_jms2win_begin v1.1.10
		if ( defined( 'MULTISITES_ID')) {
   		return dirname(dirname(__FILE__)) . '/' . 'events_config.' . MULTISITES_ID . '.ini.php';
		}
//_jms2win_end
