$(function(){
 	$("#ddetails").hide();
	
});
var link = "";
function findUser(){
	var str = $("#user").attr("value");
	$("#loading").show();
	$("#users").show();
	$.post('php/user.php',{search:str,task:'searchuser'},function(data,textStatus){
		$("#list").empty();
		$("#ddetails").hide();
		if (data!="") {
			var found = data.split(';');
			$(found).each(function(){
				var f = this.split(':');
				$("#list").append('<option value="'+f[0]+'">'+f[1]+'</option>');
			});
			$("#list").change(function(){
				var name = $(this).children("option:selected").attr("innerHTML");
				var userid = $(this).children("option:selected").attr("value");
				showInfo(name, userid);
			});		
		}
		$("#loading").hide();
	});
}

function findFriends(){
	$("#loading").show();
	$("#users").show();
	$.post('php/user.php',{task:'searchfriends'},function(data,textStatus){
		$("#list").empty();
		$("#ddetails").hide();
		if (data!=""){
			var found = data.split(';');
			$(found).each(function(){
				var f = this.split(':');
				$("#list").append('<option value="'+f[0]+'">'+f[1]+'</option>');
			});
			$("#list").change(function(){
				var name = $(this).children("option:selected").attr("innerHTML");
				var userid = $(this).children("option:selected").attr("value");
				showInfo(name, userid);
			});		
		}
		$("#loading").hide();
	});
}

function showInfo(username, userid){
	$("#loading").show();
	$.post('php/user.php',{useridtoshow:userid,task:'showinfo'},function(data,textStatus){
		eval("var user = "+data+";");
		$("#avatar").html(user.avatar);
		$("#userinfo").html(getHTMLUser(username,false));
		$("#userkarma").html(user.karma);
		$("#userrating").html(user.rating);
		$("#mood").html(user.mood);
		$("#ddetails").show();
		link = user.link;
		
		$("#preview").html(tinyMCEPopup.editor.getLang('advanced_dlg.result')+' '+getHTMLUser(username,true)+' <a href="#" id="change">'+tinyMCEPopup.editor.getLang('advanced_dlg.change')+'</a>');
		$("#change").click(function(){
			var value=$("#willbe").attr("innerHTML");
			$("#willbe").before('<input type="text" value="'+value+'" id="willbe_edit">');
			$("#willbe").hide();
			$(this).remove();
			return false;
		});

		$("#loading").hide();
	});
}

function insertUser(){
	var html,value;
	if($("#willbe_edit").size()>0){
		value=$("#willbe").attr("innerHTML");
		var text=$("#willbe_edit").attr("value");
		html = getHTMLUser(text,false);
	}else{
		value=$("#willbe").attr("innerHTML");
		html = getHTMLUser(value,false);
	}
	if (value){
		var ed = tinyMCEPopup.editor;
		ed.execCommand('mceInsertContent', false, html, {skip_undo : 1});
	}
	tinyMCEPopup.close();		
}

function getHTMLUser(text,withid){
	if (withid) withid = 'id="willbe"'; else withid = "";
	return '<b><img src="/components/com_idoblog/assets/templates/nicomo/ido-imgs/user_small.png"/><a href="'+link+'" target="_blank"'+withid+'>'+text+'</a></b>';
}