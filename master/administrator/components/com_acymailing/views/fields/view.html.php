<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class FieldsViewFields extends JView
{
	function display($tpl = null)
	{
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function form(){
		$fieldid = acymailing::getCID('fieldid');
		$fieldsClass = acymailing::get('class.fields');
		if(!empty($fieldid)){
			$field = $fieldsClass->get($fieldid);
		}else{
			$field = null;
			$field->published = 1;
			$field->type = 'text';
			$field->backend = 1;
			$field->namekey = '';
		}
		if(!empty($field->fieldid)) $fieldTitle = ' : '.$field->namekey;
		else $fieldTitle = '';
		acymailing::setTitle(JText::_('FIELD').$fieldTitle,'fields','fields&task=edit&fieldid='.$fieldid);
		$start = empty($field->value) ? 0 : count($field->value);
		$script = ' var currentid = '.($start+1).';
			function addLine(){
			var myTable=window.document.getElementById("tablevalues");
			var newline = document.createElement(\'tr\');
			var column = document.createElement(\'td\');
			var column2 = document.createElement(\'td\');
			var column3 = document.createElement(\'td\');
			var column4 = document.createElement(\'td\');
			column4.innerHTML = \'<a onclick="acymove(\'+currentid+\',1);return false;" href="#"><img src="'.ACYMAILING_IMAGES.'movedown.png" alt=" ˇ "/></a><a onclick="acymove(\'+currentid+\',-1);return false;" href="#"><img src="'.ACYMAILING_IMAGES.'moveup.png" alt=" ˆ "/></a>\';
			var input = document.createElement(\'input\');
			input.id = "option"+currentid+"title";
			var input2 = document.createElement(\'input\');
			input2.id = "option"+currentid+"value";
			var input3 = document.createElement(\'select\');
			input3.id = "option"+currentid+"disabled";
			var option1 = document.createElement(\'option\');
			var option2 = document.createElement(\'option\');
			input.type = \'text\';
			input2.type = \'text\';
			input3.class = \'checkbox\';
			input.name = \'fieldvalues[title][]\';
			input2.name = \'fieldvalues[value][]\';
			input3.name = \'fieldvalues[disabled][]\';
			option1.value= \'0\';
			option2.value= \'1\';
			option1.text= \''.JText::_('JOOMEXT_NO',true).'\';
			option2.text= \''.JText::_('JOOMEXT_YES',true).'\';
			try { input3.add(option1, null); } catch(ex) { input3.add(option1); }
			try { input3.add(option2, null); } catch(ex) { input3.add(option2); }
			column.appendChild(input);
			column2.appendChild(input2);
			column3.appendChild(input3);
			newline.appendChild(column);
			newline.appendChild(column2);
			newline.appendChild(column3);
			newline.appendChild(column4);
			myTable.appendChild(newline);
			currentid = currentid+1;
		}
		function acymove(myid,diff){
			var previousId = myid + diff;
			if(!document.getElementById(\'option\'+previousId+\'title\')) return;
			var prevtitle = document.getElementById(\'option\'+previousId+\'title\').value;
			var prevvalue = document.getElementById(\'option\'+previousId+\'value\').value;
			var prevdisabled = document.getElementById(\'option\'+previousId+\'disabled\').value;
			document.getElementById(\'option\'+previousId+\'title\').value = document.getElementById(\'option\'+myid+\'title\').value;
			document.getElementById(\'option\'+previousId+\'value\').value = document.getElementById(\'option\'+myid+\'value\').value;
			document.getElementById(\'option\'+previousId+\'disabled\').value = document.getElementById(\'option\'+myid+\'disabled\').value;
			document.getElementById(\'option\'+myid+\'title\').value = prevtitle;
			document.getElementById(\'option\'+myid+\'value\').value = prevvalue;
			document.getElementById(\'option\'+myid+\'disabled\').value = prevdisabled;
		}';
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration( $script);
		$bar = & JToolBar::getInstance('toolbar');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','fields-form');
		$this->assignRef('fieldtype',acymailing::get('type.fields'));
		$this->assignRef('field',$field);
		$this->assignRef('fieldsClass',$fieldsClass);
	}
	function listing(){
		$db =& JFactory::getDBO();
		$db->setQuery('SELECT * FROM `#__acymailing_fields` ORDER BY `ordering` ASC');
		$rows = $db->loadObjectList();
		$bar = & JToolBar::getInstance('toolbar');
		JToolBarHelper::addNew();
		JToolBarHelper::editList();
		JToolBarHelper::deleteList(JText::_('ACY_VALIDDELETEITEMS'));
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','fields-listing');
		$bar->appendButton( 'Link', 'acymailing', JText::_('JOOMEXT_CPANEL'), acymailing::completeLink('dashboard') );
		jimport('joomla.html.pagination');
		$total = count($rows);
		$pagination = new JPagination($total, 0,$total);
		acymailing::setTitle(JText::_('EXTRA_FIELDS'),'fields','fields');
		$this->assignRef('rows',$rows);
		$this->assignRef('toggleClass',acymailing::get('helper.toggle'));
		$this->assignRef('pagination',$pagination);
		$this->assignRef('fieldtype',acymailing::get('type.fields'));
		$this->assignRef('fieldsClass',acymailing::get('class.fields'));
	}
}