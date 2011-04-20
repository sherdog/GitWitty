//_jms2win_begin v1.2.37 (patch1)
      if ( defined( 'MULTISITES_ID')) {
         $ini  = $client->path.DS.'templates'.DS.$template.DS.'params_' .MULTISITES_ID .'.ini';
         jimport('joomla.filesystem.file');
         if ( !JFile::exists( $ini)) {
            $ini  = $client->path.DS.'templates'.DS.$template.DS.'params.ini';
         }
      }
      else {
         $ini  = $client->path.DS.'templates'.DS.$template.DS.'params.ini';
      }
//_jms2win_end
/*_jms2win_undo
      $ini  = $client->path.DS.'templates'.DS.$template.DS.'params.ini';
  _jms2win_undo */
