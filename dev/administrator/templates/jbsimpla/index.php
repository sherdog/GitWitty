<?php

/**
 * @copyright	Copyright (C) Joomla Bamboo. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );
$menutype = $this->params->get('menutype');
$logo = $this->params->get('logo', 'image');
$yourCopyright = $this->params->get('yourCopyright', 'Joomla admin theme designed by <a href="www.joomlabamboo.com">Joomla Bamboo</a>');
$joomlaText = $this->params->get('joomlaText', '1');
$topMenuStyle = $this->params->get('topMenuStyle', 'white');

// Overrides the combobox.js in the system with a new one that fits the template style if necessary
$change = false;
$headData = $this->getHeadData(); 
$scripts = array_keys($headData['scripts']);
$path = JURI::getInstance();
$combobox = substr($path->getPath(), 0, -23) . 'media/system/js/combobox.js';
if (in_array($combobox,$scripts)) { 
	foreach($scripts as $script) {
		if ($script !== $combobox) {
			$newscripts[$script] = 'text/javascript';
		}
		else {
			$comboboxpath = substr($path->getPath(), 0, -9) . 'templates/' . $this->template . '/js/combobox.js';
			$newscripts[$comboboxpath] = 'text/javascript';
		}
	}
	$headData['scripts'] = $newscripts;
	$this->setHeadData($headData);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo  $this->language; ?>" lang="<?php echo  $this->language; ?>">
<head>
<jdoc:include type="head" />
<link rel="stylesheet" href="templates/system/css/system.css" type="text/css" />
<?php if ($this->params->get('color') !== 'green') echo '<link href="templates/'.$this->template.'/css/'.$this->params->get('color').'.css" rel="stylesheet" type="text/css" />'; ?>
<script type="text/javascript" src="templates/<?php echo  $this->template ?>/js/admin.js"></script>
<?php if ($this->params->get('shortcut_tips') == '1') : ?>
<script type="text/javascript" src="templates/<?php echo  $this->template ?>/js/mootips.js"></script>
<?php endif; ?>
<link href="templates/<?php echo  $this->template ?>/css/template_css.css" rel="stylesheet" type="text/css" />
<!--[if IE 8]>
<link href="templates/<?php echo  $this->template ?>/css/ie8.css" rel="stylesheet" type="text/css" />
<![endif]-->
<!--[if IE 7]>
<link href="templates/<?php echo  $this->template ?>/css/ie7.css" rel="stylesheet" type="text/css" />
<![endif]-->
<!--[if lte IE 6]>

<link href="templates/<?php echo  $this->template ?>/css/ie6.css" rel="stylesheet" type="text/css" />
<![endif]-->
</head>

<body class="<?php echo $this->params->get('color'); ?><?php if ($menutype == 'topbar') echo ' full'; ?>">
<?php if ($menutype == 'sidebar' || $menutype == 'both') : ?>
<div id="header-box">
	<?php if ($logo == 'image') : ?>
	<div id="logo"> <a href="index.php"><img src="templates/<?php echo  $this->template ?>/images/logo.png" alt="" /></a> </div>
	<?php endif; ?>
	<?php if ($logo == 'text') : ?>
	<div id="logoText"> <?php echo $mainframe->getCfg('sitename');?> </div>
	<?php endif; ?>
	<div class="clear"></div>
	<div id="module-menu"<?php if ($this->params->get('hidesidebar') == 'hide') echo ' class="autohide"'; ?>>
		<?php require_once('html/mod_sidebarmenu/mod_sidebarmenu.php'); ?>
	</div>
	<div class="clr"></div>
</div>
<?php endif; ?>
<div id="content-wrap">
	<div id="module-status" class="<?php echo $topMenuStyle ?>">
		<?php if ($menutype == 'sidebar' || $menutype == 'both') : ?>
		<span class="sidebarToggle"><a id="toggleSideBar"><?php echo JText::_('TOGGLE SIDEBAR'); ?></a></span>
		<?php endif; ?>
		<jdoc:include type="modules" name="status"  />
		<span class="version"><?php echo JText::_('JOOMLA!') ?> <?php echo JText::_('VERSION') ?> <?php echo  JVERSION; ?></span>
		<?php if ($this->params->get('notifier')) require_once('html/mod_version_notifier/mod_version_notifier.php'); ?>
		<div class="clear"></div>
	</div>
	<?php if ($menutype == 'topbar' || $menutype == 'both') : ?>
	<div id="topmenu-wrap" class="<?php echo $topMenuStyle ?>">
		<?php require_once('html/mod_menu/mod_menu.php'); ?>
	</div>
	<div class="clear"></div>
	<?php endif; ?>
	<div id="border-top">
		<div>
			<div>
				<?php if ($this->params->get('shortcuts') == '1') require_once('html/mod_shortcuts/mod_shortcuts.php'); ?>
				<span class="title"><?php echo $mainframe->getCfg( 'sitename' ); ?></span> <span class="dropdownnav">
				<?php if ($this->params->get('dropdown') == '1') require_once('html/mod_dropdownmenu/mod_dropdownmenu.php'); ?>
				</span></div>
			<div class="clear"></div>
		</div>
	</div>
	<div id="content-box">
		<div id="toolbar-box">
			<div class="m">
				<jdoc:include type="modules" name="title" style="title" style="contentboxheader" />
				<jdoc:include type="modules" name="submenu" id="submenu-box" style="contentboxsubmenu" />
				<div class="clear"></div>
				<jdoc:include type="modules" name="toolbar" />
			</div>
		</div>
		<div class="clear"></div>
		<jdoc:include type="message" />
		<div id="element-box">
			<div class="m">
				<jdoc:include type="component" />
				<div class="clear"></div>
			</div>
		</div>
		<noscript>
		<?php echo  JText::_('WARNJAVASCRIPT') ?>
		</noscript>
		<div class="clear"></div>
		<p class="copyright"> <?php echo $yourCopyright ?><br />
			<?php if ($joomlaText) { ?>
			<a href="http://www.joomla.org" target="_blank">Joomla!</a> <?php echo  JText::_('ISFREESOFTWARE') ?>
			<?php } ?>
		</p>
	</div>
</div>
</body>
</html>
