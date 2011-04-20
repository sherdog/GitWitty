<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class ModuleloaderController extends JController{
	function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerDefaultTask('load');
	}
	function load(){
		$timeVar = JRequest::getInt('time');
		$time = time();
		if($time < $timeVar OR $time > $timeVar +20){ echo 'Not the right time'; exit; }
		$moduleId = (int) JRequest::getInt('id');
		if(empty($moduleId)){
			echo 'OK! TEST VALID';
			exit;
		}
		$db =& JFactory::getDBO();
	 	$db->setQuery('SELECT * FROM #__modules WHERE id = '.$moduleId.' LIMIT 1');
	 	$module = $db->loadObject();
	 	if(empty($module)){ echo 'No module found'; exit; }
		$module->user  	= substr( $module->module, 0, 4 ) == 'mod_' ?  0 : 1;
		$module->name = $module->user ? $module->title : substr( $module->module, 4 );
		$module->style = null;
		$module->module = preg_replace('/[^A-Z0-9_\.-]/i', '', $module->module);
		$params = array();
		echo JModuleHelper::renderModule($module, $params);
		exit;
	}
}