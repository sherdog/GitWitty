<?php
/**
* @version		$Id: helper.php 00002 2009-10-30 00:00:00 umitkenan $
* @package		Joomla
* @subpackage	Horizontal SlideShow Module
* @link 		http://www.jt.gen.tr
* @copyright	Copyright (C) Joomla Türkçe Eðitim ve Destek Sitesi. http://www.jt.gen.tr 
* @license		GNU/GPL
*/

class modSlideShowHelper
{
    function getParameterList( &$params )
    {
		// Loading images, urls, titles, styles, options, etc..
		$this->image 			= explode ( "\n", trim ( $params->get ( 'images' ) ) );
		$this->url 				= explode ( "\n", trim ( $params->get ( 'urls' ) ) );
		$this->title 			= htmlspecialchars ( trim ( $params->get ( 'titles' ) ) );
		$this->title 			= explode ( "\n", $this->title );
		$this->folder			= trim ( $params->get ( 'folder' ) );
		$this->showallimages	= $params->get( 'showallimages', 0 );
		$this->target			= trim ( $params->get ( 'target', '_blank' ) );
		$this->linked			= $params->get( 'linked', 1 );
		$this->space			= trim ( $params->get ( 'space', 1 ) );
		$this->space			= str_replace ( 'px', '', $this->space );
		$this->space			= str_replace ( '%', '', $this->space );
		$this->sliderwidth		= trim ( $params->get ( 'sliderwidth', '170' ) );
		$this->sliderheight		= trim ( $params->get ( 'sliderheight', '210' ) );
		$this->slidebgcolor		= trim ( $params->get ( 'sliderbgcolor', 'transparent' ) );
		$this->stopslide		= trim ( $params->get ( 'stopslide', 1 ) );
		$this->slidespeed		= trim ( $params->get ( 'speed', 2 ) );
		$this->imageheight		= trim ( $params->get ( 'imageheight', '210' ) );
		$this->imagewidth		= trim ( $params->get ( 'imagewidth', 'auto' ) );

		$this->delayb4scroll	= trim ( $params->get ( 'wait', 1000 ) );
		
		if ($this->imagewidth == '' || $this->imagewidth == 0 || $this->imagewidth == 'auto') $this->imagewidth = '';
		else $this->imagewidth = 'width: ' . $this->imagewidth . 'px;';

		if ($this->imageheight == '' || $this->imageheight == 0 || $this->imageheight == 'auto') $this->imageheight = '';
		else $this->imageheight = 'height: ' . $this->imageheight . 'px;';
		
		// If adding "http" is YES
		if (trim ( $params->get ( 'addhttp' ) )) {
			for ( $i=0 ; $i < count($this->image) ; $i++ )
				$this->url[$i]="http://".$this->url[$i]; }

		// Show all images
		if ($this->showallimages) {
			// Directory SlideShow
			// if subdirectory parameter is yes
			$jpgimages = glob("".$this->folder."/*.jpg");
			$pngimages = glob("".$this->folder."/*.png");
			$gifimages = glob("".$this->folder."/*.gif");

			// Generating image array
			// Adding jpeg files to (directory) slideshow
			$this->image = $jpgimages;

			// Adding png files to (directory) slideshow
			$j=0;
			for ($i = count($jpgimages); $i < count($jpgimages)+count($pngimages); $i++) {
				$this->image[$i]=$pngimages[$j];
				$j=$j+1;
			}
		
			// Adding gif files to (directory) slideshow
			$j=0;
			for ($i = count($this->image); $i < count($jpgimages)+count($pngimages)+count($gifimages); $i++) {
				$this->image[$i]=$gifimages[$j];
				$j=$j+1; 
			}	
		}

		// SlideShow Content - Loop
		for ( $i=0 ; $i < count($this->image) ; $i++ ) {
			// Preparing Titles
			$this->alt[$i] 				= $this->title[$i] ? ' alt="'. $this->title[$i] .'"' : '';
			$this->alttitle[$i] 		= $this->title[$i] ? ' title="'. $this->title[$i] .'"' : '';
			// Preparing Links
			$this->imagelinkstart[$i] 	= $this->linked ? '<a href="'. $this->url[$i] .'" target="'. $this->target .'">' : '<a href="javascript:void(0);">';
			$this->imagelinkend		 	= '<\/a>';
			
			// Show Only Entered Images
			if (!$this->showallimages) {
				// External Image Resource
				if (strstr($this->image[$i],'<ext>')) {
					$this->image[$i] = str_replace('<ext>','',$this->image[$i]);
					$this->imagewithpath[$i] = '<img src="'. $this->image[$i] . '" border="0" style="'.$this->imagewidth . $this->imageheight.'"'. $this->alt[$i] . $this->alttitle[$i] .'\/>';
				}
				// Internal Image Resource
				else $this->imagewithpath[$i] = '<img src="'.JURI::root() . $this->folder . '/' . $this->image[$i] . '" border="0" style="'.$this->imagewidth . $this->imageheight.'"'. $this->alt[$i] . $this->alttitle[$i] .'\/>';			
			}
			// Show All Images in the folder
			else $this->imagewithpath[$i] = '<img src="'.JURI::root() . $this->image[$i] . '" border="0" style="'.$this->imagewidth . $this->imageheight.'"'. $this->alt[$i] . $this->alttitle[$i] .'\/>';
			
			// Prepared SlideShow (Content) Image:
			$this->slideshowcontent[$i]	= $this->showallimages ? $this->imagelinkstart[$i].$this->imagewithpath[$i].$this->imagelinkend : $this->imagelinkstart[$i].$this->imagewithpath[$i].$this->imagelinkend;
		}

		// SlideShow Footer
		$this->slideshowfooter = '';
		if ( $params->get ( 'slideshowfooter' ) == null || $params->get ( 'slideshowfooter' ) == ''  ) $params->set ( 'slideshowfooter', 1 );
		if ( $params->get ( 'slideshowfooter' ) == 1 )
			$this->slideshowfooter = '<div class="slideshowfooter">'.JText::_( 'SlideShow-Footer').'</div>';

		// Preparing CSS style
		$css = ' #marqueecontainer { position: relative; width: '.$this->sliderwidth.'px; height: '.$this->sliderheight.'px; background-color: '.$this->slidebgcolor.'; overflow: hidden; } ';
		// Horizontal SlideShow Footer
		$css = $css . ' .slideshowfooter { padding-top: 10px; font-family: Tahoma,Verdana,sans-serif; font-size: 8px; font-weight: bold; } ';
		// Apply new CSS
		$document =& JFactory::getDocument();
		$document->addStyleDeclaration($css);
    }
}