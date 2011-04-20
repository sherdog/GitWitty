<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
acymailing::get('controller.newsletter');
class FollowupController extends NewsletterController{
	function listing(){
		$this->setRedirect(acymailing::completeLink('campaign',false,true));
	}
}