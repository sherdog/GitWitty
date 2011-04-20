var phtml='', fhtml='';
var pattern4checkname = tinyMCEPopup.editor.getParam("pattern4checkname");
var applicationName = tinyMCEPopup.editor.getParam("application_name");

$(function(){
	$("#exts").html(tinyMCEPopup.editor.getParam("upload_audio_ext").replace(/ /g,", "));

});

function ajaxFileUpload()
{
	//starting setting some animation when the ajax starts and completes
	$("#loading")
	.ajaxStart(function(){
		$(this).show();
	});
	
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
			url:'php/audio.php?applicationName='+applicationName, 
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
function uploadComplete(data, status) {
	eval("data = "+data+";");
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
	
	phtml = data.filename;
	fhtml = '<object type="application/x-shockwave-flash" data="/plugins/editors/idoeditor/themes/advanced/player/audioplayer.swf" width="290" height="24" id="audioplayer1"><param name="wmode" value="transparent" /><param name="movie" value="/plugins/editors/idoeditor/themes/advanced/player/audioplayer.swf" /><param name="FlashVars" value="playerID=1&amp;bg=0xf8f8f8&amp;leftbg=0xeeeeee&amp;lefticon=0x666666&amp;rightbg=0xcccccc&amp;rightbghover=0x999999&amp;righticon=0x666666&amp;righticonhover=0xFFFFFF&amp;text=0x666666&amp;slider=0x666666&amp;track=0xFFFFFF&amp;border=0x666666&amp;loader=0x9FFFB8&amp;soundFile='+phtml+'" /><param name="quality" value="high" /><param name="menu" value="false" /><param name="bgcolor" value="#FFFFFF" /></object>'
	$("#preview").html(fhtml);
	//$("#src").attr("value",phtml);
	$("#loading").hide();
}
function removeFile(){
	$.post('php/audio.php',{applicationName:applicationName,removeFile:phtml},function(){
		tinyMCEPopup.close();
	});
}

function insertAudio(){
	var ed = tinyMCEPopup.editor;
	ed.execCommand('mceInsertContent', false, fhtml, {skip_undo : 1});
	//document.forms[0].submit();
	//insertMedia();
	tinyMCEPopup.close();
}

function renameFile(oldNameWithPath, newName){
	$("#loading").show();
	$.post('php/audio.php',{applicationName:applicationName,renameFile:true,newNameFile:newName,oldNameFile:oldNameWithPath},uploadComplete);
}