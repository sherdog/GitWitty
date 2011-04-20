<?php
/**
* @copyright Copyright (C) 2009 JoomlaPraise. All rights reserved.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >
<head>
<?php // Detecting Home
$menu = & JSite::getMenu();
if ($menu->getActive() == $menu->getDefault()) {
$siteHome = 1;
}

// Detecting Active Variables
$option = JRequest::getCmd('option', '');
$view = JRequest::getCmd('view', '');
$layout = JRequest::getCmd('layout', '');
$task = JRequest::getCmd('task', '');
$itemid = JRequest::getCmd('Itemid', '');
$templateTheme = $this->params->get('templateTheme');
$menuColor = $this->params->get('menuColor');
$fontFamily = $this->params->get('fontFamily');
$headingFontFamily = "heading-" . $this->params->get('headingFontFamily');

// set custom template theme for user
$user = &JFactory::getUser();
if( !is_null( JRequest::getCmd('templateTheme', NULL) ) ) {
$user->setParam($this->template.'_theme', JRequest::getCmd('templateTheme'));
$user->save(true);
}
if( !is_null( JRequest::getCmd('menuColor', NULL) ) ) {
$user->setParam($this->template.'_color', JRequest::getCmd('menuColor'));
$user->save(true);
}

if($user->getParam($this->template.'_theme')) {
$this->params->set('templateTheme', $user->getParam($this->template.'_theme'));
}
if($user->getParam($this->template.'_color')) {
$this->params->set('menuColor', $user->getParam($this->template.'_color'));
}

if($task == "edit" || $layout == "form" ) {
$fullWidth = 1;
}
?>
<jdoc:include type="head" />
<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/system/css/general.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/system/css/system.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template ?>/css/template.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template ?>/css/<?php echo $this->params->get('templateTheme'); ?>.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template ?>/css/<?php echo $this->params->get('templateTheme'); ?>/k2.css" type="text/css" />

<style type="text/css">
<?php if(($this->countModules('left') == 0) && ($this->countModules('right') == 0)) { ?>
#mainbody{width:100%; background:none;} #content{width:100%;}
<?php } ?>
<?php if(($this->countModules('left') >= 1) && ($this->countModules('right') == 0)) { ?>
#mainbody{width:100%;}#content{width:710px;}
<?php } ?>
<?php if(($this->countModules('left') == 0) && ($this->countModules('right') >= 1)) { ?>
#mainbody{background:none;} #content{width:100%;}
<?php } ?>
<?php if($this->params->get('fontColor')){ ?>
body{color:<?php echo $this->params->get('fontColor'); ?>}
<?php } ?>
<?php if($this->params->get('headingColor')){ ?>
h1, h2, h3, h4, h5, h6, .componentheading, .contentheading{color:<?php echo $this->params->get('headingColor'); ?>}
<?php } ?>
<?php if($this->params->get('linkColor')){ ?>
a:link, a:active, a:visited{color:<?php echo $this->params->get('linkColor'); ?>}
<?php } ?>
<?php if($this->params->get('linkHoverColor')){ ?>
a:hover{color:<?php echo $this->params->get('linkHoverColor'); ?>}
<?php } ?>
<?php if($fullWidth){ ?>
#mainbody{width:100%; background:none;} #content{width:100%;} #sidebar{display:none;} #sidebar2{display:none;}
<?php } ?>
</style>
</head>
<body class="<?php echo $option . " " . $view . " " . $layout . " " . $task . " " . $itemid . " " . $fontFamily . " " . $headingFontFamily;?>  <?php if($siteHome){ echo "homepage";}?>">

<div id="wrapper">
  <div id="header">
    <div class="width">
      <div class="inside"> <a href="<?php echo $mainframe->getCfg('live_site'); ?>" id="logo" title="<?php echo $mainframe->getCfg('sitename'); ?>"><h1><?php echo $mainframe->getCfg('sitename'); ?></h1></a>
        <div id="toolbar">
          <div id="feed"><jdoc:include type="modules" name="syndicate" /></div>
          <div id="register"><jdoc:include type="modules" name="user5" /></div>
          <div id="search"><jdoc:include type="modules" name="user4" /></div>
        </div>
        <div class="clr"></div>
      </div>
    </div>
  </div>
  <?php if ($this->countModules('user3')) { ?>
  <div id="mainmenu-outer" class="<?php echo $menuColor;?>">
    <div class="width">
      <div class="inside">
        <div id="mainmenu">
          <jdoc:include type="modules" name="user3" />
          <div class="clr"></div>
        </div>
      </div>
    </div>
  </div>
  <?php } ?>
  <?php if ($this->countModules('banner')) { ?>
    <div id="banner">
      <div class="width">
        <div class="inside">
          <jdoc:include type="modules" name="banner" style="xhtml" />
          <div class="clr"></div>
        </div>
      </div>
  </div>
  <?php } ?>
  <?php if ($this->countModules('breadcrumb')) { ?>
  <div id="pathway">
    <div class="width">
      <div class="inside"> 
        <jdoc:include type="modules" name="breadcrumb" />
        <div class="clr"></div>
      </div>
    </div>
  </div>
  <?php } ?>
  <div id="container">
    <div class="width">
      <div class="inside">
        <div id="mainbody">
        	<?php if (($this->countModules('user1')) || ($this->countModules('user2'))) { ?>          
            <table class="elements">
              <tr>
                <?php if ($this->countModules('user1')) { ?>
                <td class="elements1"><jdoc:include type="modules" name="user1" style="xhtml" /></td>
                <?php } ?>
                <?php if ($this->countModules('user2')) { ?>
                <td class="elements2"><jdoc:include type="modules" name="user2" style="xhtml" /></td>
                <?php } ?>
              </tr>
            </table>
            <?php } ?>
            <div id="content">
              <div class="inside">
                <jdoc:include type="modules" name="top" style="xhtml" />
                <jdoc:include type="message" />
                <jdoc:include type="component" />
                <jdoc:include type="modules" name="bottom" style="xhtml" />
              </div>
            </div>
            <?php if ($this->countModules('left')) { ?>
            <div id="sidebar"><jdoc:include type="modules" name="left" style="xhtml" /></div>
            <?php } ?>
            <div class="clr"></div>
        </div>
        <?php if ($this->countModules('right')) { ?>
        <div id="sidebar2"><jdoc:include type="modules" name="right" style="rounded" /></div>
        <?php } ?>
        <div class="clr"></div>
      </div>
    </div>
  </div>
  <div id="footer">
      <div class="width">
        <div class="inside">
          <div id="copy">
            <jdoc:include type="modules" name="footer" />
        <a href="http://www.joomlapraise.com" title="Joomla! Templates and Extensions" target="_blank">Joomla! Templates &amp; Extensions</a> by <a href="http://www.joomlapraise.com" title="Joomla! Templates and Extensions" target="_blank">JoomlaPraise</a>
          </div>
          <?php if ($this->countModules('user6')) { ?>
          <div id="link">
            <jdoc:include type="modules" name="user6" />
          </div>
          <?php } ?>
          <div class="clr"></div>
        </div>
      </div>
    </div>
</div>
</body>
</html>
