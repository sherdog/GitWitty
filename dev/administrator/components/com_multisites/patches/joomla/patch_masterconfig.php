//_jms2win_begin v1.2.14
if ( !defined( 'MULTISITES_ID')) {
   if ( !defined( 'JPATH_MULTISITES')) define( 'JPATH_MULTISITES', (defined( 'JPATH_ROOT') ? JPATH_ROOT : dirname(__FILE__)) .DIRECTORY_SEPARATOR. 'multisites');
   if ( !defined( '_EDWIN2WIN_')) define( '_EDWIN2WIN_', true);
   @include( (defined( 'JPATH_ROOT') ? JPATH_ROOT : dirname(__FILE__)) .DIRECTORY_SEPARATOR. 'includes' .DIRECTORY_SEPARATOR. 'multisites.php');
   if ( class_exists( 'Jms2Win')) Jms2Win::matchSlaveSite();
}
if ( (!isset( $MULTISITES_FORCEMASTER) || !$MULTISITES_FORCEMASTER)
  && defined( 'MULTISITES_ID')
  && file_exists(MULTISITES_CONFIG_PATH .DIRECTORY_SEPARATOR. 'configuration.php')) {
   require_once( MULTISITES_CONFIG_PATH .DIRECTORY_SEPARATOR. 'configuration.php');
} else if ( !class_exists( 'JConfig')) {
//_jms2win_end
