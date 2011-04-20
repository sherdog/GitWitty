<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
acymailing::get('controller.newsletter');
class AutonewsController extends NewsletterController{
	function generate(){
		$app =& JFactory::getApplication();
		$autoNewsHelper = acymailing::get('helper.autonews');
		if(!$autoNewsHelper->generate()){
			$app->enqueueMessage(JText::_('NO_AUTONEWSLETTERS'),'notice');
			$db =& JFactory::getDBO();
			$db->setQuery("SELECT * FROM ".acymailing::table('mail')." WHERE `type` = 'autonews'");
			$allAutonews = $db->loadObjectList();
			if(!empty($allAutonews)){
				$time = time();
				foreach($allAutonews as $oneAutonews){
					if(($oneAutonews->published != 1)){
						$app->enqueueMessage(JText::sprintf('AUTONEWS_NOT_PUBLISHED',$oneAutonews->subject),'notice');
					}elseif($oneAutonews->senddate >= $time){
						$app->enqueueMessage(JText::sprintf('AUTONEWS_NOT_READY',$oneAutonews->subject),'notice');
					}
				}
			}
		}else{
			foreach($autoNewsHelper->messages as $oneMessage){
				$app->enqueueMessage($oneMessage);
			}
		}
		return $this->listing();
	}
}