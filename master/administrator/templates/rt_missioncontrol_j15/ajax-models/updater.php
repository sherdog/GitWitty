<?php
/**
 * @version � 0.1.4 December 6, 2010
 * @author � �RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2010 RocketTheme, LLC
 * @license � http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted index access' );

require_once(dirname(__FILE__).DS.'..'.DS.'lib'.DS.'rtmcupdater.class.php');

echo MCUpdater::display(true);

?>

