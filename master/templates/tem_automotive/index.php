<?php defined( "_VALID_MOS" ) or die( "Direct Access to this location is not allowed." );?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php if ( $my->id ) { initEditor(); } ?>
<meta http-equiv="Content-Type" content="text/html;><?php echo _ISO; ?>" />
<?php mosShowHead(); ?>
<?php echo "<link rel=\"stylesheet\" href=\"$GLOBALS[mosConfig_live_site]/templates/$GLOBALS[cur_template]/css/template_css.css\" type=\"text/css\"/>" ; ?><?php echo "<link rel=\"shortcut icon\" href=\"$GLOBALS[mosConfig_live_site]/images/favicon.ico\" />" ; ?>
<?php echo "<script type=\"text/javascript\" src=\"$GLOBALS[mosConfig_live_site]/templates/$GLOBALS[cur_template]/overIE.js\"></script>"; ?>
</head>

<body id="body">
<div id="wrapper">
	<div id="header"> 
	<div id="headerheight"> 
		<div id="logo">
		<?php echo "<a href=\"$GLOBALS[mosConfig_live_site]\"><img src=\"$GLOBALS[mosConfig_live_site]/templates/$GLOBALS[cur_template]/images/logo.jpg\" alt=\"image\" height=\"60\" width=\"265\" /></a>" ; ?>
		</div>
		<div id="menu">
	  <?php mosLoadModules ( 'user1', -2 ); ?>
			<div id="menu2">
			</div>
		</div>
	</div>
<? if ($option == "com_frontpage") { ?>
		<div id="banner">
	  <?php mosLoadModules ( 'user2', -2 ); ?> 
		</div>
<? } ?>
	</div>
	<div id="content">
<? if ($option != "com_frontpage") { ?>
		<div id="main">
		<div id="main-padding">
	    <?php mosMainBody(); ?>
		</div>
	    </div>
	    <div id="right">
	  <?php mosLoadModules ( 'right', -2 ); ?>
		</div>
	<div style="clear:both"></div>
	<? } ?>
	</div>
<? if ($option == "com_frontpage") { ?>
	    <div id="lowerpanel">
	    <div id="block1">
	  <?php mosLoadModules ( 'user6', -2 ); ?>
		</div>
	    <div id="block2">
	  <?php mosLoadModules ( 'user7', -2 ); ?>
		</div>
	    <div id="block3">
	  <?php mosLoadModules ( 'user9', -2 ); ?>
		</div>
	<div style="clear:both"></div>
	</div>
<? } ?>

	<div id="footer">
	    <?php mosLoadModules ( 'bottom', -2 ); ?>	
	</div>
</div>
	
</body>
</html>