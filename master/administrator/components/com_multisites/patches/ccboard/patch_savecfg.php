//_jms2win_begin v1.2.26
   	jimport('joomla.filesystem.file');
   	// If this is a Slave Site, let use the standard forma
   	if ( defined( 'MULTISITES_ID')) {
   		// Set the configuration filename
   		$file = JPATH_COMPONENT.DS.'ccboard-config.' .MULTISITES_ID. '.php';
   		// This is the slave sites. So keep the normal configuration files layout (no wrapper).
   		$configStr = $registry->toString('PHP', 'config', array('class' => 'ccboardConfig'));
   	}
   	else {
   		// Set the configuration filename
   
   		$file = JPATH_COMPONENT.DS.'ccboard-config.php';
   	   // This is a Master website, so add the MULTISITE wrapper
   		$str = $registry->toString('PHP', 'config', array('class' => 'ccboardConfig'));
   		$begPos = strpos( $str, 'class');
   		$endPos = strpos( $str, '?>');
         $configStr = substr( $str, 0, $begPos)
                    . "//_jms2win_begin v1.2.26\n"
                    . "if ( defined( 'MULTISITES_ID')\n"
                    . "  && file_exists( dirname(__FILE__) .DS. 'ccboard-config.' .MULTISITES_ID. '.php')) {\n"
                    . "   require_once(  dirname(__FILE__) .DS. 'ccboard-config.' .MULTISITES_ID. '.php');\n"
                    . "} else if ( !class_exists( 'ccboardConfig')) {\n"
                    . "//_jms2win_end\n"
                    . substr( $str, $begPos, $endPos-$begPos)
                    . "//_jms2win_begin\n"
                    . "}\n"
                    . "//_jms2win_end\n"
                    . "?>\n";
   	}
   	// Get the config registry in PHP class format and write it to ccboard-config.php
   	if (JFile::write( $file, $configStr)) {
//_jms2win_end
/*_jms2win_undo
		if( !JFile::write($file,$registry->toString('PHP', 'config', array('class' => 'ccboardConfig'))) ) {
  _jms2win_undo */
