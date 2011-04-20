<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class urlType{
	function urlType(){
		$selectedMail = JRequest::getInt('filter_mail');
		if(!empty($selectedMail)){
			$query = 'SELECT DISTINCT a.name,a.urlid FROM '.acymailing::table('urlclick').' as b LEFT JOIN '.acymailing::table('url').' as a on a.urlid = b.urlid WHERE b.mailid = '.$selectedMail;
		}else{
			$query = 'SELECT a.name,a.urlid FROM '.acymailing::table('url').' as a';
		}
		$db =& JFactory::getDBO();
		$db->setQuery($query);
		$urls = $db->loadObjectList();
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0', JText::_('ALL_URLS') );
		foreach($urls as $onrUrl){
			$this->values[] = JHTML::_('select.option', $onrUrl->urlid, $onrUrl->name );
		}
	}
	function display($map,$value){
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', (int) $value );
	}
}