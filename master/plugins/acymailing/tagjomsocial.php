<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class plgAcymailingTagjomsocial extends JPlugin
{
	function plgAcymailingTagjomsocial(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin =& JPluginHelper::getPlugin('acymailing', 'tagjomsocial');
			$this->params = new JParameter( $plugin->params );
		}
	}
	 function acymailing_getPluginType() {
	 	$onePlugin = null;
	 	$onePlugin->name = 'jomSocial';
	 	$onePlugin->function = 'acymailingtagjomsocial_show';
	 	$onePlugin->help = 'plugin-tagjomsocial';
	 	return $onePlugin;
	 }
	 function acymailingtagjomsocial_show(){
		$text = '<table class="adminlist" cellpadding="1">';
		$db =& JFactory::getDBO();
		$tableInfos = $db->getTableFields(acymailing::table('community_users',false));
		$fields = reset($tableInfos);
		$db->setQuery("SELECT `name`, `id` FROM `#__community_fields` WHERE `type` != 'group' ORDER BY `ordering` ASC");
		$extraFields = $db->loadObjectList();
		$k = 0;
		foreach($extraFields as $oneField){
			$text .= '<tr style="cursor:pointer" class="row'.$k.'" onclick="setTag(\'{jomsocial:'.$oneField->id.'}\');insertTag();" ><td>'.$oneField->name.'</td></tr>';
			$k = 1-$k;
		}
		foreach($fields as $fieldname => $oneField){
			if(in_array($fieldname,array('params'))) continue;
			$type = '';
			if(strpos(strtolower($oneField),'date') !== false) $type = '|type:date';
			$text .= '<tr style="cursor:pointer" class="row'.$k.'" onclick="setTag(\'{jomsocial:'.$fieldname.$type.'}\');insertTag();" ><td>'.$fieldname.'</td></tr>';
			$k = 1-$k;
		}
		$text .= '</table>';
		echo $text;
	 }
	function acymailing_replaceusertagspreview(&$email,&$user){
		return $this->acymailing_replaceusertags($email,$user);
	}
	function acymailing_replaceusertags(&$email,&$user){
		$db= JFactory::getDBO();
		$match = '#{jomsocial:(.*)}#Ui';
		$variables = array('subject','body','altbody');
		$found = false;
		foreach($variables as $var){
			if(empty($email->$var)) continue;
			$found = preg_match_all($match,$email->$var,$results[$var]) || $found;
			if(empty($results[$var][0])) unset($results[$var]);
		}
		if(!$found) return;
		$valuesNum = null;
		$valuesString = null;
		$tags = array();
		foreach($results as $var => $allresults){
			foreach($allresults[0] as $i => $oneTag){
				if(isset($tags[$oneTag])) continue;
				$field = $allresults[1][$i];
				$tags[$oneTag] = $this->params->get('default_'.$field,'');
				if(empty($user->userid)) continue;
				if(is_numeric($field)){
					if($valuesNum === null){
						$db->setQuery('SELECT `field_id`,`value` FROM '.acymailing::table('community_fields_values',false).' WHERE user_id = '.$user->userid);
						$valuesNum = $db->loadObjectList('field_id');
					}
					if(isset($valuesNum[$field]->value)) $tags[$oneTag] = $valuesNum[$field]->value;
				}else{
					if($valuesString === null){
						$db->setQuery('SELECT * FROM '.acymailing::table('community_users',false).' WHERE userid = '.$user->userid.' LIMIT 1');
						$valuesString = $db->loadObject();
					}
					if(isset($valuesString->$field)) $tags[$oneTag] = $valuesString->$field;
					if(in_array($field,array('avatar','thumb'))){
						$tags[$oneTag] = '<img src="'.ACYMAILING_LIVE.$valuesString->$field.'"/>';
					}
				}
			}
		}
		foreach($results as $var => $allresults){
			$email->$var = str_replace(array_keys($tags),$tags,$email->$var);
		}
	 }//endfct
 	function onAcyDisplayFilters(&$type){
		$db =& JFactory::getDBO();
		$db->setQuery("SELECT `id`,`name` FROM #__community_fields WHERE `type` != 'group' ORDER BY `ordering` ASC");
		$fields = $db->loadObjectList();
		if(empty($fields)) return;
		$type['jomsocialfield'] = 'jomSocial';
		$jomsocialfields = array();
		foreach($fields as $oneField => $fieldType){
			$jomsocialfields[] = JHTML::_('select.option',$fieldType->id,$fieldType->name);
		}
		$operators = acymailing::get('type.operators');
		$return = '<div id="filter__num__jomsocialfield">'.JHTML::_('select.genericlist',   $jomsocialfields, "filter[__num__][jomsocialfield][map]", 'class="inputbox" size="1"', 'value', 'text');
		$return.= ' '.$operators->display("filter[__num__][jomsocialfield][operator]").' <input class="inputbox" type="text" name="filter[__num__][jomsocialfield][value]" size="50" value=""></div>';
		$db->setQuery('SELECT a.id as groupid, a.name as groupname, b.name as categoryname, a.categoryid, a.membercount FROM #__community_groups as a LEFT JOIN #__community_groups_category as b on a.categoryid = b.id ORDER BY categoryname ASC, groupname ASC');
		$allGroups = $db->loadObjectList();
		if(empty($allGroups)) return $return;
		$type['jomsocialgroup'] = 'jomSocial Group';
		$currentCat = '';
		$dispGroups = array();
		foreach($allGroups as $oneGroup){
			if($currentCat != $oneGroup->categoryid){
				if(!empty($dispGroups)) $dispGroups[] = JHTML::_('select.option','</OPTGROUP>');
				$dispGroups[] = JHTML::_('select.option','<OPTGROUP>',$oneGroup->categoryname);
				$currentCat = $oneGroup->categoryid;
			}
			$dispGroups[] = JHTML::_('select.option',$oneGroup->groupid,$oneGroup->groupname.'  ( '.$oneGroup->membercount.' )');
		}
		$inoperator = acymailing::get('type.operatorsin');
		$return .= '<div id="filter__num__jomsocialgroup">';
		$return .= $inoperator->display("filter[__num__][jomsocialgroup][type]").' '.JHTML::_('select.genericlist',   $dispGroups, "filter[__num__][jomsocialgroup][group]", 'class="inputbox" size="1"', 'value', 'text');
		$return .= '</div>';
		return $return;
	 }
	 function onAcyProcessFilter_jomsocialgroup(&$query,$filter,$num){
	 	$query->leftjoin['jomsocial_group_'.$num] = "`#__community_groups_members` as jomsocialgroup$num ON jomsocialgroup$num.`memberid` = sub.`userid` AND jomsocialgroup$num.`groupid` = ".(int) $filter['group']." AND jomsocialgroup$num.`approved` = 1";
	 	$query->where['member'] = 'sub.userid > 0';
	 	$operator = ($filter['type'] == 'IN') ? 'IS NOT NULL' : "IS NULL";
	 	$query->where[] = "jomsocialgroup$num.`memberid` ".$operator;
	 }
	  function onAcyProcessFilter_jomsocialfield(&$query,$filter,$num){
		$myquery = "SELECT DISTINCT a.user_id FROM #__community_fields_values as a WHERE a.field_id = ".intval($filter['map'])." AND ".$query->convertQuery('a','value',$filter['operator'],$filter['value']);
		$query->db->setQuery($myquery);
		$allUseridJomSocial  = $query->db->loadResultArray();
		if(empty($allUseridJomSocial)) $allUseridJomSocial[] = -1;
		$query->where[] = 'sub.userid IN ('.implode(',',$allUseridJomSocial).')';
	 }
}//endclass