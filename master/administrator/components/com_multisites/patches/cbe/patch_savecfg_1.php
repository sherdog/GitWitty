//_jms2win_begin v1.2.52
	global $mainframe;

	//Add code to check if config file is writeable.
	$patchBegin = '';
	$patchEnd   = '';
	if ( defined( 'MULTISITES_ID')) {
   	$configfile          =	"components/com_cbe/enhanced_admin/enhanced_config_" . MULTISITES_ID . ".php";
   	$configfile_master   =	"components/com_cbe/enhanced_admin/enhanced_config.php";
		// If the slave site params_ini file does not exists and a master one exists
      jimport('joomla.filesystem.file');
      if ( !JFile::exists( $configfile) && JFile::exists( $configfile_master)) {
         // Duplicate the master file as slave site
         JFile::copy( $configfile_master, $configfile);
      }
	}
	else {
   	$configfile = "components/com_cbe/enhanced_admin/enhanced_config.php";
   	$patchBegin = "//_jms2win_begin v1.2.52\n"
   	            . "if ( defined( 'MULTISITES_ID') && file_exists( dirname( __FILE__).DS.'enhanced_config_' . MULTISITES_ID . '.php')) {\n"
   	            . "   include( dirname( __FILE__).DS.'enhanced_config_' . MULTISITES_ID . '.php');\n"
   	            . "} else {\n"
                  . "//_jms2win_end\n"
   	            ;
   	$patchEnd   = "//_jms2win_begin v1.2.52\n"
   	            . "}\n"
   	            . "//_jms2win_end\n"
   	            ;
	}

	@chmod ($configfile, 0766);
	if (!is_writable($configfile)) {
		//mosRedirect("index2.php?option=$option", "FATAL ERROR: Config File Not writeable" );
		$mainframe->redirect( "index.php?option=$option", "FATAL ERROR: Config File not writeable");

	}

	$txt = '<'.'?php'."\n";
	$txt .= $patchBegin;
	foreach ($_POST as $k=>$v) {
		if (strpos( $k, 'cfg_' ) === 0) {
			if (!get_magic_quotes_gpc()) {
				$v = addslashes( $v );
			}
			$txt .= "\$enhanced_Config['".substr( $k, 4 )."']='$v';\n";
		}
	}
	$txt .= $patchEnd;
	$txt .= "?>";
//_jms2win_end
/*_jms2win_undo
	global $mainframe;

	//Add code to check if config file is writeable.
	$configfile = "components/com_cbe/enhanced_admin/enhanced_config.php";
	@chmod ($configfile, 0766);
	if (!is_writable($configfile)) {
		//mosRedirect("index2.php?option=$option", "FATAL ERROR: Config File Not writeable" );
		$mainframe->redirect( "index.php?option=$option", "FATAL ERROR: Config File not writeable");

	}

	$txt = '<'.'?php'."\n";
	foreach ($_POST as $k=>$v) {
		if (strpos( $k, 'cfg_' ) === 0) {
			if (!get_magic_quotes_gpc()) {
				$v = addslashes( $v );
			}
			$txt .= "\$enhanced_Config['".substr( $k, 4 )."']='$v';\n";
		}
	}
	$txt .= "?>";
  _jms2win_undo */
