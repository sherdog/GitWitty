<?php defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' ); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo _LANGUAGE; ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; <?php echo _ISO; ?>" />
<?php 
if ( $my->id ) {
initEditor();
}
mosShowHead();?>

<link href="templates/<?php echo $cur_template; ?>/css/template_css.css" rel="stylesheet" type="text/css" media="screen" />
<?php 
if (mosCountModules('left') <=0) $style = "_no_left";
if (mosCountModules('right') <=0) $style = "_no_right";
if (mosCountModules('left') + mosCountModules('right') <=0) $style = "_full";
if (mosCountModules('left') && mosCountModules('right')) $style = "";
?>
<script language="javascript" type="text/javascript" src="<?php echo $mosConfig_live_site;?>/templates/<?php echo $mainframe->getTemplate();?>/js/styleswitcher.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $mosConfig_live_site;?>/templates/<?php echo $mainframe->getTemplate();?>/js/matching_columns.js"></script>
<!--[if lt IE 7]>
<script type="text/javascript" language="javascript" src="<?php echo $mosConfig_live_site;?>/templates/<?php echo $cur_template;?>/js/iehover.js"></script>
<![endif]-->
</head>
<body>
<script type="text/javascript">js_init();</script>
	<div id="header">
		<h1><a href="<?php echo $mosConfig_live_site;?>"><?php echo $mosConfig_sitename; ?></a></h1>
		<?php if (mosCountModules('top')) { ?>
			<div id="tabmenu">
				<?php mosLoadModules( 'top', -1 );?>
    		</div><!-- end div#tabmenu -->
		<?php } ?>
	</div><!-- end div#header -->
	
	<div id="navbar">
	
		<?php if (mosCountModules('user1')) { ?>
			<?php mosLoadModules( 'user1', -1 );?>
		<?php } ?>
		
		<div id="access">
		<a href="index.php" title="Increase size" onclick="changeFontSize(1);return false;"><img src="<?php echo $mosConfig_live_site;?>/templates/<?php echo $mainframe->getTemplate(); ?>/images/larger.png" alt="larger" width="21" height="21" border="0" /></a>
		<a href="index.php" title="Decrease size" onclick="changeFontSize(-1);return false;"><img src="<?php echo $mosConfig_live_site;?>/templates/<?php echo $mainframe->getTemplate(); ?>/images/smaller.png" alt="smaller" width="21" height="21" border="0" /></a>
		<a href="index.php" title="Revert styles to default" onclick="revertStyles(); return false;"><img src="<?php echo $mosConfig_live_site;?>/templates/<?php echo $mainframe->getTemplate(); ?>/images/reset.png" alt="reset" width="55" height="21" border="0" /></a>
		</div><!-- end div#access -->
		
	</div><!-- end div#navbar -->
	
	<div id="main_content_wrapper">
		<div id="wrap">
		
			<?php if (mosCountModules('left')) { ?>
			<div id="left_sidebar" class="column">
				<?php mosLoadModules( 'left', -2 );?>
    		</div><!-- end div#left_sidebar -->
			<?php } ?>
		
			<div id="main_content<?php echo $style; ?>" class="column">
				<?php mosMainBody(); ?>
			</div><!-- end div#main_content -->
		
			<?php if (mosCountModules('right')) { ?>
			<div id="right_sidebar" class="column">
				<?php mosLoadModules( 'right', -2 );?>
    		</div><!-- end div#right_sidebar -->
			<?php } ?>
			
			<?php if (mosCountModules('footer')) { ?>
			<div id="footer">
				<?php mosLoadModules( 'footer', -2 );?>
    		</div><!-- end div#footer -->
			<?php } ?>
			
			<div id="designer">
				<?php include($mosConfig_absolute_path."/templates/" . $mainframe->getTemplate() . "/js/template.css.php"); ?>
			</div>
			
		</div><!-- end div#wrap -->
	</div><!-- end div#main_content_wrapper -->
</body>
</html>