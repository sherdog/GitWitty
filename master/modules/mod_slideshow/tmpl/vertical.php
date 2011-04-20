<?php
/**
* @version		$Id: vertical.php 00002 2009-10-30 00:00:00 umitkenan $
* @package		Joomla
* @subpackage	Horizontal SlideShow Module
* @link 		http://www.jt.gen.tr
* @copyright	Copyright (C) Joomla Türkçe Eðitim ve Destek Sitesi. http://www.jt.gen.tr 
* @license		GNU/GPL
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
?>

<script type="text/javascript">
	var delayb4scroll	=	<?php echo $this->delayb4scroll; ?>;
	var marqueespeed	=	<?php echo $this->slidespeed; ?>;
	var pauseit			=	<?php echo $this->stopslide; ?>;
</script>

<script src = "<?php echo JURI::root(); ?>modules/mod_slideshow/scripts/vertical.js" type="text/javascript"></script>

<div id="marqueecontainer" onMouseover="copyspeed=pausespeed" onMouseout="copyspeed=marqueespeed">
	<div id="vmarquee" style="text-align:center; position: absolute; width: 98%;">
		<?php for ( $i=0 ; $i < count($this->image) ; $i++ ) echo str_replace ( '\/', '/', $this->slideshowcontent[$i] ).'<div style="display: block; height:'.$this->space.'px;"></div>'; ?>
	</div>
</div>

<?php echo $this->slideshowfooter; ?>