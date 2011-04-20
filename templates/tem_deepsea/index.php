<?php defined( "_VALID_MOS" ) or die( "Direct Access to this location is not allowed." );?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php if ( $my->id ) { initEditor(); } ?>
<meta http-equiv="Content-Type" content="text/html;><?php echo _ISO; ?>" />
<?php mosShowHead(); ?>
<?php echo "<link rel=\"stylesheet\" href=\"$GLOBALS[mosConfig_live_site]/templates/$GLOBALS[cur_template]/css/template_css.css\" type=\"text/css\"/>" ; ?><?php echo "<link rel=\"shortcut icon\" href=\"$GLOBALS[mosConfig_live_site]/images/favicon.ico\" />" ; ?>
</head>

<body>
	
<div id="wrap">
	<div id="header"> 
		<div id="smallmenu">
	  <?php mosLoadModules ( 'top' ); ?>
		</div>
	</div>
	<div id="menu">
	  <?php mosLoadModules ( 'user1' ); ?>
	</div>

	<div id="left">
	  <?php mosLoadModules ( 'user8' ); ?>
	  <?php mosLoadModules ( 'user2' ); ?>
	</div>
	<div id="content">
		<p>
	    <?php mosMainBody(); ?>
	</p>		
	</div>

	
	<div id="footer">
	    <?php mosLoadModules ( 'bottom' ); ?>	</div>

</div>

</div>
	
</body>
</html>
