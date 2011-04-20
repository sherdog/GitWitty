<?
require_once ('init.php');
require_once ('utils4file.php');

$filetypes = array("zip","rar","avi","mp3","jpg","pdf","doc","xls","swf");
/*
$blacklist = array("php","html","js");
$blackmime = array("application/x-rar","text/javascript","text/html");
*/
$path = JPATH_BASE.DS.'images/idoblog/upload/'.$user->id;

if ( isset( $_POST['renameFile'] ) ){
	$newfile = JRequest::getVar('newNameFile','post');
	$oldfile = JRequest::getVar('oldNameFile','post');
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

if($_FILES['file'] and $_FILES['file']['error']==0){
	$mime_type = $_FILES['file']['type'];
	$ext = strtolower(JFile::getExt($_FILES['file']['name']));
	$filename = $_FILES['file']['name'];
	
	if (!utils4file_checkname($filename)) {
		$badfilename = utils4file_getName($filename);
		$filename = md5($filename).".".$ext;
	}
	
	if (!in_array(strtolower($ext),split(" ",strtolower($params_plugin->get("file_ext"))))){
		echo "{status:'error.incorrect_ext'}";
		exit();
	}
	
	$filename = utils4file_getUName($path,$filename);	
	
	if ( !JFolder::exists( JPATH_BASE.DS.'images'.DS.'idoblog'.DS.'upload'.DS.$user->id ) )
		JFolder::create( JPATH_BASE.DS.'images'.DS.'idoblog'.DS.'upload'.DS.$user->id, 0777 );
		
	JFile::upload($_FILES['file']['tmp_name'],$path.DS.$filename);
}
in_array($ext,$filetypes) ? $ico = "mime_$ext.png" : $ico = "default.png";
$html = '<img src="/plugins/editors/idoeditor/themes/advanced/img/filetypes/'.$ico.'" width="16" height="16" align="absmiddle"><a href="/images/idoblog/upload/'.$user->id.'/'.$filename.'">'.$filename.'</a>';
$badfilename = addslashes($badfilename);
echo "{
	status : 'OK',\n
	html : '$html',\n
	filename : '$filename',\n
	badfilename : '$badfilename'\n
}
"

?>