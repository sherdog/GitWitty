<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');

class plgSystemContactacymailing extends JPlugin
{
	function plgSystemContactacymailing(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin =& JPluginHelper::getPlugin('system', 'contactacymailing');
			$this->params = new JParameter( $plugin->params );
		}
    }

	function onSubmitContact($contact,$post){
		//Let's create the user first
		//If he's already created, we won't create him back.

		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php')) return;

		if($this->params->get('dispcheck',1) == 1 AND empty($post['acysubscribe'])) return;
		if($this->params->get('dispcheck',1) == 2 AND empty($post['acysub'])) return;

		$user = null;
		$user->email = trim(strip_tags($post['email']));

		//Avoid any problem...
		$userHelper = acymailing::get('helper.user');
		if(!$userHelper->validEmail($user->email)) return;

		if(!empty($post['name'])) $user->name = $post['name'];
		if($this->params->get('sendconf','default') == 'no') $user->confirmed = 1;

		$userClass = acymailing::get('class.subscriber');
		$userClass->checkVisitor = false;

		//Here we updated the user or added a new one properly in all cases
		$subid = $userClass->save($user);

		if(empty($subid)) return;

		if($this->params->get('dispcheck',1) == 2){
			$listsToSubscribe = implode(',',$post['acysub']);
		}else{
			$listsToSubscribe = $this->params->get('autosub','all');
		}
		$config = acymailing::config();

		$listsClass = acymailing::get('class.list');
		$allLists = $listsClass->getLists('listid');
		if(acymailing::level(1)){
			$allLists = $listsClass->onlyCurrentLanguage($allLists);
		}

		$listsArray = array();
		if(strpos($listsToSubscribe,',') OR is_numeric($listsToSubscribe)){
			$listsArrayParam = explode(',',$listsToSubscribe);
			foreach($allLists as $oneList){
				if($oneList->published AND in_array($oneList->listid,$listsArrayParam)){$listsArray[] = $oneList->listid;}
			}
		}elseif(strtolower($listsToSubscribe) == 'all'){
			foreach($allLists as $oneList){
				if($oneList->published){$listsArray[] = $oneList->listid;}
			}
		}

		if(empty($listsArray)) return;

		//Get the saved subscriber to make sure it's updated and the good one
		$inserteduser = $userClass->get($subid);
		$currentSubscription = $userClass->getSubscriptionStatus($subid);

		$statusAdd = (empty($inserteduser->confirmed) AND $config->get('require_confirmation',false)) ? 2 : 1;

		$addlists = array();
		foreach($listsArray as $idOneList){
			//The user is not already subscribed to this list... so we add it
			if(!isset($currentSubscription[$idOneList])){
				$addlists[$statusAdd][] = $idOneList;
			}
		}

		//Now we have everything to be able to add the subscription
		if(!empty($addlists)) {
			$listsubClass = acymailing::get('class.listsub');
			$listsubClass->addSubscription($subid,$addlists);
		}

	}

	function onAfterRender(){

		$app =& JFactory::getApplication();
		if($app->isAdmin()) return false;

		$option = JRequest::getString('option');
		$view = JRequest::getString('view');

		if(empty($option) OR empty($view)) return;

		$components = array();
		if(version_compare(JVERSION,'1.6.0','<')){
			$components['com_contact'] = array('view' => 'contact','lengthafter' => 200);
		}else{
			$components['com_contact'] = array('view' => 'contact','lengthafter' => 200,'contact_text' => 'contact-text', 'contact_email' => 'contact-email','contact_email_copy'=>'contact-email-copy');
		}
		$components['com_qcontacts'] = array('view' => 'contact','lengthafter' => 400);
		$components['com_contact_enhanced'] = array('view' => 'contact','lengthafter' => 200, 'contact_text' => 'cf_4', 'contact_email' => 'email');
		$components['com_gcontact'] = array('view' => 'contact','lengthafter' => 200);
		if(!isset($components[$option]) || $view != $components[$option]['view']) return;

		if(!$this->params->get('dispcheck',1)) return;

		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php')) return;

		$subText = $this->params->get('checktext');
		if(empty($subText)){
			$lang =& JFactory::getLanguage();
			$lang->load('com_acymailing',JPATH_SITE);
			if($this->params->get('dispcheck',1) == 2){
				$subText = JText::_('SUBSCRIPTION').':';
			}else{
				$subText = JText::_('YES_SUBSCRIBE_ME');
			}
		}

		//We have to display it and we are on the right page...
		if($this->params->get('dispcheck',1) == 2){
			$visibleListsArray = array();
			$listsClass = acymailing::get('class.list');
			$allLists = $listsClass->getLists('listid');
			if(acymailing::level(1)){
				$allLists = $listsClass->onlyCurrentLanguage($allLists);
			}
			$visibleLists = $this->params->get('autosub','all');
			if(strpos($visibleLists,',') OR is_numeric($visibleLists)){
				$allvisiblelists = explode(',',$visibleLists);
				foreach($allLists as $oneList){
					if($oneList->published AND in_array($oneList->listid,$allvisiblelists)) $visibleListsArray[] = $oneList->listid;
				}
			}elseif(strtolower($visibleLists) == 'all'){
				foreach($allLists as $oneList){
					if($oneList->published){$visibleListsArray[] = $oneList->listid;}
				}
			}

			if(empty($visibleListsArray)) return;
			$return = '<div class="acycontactform"><label class="labelacysubscribe">'.$subText.'</label>';
			foreach($visibleListsArray as $oneList){
				$check = $this->params->get('checkcheck',1) ? 'checked="checked"' : '';
				$return .= '<p class="contactacy"><input type="checkbox" id="acy_list_'.$oneList.'" class="acymailing_checkbox" name="acysub[]" '.$check.' value="'.$oneList.'"/> <label for="acy_list_'.$oneList.'" class="acylabellist">';
				$return .= $allLists[$oneList]->name;
				$return .= '</label></p>';
			}
			$return .= '</div>';

		}else{
			$return = '<span class="acycontactform"><input type="checkbox" name="acysubscribe" id="acysubscribe" value="1" '.($this->params->get('checkcheck',1) ? 'checked="checked"' : '').' /> ';
			$return .= '<label for="acysubscribe">'.$subText.'</label></span><br />';
		}

		$id = $this->params->get('checkpos','contact_text');
		if(isset($components[$option][$id])) $id = $components[$option][$id];

		$body = JResponse::getBody();
		$replace = 'id="'.$id.'".{0,'.$components[$option]['lengthafter'].'}(<br */>|</p>|</div>)';
		if(preg_match('#'.$replace.'#Uis',$body)){
			//Lets add the CSS if some is defined
			$style = $this->params->get('customcss');
			if(!empty($style)){
				$stylestring = '<style type="text/css">'."\n".$style."\n".'</style>'."\n";
				$return = $stylestring.$return;
			}

			$body = preg_replace('#('.$replace.')#Uis','$1'.$return,$body,1);
			JResponse::setBody($body);
			return;
		}

		if($option == 'com_gcontact') return;
		echo 'Error : Could not add the AcyMailing text in the contact form';
	}
}//endclass