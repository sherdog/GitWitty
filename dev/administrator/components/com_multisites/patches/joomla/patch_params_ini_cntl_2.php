//_jms2win_begin v1.2.37 (patch2)
		if ( defined( 'MULTISITES_ID')) {
   		$file = $client->path.DS.'templates'.DS.$template.DS.'params_' .MULTISITES_ID .'.ini';
   		$file_master = $client->path.DS.'templates'.DS.$template.DS.'params.ini';
   		// If the slave site params_ini file does not exists and a master one exists
         jimport('joomla.filesystem.file');
         if ( !JFile::exists( $file) && JFile::exists( $file_master)) {
            // Duplicate the master file as slave site
            JFile::copy( $file_master, $file);
         }
		}
		else {
   		$file = $client->path.DS.'templates'.DS.$template.DS.'params.ini';
		}
//_jms2win_end
/*_jms2win_undo
		$file = $client->path.DS.'templates'.DS.$template.DS.'params.ini';
  _jms2win_undo */
