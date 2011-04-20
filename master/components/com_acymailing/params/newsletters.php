<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
if(version_compare(JVERSION,'1.6.0','<')){
	class JElementNewsletters extends JElement
	{
		function fetchElement($name, $value, &$node, $control_name)
		{
			if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php')){
				echo 'AcyMailing is required for this plugin';
				return;
			}
			$db =& JFactory::getDBO();
			$db->setQuery("SELECT `mailid`, CONCAT(subject,' ( ',mailid,' )') as `title` FROM #__acymailing_mail WHERE `type`='news' AND (`senddate` IS NULL OR `senddate` < 1)AND `type` = 'news' ORDER BY `subject` ASC");
			$results = $db->loadObjectList();
			$novalue = null;
			$novalue->mailid = 0;
			$novalue->title = ' - - - - - ';
			array_unshift($results,$novalue);
			return JHTML::_('select.genericlist', $results, $control_name.'['.$name.']' , 'size="1"', 'mailid', 'title', $value);
		}
	}
}else{
	class JFormFieldNewsletters extends JFormField
	{
		var $type = 'newsletters';
		function getInput() {
			if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php')){
				echo 'AcyMailing is required for this plugin';
				return;
			}
			$db =& JFactory::getDBO();
			$db->setQuery("SELECT `mailid`, CONCAT(subject,' ( ',mailid,' )') as `title` FROM #__acymailing_mail WHERE `type`='news' AND (`senddate` IS NULL OR `senddate` < 1)AND `type` = 'news' ORDER BY `subject` ASC");
			$results = $db->loadObjectList();
			$novalue = null;
			$novalue->mailid = 0;
			$novalue->title = ' - - - - - ';
			array_unshift($results,$novalue);
			return JHTML::_('select.genericlist', $results, $this->control_name.'['.$this->name.']' , 'size="1"', 'mailid', 'title', $this->value);
		}
	}
}