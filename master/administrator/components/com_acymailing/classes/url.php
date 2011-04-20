<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class urlClass extends acymailingClass{
	var $tables = array('urlclick','url');
	var $pkey = 'urlid';
	function get($urlid){
		$column = is_numeric($urlid) ? 'urlid' : 'url';
		$query = 'SELECT * FROM '.acymailing::table('url').' WHERE '.$column.' = '.$this->database->Quote($urlid).' LIMIT 1';
		$this->database->setQuery($query);
		return $this->database->loadObject();
	}
	function getUrl($url,$mailid,$subid){
		static $allurls;
		$url = str_replace('&amp;','&',$url);
		if(strlen($url) > 249 ) return;
		if(empty($allurls[$url])){
			$currentURL = $this->get($url);
			if(empty($currentURL->urlid)){
				$currentURL = null;
				$currentURL->url = $url;
				$currentURL->name = $url;
				$currentURL->urlid = $this->save($currentURL);
			}
			$allurls[$url] = $currentURL;
		}else{
			$currentURL = $allurls[$url];
		}
		$config = acymailing::config();
		$itemId = $config->get('itemid',0);
		$item = empty($itemId) ? '' : '&Itemid='.$itemId;
		if(empty($currentURL->urlid)) return;
		return str_replace('&amp;','&',acymailing::frontendLink('index.php?option=com_acymailing&ctrl=url&urlid='.$currentURL->urlid.'&mailid='.$mailid.'&subid='.$subid.$item));
	}
	function saveForm(){
		$object = null;
		$object->urlid = acymailing::getCID('urlid');
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		foreach($formData['url'] as $column => $value){
			acymailing::secureField($column);
			$object->$column = strip_tags($value);
		}
		$urlid = $this->save($object);
		if(!$urlid) return false;
		$js = "window.addEvent('domready', function(){
				var allLinks = window.parent.document.getElements('a[id^=urlink_".$urlid."_]');
				i=0;
				while(allLinks[i]){
					allLinks[i].innerHTML = '".str_replace(array("'",'"'),array("&#039;",'&quot;'),$object->name)."';
					i++;
				}
				try{ window.parent.document.getElementById('sbox-window').close(); }catch(err){ window.parent.SqueezeBox.close(); }
				})";
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
		return true;
	}
}