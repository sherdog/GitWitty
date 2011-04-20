<?
require_once ('init.php');
require_once ('resizer.php');
require_once ('utils4file.php');

$path = JPATH_BASE.DS.'images'.DS.'idoblog'.DS.'upload'.DS.$user->id;
$path_link = "/images/idoblog/upload/{$user->id}/";

if ( isset( $_POST['removeFile'] ) ){
	$file = JRequest::getVar('removeFile','post');
	$file = substr($file,strrpos($file,"/")+1);
	JFile::delete($path.DS.$file);
	JFile::delete($path.DS."preview".DS."small_".$file);
	JFile::delete($path.DS."sm".DS.$file);
	exit();
}

if ( isset( $_POST['renameFile'] ) ){
	$newfile = JRequest::getVar('newNameFile','post');
	$oldfile = JRequest::getVar('oldNameFile','post');
	$oldfile = substr($oldfile,strrpos($oldfile,"/")+1);
	$ext = JFile::getExt($oldfile);
	if (!utils4file_checkname($newfile)) {
		$badfilename = utils4file_getName($newfile);
		$filename = $oldfile;
	} else {
		$newfile = utils4file_getUName($path,$newfile.".".$ext);
		$oldfile = utils4file_getName($oldfile);
		JFile::move($path.DS.$oldfile.".".$ext,$path.DS.$newfile);
		JFile::move($path.DS."preview".DS."small_".$oldfile.".".$ext,$path.DS."preview".DS."small_".$newfile);
		JFile::move($path.DS."sm".DS.$oldfile.".".$ext,$path.DS."sm".DS.$newfile);
		$filename = $newfile;
	}
	
}
if (isset($_POST['linkGetWH'])) {
	$link = JRequest::getVar('linkGetWH','post'); 
	$size = getimagesize($link);
	$max = max($size[0],$size[1]);
	$wh = $params_plugin->get("max_image_wh");
	if ($max>$wh){
		$r = $max/$wh;
		$width = $size[0]/$r;
		$height = $size[1]/$r;
		echo "{
			status:'OK',
			width:'{$width}',
			height:'{$height}',
			filename:'{$link}',
			filename_original:'{$link}',
			original_size: '{$size[0]} {$size[1]}'
		}";	
	} else {
		echo "{
			status:'OK',
			filename:'{$link}'
		}";	
	}
	
	exit();	
}

$upload = false;
if (isset($_POST['link'])){
	$link = JRequest::getVar('link','post'); 
	$filename = substr($link, strrpos($link,"/")+1);
	$ext = JFile::getExt($filename);
	$upload = "link";
}
if ( $_FILES['pfile'] and $_FILES['pfile']['error']==0 ){
	$filename = $_FILES['pfile']['name'];
	$ext = JFile::getExt($filename);
	$upload = "file";
}
if ($upload!==false){
	if (!utils4file_checkname($filename)) {
		$badfilename = utils4file_getName($filename);
		$filename = md5($filename).".".$ext;
	}
	if (!in_array(strtolower($ext),split(" ",strtolower($params_plugin->get("image_ext"))))){
		echo "{status:'error.incorrect_ext'}";
		exit();
	}
	$filename = utils4file_getUName($path,$filename);
	
	if ( !JFolder::exists( $path ) ) JFolder::create( $path, 0777 );
	if ( !JFolder::exists( $path.DS."preview" )) JFolder::create( $path.DS."preview", 0777 );
	if ( !JFolder::exists( $path.DS."sm" )) JFolder::create( $path.DS."sm", 0777 );

	if ($upload=="link"){
		JFile::write($path.DS.$filename,file_get_contents($link));	
	} else {
		JFile::upload($_FILES['pfile']['tmp_name'],$path.DS.$filename );
	}
	resize_picture($path.DS.$filename,$path.DS."preview".DS."small_".$filename,100);
	$sm = resize_picture($path.DS.$filename,$path.DS."sm".DS.$filename,$params_plugin->get("max_image_wh"));
	if (!$sm) JFile::delete($path.DS."sm".DS.$filename);
}
if (JFile::exists($path.DS."sm".DS.$filename)){
	$path_filename = $path_link."sm/{$filename}";
	$path_filename_original = $path_link.$filename;
	$size = getimagesize($path.DS.$filename);
	$original_size = $size[0]." ".$size[1];
} else {
	$path_filename = $path_link.$filename;
	$path_filename_original = "";
}
$badfilename = addslashes($badfilename);
echo "{
	status : 'OK',
	badfilename : '{$badfilename}',
	filename : '{$path_filename}',
	filename_original : '{$path_filename_original}',
	filename_preview : '{$path_link}preview/small_{$filename}',
	original_size : '{$original_size}'
}";

?>