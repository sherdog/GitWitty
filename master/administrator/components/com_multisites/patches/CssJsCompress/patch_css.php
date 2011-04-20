//_jms2win_begin v1.2.39
define('DS', DIRECTORY_SEPARATOR);
// Try detect if this is a slave site and this should set the define MULTISITES_ID
if ( !defined( 'MULTISITES_ID')) {
   if ( !defined( 'JPATH_MULTISITES')) define( 'JPATH_MULTISITES', dirname( dirname( dirname( dirname(__FILE__)))) .DIRECTORY_SEPARATOR. 'multisites');
   if ( !defined( '_EDWIN2WIN_')) define( '_EDWIN2WIN_', true);
   @include( dirname( dirname( dirname(dirname(__FILE__)))) .DIRECTORY_SEPARATOR. 'includes' .DIRECTORY_SEPARATOR. 'multisites.php');
   if ( defined( 'JMS2WIN_VERSION')) {
      if ( !defined( 'MULTISITES_ADMIN')) define( 'MULTISITES_ADMIN', true);
      if ( class_exists( 'Jms2Win')) Jms2Win::matchSlaveSite();
   }
}

// If this is a slave site, check if it has a specific deploy directory (if YES, use its path to compute the JPATH_BASE)
if ( defined( 'MULTISITES_ID')) {
   if ( defined( 'MULTISITES_ID_PATH'))   { $filename = MULTISITES_ID_PATH.DIRECTORY_SEPARATOR.'config_multisites.php'; }
   else                                   { $filename = JPATH_MULTISITES.DS.MULTISITES_ID.DIRECTORY_SEPARATOR.'config_multisites.php'; }
   @include($filename);
   if ( isset( $config_dirs) && !empty( $config_dirs) && !empty( $config_dirs['deploy_dir'])) {
      define('JPATH_BASE', $config_dirs['deploy_dir']);
   }
   else {
      define('JPATH_BASE', dirname( dirname( dirname( dirname(__FILE__)))) );
   }
}
else {
   define('JPATH_BASE', dirname( dirname( dirname( dirname(__FILE__)))) );
}
$file=JPATH_BASE.DS.'cache'.DS.'css'.DS.$cssFileName;
//_jms2win_end
/*_jms2win_undo
define('DS', DIRECTORY_SEPARATOR);
define('PATH_ROOT', dirname(__FILE__) . DS);
$file=PATH_ROOT.'..'.DS.'..'.DS.'..'.DS.'cache'.DS.'css'.DS.$cssFileName;
  _jms2win_undo */
  