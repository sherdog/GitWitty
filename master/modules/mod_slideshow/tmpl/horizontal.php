<?php
/**
* @version		$Id: horizontal.php 00002 2009-10-30 00:00:00 umitkenan $
* @package		Joomla
* @subpackage	Horizontal SlideShow Module
* @link 		http://www.jt.gen.tr
* @copyright	Copyright (C) Joomla Türkçe Eğitim ve Destek Sitesi. http://www.jt.gen.tr 
* @license		GNU/GPL
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// Spaces ( Characters )		
for( $i = 0 ; $i < $this->space ; $i++ ) $spacetext .= "&nbsp;";
?>

<script type="text/javascript">
/* <![CDATA[ */

	// Initializing
	var leftrightslide	=	new Array();
	var finalslide		=	'';
	var sliderwidth		=	"<?php echo $this->sliderwidth; ?>px";
	var sliderheight	=	"<?php echo $this->sliderheight; ?>px";
	var slidebgcolor	=	"<?php echo $this->slidebgcolor; ?>";
	var stopslide		=	"<?php echo $this->stopslide; ?>";
	var imagegap		=	"<?php echo $spacetext; ?>";
	var slidespeed		=	<?php echo $this->slidespeed; ?>;

	// Starting SlideShow
	<?php for ( $i=0 ; $i < count($this->image) ; $i++ ) echo "leftrightslide[".$i."]='".$this->slideshowcontent[$i]."';"; ?>

/* ]]> */
</script>

<script src="<?php echo JURI::root(); ?>modules/mod_slideshow/scripts/horizontal.js" type="text/javascript"></script>

<?php echo $this->slideshowfooter; ?>