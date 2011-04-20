<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class plgAcymailingTagmodule extends JPlugin
{
	function plgAcymailingTagmodule(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$db =& JFactory::getDBO();
			$db->setQuery("SELECT `params` FROM `#__plugins` WHERE `element` = 'tagmodule' AND `folder`= 'acymailing' LIMIT 1");
			$params = $db->loadResult();
			$this->params = new JParameter( $params );
	    }
    }
	 function acymailing_getPluginType() {
	 	$onePlugin = null;
	 	$onePlugin->name = 'Joomla Module';
	 	$onePlugin->function = 'acymailingtagmodule_show';
	 	$onePlugin->help = 'plugin-tagmodule';
	 	return $onePlugin;
	 }
	 function acymailingtagmodule_show(){
	?>
<script language="javascript" type="text/javascript">
		<!--
			function insertModule(id){
				tagString = '{module:'+id;
				if(window.document.getElementById('jflang')  && window.document.getElementById('jflang').value != ''){
					tagString += '|lang:';
					tagString += window.document.getElementById('jflang').value;
				}
				tagString += '}';
				setTag(tagString);
				insertTag();
			}
		//-->
</script>
	<?php
		$jflanguages = acymailing::get('type.jflanguages');
		echo $jflanguages->display('lang');
		$excludedModules = array('mod_poll','mod_login','mod_breadcrumbs','mod_acymailing','mod_wrapper');
		$text = '<table class="adminlist" cellpadding="1" width="100%">';
		$db =& JFactory::getDBO();
		$db->setQuery('SELECT id, title, position, module FROM #__modules WHERE `module` NOT IN (\''.implode('\',\'',$excludedModules).'\') ORDER BY `position`,`ordering`');
		$modules =$db->loadObjectList();
		$k = 0;
		foreach($modules as $oneModule){
			$text .= '<tr style="cursor:pointer" class="row'.$k.'" onclick="insertModule(\''.$oneModule->id.'\');" ><td>'.$oneModule->title.'</td><td nowrap="nowrap" width="60px">'.$oneModule->module.'</td><td nowrap="nowrap" width="40px">'.$oneModule->position.'</td></tr>';
			$k = 1-$k;
		}
		$text .= '</table>';
		echo $text;
	 }
	 function acymailing_replacetags(&$email){
		$match = '#{module:([0-9]*)(\|lang:(.*))?}#Ui';
		$variables = array('body','altbody');
		$found = false;
		foreach($variables as $var){
			if(empty($email->$var)) continue;
			$found = preg_match_all($match,$email->$var,$results[$var]) || $found;
			if(empty($results[$var][0])) unset($results[$var]);
		}
		if(!$found) return;
		$values = null;
		$tags = array();
		$textVersion = array();
		$config =& acymailing::config();
		$mailHelper = acymailing::get('helper.mailer');
		$itemid = $config->get('itemid');
		$item = empty($itemid) ? '' : '&Itemid='.$itemid;
		@ini_set('default_socket_timeout',10);
		@ini_set('user_agent', 'My-Application/2.5');
		@ini_set('allow_url_fopen', '1');
		foreach($results as $var => $allresults){
			foreach($allresults[0] as $i => $oneTag){
				$lang = empty($allresults[3][$i]) ? '' : '&lang='.substr($allresults[3][$i],0,strpos($allresults[3][$i],','));
				if(isset($tags[$oneTag])) continue;
				$loc = ACYMAILING_LIVE.'index.php?option=com_acymailing&tmpl=component&ctrl=moduleloader&id='.$allresults[1][$i].'&time='.time().$lang.$item;
				if(function_exists('curl_init') AND ($this->params->get('getmethod') =='curl' OR !ini_get('allow_url_fopen'))){
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL,$loc);
					curl_setopt($ch, CURLOPT_FAILONERROR, 1);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
					curl_setopt($ch, CURLOPT_TIMEOUT, 10);
					$tags[$oneTag] = curl_exec($ch);
					curl_close($ch);
				}else{
					$tags[$oneTag] = file_get_contents($loc);
				}
				$localone = str_replace(ACYMAILING_LIVE,'',$loc);
				$tags[$oneTag] = str_replace(array($localone,str_replace('&','&amp;',$localone)),'index.php',$tags[$oneTag]);
				$tags[$oneTag] = preg_replace("#(onclick|onfocus|onload|onblur) *= *\"(?:(?!\").)*\"#iU",'',$tags[$oneTag]);
				$tags[$oneTag] =  preg_replace("#< *script(?:(?!< */ *script *>).)*< */ *script *>#isU",'',$tags[$oneTag]);
				$textVersion[$oneTag] = $mailHelper->textVersion($tags[$oneTag]);
			}
		}
		if(!empty($email->body)) $email->body = str_replace(array_keys($tags),$tags,$email->body);
		if(!empty($email->altbody)) $email->altbody = str_replace(array_keys($textVersion),$textVersion,$email->altbody);
	 }//endfct
	 function onTestPlugin(){
	 	$config =& acymailing::config();
		$itemid = $config->get('itemid');
		$item = empty($itemid) ? '' : '&Itemid='.$itemid;
	 	@ini_set('default_socket_timeout',10);
		@ini_set('user_agent', 'My-Application/2.5');
		@ini_set('allow_url_fopen', '1');
		$loc = ACYMAILING_LIVE.'index.php?option=com_acymailing&tmpl=component&ctrl=moduleloader&time='.time().$item;
		if(function_exists('curl_init') AND ($this->params->get('getmethod') =='curl' OR !ini_get('allow_url_fopen'))){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$loc);
			curl_setopt($ch, CURLOPT_FAILONERROR, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			$result = curl_exec($ch);
			curl_close($ch);
			acymailing::display('Using CURL method','info');
			if($result){
				acymailing::display($result,'success');
			}else{
				acymailing::display($result,'error');
			}
		}else{
			acymailing::display('Using File_get_contents function','info');
			$result = file_get_contents($loc);
			if($result){
				acymailing::display($result,'success');
			}else{
				acymailing::display('Error. Please make sure the function file_get_contents is enabled on your website','error');
				if(function_exists('curl_init')){
					acymailing::display('The cURL function is apparently enabled on your server so you should select the cURL option','info');
				}
			}
		}
	 }
}//endclass