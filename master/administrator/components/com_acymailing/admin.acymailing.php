<?php
/**
 * @copyright	Copyright (C) 2009-2010 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php')){
	echo "Could not load helper file";
	return;
}
if(defined(JDEBUG) AND JDEBUG) acymailing::displayErrors();
$taskGroup = JRequest::getCmd('ctrl',JRequest::getCmd('gtask','dashboard'));
$config =& acymailing::config();
$doc =& JFactory::getDocument();
$cssBackend = $config->get('css_backend','default');
if(!empty($cssBackend)){
	$doc->addStyleSheet( ACYMAILING_CSS.'component_'.$cssBackend.'.css' );
}
JHTML::_('behavior.tooltip');
$bar = & JToolBar::getInstance('toolbar');
$bar->addButtonPath(ACYMAILING_BUTTON);
if($taskGroup != 'update'){
	$app =& JFactory::getApplication();
	if(!$config->get('installcomplete')){
		$app->redirect(acymailing::completeLink('update&task=install',false,true));
	}
	// Added the License Check
	$_SESSION['acymailing']['li'] = true;
	// End of Added the License Check
	if(empty($_SESSION['acymailing']['li'])){
		$updateHelper = acymailing::get('helper.update');
		if(!$updateHelper->check()){
			$try = $config->get('litry','0') +1;
			$newConf = null;
			$newConf->litry = $try;
			if($newConf->litry>2) $newConf->litry=0;
			$config->save($newConf);
			if($try==3){
				$app->redirect(acymailing::completeLink('update&task=licensejs',false,true));
			}else{
				$app->redirect(acymailing::completeLink('update&task=license',false,true));
			}
		}
	}
}
$lang =& JFactory::getLanguage();
$lang->load(ACYMAILING_COMPONENT,JPATH_SITE);
include(ACYMAILING_CONTROLLER.$taskGroup.'.php');
$className = ucfirst($taskGroup).'Controller';
$classGroup = new $className();
JRequest::setVar( 'view', $classGroup->getName() );
$classGroup->execute( JRequest::getCmd('task','listing'));
$classGroup->redirect();
if(JRequest::getString('tmpl') !== 'component'){
	echo acymailing::footer();
}