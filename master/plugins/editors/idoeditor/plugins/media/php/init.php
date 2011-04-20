<?

define( '_JEXEC', 1 );

define('JPATH_BASE', dirname('../../../../../../index.php') );
define('URL','http://'.$_SERVER['SERVER_NAME']);

define( 'DS', DIRECTORY_SEPARATOR );

require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );
require_once ( JPATH_BASE .DS.'libraries'.DS.'joomla/filesystem/file.php' );
require_once ( JPATH_BASE .DS.'libraries'.DS.'joomla/filesystem/folder.php' );
require_once ( JPATH_BASE .DS.'libraries'.DS.'joomla/filesystem/path.php' );


JDEBUG ? $_PROFILER->mark( 'afterLoad' ) : null;


?>