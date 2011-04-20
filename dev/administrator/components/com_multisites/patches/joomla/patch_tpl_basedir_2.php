//_jms2win_begin v1.2.36
			$baseDir    = $clientInfo->path.DS.'templates';
   		// If there is a specific template folder specified, give this one to the "template" installer.
   		if ( defined( 'MULTISITES_ID')) {
            if ( defined( 'MULTISITES_ID_PATH'))   { $filename = MULTISITES_ID_PATH.DIRECTORY_SEPARATOR.'config_multisites.php'; }
            else                                   { $filename = JPATH_MULTISITES.DS.MULTISITES_ID.DS.'config_multisites.php'; }
            @include($filename);
            if ( isset( $config_dirs) && !empty( $config_dirs) && !empty( $config_dirs['templates_dir'])) {
               $baseDir = JPath::clean( $config_dirs['templates_dir']);
            }
   		}
			$templateDirs = JFolder::folders( $baseDir);

			for ($i=0; $i < count($templateDirs); $i++) {
				$template = new stdClass();
				$template->folder = $templateDirs[$i];
				$template->client = $clientInfo->id;
				$template->baseDir = $baseDir;
//_jms2win_end
/*_jms2win_undo
			$templateDirs = JFolder::folders($clientInfo->path.DS.'templates');
			for ($i=0; $i < count($templateDirs); $i++) {
				$template = new stdClass();
				$template->folder = $templateDirs[$i];
				$template->client = $clientInfo->id;
				$template->baseDir = $clientInfo->path.DS.'templates';
  _jms2win_undo */
