//_jms2win_begin v1.2.54
            if ( defined( 'MULTISITES_ID')) {
   				$file = $client->path.DS.'templates'.DS.$template.DS.'params_' . MULTISITES_ID . '.ini';
      		}
      		else {
   				$file = $client->path.DS.'templates'.DS.$template.DS.'params.ini';
            }
//_jms2win_end
/*_jms2win_undo
				$file = $client->path.DS.'templates'.DS.$template.DS.'params.ini';
  _jms2win_undo */
