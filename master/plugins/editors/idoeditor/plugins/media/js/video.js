var phtml='', fhtml='';
var applicationName = tinyMCEPopup.editor.getParam("application_name");
var useNoindex = tinyMCEPopup.editor.getParam("use_noindex");

var pattern4checkname = tinyMCEPopup.editor.getParam("pattern4checkname");
$(function(){
	$("#exts").html(tinyMCEPopup.editor.getParam("upload_video_ext").replace(/ /g,", "));

});

function insertLink(){
	var code = $("#code").val();
	code = code.replace(/<object(.*?)>(.*)<\/object>/i,'<object$1><param name="wmode" value="transparent" />$2</object>');
	code = code.replace(/<object(.*?)>(.*)<embed(.*?)>(.*)<\/object>/i,'<object$1>$2<embed$3 wmode="transparent">$4</object>');
	var reg = /<object(.*)>(.*)<\/object>/i;
	var arr = reg.exec(code);
	if(arr!=null && arr[0]!=''){
		var ed = tinyMCEPopup.editor;
		var q = arr[0];
		if (useNoindex) q = "<noindex>"+q+"</noindex>";
		ed.execCommand('mceInsertContent', false, q, {skip_undo : 1});
		tinyMCEPopup.close();		
	}
};

function ajaxFileUpload()
{
	//starting setting some animation when the ajax starts and completes
	$("#loading")
	.ajaxStart(function(){
		$(this).show();
	})
	.ajaxComplete(function(){
		$(this).hide();
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
			url:'php/video.php?applicationName='+applicationName, 
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
	plr = '/plugins/editors/idoeditor/themes/advanced/player/player-viral.swf';
	w = 480; h = 330;
	fhtml = '<object id="player" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" name="player" width="'+w+'" height="'+h+'"><param name="wmode" value="transparent" /><param name="movie" value="'+plr+'" /><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="flashvars" value="file='+phtml+'" /><object type="application/x-shockwave-flash" data="'+plr+'" width="'+w+'" height="'+h+'"><param name="movie" value="'+plr+'" /><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="flashvars" value="file='+phtml+'" /><p><a href="http://get.adobe.com/flashplayer">Get Flash</a> to see this player.</p></object></object>'
	//fhtml = '<object type="application/x-shockwave-flash" data="'+plr+'" height="100" width="150"><param name="bgcolor" value="#000000"/><param name="allowFullScreen" value="true" /><param name="allowScriptAccess" value="always" /><param name="movie" value="'+plr+'" /><param name="FlashVars" value="way='+phtml+'&amp;swf='+plr+'&amp;w=150&amp;h=100&amp;autoplay=0&amp;tools=1&amp;skin=#000000&amp;volume=70&amp;q=1&amp;comment=" /></object>';
	$("#preview").html(fhtml);
	//$("#src").attr("value",phtml);
	$("#loading").hide();
}
function removeFile(){
	$.post('php/video.php',{applicationName:applicationName,removeFile:phtml},function(){
		tinyMCEPopup.close();
	});
}

function insertVideo(){
	var ed = tinyMCEPopup.editor;
	ed.execCommand('mceInsertContent', false, fhtml, {skip_undo : 1});
	//document.forms[0].submit();
	//insertMedia();
	tinyMCEPopup.close();
}
function renameFile(oldNameWithPath, newName){
	$("#loading").show();
	$.post('php/video.php',{applicationName:applicationName,renameFile:true,newNameFile:newName,oldNameFile:oldNameWithPath},uploadComplete);
}