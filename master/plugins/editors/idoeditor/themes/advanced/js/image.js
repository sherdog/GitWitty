var pdata = {
	phtml : "",
	phtml4file : "",
	phtml4link : "",
	link : "",
	linkOriginal : "",
	linkPreview : "",
	originalSize : "",
	style : "",
	loadFile : false,
	closeAfterLoad : false,
	width:0,
	height:0,
	external:false
};

var pattern4checkname = tinyMCEPopup.editor.getParam("pattern4checkname");
var applicationName = tinyMCEPopup.editor.getParam("application_name");
var useNoindex = tinyMCEPopup.editor.getParam("use_noindex");

$(function(){
	window.setTimeout(function(){
		$("#exts").html(tinyMCEPopup.editor.getParam("upload_image_ext").replace(/ /g,", "));
	
		$("#removePicture").live('click',function(){
			$("#preview").html(tinyMCEPopup.editor.getLang('advanced_dlg.upload_image'));
			$("#pfile").attr("value","");
			removeFile();
			pdata.phtml = pdata.phtml4file = pdata.phtml4link = "";
			pdata.link = pdata.linkOriginal = "";
			return false;
		});
		var ed = tinyMCEPopup.editor;
		e = ed.selection.getNode();
	
		if (e.nodeName == 'IMG') {
			var src = ed.dom.getAttrib(e, 'src');
			if (ed.dom.getAttrib(e,"class")=="mceBigImg"){
				pdata.link = src;
				pdata.linkOriginal = ed.dom.getAttrib(e,"original");
				pdata.linkPreview = "";
				pdata.originalSize = ed.dom.getAttrib(e,"original_size");
				pdata.style = ed.dom.getAttrib(e,"style");
				pdata.phtml = pdata.phtml4link = "<img src='"+src+"' id='pic'/>"
				if (pdata.link==pdata.linkOriginal)
					$("#plink").attr("value",pdata.link);
			} else {
				$("#plink").attr("value",src);
				pdata.link = src;
				pdata.phtml = pdata.phtml4link = "<img src='"+src+"' id='pic'/>"
			}
			generatePreview();
		}
	},100);
});
function generatePreview(){
	$("#preview").html(pdata.phtml);
	$("#preview").append('<img src="img/delete.gif" id="removePicture">');
	if (pdata.phtml!=pdata.phtml4file) $("#preview #pic").css({width:"100px"});
}
function changeLink(){
	var src = $("#plink").attr("value");
	if (src != "") pdata.phtml = pdata.phtml4link = '<img src="'+src+'" id="pic">';
	else pdata.phtml = pdata.phtml4link = pdata.phtml4file;
	generatePreview();
	return false;
}
function getCodeForInsert(){
	var src = $("#plink").attr("value");
	if (src!="") {
		pdata.link = src;
		pdata.linkOriginal = "";
	}
	if (pdata.link=="") return "";
	var size = "";
	if (pdata.width>0) size+=" width='"+pdata.width+"' ";
	if (pdata.height>0) size+=" height='"+pdata.height+"' ";
	var result = '';
	var ni = "";
	if (useNoindex && pdata.external===true){
		ni = "noindex='true' ";		
	}
	if (pdata.linkOriginal==""){
		result = "<img "+ni+size+"src='"+pdata.link+"'/>";
	} else {
		result = '<img '+ni+size+'class="mceBigImg" alt="'+tinyMCEPopup.editor.getLang('advanced_dlg.resize_image_alt')+'" style="'+pdata.style+'" original_size="'+pdata.originalSize+'" original="'+pdata.linkOriginal+'" src="'+pdata.link+'"/>';
	}
	return result;
}

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
			url:'php/image.php?applicationName='+applicationName, 
			secureuri:false,
			params:{applicationName: applicationName},
			fileElementId:'pfile',
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
		hideLoading();
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
	pdata.link = data.filename;
	pdata.linkPreview = data.filename_preview;
	pdata.linkOriginal = data.filename_original;
	pdata.originalSize = data.original_size;
	pdata.width = (data.width)?data.width:0;
	pdata.height = (data.height)?data.height:0;
	pdata.phtml = pdata.phtml4file = '<img src="'+pdata.linkPreview+'" id="pic">';
	pdata.phtml4link = "";
	pdata.loadFile = true;
	$("#plink").attr("value","");
	generatePreview();
	hideLoading();
	if (pdata.closeAfterLoad===true){
		insertImage();
	}
}

function insertImage(){
	var src = $("#plink").attr("value");
	if (pdata.phtml4link!="" && src!="" && /^http:\/\//.test(src)){
		pdata.closeAfterLoad = true;
		pdata.external = true;
		uploadFromLink(src);
	} else {
		var ed = tinyMCEPopup.editor;
		ed.execCommand('mceInsertContent', false, getCodeForInsert(), {skip_undo : 1});
		tinyMCEPopup.close();
	}
}

function hideLoading(){
	$("#loading").hide();
}
function removeFile(){
	if (pdata.loadFile){
		$.post('php/image.php',{applicationName: applicationName,removeFile:pdata.link},hideLoading);
	}
}

function renameFile(oldNameWithPath, newName){
	$("#loading").show();
	$.post('php/image.php',{applicationName: applicationName,renameFile:true,newNameFile:newName,oldNameFile:oldNameWithPath},uploadComplete);
}

function uploadFromLink(link){
	$("#loading").show();
	var params = {applicationName: applicationName,linkGetWH : link};
	if ($("#loadOnServer").is(':checked')) params = {applicationName: applicationName,link : link};
	$.post('php/image.php',params,uploadComplete);	
}