<?php
defined( '_JEXEC' ) or die( 'Restricted Access' );

require_once(JApplicationHelper::getPath('toolbar_html'));

switch($task) {
	case 'add' :
		TOOLBAR_gwfeature::_NEW();
	break;
	default:
		TOOLBAR_gwfeature::_DEFAULT();
	break;
}	

?>