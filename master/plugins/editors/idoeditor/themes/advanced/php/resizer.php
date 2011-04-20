<?
function resize_picture($src, $dest, $width, $height = null) {
	if (is_null($height)) $height = $width;
	
	if (!file_exists($src)) return false;
	$size = getimagesize($src);
	if ($size === false) return false;

	// Определяем исходный формат по MIME-информации, предоставленной
	// функцией getimagesize, и выбираем соответствующую формату
	// imagecreatefrom-функцию.

	$format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));

	$icfunc = "imagecreatefrom" . $format;
	$ifunc = "image" . $format;

	if (!function_exists($icfunc)) return false;
	if (!function_exists($ifunc)) return false;

	$x_ratio = $width / $size[0];
	$y_ratio = $height / $size[1];

	$ratio       = min($x_ratio, $y_ratio);
	if ($ratio>1) {
		$width = $size[0];
		$height = $size[1];
		$ratio = 1;
	}
	
	$use_x_ratio = ($x_ratio == $ratio);

	$new_width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
	$new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);
	$new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
	$new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);

	$isrc = $icfunc($src);
	$idest = imagecreatetruecolor($new_width, $new_height);

	imagecopyresampled($idest, $isrc, 0, 0, 0, 0, $new_width, $new_height, $size[0], $size[1]);
	$ifunc($idest, $dest);

	

	imagedestroy($isrc);
	imagedestroy($idest);
	return $ratio<1;
}
?>