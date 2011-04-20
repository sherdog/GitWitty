<?
require_once ('../../../themes/advanced/php/init.php');
require_once ('init.php');
require_once ('../../../themes/advanced/php/utils4file.php');

$path = JPATH_BASE.DS.'images/idoblog/upload/'.$user->id;

if(isset($_POST['removeFile'])){
	$file = JRequest::getVar('removeFile','post');
	$file = substr($file,strrpos($file,"/")+1);
	JFile::delete($path.DS.$file);
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
		$filename = $newfile;
	}
	
}

if ( $_FILES['file'] and $_FILES['file']['error']==0 ){
	$filename = $_FILES['file']['name'];
	$mime_type = $_FILES['file']['type'];
	$ext = strtolower(JFile::getExt($filename));
	/*
	if($mime_type!="audio/mpeg"){
		echo '{status:"error.incorrect_ext"}';
		exit();
	}
	*/
	if (!utils4file_checkname($filename)) {
		$badfilename = utils4file_getName($filename);
		$filename = md5($filename).".".$ext;
	}
	if (!in_array(strtolower($ext),split(" ",strtolower($params_plugin->get("audio_ext"))))){
		echo "{status:'error.incorrect_ext'}";
		exit();
	}
	
	$filename = utils4file_getUName($path,$filename);
	
	if ( !JFolder::exists($path) )	JFolder::create( $path, 0777 );
	
	JFile::upload($_FILES['file']['tmp_name'],$path.DS.$filename);
}
$badfilename = addslashes($badfilename);
echo '{
	status : "OK",
	filename : "/images/idoblog/upload/'.$user->id.'/'.$filename.'",
	badfilename : "'.$badfilename.'"
}';

?>