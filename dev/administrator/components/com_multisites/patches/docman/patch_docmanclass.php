//_jms2win_begin v1.1.2
      if ( defined( 'MULTISITES_ID')) {
         // If the slave config does not exists yet, create one base on the master config
         $filename = dirname(__FILE__)."/docman.config." . MULTISITES_ID . ".php";
         jimport( 'joomla.filesystem.file');
         if ( !JFile::exists( $filename)) {
            JFile::copy( dirname(__FILE__)."/docman.config.php", $filename);
         }
		   $this->_config = new DOCMAN_Config('dmConfig', $filename);
      }
      else {
		   $this->_config = new DOCMAN_Config('dmConfig', dirname(__FILE__)."/docman.config.php" );
		}
//_jms2win_end
