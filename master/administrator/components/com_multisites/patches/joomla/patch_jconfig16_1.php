//_jms2win_begin v1.2.52
		// If this is a Slave Site, let use the standard forma
		if ( defined( 'MULTISITES_ID')) {
   		$configStr = $config->toString('PHP', array('class' => 'JConfig', 'closingtag' => false));
		}
		else {
		   // This is a Master website, so add the MULTISITE wrapper
   		$str = $config->toString('PHP', array('class' => 'JConfig', 'closingtag' => false));
   		$begPos = strpos( $str, 'class');
   		$endPos = strpos( $str, '?>');
   		if ( $endPos === false) { $endPos = strlen( $str); }
         $configStr = substr( $str, 0, $begPos)
                    . "//_jms2win_begin v1.2.39\n"
                    . "if ( !defined( 'MULTISITES_ID')) {\n"
                    . "   if ( !defined( 'JPATH_MULTISITES')) define( 'JPATH_MULTISITES', (defined( 'JPATH_ROOT') ? JPATH_ROOT : dirname(__FILE__)) .DIRECTORY_SEPARATOR. 'multisites');\n"
                    . "   if ( !defined( '_EDWIN2WIN_')) define( '_EDWIN2WIN_', true);\n"
                    . "   @include( (defined( 'JPATH_ROOT') ? JPATH_ROOT : dirname(__FILE__)) .DIRECTORY_SEPARATOR. 'includes' .DIRECTORY_SEPARATOR. 'multisites.php');\n"
                    . "   if ( class_exists( 'Jms2Win')) Jms2Win::matchSlaveSite();\n"
                    . "}\n"
                    . "if ( (!isset( \$MULTISITES_FORCEMASTER) || !\$MULTISITES_FORCEMASTER)\n"
                    . "  && defined( 'MULTISITES_ID')\n"
                    . "  && file_exists(MULTISITES_CONFIG_PATH .DIRECTORY_SEPARATOR. 'configuration.php')) {\n"
                    . "   require_once( MULTISITES_CONFIG_PATH .DIRECTORY_SEPARATOR. 'configuration.php');\n"
                    . "} else if ( !class_exists( 'JConfig')) {\n"
                    . "//_jms2win_end\n"
                    . substr( $str, $begPos, $endPos-$begPos)
                    . "//_jms2win_begin v1.2.39\n"
                    . "}\n"
                    . "//_jms2win_end\n"
                    . "?>\n";
		}
		if (!JFile::write($file, $configStr)) {
//_jms2win_end
/*_jms2win_undo
		$configString = $config->toString('PHP', array('class' => 'JConfig', 'closingtag' => false));
		if (!JFile::write($file, $configString)) {
  _jms2win_undo */
