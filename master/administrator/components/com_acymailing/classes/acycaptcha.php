<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class acycaptchaClass{
	var $letters='abcdefghijkmnpqrstwxyz23456798ABCDEFGHJKLMNPRSTUVWXYZ';
	var $background_color='';
	var $colors = array();
	var $width;
	var $height;
	var $nb_letters;
	var $rotated = true;
	var $font = '';
	var $image = null;
	var $code = '';
	var $error='';
	var $_footprint = array();
	var $_angle = array();
	var $_rest = 0;
	var $space = 3;
	var $size = 16;
	var $state;
	function acycaptchaClass(){
		$this->font = ACYMAILING_FRONT.'inc'.DS.'font'.DS.'mgopencosmeticabold.ttf';
	}
	function available(){
		if(!function_exists('imagettftext')){
			$this->error= 'The GD library is not installed.';
			return false;
		}
		if(!function_exists('imagecreatetruecolor')){
			$this->error= 'GD library version is too old. Update to GD2';
			return false;
		}
		if(!file_exists($this->font)){
			$this->error = 'Font missing';
			return false;
		}
		return true;
	}
	function get(){
		if(empty($this->code)){
			$this->_generateCode();
		}
		$this->_initImage();
		$this->_addCode();
		@session_start();
		$_SESSION[$this->state] = $this->code;
		ob_start();
		imagepng($this->image);
		$image = ob_get_clean();
		imagedestroy($this->image);
		$this->image = $image;
		return true;
	}
	function check($input){
		@session_start();
		$code = @$_SESSION[$this->state];
		unset($_SESSION[$this->state]);
		if(empty($code) || empty($input)) return false;
		if(strtolower($code)==strtolower($input)){
			return true;
		}
		return false;
	}
	function returnError(){
		echo "<script> alert('".JText::_('ERROR_CAPTCHA',true)."'); window.history.go(-1);</script>\n";
		exit;
	}
    function displayImage(){
    	@ob_end_clean();
    	header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Cache-Control: post-check=0, pre-check=0', false );
		header( 'Pragma: no-cache' );
    	header('Content-type: image/png');
    	echo $this->image;
    	exit;
    }
	function _addCode(){
		if($this->rotated){
			$this->_generateRotate();
		}
		$this->_generateFootprint();
		$i=0;
		$test = 0;
		$old='size';
		while(!$this->_fit() && $test < 50){
			if($old=='size'){
				$old = 'space';
				$this->size--;
			}else{
				$old = 'size';
				$this->space--;
			}
			$this->_generateFootprint();
			$test++;
		}
		$outerspace=round($this->_rest/2);
		$x = ($outerspace < $this->_vals[$i]['x']/2 ? $this->_vals[$i]['x']/2 : $outerspace);
		$y = rand($this->_vals[$i]['y']+5, $this->height-$this->_vals[$i]['y']+5);
		while($i<$this->nb_letters){
			list($r,$g,$b)= $this->_color_hex2dec($this->colors[mt_rand(0,count($this->colors)-1)]);
			$clr = imagecolorallocate($this->image, $r, $g, $b);
			if($this->rotated){
				$angle = $this->_angle[$i];
			}else{
				$angle = 0;
			}
			imagettftext($this->image,$this->size,$angle,$x,$y,$clr,$this->font,$this->code[$i]);
			$x = $x + $this->_vals[$i]['x'] + $this->space;
			$y = rand($this->_vals[$i]['y']+5, $this->height-$this->_vals[$i]['y']+5);
			$i++;
		}
	}
	function _fit(){
		$i = 0;
		$px = 0;
		while($i<$this->nb_letters){
			$maxx = $this->_letter($i,'max','x');
			$minx = $this->_letter($i,'min','x');
			$maxy = $this->_letter($i,'max','y');
			$miny = $this->_letter($i,'min','y');
			$val = $maxx-$minx;
			if(!isset($this->_vals))
			{
				$this->_vals = array();
			}
			if(!array_key_exists($i,$this->_vals))
			{
				$this->_vals[$i] = array();
			}
			$this->_vals[$i]['x']=$val;
			$this->_vals[$i]['y']=$maxy-$miny;
			$px+= $val;
			$i++;
		}
		$spaces = ($this->nb_letters+1)*$this->space;
		$rest = $this->width - ($spaces+$px+13);
		if( $rest > 0)
		{
			$this->_rest = $rest;
			return true;
		}
		return false;
	}
	function _letter($i,$type){
		$start = 0;
		$extreme = $this->_footprint[$i][$start];
		$start+=2;
		while(array_key_exists($start,$this->_footprint[$i])){
			switch($type){
				case 'min':
					if($this->_footprint[$i][$start] < $extreme){
						$extreme = $this->_footprint[$i][$start];
					}
					break;
				case 'max':
					if($this->_footprint[$i][$start] > $extreme){
						$extreme = $this->_footprint[$i][$start];
					}
					break;
			}
			$start+=2;
		}
		return $extreme;
	}
	function _generateCode(){
		$this->code = '';
		$length = strlen($this->letters)-1;
		$tmp = $this->nb_letters;
		while($tmp > 0){
			$this->code .= $this->letters[mt_rand(0,$length)];
			$tmp--;
		}
		return true;
	}
	function _generateRotate(){
		$i=0;
		while($i<$this->nb_letters){
			$this->_angle[$i] = 360+mt_rand(-15,15);
			$i++;
		}
	}
	function _generateFootprint(){
		$i=0;
		while($i<$this->nb_letters)
		{
			if($this->rotated){
				$angle = $this->_angle[$i];
			}else{
				$angle = 0;
			}
			$this->_imagettfbbox_t($angle,$this->code[$i]);
			$i++;
		}
	}
	function _imagettfbbox_t($angle, $text) {
	    $coords = imagettfbbox($this->size, 0, $this->font, $text);
	    $a = deg2rad($angle);
	    $ca = cos($a);
	    $sa = sin($a);
	    $ret = array();
	    for($i = 0; $i < 7; $i += 2){
	        $ret[$i] = round($coords[$i] * $ca + $coords[$i+1] * $sa);
	        $ret[$i+1] = round($coords[$i+1] * $ca - $coords[$i] * $sa);
	    }
	    $this->_footprint[] = $ret;
	}
	function _initImage(){
		$this->image = imagecreatetruecolor($this->width, $this->height);
		if(empty($this->background_color)){
			$ImgWhite = imagecolorallocate($this->image, 255, 255, 255);
			imagefill($this->image, 0, 0, $ImgWhite);
			imagecolortransparent($this->image, $ImgWhite);
		}else{
			list($r, $g, $b) = $this->_color_hex2dec($this->background_color);
			$clr = imagecolorallocate($this->image, $r, $g, $b);
			imagefill($this->image,0,0,$clr);
		}
	}
	function _color_hex2dec($color) {
		if($color[0]=='#'){
			$color = substr($color,1);
		}
        return array (hexdec (substr ($color, 0, 2)), hexdec (substr ($color, 2, 2)), hexdec (substr ($color, 4, 2)));
    }
}
