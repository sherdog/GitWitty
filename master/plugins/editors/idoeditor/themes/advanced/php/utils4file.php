<?
function utils4file_checkname($filename){
	$name = utils4file_getName($filename);
	return preg_match("/^[\w\-]+$/",$name);
}
function utils4file_getUName($path,$filename){
	$ext = strtolower(JFile::getExt($filename));
	$name = utils4file_getName($filename);
	$i = 1;
	while( file_exists( $path.DS.$filename )) {
		$filename = $name.'_'.$i.'.'.$ext;
		$i++;
	}
	return $filename;
}
function utils4file_getName($filename){
	$arr = explode('.',$filename);
	return $arr[0];
}
?>