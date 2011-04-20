//_jms2win_begin v1.2.12
   $config_master = _JRECACHE_DIR .  _DS . 'jrecache.config.php';
   if ( defined( 'MULTISITES_ID')) {
      $config_slave = _JRECACHE_DIR .  _DS . 'jrecache.config.' . MULTISITES_ID . '.php';
      if ( file_exists( $config_slave)) {
      	$jrecache_config =& new jrecache_config("_JRECache_Config", $config_slave);
      }
      else {
      	$jrecache_config =& new jrecache_config("_JRECache_Config", $config_master);
      	$jrecache_config->_path = $config_slave;
      }
   }
   else {
   	$jrecache_config =& new jrecache_config("_JRECache_Config", $config_master);
   }
//_jms2win_end
/*_jms2win_undo
	$jrecache_config =& new jrecache_config("_JRECache_Config", _JRECACHE_DIR .  _DS . "jrecache.config.php");
  _jms2win_undo */
