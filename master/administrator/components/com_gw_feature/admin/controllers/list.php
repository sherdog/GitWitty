<?php
jimport('joomla.application.component.controller');

class GwFeatureControllerList extends JController
{
	function __contruct()
	{
		parent::__contruct();
	}
	
	function display() 
	{
		JRequest::setVar('view', 'list');
		parent::display();
	}
}

?>