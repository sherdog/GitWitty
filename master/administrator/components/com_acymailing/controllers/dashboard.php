<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class DashboardController extends JController{
	function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask('listing','display');
		$this->registerDefaultTask('listing');
	}
}