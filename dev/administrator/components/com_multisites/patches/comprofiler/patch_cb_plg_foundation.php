//_jms2win_begin v1.1.6
if ( defined( 'MULTISITES_ID')) {
   jimport('joomla.filesystem.file');
   if ( JFile::exists( dirname( __FILE__ ) . '/ue_config_' . MULTISITES_ID . '.php')) {
      include_once( dirname( __FILE__ ) . '/ue_config_' . MULTISITES_ID . '.php' );
   }
   else {
      include_once( dirname( __FILE__ ) . '/ue_config.php' );
   }
}
else {
   include_once( dirname( __FILE__ ) . '/ue_config.php' );
}
//_jms2win_end
/*_jms2win_undo
include_once( dirname( __FILE__ ) . '/ue_config.php' );
  _jms2win_undo */

