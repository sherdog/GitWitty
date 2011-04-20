<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class UrlController extends JController{
	function __construct($config = array())
	{
		parent::__construct($config);
		JRequest::setVar('tmpl','component');
		$this->registerDefaultTask('click');
	}
	function click(){
		$urlid = JRequest::getInt('urlid');
		$mailid = JRequest::getInt('mailid');
		$subid = JRequest::getInt('subid');
		$urlClass = acymailing::get('class.url');
		$urlObject = $urlClass->get($urlid);
		if(empty($urlObject->urlid)){
			return JError::raiseError( 404, JText::_( 'Page not found'));
		}
		$urlClickClass = acymailing::get('class.urlclick');
		$urlClickClass->addClick($urlObject->urlid,$mailid,$subid);
		$this->setRedirect($urlObject->url);
	}
}