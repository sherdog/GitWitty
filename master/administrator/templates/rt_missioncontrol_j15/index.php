<?php
/**
 * @version � 0.1.3 November 2, 2010
 * @author � �RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2010 RocketTheme, LLC
 * @license � http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted index access' );

// load and init the MissioControl Class
require_once('lib/missioncontrol.class.php');

global $mctrl;
$mctrl =& MissionControl::getInstance();
$mctrl->processAjax();
$mctrl->initRenderer();
$mctrl->addStyle("core.css");
$mctrl->addStyle("menu.css");
$mctrl->addStyle("colors.css.php");
$mctrl->addScript('MC.js');
$mctrl->addStyle("http://fonts.googleapis.com/css?family=Yanone+Kaffeesatz");

$mctrl->addOverrideStyles();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $mctrl->language; ?>" lang="<?php echo $mctrl->language; ?>" dir="<?php echo $mctrl->direction; ?>">
	<head>
		<jdoc:include type="head" />
        <script src="http://code.jquery.com/jquery-1.4.4.min.js"></script>
        <script type="text/javascript">jQuery.noConflict();</script>
	</head>
	<body id="mc-standard" class="<?php $mctrl->displayBodyTags(); ?>">
		<div id="mc-frame">
			<div id="mc-header">
				<div class="mc-wrapper">
					<div id="mc-status">
						<?php $mctrl->displayStatus(); ?>
					</div>
					<div id="mc-logo">
						<?php $mctrl->displayLogo(); ?>
						<h1><?php echo $mctrl->params->get('adminTitle') ? $mctrl->params->get('adminTitle') : JText::_('Administration'); ?></h1>
					</div>
					<div id="mc-nav">
						<?php $mctrl->displayMenu(); ?>
					</div>
					<div id="mc-userinfo">
						<?php $mctrl->displayUserInfo(); ?>
					</div>
					<div class="clr"></div>
				</div>
			</div>
			<div id="mc-body">
				<div class="mc-wrapper">
					<jdoc:include type="message" />
					<div id="mc-title">
						<?php $mctrl->displayTitle(); ?>
						<?php $mctrl->displayHelpButton(); ?>
						<?php $mctrl->displayToolbar(); ?>
						<div class="clr"></div>
					</div>
					<div id="mc-submenu">
						<?php $mctrl->displaySubMenu(); ?>
					</div>
					
				
					<?php if ($option == 'com_cpanel') : ?>
					<div id="mc-sidebar">
						<jdoc:include type="modules" name="sidebar" style="sidebar"  />
					</div>
					<div id="mc-cpanel">
						<?php $mctrl->displayDashText(); ?>
						<jdoc:include type="modules" name="dashboard" style="standard"  />
					<?php endif; ?>
					
					<div id="mc-component">
						<jdoc:include type="component" />
					</div>
					<?php if ($option == 'com_cpanel') : ?>
					</div>					
					<?php endif; ?>
					<div class="clr"></div>
				</div>
			</div>	
			<div id="mc-footer">
				<div class="mc-wrapper">
					<p class="copyright">
						
					</p>
				</div>
			</div>
			<div id="mc-message">
				
			</div>
		</div>
	</body>
</html>
