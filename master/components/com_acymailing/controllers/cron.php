<?php
/**
 * @copyright
 * @
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class CronController extends JController{
	function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerDefaultTask('cron');
	}
	function cron(){
		$config = acymailing::config();
		if($config->get('queue_type') == 'manual'){
			acymailing::display(JText::_('MANUAL_ONLY'),'info');
			return false;
		}
		$cronHelper = acymailing::get('helper.cron');
		$cronHelper->report = true;
		$launched = $cronHelper->cron();
		if($launched){
			$cronHelper->report();
		}
	}
}