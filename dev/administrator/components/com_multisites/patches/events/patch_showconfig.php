//_jms2win_begin v1.1.10
		if ( defined( 'MULTISITES_ID')) {
         jimport( 'joomla.filesystem.file');
   		$slaveconfigfile 	= $mosConfig_absolute_path . '/administrator/components/' . $option . '/events_config.' . MULTISITES_ID . '.ini.php';
   		if ( !JFile::exists($slaveconfigfile)) {
   		   JFile::copy( $configfile, $slaveconfigfile);
   		}
   		$configfile 	= $slaveconfigfile;
		}
//_jms2win_end
