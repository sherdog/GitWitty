<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
include(ACYMAILING_BACK.'views'.DS.'newsletter'.DS.'view.html.php');
class AutonewsViewAutonews extends NewsletterViewNewsletter
{
	var $type = 'autonews';
	var $ctrl = 'autonews';
	var $nameListing = 'AUTONEWSLETTERS';
	var $nameForm = 'AUTONEW';
	var $icon = 'autonewsletter';
}