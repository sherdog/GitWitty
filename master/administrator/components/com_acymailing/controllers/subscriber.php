<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class SubscriberController extends JController{
	var $allowedInfo = array();
	function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerDefaultTask('listing');
	}
	function listing(){
		JRequest::setVar( 'layout', 'listing'  );
		return parent::display();
	}
	function choose(){
		JRequest::setVar( 'layout', 'choose'  );
		return parent::display();
	}
	function apply(){
		$this->store();
		JRequest::setVar('hidemainmenu',1);
		JRequest::setVar( 'layout', 'form'  );
		return parent::display();
	}
	function export(){
		$cids = JRequest::getVar('cid');
		if(!empty($cids)){
			$_SESSION['acymailing']['exportusers'] = $cids;
			$this->setRedirect(acymailing::completeLink('data&task=export&sessionvalues=1',false,true));
		}else{
			$this->setRedirect(acymailing::completeLink('data&task=export',false,true));
		}
	}
	function save(){
		$this->store();
		JRequest::setVar( 'layout', 'listing'  );
		return parent::display();
	}
	function store(){
		JRequest::checkToken() or die( 'Invalid Token' );
		$app =& JFactory::getApplication();
		$subscriberClass = acymailing::get('class.subscriber');
		$status = $subscriberClass->saveForm();
		if($status){
			$app->enqueueMessage(JText::_( 'JOOMEXT_SUCC_SAVED' ), 'message');
		}else{
			$app->enqueueMessage(JText::_( 'ERROR_SAVING' ), 'error');
			if(!empty($subscriberClass->errors)){
				foreach($subscriberClass->errors as $oneError){
					$app->enqueueMessage($oneError, 'error');
				}
			}
		}
	}
	function edit(){
		JRequest::setVar('hidemainmenu',1);
		JRequest::setVar( 'layout', 'form'  );
		return parent::display();
	}
	function add(){
		return $this->edit();
	}
	function remove(){
		JRequest::checkToken() or die( 'Invalid Token' );
		$subscriberIds = JRequest::getVar( 'cid', array(), '', 'array' );
		$subscriberObject = acymailing::get('class.subscriber');
		$num = $subscriberObject->delete($subscriberIds);
		$app =& JFactory::getApplication();
		$app->enqueueMessage(JText::sprintf('SUCC_DELETE_ELEMENTS',$num), 'message');
		JRequest::setVar( 'layout', 'listing'  );
		return parent::display();
	}
}