<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class StatsurlController extends acymailingController{
	function save(){
		JRequest::checkToken() or die( 'Invalid Token' );
		$class = acymailing::get('class.url');
		$status = $class->saveForm();
		if($status){
			acymailing::display(JText::_( 'JOOMEXT_SUCC_SAVED'),'success');
			return true;
		}else{
			acymailing::display(JText::_( 'ERROR_SAVING'),'success');
		}
		return $this->edit();
	}
	function detaillisting(){
		JRequest::setVar( 'layout', 'detaillisting'  );
		return parent::display();
	}
	function export(){
		$selectedMail = JRequest::getInt('filter_mail',0);
		$selectedUrl = JRequest::getInt('filter_url',0);
		$filters = array();
		if(!empty($selectedMail)) $filters[] = 'a.mailid = '.$selectedMail;
		if(!empty($selectedMail)) $filters[] = 'a.urlid = '.$selectedUrl;
		$query = 'SELECT `subid` FROM `#__acymailing_urlclick` as a ';
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (',$filters).')';
		$db =& JFactory::getDBO();
		$db->setQuery($query);
		$_SESSION['acymailing']['exportusers'] = $db->loadResultArray();
		$this->setRedirect(acymailing::completeLink('data&task=export&sessionvalues=1',true,true));
	}
}