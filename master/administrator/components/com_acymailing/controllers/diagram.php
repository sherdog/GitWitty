<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class DiagramController extends acymailingController{
	function listing(){
		return $this->lists();
	}
	function lists(){
		JRequest::setVar( 'layout', 'lists'  );
		return parent::display();
	}
	function subscription(){
		JRequest::setVar( 'layout', 'subscription'  );
		return parent::display();
	}
	function mailing(){
		JRequest::setVar( 'layout', 'mailing'  );
		return parent::display();
	}
}