<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
include(ACYMAILING_BACK.'views'.DS.'newsletter'.DS.'view.html.php');
class FollowupViewFollowup extends NewsletterViewNewsletter
{
	var $type = 'followup';
	var $ctrl = 'followup';
	var $nameForm = 'FOLLOWUP';
}