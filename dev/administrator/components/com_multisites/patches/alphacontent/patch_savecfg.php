//_jms2win_begin v1.1.9
		jimport('joomla.filesystem.file');
		// If this is a Slave Site, let use the standard forma
		if ( defined( 'MULTISITES_ID')) {
   		// Set the configuration filename
   		$filename = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_alphacontent'.DS.'configuration'.DS.'configuration.' .MULTISITES_ID. '.php';
   		if ( file_exists( $filename) && JPath::isOwner($filename) && !JPath::setPermissions($filename, '0644')) {
   			JError::raiseNotice('2002', 'Could not make the ' . $filename . '  writable');
   		}
   		// This is the slave sites. So keep the normal configuration files layout (no wrapper).
   		$configStr = $configuration->toString('PHP', 'configuration', array('class' => 'alphaConfiguration'));
		}
		else {
   		// Set the configuration filename
   		$filename = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_alphacontent'.DS.'configuration'.DS.'configuration.php';
   		if ( JPath::isOwner($filename) && !JPath::setPermissions($filename, '0644')) {
   			JError::raiseNotice('2002', 'Could not make the ' . $filename . '  writable');
   		}
   		
		   // This is a Master website, so add the MULTISITE wrapper
   		$str = $configuration->toString('PHP', 'configuration', array('class' => 'alphaConfiguration'));
   		$begPos = strpos( $str, 'class');
   		$endPos = strpos( $str, '?>');
         $configStr = substr( $str, 0, $begPos)
                    . "if ( defined( 'MULTISITES_ID')\n"
                    . "  && file_exists( dirname(__FILE__) .DS. 'configuration.' .MULTISITES_ID. '.php')) {\n"
                    . "   require_once(  dirname(__FILE__) .DS. 'configuration.' .MULTISITES_ID. '.php');\n"
                    . "} else if ( !class_exists( 'alphaConfiguration')) {\n"
                    . substr( $str, $begPos, $endPos-$begPos)
                    . "}\n"
                    . "?>\n";
		}
		if (JFile::write($filename, $configStr)) {
//_jms2win_end
/*_jms2win_undo
		// Set the configuration filename
		$filename = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_alphacontent'.DS.'configuration'.DS.'configuration.php';

		if ( JPath::isOwner($filename) && !JPath::setPermissions($filename, '0644')) {
			JError::raiseNotice('2002', 'Could not make the ' . $filename . '  writable');
		}

		jimport('joomla.filesystem.file');
		if (JFile::write($filename, $configuration->toString('PHP', 'configuration', array('class' => 'alphaConfiguration')))) {
  _jms2win_undo */
