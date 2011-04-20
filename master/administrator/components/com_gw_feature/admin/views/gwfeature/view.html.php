<?php

jimport('joomla.application.component.view');

class GwFeatureViewGwFeature extends JView
{
	function display($tpl = null) 
	{
		JToolBarHelper::title(JText::_('Feature Banner Manager'));
		parent::display($tpl);	
	}
}
?>