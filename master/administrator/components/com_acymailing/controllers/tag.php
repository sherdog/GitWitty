<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class TagController extends acymailingController{
	function __construct($config = array())
	{
		parent::__construct($config);
		JHTML::_('behavior.tooltip');
		JRequest::setVar('tmpl','component');
		$this->registerDefaultTask('tag');
	}
	function tag(){
		JRequest::setVar( 'layout', 'tag'  );
		return parent::display();
	}
}