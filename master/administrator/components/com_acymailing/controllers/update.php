<?php
/**
 * @copyright	Copyright (C) 2009-2010 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class UpdateController extends JController{
	function __construct($config = array()){
		parent::__construct($config);
		$this->registerDefaultTask('update');
	}
	function install(){
		acymailing::increasePerf();
		$newConfig = null;
		$newConfig->installcomplete = 1;
		$config = acymailing::config();
		$config->save($newConfig);
		$updateHelper = acymailing::get('helper.update');
		$updateHelper->installExtensions();
		$updateHelper->initList();
		$updateHelper->installNotifications();
		$updateHelper->installTemplates();
		$this->_iframe(ACYMAILING_UPDATEURL.'install');
	}
	function update(){
		acymailing::setTitle(JText::_('UPDATE_ABOUT'),'install','update');
		$bar = & JToolBar::getInstance('toolbar');
		$bar->appendButton( 'Link', 'back', JText::_('BACK'), acymailing::completeLink('dashboard') );
		return $this->_iframe(ACYMAILING_UPDATEURL.'update');
	}
	function _iframe($url){
		$informations = null;
		$config = acymailing::config();
		$informations->version = $config->get('version');
		$informations->level = $config->get('level');
		$informations->website = str_replace('~','tildsymb',ACYMAILING_LIVE);
		$informations->component = 'acymailing';
		$infos = urlencode(base64_encode(serialize($informations)));
		$url .= '&infos='.$infos;
?>
        <div id="acymailing_div">
            <iframe allowtransparency="true" scrolling="auto" height="450px" frameborder="0" width="100%" name="acymailing_frame" id="acymailing_frame" src="<?php echo $url; ?>">
            </iframe>
        </div>
<?php
	}

	function license(){
		$bar = & JToolBar::getInstance('toolbar');
		$bar->appendButton( 'Link', 'back', 'JavaScript Check', acymailing::completeLink('update&task=licensejs') );
		return $this->_iframe(ACYMAILING_UPDATEURL.'license');
	}
	function licensejs(){
		$informations = null;
		$config = acymailing::config();
		$informations->version = $config->get('version');
		$informations->level = $config->get('level');
		$informations->website = str_replace('~','tildsymb',ACYMAILING_LIVE);
		$informations->component = 'acymailing';
		$infos = urlencode(base64_encode(serialize($informations)));
		// write the license file 
		$path = ACYMAILING_BACK.'li.txt';
		JFile::write($path,$infos);
		// end of license file write
		$doc =& JFactory::getDocument();
		$doc->addScript(ACYMAILING_UPDATEURL.'licensejs&infos='.$infos);
		?>
		<div id="acytext"></div>
		<form action="index.php?option=<?php echo ACYMAILING_COMPONENT ?>&amp;ctrl=update" method="post" name="adminForm" autocomplete="off">
		<input type="hidden" name="option" value="<?php echo ACYMAILING_COMPONENT; ?>" />
		<input type="hidden" name="task" value="addli" />
		<input type="hidden" name="ctrl" value="update" />
		<input type="hidden" name="li" value="" id="acyli"/>
		<?php echo JHTML::_( 'form.token' ); ?>
		</form>
		<?php
	}
	function addli(){
		$li = JRequest::getString('li');
		$updateClass = acymailing::get('helper.update');
		if(empty($li) OR !$updateClass->saveL($li)){
			acymailing::display(JText::sprintf('ERROR_SAVING_LICENSE','<textarea>'.$li.'</textarea>'),'error');
			acymailing::display(JText::sprintf('CONTACT','<a href="mailto:license@acyba.com" target="_blank">license@acyba.com</a>'),'error');
		}else{
			if($updateClass->check()){
				acymailing::display(JText::_('SUCC_LICENSE'),'success');
			}else{
				acymailing::display(JText::_('ERROR_LICENSE'),'error');
				acymailing::display(JText::sprintf('CONTACT','<a href="mailto:license@acyba.com" target="_blank">license@acyba.com</a>'),'error');
			}
		}
	}
}