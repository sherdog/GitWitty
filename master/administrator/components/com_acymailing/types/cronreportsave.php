<?php
/**
 * @copyright
 * @
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class cronreportsaveType{
	function cronreportsaveType(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0',JText::_('No'));
		$this->values[] = JHTML::_('select.option', '1',JText::_('SIMPLIFIED_REPORT'));
		$this->values[] = JHTML::_('select.option', '2',JText::_('DETAILED_REPORT'));
		$js = "function updateCronReportSave(){";
			$js .= "cronsavereport = window.document.getElementById('cronsavereport').value;";
			$js .= "if(cronsavereport != 0) {window.document.getElementById('cronreportsave').style.display = 'block';}else{window.document.getElementById('cronreportsave').style.display = 'none';}";
		$js .= '}';
		$js .='window.addEvent(\'domready\', function(){ updateCronReportSave(); });';
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
	}
	function display($map,$value){
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" onchange="updateCronReportSave();"', 'value', 'text', (int) $value ,'cronsavereport');
	}
}