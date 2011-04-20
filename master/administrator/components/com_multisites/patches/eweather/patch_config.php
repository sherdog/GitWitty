//_jms2win_begin v1.2.16
      if ( defined( 'MULTISITES_ID')) {
         jimport( 'joomla.filesystem.file');
   		$masterFilename = JPATH_ADMINISTRATOR.DS."components/com_eweather/eweather.config.data.php";
   		$slaveFilename  = JPATH_ADMINISTRATOR.DS.'components/com_eweather/eweather.config.data.' .MULTISITES_ID. '.php';
         if ( !JFile::exists( $slaveFilename) &&  JFile::exists( $masterFilename)) {
            JFile::copy( $masterFilename, $slaveFilename);
         }
   		$this->configFileName_ = $slaveFilename;
      }
      else {
   		$this->configFileName_ = JPATH_ADMINISTRATOR.DS."components/com_eweather/eweather.config.data.php";
      }
//_jms2win_end
/*_jms2win_undo
		$this->configFileName_ = JPATH_ADMINISTRATOR.DS."components/com_eweather/eweather.config.data.php";
  _jms2win_undo */
