<?php
/**
 * @copyright
 * @
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class cronreportType{
	function cronreportType(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0',JText::_('NONE'));
		$this->values[] = JHTML::_('select.option', '1',JText::_('EACH_TIME'));
		$this->values[] = JHTML::_('select.option', '2',JText::_('ONLY_ACTION'));
		$js = "function updateCronReport(){";
			$js .= "cronsendreport = window.document.getElementById('cronsendreport').value;";
			$js .= "if(cronsendreport != 0) {window.document.getElementById('cronreportdetail').style.display = 'block';}else{window.document.getElementById('cronreportdetail').style.display = 'none';}";
		$js .= '}';
		$js .='window.addEvent(\'domready\', function(){ updateCronReport(); });';
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
	}
	function display($map,$value){
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" onchange="updateCronReport();"', 'value', 'text', (int) $value ,'cronsendreport');
	}
}