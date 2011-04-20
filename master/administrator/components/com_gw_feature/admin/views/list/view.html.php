<?php

jimport('joomla.application.component.view');
	class GwFeatureViewlist extends JView
	{
		function display($tpl=null) {
			JToolBarHelper::title(JText::_('Select a banner'));
			parent::display($tpl);	
		}
	}

?>