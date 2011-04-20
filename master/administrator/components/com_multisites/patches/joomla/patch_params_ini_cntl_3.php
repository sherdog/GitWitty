//_jms2win_begin v1.2.37 (patch3)
      jimport( 'joomla.filesystem.path');
		// If there is a specific template folder specified, use the parent folder and assume that leaf directory is "templates".
		if ( JRequest::getVar('client', '0', '', 'int') == 0
		  && defined( 'MULTISITES_ID')) {
         if ( defined( 'MULTISITES_ID_PATH'))   { $filename = MULTISITES_ID_PATH.DIRECTORY_SEPARATOR.'config_multisites.php'; }
         else                                   { $filename = JPATH_MULTISITES.DS.MULTISITES_ID.DS.'config_multisites.php'; }
         @include($filename);
         if ( isset( $config_dirs) && !empty( $config_dirs) && !empty( $config_dirs['templates_dir'])) {
            $templates_dir = JPath::clean( $config_dirs['templates_dir']);
            $parts = explode( DS, $templates_dir );
            array_pop( $parts );
            $tmp          = $client;
            $client       = clone( $tmp);
            $client->path = implode( DS, $parts );
         }
		}
//_jms2win_end
/*_jms2win_undo
  _jms2win_undo */
