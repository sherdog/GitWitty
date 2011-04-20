<?php
defined( '_JEXEC' ) or die( 'Restricted Access' );

class TOOLBAR_gwfeature 
{
	
	function _NEW() {
		JToolBarHelper::save();
		JToolBarHelper::apply();	
		JToolBarHelper::cancel();
	}
	
	function _DEFAULT() {
		JToolBarHelper::makeDefault();
		JToolBarHelper::cancel();
	}
		
}

?>