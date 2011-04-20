//_jms2win_begin v1.1.6
	if ( defined( 'MULTISITES_ID')) {
   	$configfile          =	$_CB_adminpath."/ue_config_" . MULTISITES_ID . ".php";
   	$configfile_master   =	$_CB_adminpath."/ue_config.php";
		// If the slave site params_ini file does not exists and a master one exists
      jimport('joomla.filesystem.file');
      if ( !JFile::exists( $configfile) && JFile::exists( $configfile_master)) {
         // Duplicate the master file as slave site
         JFile::copy( $configfile_master, $configfile);
      }
	}
	else {
   	$configfile			=	$_CB_adminpath."/ue_config.php";
	}
//_jms2win_end
/*_jms2win_undo
	$configfile			=	$_CB_adminpath."/ue_config.php";
  _jms2win_undo */
