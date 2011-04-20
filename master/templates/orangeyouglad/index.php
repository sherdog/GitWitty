<?php defined( '_JEXEC' ) or die( 'Restricted access' );?>
<?php
$js = "templates/".$this->template."/js/jquery-1.3.1.min.js";
$this->addScript(JURI::base() . $js);
$js = "templates/".$this->template."/js/hoverIntent.js";
$this->addScript(JURI::base() . $js);
$js = "templates/".$this->template."/js/jquery.dropdownPlain.js";
$this->addScript(JURI::base() . $js);
$js = "templates/".$this->template."/js/orangeyouglad.js";
$this->addScript(JURI::base() . $js);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" 
   xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >
<head>
<jdoc:include type="head" />
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/system.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/general.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template?>/css/template.css" type="text/css" />
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-22304590-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</head>
<?php
$menu = & JSite::getMenu();
if ($menu->getActive() == $menu->getDefault()) {
    echo '<body style="background:url('. $this->baseurl.'/templates/'.$this->template.'/images/website-bg.jpg) repeat-x;">';
} else { 
	 echo '<body style="background:url('. $this->baseurl.'/templates/'.$this->template.'/images/website-bg-sub.jpg) repeat-x;">';
}
?>

<body>
<div id="siteContainer">
<div id="header">
	<jdoc:include type="modules" name="header" />
    <div id="logo">
    	<a href="<?php echo JURI::base(); ?>"><img src="<?php echo $this->baseurl.'/templates/'.$this->template?>/images/logo.jpg" border="0" /></a>
    </div>
    <div id="navigation">
		<jdoc:include type="modules" name="left" /> 
    <div class="clear"></div>
</div>
</div>
<jdoc:include type="modules" name="feature" />
<div id="content">
	<jdoc:include type="component" />
    <jdoc:include type="modules" name="advert3" /> 
</div>

<div id="footer">
	<jdoc:include type="modules" name="footer" />
</div>
</div>


</body>
</html>
