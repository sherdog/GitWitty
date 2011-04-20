var currentA = null;
var useNoindex = tinyMCEPopup.editor.getParam("use_noindex");
$(function(){
	window.setTimeout(function(){
		var ed = tinyMCEPopup.editor;
		tinyMCEPopup.restoreSelection();
		currentA = e = ed.dom.getParent(ed.selection.getNode(), 'A');
		var t = ed.selection.getContent();
		if (currentA) t = currentA.innerHTML;
		$("#text").attr("value",t);
		if(t!="") $("#text").hide();
		
		if (currentA) {
			$("#url").attr("value",ed.dom.getAttrib(currentA, 'href'));
		}
	},100);	
});

function insertLink(){
	var url = $("#url").attr("value");
	var text = $("#text").attr("value");
	if (/^\s*www\./i.test(url)) url="http://"+url;
	if(url!='' && text!=""){
		var ed = tinyMCEPopup.editor;
		if (currentA){
			ed.dom.setAttrib(currentA,'href',url);
		} else {
			var t = (useNoindex)?' rel="nofollow"':""
			t = '<a href="'+url+'"'+t+'>'+text+'</a>';
			if (useNoindex) t = "<noindex>"+t+"</noindex>";
			ed.execCommand('mceInsertContent', false, t, {skip_undo : 1});
		}
		tinyMCEPopup.close();		
	}
}