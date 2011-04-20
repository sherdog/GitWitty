var phtml='';
var pattern4checkname = tinyMCEPopup.editor.getParam("pattern4checkname");
var applicationName = tinyMCEPopup.editor.getParam("application_name");

$(function(){
	$("#exts").html(tinyMCEPopup.editor.getParam("upload_file_ext").replace(/ /g,", "));


});

function ajaxFileUpload()
{
	//starting setting some animation when the ajax starts and completes
	$("#loading")
	.ajaxStart(function(){
		$(this).show();
	})
	
	/*
		prepareing ajax file upload
		url: the url of script file handling the uploaded files
					fileElementId: the file type of input element id and it will be the index of  $_FILES Array()
		dataType: it support json, xml
		secureuri:use secure protocol
		success: call back function when the ajax complete
		error: callback function when the ajax failed
		
			*/
	$.ajaxFileUpload
	(
		{
			url:'php/file.php?applicationName='+applicationName, 
			secureuri:false,
			fileElementId:'file',
			dataType: 'text',
			success: uploadComplete,
			error: function (data, status, e) {
				alert(e);
			}
		}
	)
	
	return false;

}

function uploadComplete(data,status){
	eval("data = "+data+";");
	if (typeof(data.status)!="undefined"){
		if (data.status!="OK") {
			alert(tinyMCEPopup.editor.getLang(data.status));
			$("#loading").hide();
			return;
		}
		if (data.badfilename!=""){
			var newName = data.badfilename;
			
			while (!pattern4checkname.test(newName)){
				newName = window.prompt(tinyMCEPopup.editor.getLang('advanced_dlg.incorrect_name_file'),newName);
				if (!newName) break;
			}
			if (newName) {
				renameFile(data.filename,newName);
				return;
			}
		}
		phtml = data.html;
		$("#preview").html(phtml);
		$("#loading").hide();
	}
}
function insertFile(){
	var ed = tinyMCEPopup.editor;
	ed.execCommand('mceInsertContent', false, phtml, {skip_undo : 1});
	tinyMCEPopup.close();
}
function renameFile(oldNameWithPath, newName){
	$("#loading").show();
	$.post('php/file.php',{applicationName:applicationName,renameFile:true,newNameFile:newName,oldNameFile:oldNameWithPath},uploadComplete);
}