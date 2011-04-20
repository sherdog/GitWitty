<?
error_reporting(E_ERROR | E_PARSE);
define( '_JEXEC', 1 );
define( 'DS', DIRECTORY_SEPARATOR );

define('JPATH_BASE', dirname('../../../../../../index.php') );
define('URL','http://'.$_SERVER['SERVER_NAME']);

define('JPATH_COMPONENT', JPATH_BASE.DS."components" );
define('JPATH_ADMINISTRATOR', JPATH_BASE.DS."administrator" );

require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );
require_once ( JPATH_BASE .DS.'libraries'.DS.'joomla/filesystem/file.php' );
require_once ( JPATH_BASE .DS.'libraries'.DS.'joomla/filesystem/folder.php' );
require_once ( JPATH_BASE .DS.'libraries'.DS.'joomla/filesystem/path.php' );

JDEBUG ? $_PROFILER->mark( 'afterLoad' ) : null;

jimport( 'joomla.application.application' );
$applicationName = JRequest::getString("applicationName","site");
//$applicationName = "administrator";
$mainframe = &JFactory::getApplication($applicationName);
$user = & JFactory::getUser();

if($user->get('guest')) die('{status:"Restricted access"}');

$sql = "select * from #__plugins where element='idoeditor' and folder='editors'";
$db	=& JFactory::getDBO();
$db->setQuery($sql);
$a = $db->loadAssoc();

$params_plugin = new JParameter($a["params"], JPATH_BASE .DS."plugins".DS."editors".DS."idoeditor.xml");

?>