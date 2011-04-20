<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class fieldsClass extends acymailingClass{
	var $tables = array('fields');
	var $pkey = 'fieldid';
	var $errors = array();
	var $prefix = 'field_';
	var $suffix = '';
	var $excludeValue = array();
	function getFields($area,&$user){
		$where = array();
		$where[] = 'a.`published` = 1';
		if($area == 'backend'){
			$where[] = 'a.`backend` = 1';
			$where[] = 'a.`core` = 0';
		}elseif($area == 'frontcomp'){
			$where[] = 'a.`frontcomp` = 1';
		}elseif($area != 'module'){
			return false;
		}
		$this->database->setQuery('SELECT * FROM `#__acymailing_fields` as a WHERE '.implode(' AND ',$where).' ORDER BY a.`ordering` ASC');
		$fields = $this->database->loadObjectList('namekey');
		foreach($fields as $namekey => $field){
			if(!empty($fields[$namekey]->options)){
				$fields[$namekey]->options = unserialize($fields[$namekey]->options);
			}
			if(!empty($field->value)){
				$fields[$namekey]->value = $this->explodeValues($fields[$namekey]->value);
			}
			if(empty($user->subid)) $user->$namekey = $field->default;
		}
		return $fields;
	}
	function getFieldName($field){
		return '<label for="'.$this->prefix.$field->namekey.$this->suffix.'">'.$this->trans($field->fieldname).'</label>';
	}
	function trans($name){
		if(preg_match('#^[A-Z_]*$#',$name)){
			return JText::_($name);
		}
		return $name;
	}
	function explodeValues($values){
		$allValues = explode("\n",$values);
		$returnedValues = array();
		foreach($allValues as $id => $oneVal){
			$line = explode('::',trim($oneVal));
			$var = @$line[0];
			$val = @$line[1];
			if(strlen($val)<1) continue;
			$obj = null;
			$obj->value = $val;
			for($i=2;$i<count($line);$i++){
				$obj->{$line[$i]} = 1;
			}
			$returnedValues[$var] = $obj;
		}
		return $returnedValues;
	}
	function get($fieldid){
		$query = 'SELECT a.* FROM '.acymailing::table('fields').' as a WHERE a.`fieldid` = '.intval($fieldid).' LIMIT 1';
		$this->database->setQuery($query);
		$field = $this->database->loadObject();
		if(!empty($field->options)){
			$field->options = unserialize($field->options);
		}
		if(!empty($field->value)){
			$field->value = $this->explodeValues($field->value);
		}
		return $field;
	}
function saveForm(){
    $field = null;
    $field->fieldid = acymailing::getCID('fieldid');
    $formData = JRequest::getVar( 'data', array(), '', 'array' );
    foreach($formData['fields'] as $column => $value){
      acymailing::secureField($column);
      if(is_array($value)) $value = implode(',',$value);
      $field->$column = strip_tags($value);
    }
	$fieldValues = JRequest::getVar('fieldvalues', array(), '', 'array' );
    if(!empty($fieldValues)){
    	$field->value = array();
    	foreach($fieldValues['title'] as $i => $title){
    		if(strlen($title)<1 AND strlen($fieldValues['value'][$i])<1) continue;
    		$value = strlen($fieldValues['value'][$i])<1 ? $title : $fieldValues['value'][$i];
    		$extra = '';
    		if(!empty($fieldValues['disabled'][$i])) $extra .= '::disabled';
    		$field->value[] = strip_tags($title).'::'.strip_tags($value).$extra;
    	}
    	$field->value = implode("\n",$field->value);
    }
	$fieldsOptions = JRequest::getVar( 'fieldsoptions', array(), '', 'array' );
    foreach($fieldsOptions as $column => $value){
      $fieldsOptions[$column] = strip_tags($value);
    }
    if($field->type == "customtext"){
		 $fieldsOptions['customtext'] = JRequest::getVar('fieldcustomtext','','','string',JREQUEST_ALLOWRAW);
		 if(empty($field->fieldid)) $field->namekey = 'customtext_'.date('z_G_i_s');
	}
    $field->options = serialize($fieldsOptions);
	if(empty($field->fieldid) AND $field->type != 'customtext'){
		if(empty($field->namekey)) $field->namekey = $field->fieldname;
		$field->namekey = preg_replace('#[^a-z0-9_\-]#i', '',strtolower($field->namekey));
		if(empty($field->namekey)){
			$this->errors[] = 'Please specify a namekey';
			return false;
		}
		$columnsTable = $this->database->getTableFields(acymailing::table('subscriber'));
		$columns = reset($columnsTable);
		if(isset($columns[$field->namekey])){
			$this->errors[] = 'The field "'.$field->namekey.'" already exists';
			return false;
		}
		$query = 'ALTER TABLE `#__acymailing_subscriber` ADD `'.$field->namekey.'` VARCHAR ( 250 ) NULL';
		$this->database->setQuery($query);
		$this->database->query();
	}
    $fieldid = $this->save($field);
    if(!$fieldid) return false;
    if(empty($field->fieldid)){
      $orderClass = acymailing::get('helper.order');
      $orderClass->pkey = 'fieldid';
      $orderClass->table = 'fields';
      $orderClass->reOrder();
    }
    JRequest::setVar( 'fieldid', $fieldid);
    return true;
  }
	function delete($elements){
		if(!is_array($elements)){
			$elements = array($elements);
		}
		foreach($elements as $key => $val){
			$elements[$key] = $this->database->getEscaped($val);
		}
		if(empty($elements)) return false;
		$this->database->setQuery('SELECT `namekey`,`fieldid` FROM `#__acymailing_fields`  WHERE `core` = 0 AND `fieldid` IN ('.implode(',',$elements).')');
		$fieldsToDelete = $this->database->loadObjectList('fieldid');
		if(empty($fieldsToDelete)) return false;
		$namekeys = array();
		foreach($fieldsToDelete as $oneField){
			if(substr($oneField->namekey,0,11) == 'customtext_') continue;
			$namekeys[] = $oneField->namekey;
		}
		if(!empty($namekeys)){
			$this->database->setQuery('ALTER TABLE `#__acymailing_subscriber` DROP `'.implode('`, DROP `',$namekeys)).'`';
			$this->database->query();
		}
		$this->database->setQuery('DELETE FROM `#__acymailing_fields` WHERE `fieldid` IN ('.implode(',',array_keys($fieldsToDelete)).')');
		$result = $this->database->query();
		if(!$result) return false;
		$affectedRows = $this->database->getAffectedRows();
		$orderClass = acymailing::get('helper.order');
		$orderClass->pkey = 'fieldid';
		$orderClass->table = 'fields';
		$orderClass->reOrder();
		return $affectedRows;
	}
	function display($field,$value,$map,$inside = false){
		$functionType = '_display'.ucfirst($field->type);
		return $this->$functionType($field,$value,$map,$inside);
	}
	function _displayText($field,$value,$map,$inside){
		$size = empty($field->options['size']) ? '' : 'size="'.intval($field->options['size']).'"';
		$js = '';
		if($inside AND strlen($value) < 1){
			$value = addslashes($this->trans($field->fieldname));
			$this->excludeValue[$field->namekey] = $value;
			$js = 'onfocus="if(this.value == \''.$value.'\') this.value = \'\';" onblur="if(this.value==\'\') this.value=\''.$value.'\';"';
		}
		$id = str_replace(' ','_',$this->prefix.$field->namekey.$this->suffix);
		return '<input id="'.$id.'" '.$size.' '.$js.' type="text" class="inputbox" name="'.$map.'" value="'.htmlspecialchars($value,ENT_COMPAT, 'UTF-8').'" />';
	}
	function _displayTextarea($field,$value,$map,$inside){
		$js = '';
		if($inside AND strlen($value) < 1){
			$value = addslashes($this->trans($field->fieldname));
			$this->excludeValue[$field->namekey] = $value;
			$js = 'onfocus="if(this.value == \''.$value.'\') this.value = \'\';" onblur="if(this.value==\'\') this.value=\''.$value.'\';"';
		}
		$cols = empty($field->options['cols']) ? '' : 'cols="'.intval($field->options['cols']).'"';
		$rows = empty($field->options['rows']) ? '' : 'rows="'.intval($field->options['rows']).'"';
		return '<textarea class="inputbox" id="'.$this->prefix.$field->namekey.$this->suffix.'" name="'.$map.'" '.$cols.' '.$rows.' '.$js.'>'.$value.'</textarea>';
	}
	function _displayCustomtext($field,$value,$map,$inside){
		return @$field->options['customtext'];
	}
	function _displayRadio($field,$value,$map,$inside){
		return $this->_displayRadioCheck($field,$value,$map,'radio',$inside);
	}
	function _displaySingledropdown($field,$value,$map,$inside){
		return $this->_displayDropdown($field,$value,$map,'single',$inside);
	}
	function _displayMultipledropdown($field,$value,$map,$inside){
		$value = explode(',',$value);
		return $this->_displayDropdown($field,$value,$map,'multiple',$inside);
	}
	function _displayDropdown($field,$value,$map,$type,$inside){
		$string = '';
		if($type == "multiple"){
			$string.= '<input type="hidden" name="'.$map.'" value=" "/>';
			$map.='[]';
			$arg = 'multiple="multiple"';
			if(!empty($field->options['size'])) $arg .= ' size="'.intval($field->options['size']).'"';
		}else{
			$arg = 'size="1"';
		}
		$string .= '<select id="'.$this->prefix.$field->namekey.$this->suffix.'" name="'.$map.'" '.$arg.' >';
		if(empty($field->value)) return $string;
		foreach($field->value as $oneValue => $myValue){
			$selected = ((is_string($value) AND $oneValue == $value) OR is_array($value) AND in_array($oneValue,$value)) ? 'selected="selected"' : '';
			$id = str_replace(' ','_',$this->prefix.$field->namekey.$this->suffix.'_'.$oneValue);
			$disabled = empty($myValue->disabled) ? '' : 'disabled="disabled"';
			$string .= '<option value="'.$oneValue.'" id="'.$id.'" '.$disabled.' '.$selected.' >'.$this->trans($myValue->value).'</option>';
		}
		$string .= '</select>';
		return $string;
	}
	function _displayRadioCheck($field,$value,$map,$type,$inside){
		$string = '';
		if($inside) $string = $this->trans($field->fieldname).' ';
		if($type == 'checkbox'){
			$string.= '<input type="hidden" name="'.$map.'" value=" " />';
			$map.='[]';
		}
		if(empty($field->value)) return $string;
		foreach($field->value as $oneValue => $myValue){
			$checked = ((is_string($value) AND $oneValue == $value) OR is_array($value) AND in_array($oneValue,$value)) ? 'checked="checked"' : '';
			$id = str_replace(' ','_',$this->prefix.$field->namekey.$this->suffix.'_'.$oneValue);
			$disabled = empty($myValue->disabled) ? '' : 'disabled="disabled"';
			$string .= '<input type="'.$type.'" name="'.$map.'" value="'.$oneValue.'" id="'.$id.'" '.$disabled.' '.$checked.' /><label for="'.$id.'">'.$this->trans($myValue->value).'</label>';
		}
		return $string;
	}
	function _displayDate($field,$value,$map,$inside){
		if(empty($field->options['format'])) $field->options['format'] = "%Y-%m-%d";
		$extra = empty($field->options['size']) ? '' : 'size="'.$field->options['size'].'"';
		if($inside AND strlen($value) < 1){
			$value = addslashes($this->trans($field->fieldname));
			$this->excludeValue[$field->namekey] = $value;
			$extra .= ' onfocus="if(this.value == \''.$value.'\') this.value = \'\';" onblur="if(this.value==\'\') this.value=\''.$value.'\';"';
		}
		return JHTML::_('calendar', $value, $map,$this->prefix.$field->namekey.$this->suffix,$field->options['format'],$extra);
	}
	function _displayBirthday($field,$value,$map,$inside){
		if(empty($field->options['format'])) $field->options['format'] = "%d %m %Y";
		$vals = explode('-',$value);
		$days = array();
		$days[] =  JHTML::_('select.option','','- - -');
		for($i=1;$i<32;$i++) $days[] = JHTML::_('select.option',$i,$i);
		$years = array();
		$years[] =  JHTML::_('select.option','','- - -');
		for($i=date('Y');$i>1901;$i--) $years[] = JHTML::_('select.option',$i,$i);
		$months = array();
		$months[] = JHTML::_('select.option','','- - -');
		$months[] = JHTML::_('select.option',1,JText::_('JANUARY'));
		$months[] = JHTML::_('select.option',2,JText::_('FEBRUARY'));
		$months[] = JHTML::_('select.option',3,JText::_('MARCH'));
		$months[] = JHTML::_('select.option',4,JText::_('APRIL'));
		$months[] = JHTML::_('select.option',5,JText::_('MAY'));
		$months[] = JHTML::_('select.option',6,JText::_('JUNE'));
		$months[] = JHTML::_('select.option',7,JText::_('JULY'));
		$months[] = JHTML::_('select.option',8,JText::_('AUGUST'));
		$months[] = JHTML::_('select.option',9,JText::_('SEPTEMBER'));
		$months[] = JHTML::_('select.option',10,JText::_('OCTOBER'));
		$months[] = JHTML::_('select.option',11,JText::_('NOVEMBER'));
		$months[] = JHTML::_('select.option',12,JText::_('DECEMBER'));
		$dayField = JHTML::_('select.genericlist',   $days, $map.'[day]', '', 'value', 'text',intval(@$vals[2]),$this->prefix.$field->namekey.$this->suffix.'_day');
		$monthField = JHTML::_('select.genericlist', $months  , $map.'[month]', '', 'value', 'text',intval(@$vals[1]),$this->prefix.$field->namekey.$this->suffix.'_month');
		$yearField = JHTML::_('select.genericlist',$years   , $map.'[year]', '', 'value', 'text',intval(@$vals[0]),$this->prefix.$field->namekey.$this->suffix.'_year');
		return str_replace(array('%d','%m','%Y'),array($dayField,$monthField,$yearField),$field->options['format']);
	}
	function _displayCheckbox($field,$value,$map,$inside){
		$value = explode(',',$value);
		return $this->_displayRadioCheck($field,$value,$map,'checkbox',$inside);
	}
}