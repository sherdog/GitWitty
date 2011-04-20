//_jms2win_begin v1.2.17
	jimport('joomla.filesystem.file');
	// If this is a Slave Site, let use the standard forma
	if ( defined( 'MULTISITES_ID')) {
		// Set the configuration filename
   	$fname =  JPATH_ADMINISTRATOR.DS.'components'.DS.'com_fpslideshow'.DS.'configuration.' .MULTISITES_ID. '.php';
		// This is the slave sites. So keep the normal configuration files layout (no wrapper).
		$configStr = $config->toString('PHP', 'config', array('class' => 'FPSSConfig'));
	}
	else {
		// Set the configuration filename
   	$fname =  JPATH_ADMINISTRATOR.DS.'components'.DS.'com_fpslideshow'.DS.'configuration.php';
	   // This is a Master website, so add the MULTISITE wrapper
		$str = $config->toString('PHP', 'config', array('class' => 'FPSSConfig'));
		$begPos = strpos( $str, 'class');
		$endPos = strpos( $str, '?>');
      $configStr = substr( $str, 0, $begPos)
                 . "//_jms2win_begin v1.2.17\n"
                 . "if ( defined( 'MULTISITES_ID')\n"
                 . "  && file_exists( dirname(__FILE__) .DS. 'configuration.' .MULTISITES_ID. '.php')) {\n"
                 . "   require_once(  dirname(__FILE__) .DS. 'configuration.' .MULTISITES_ID. '.php');\n"
                 . "} else if ( !class_exists( 'FPSSConfig')) {\n"
                 . "//_jms2win_end\n"
                 . substr( $str, $begPos, $endPos-$begPos)
                 . "//_jms2win_begin\n"
                 . "}\n"
                 . "//_jms2win_end\n"
                 . "?>\n";
	}
	JClientHelper::getCredentials('ftp', true);

	// Try to make configuration.php writeable
	jimport('joomla.filesystem.path');
	if ( file_exists( $fname) && !JPath::setPermissions($fname, '0644')) {
		JError::raiseNotice('SOME_ERROR_CODE', 'Could not make configuration.php writable');
	}

	// Get the config registry in PHP class format and write it to configuation.php
	if (JFile::write($fname, $configStr)) {
//_jms2win_end
/*_jms2win_undo
	// Get the path of the configuration file
	$fname =  JPATH_ADMINISTRATOR.DS.'components'.DS.'com_fpslideshow'.DS.'configuration.php';

	JClientHelper::getCredentials('ftp', true);

	// Try to make configuration.php writeable
	jimport('joomla.filesystem.path');
	if (!JPath::setPermissions($fname, '0644')) {
		JError::raiseNotice('SOME_ERROR_CODE', 'Could not make configuration.php writable');
	}

	// Get the config registry in PHP class format and write it to configuation.php
	jimport('joomla.filesystem.file');
	if (JFile::write($fname, $config->toString('PHP', 'config', array('class' => 'FPSSConfig')))) {
  _jms2win_undo */
