<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
jimport('joomla.user.helper');
class plgAcymailingTagvmcoupon extends JPlugin
{
	function plgAcymailingTagvmcoupon(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin =& JPluginHelper::getPlugin('acymailing', 'tagvmcoupon');
			$this->params = new JParameter( $plugin->params );
		}
	}
	 function acymailing_getPluginType() {
	 	$onePlugin = null;
	 	$onePlugin->name = JText::_('ACY_COUPON');
	 	$onePlugin->function = 'acymailingtagvmcoupon_show';
	 	$onePlugin->help = 'plugin-tagvmcoupon';
	 	return $onePlugin;
	 }
	 function acymailingtagvmcoupon_show(){
	 	$value= array();
	 	$value[] = JHTML::_('select.option', 'percent|',JText::_('COUPON_PERCENT'));
	 	$value[] = JHTML::_('select.option', 'total|',JText::_('COUPON_TOTAL'));
	 	$percent_total = JHTML::_('select.radiolist', $value, 'coupon_percent' , 'onclick="updateTag();"', 'value', 'text', 'percent|');
	 	$value= array();
	 	$value[] = JHTML::_('select.option', 'permanent|',JText::_('COUPON_PERMANENT'));
	 	$value[] = JHTML::_('select.option', 'gift|',JText::_('COUPON_GIFT'));
	 	$permanent = JHTML::_('select.radiolist', $value, 'coupon_permanent' , 'onclick="updateTag();"', 'value', 'text', 'gift|');
?>
		<script language="javascript" type="text/javascript">
		<!--
			function updateTag(){
				tagname = '';
				for(var i=0; i < document.adminForm.coupon_percent.length; i++){
				   if (document.adminForm.coupon_percent[i].checked){ tagname += document.adminForm.coupon_percent[i].value; }
				}
				for(var i=0; i < document.adminForm.coupon_permanent.length; i++){
				   if (document.adminForm.coupon_permanent[i].checked){ tagname += document.adminForm.coupon_permanent[i].value; }
				}
				tagname += document.adminForm.coupon_value.value+'|';
				tagname += document.adminForm.coupon_name.value;
				setTag('{vmcoupon:'+tagname+'}');
			}
		//-->
		</script>
		<table class="adminlist" cellpadding="1">
		<tr><td><?php echo JText::_('COUPON_NAME'); ?></td><td><input id="coupon_name" onchange="updateTag();" type="text" size="30" value="[name][key][value]"></td><td><?php echo $permanent; ?></td></tr>
		<tr><td><?php echo JText::_('COUPON_VALUE'); ?></td><td><input id="coupon_value" onchange="updateTag();" type="text" size="10" value=""></td><td><?php echo $percent_total; ?></td></tr>
		</table>
<?php	 }
	function acymailing_replaceusertags(&$email,&$user){
		if(empty($user->subid)) return;
		$match = '#{vmcoupon:(.*)}#Ui';
		$variables = array('subject','body','altbody');
		$found = false;
		foreach($variables as $var){
			if(empty($email->$var)) continue;
			$found = preg_match_all($match,$email->$var,$results[$var]) || $found;
			if(empty($results[$var][0])) unset($results[$var]);
		}
		if(!$found) return;
		$tags = array();
		foreach($results as $var => $allresults){
			foreach($allresults[0] as $i => $oneTag){
				if(isset($tags[$oneTag])) continue;
				$tags[$oneTag] = $this->generateCoupon($allresults,$i,$user);
			}
		}
		foreach(array_keys($results) as $var){
			$email->$var = str_replace(array_keys($tags),$tags,$email->$var);
		}
	}
	function generateCoupon(&$allresults,$i,&$user){
		list($percent,$gift,$value,$name) = explode('|',$allresults[1][$i]);
		$db =& JFactory::getDBO();
		$key = JUserHelper::genrandompassword(5);
		$value = str_replace(',','.',$value);
		$name = str_replace(array('[name]','[subid]','[email]','[key]','[value]'),array($user->name,$user->subid,$user->email,$key,$value),$name);
		$db->setQuery('INSERT INTO '.acymailing::table('vm_coupons',false). '(`coupon_code`,`percent_or_total`,`coupon_type`,`coupon_value`) VALUES ('.$db->Quote($name).','.$db->Quote($percent).','.$db->Quote($gift).','.$db->Quote($value).')');
		$db->query();
		return $name;
	}
}//endclass