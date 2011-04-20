<?php
/**
 * @copyright	Copyright (C) 2009 Joomla Bamboo. All rights reserved.
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
<jdoc:include type="head" />
<link href="templates/<?php echo  $this->template ?>/css/template_css.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="templates/system/css/system.css" type="text/css" />
<?php if ($this->params->get('color') !== 'green') echo '<link href="templates/'.$this->template.'/css/'.$this->params->get('color').'.css" rel="stylesheet" type="text/css" />'; ?>
<script type="text/javascript" src="templates/<?php echo  $this->template ?>/js/admin.js"></script>
<?php if ($this->params->get('shortcut_tips') == '1') : ?>
<script type="text/javascript" src="templates/<?php echo  $this->template ?>/js/mootips.js"></script>
<?php endif; ?>
<!--[if IE 7]>
<link href="templates/<?php echo  $this->template ?>/css/ie7.css" rel="stylesheet" type="text/css" />
<![endif]-->

<!--[if lte IE 6]>
<script type="text/javascript" src="templates/<?php echo  $this->template ?>/js/supersleight-min.js"></script>
<link href="templates/<?php echo  $this->template ?>/css/ie6.css" rel="stylesheet" type="text/css" />
<![endif]-->
<script language="javascript" type="text/javascript">
	function setFocus() {
		document.login.username.select();
		document.login.username.focus();
	}
</script>
</head>
<body onload="javascript:setFocus()" id="login"<?php if ($this->params->get('color') !== 'green') echo ' class="'.$this->params->get('color').'"'; ?>>
		
		<div id="login-wrapper">
			<div id="login-top">
				<div id="logo">	
					<?php if ($logo == 'image') : ?>
						<div id="logo"> <a href="index.php"><img src="templates/<?php echo  $this->template ?>/images/logo.png" alt="" /></a> </div>
					<?php endif; ?>
					<?php if ($logo == 'text') : ?>
						<div id="logoText"> <?php echo $mainframe->getCfg('sitename');?> </div>
					<?php endif; ?>
				</div>
					<h3><?php echo $mainframe->getCfg( 'sitename' ); ?> <?php echo JText::_('Admin Login') ?></h3>
			</div>
			<div id="login-content">

					<jdoc:include type="component" />
					
					<div class="notification information"><div><?php echo JText::_('DESCUSEVALIDLOGIN') ?></div></div>
					<p class="home-page">
						<a href="<?php echo JURI::root(); ?>"><?php echo JText::_('Return to site Home Page') ?></a>
					</p>
					<div class="clear"></div>
				</div>
			</div>
			<noscript>
				<?php echo JText::_('WARNJAVASCRIPT') ?>
			</noscript>
			<div class="clr"></div>
		</div>
	</div>
</div>
</body>
</html>
