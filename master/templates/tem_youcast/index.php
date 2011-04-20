<?php defined( "_VALID_MOS" ) or die( "Direct Access to this location is not allowed." );$iso = split( '=', _ISO );echo '<?xml version="1.0" encoding="'. $iso[1] .'"?' .'>';?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php if ( $my->id ) { initEditor(); } ?>
<?php mosShowHead(); ?>
<meta http-equiv="Content-Type" content="text/html;><?php echo _ISO; ?>" />

<?php echo "<link rel=\"stylesheet\" href=\"$GLOBALS[mosConfig_live_site]/templates/$GLOBALS[cur_template]/css/template_css.css\" type=\"text/css\"/>" ; ?>

</head>
<body>

<div id="thinkbig"></div>
<div id="wrapper">

	<div id="header">
			<?php echo "<img src=\"$GLOBALS[mosConfig_live_site]/templates/$GLOBALS[cur_template]/images/rightbg.jpg\" alt=\"image\" height=\"206\" width=\"232\" />" ; ?>
	</div> 

	<div id="menu">
				<?php mosLoadModules ( 'top' ); ?>
	</div> 

	<div id="content">
          <div id="content-padding">
	    <?php mosMainBody(); ?>
          </div>
	</div>

	<div id="right">
				<?php mosLoadModules ( 'user8' ); ?>
				<?php mosLoadModules ( 'user2' ); ?>
				<?php mosLoadModules ( 'user5' ); ?>
</div>
	<div id="latestnews">
				<?php mosLoadModules ( 'user6' ); ?>
				<?php mosLoadModules ( 'user7' ); ?>
	</div>

	</div>

</div> <!-- wrapper end -->

	<div id="footer">	
		<div id="footermenu">
				<?php mosLoadModules ( 'bottom' ); ?>
		</div>

</body>
</html>