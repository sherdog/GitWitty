/*  styleswitcher.js - Copyright 2006 JoomlaShack
 *  http://www.joomlashack.com/ - Professional Joomla Templates
 *  For use with JoomlaShack templates only
 */ 

var defaultFontSize =  82;
var minimumFontSize =  60;
var maximumFontSize = 100;

var defaultStyle = "Fixed";

$debug_this_script = false;

var prefsLoaded = false;
var currentFontSize = defaultFontSize;



function do_template_specific_stuff(){
}


function toggleFluid(){
	var switchTo = (currentStyle == "Fixed") ? "Fluid" : "Fixed";

	document.body.className = switchTo;

}


function setWidth(width){
	
	if(width != "Fluid"){
		document.body.className = '';
		currentStyle = "Fixed";
	}else{
		document.body.className = 'bodyfluid';
		currentStyle = "Fluid";
	}
}



	function revertStyles(){
		currentFontSize = defaultFontSize;
		changeFontSize(0);
	}

	
	function changeFontSize(sizeDifference){
		currentFontSize = parseInt(currentFontSize) + parseInt(sizeDifference * 5);
	
		if(currentFontSize > maximumFontSize){
			currentFontSize = maximumFontSize;
		}else if(currentFontSize < minimumFontSize){
			currentFontSize = minimumFontSize;
		}
		setFontSize(currentFontSize);
	}

	function setFontSize(fontSize){
		if($debug_this_script){alert ('fontsize is being set: ' + fontSize);}
		var stObj = (document.getElementById) ? document.getElementById('content_area') : document.all('content_area');
		document.body.style.fontSize = fontSize + '%';
	}




	function setActiveStyleSheet(title) {
	  var i, a, main;
	  for(i=0; (a = document.getElementsByTagName("link")[i]); i++) {
	    if(a.getAttribute("rel").indexOf("style") != -1 && a.getAttribute("title")) {
	      a.disabled = true;
	      if(a.getAttribute("title") == title) a.disabled = false;
	    }
	  }
	}

	function getActiveStyleSheet() {
	  var i, a;
	  for(i=0; (a = document.getElementsByTagName("link")[i]); i++) {
	    if(a.getAttribute("rel").indexOf("style") != -1 && a.getAttribute("title") && !a.disabled) return a.getAttribute("title");
	  }
	  return null;
	}

	function getPreferredStyleSheet() {
	  var i, a;
	  for(i=0; (a = document.getElementsByTagName("link")[i]); i++) {
	    if(a.getAttribute("rel").indexOf("style") != -1
	       && a.getAttribute("rel").indexOf("alt") == -1
	       && a.getAttribute("title")
	       ) return a.getAttribute("title");
	  }
	  return null;
	}




	function createCookie(name,value,days) {
	  if (days) {
	    var date = new Date();
	    date.setTime(date.getTime()+(days*24*60*60*1000));
	    var expires = "; expires="+date.toGMTString();
	  }
	  else expires = "";
	  document.cookie = name+"="+value+expires+"; path=/";
	}

	function readCookie(name) {
	  var nameEQ = name + "=";
	  var ca = document.cookie.split(';');
	  for(var i=0;i < ca.length;i++) {
	    var c = ca[i];
	    while (c.charAt(0)==' ') c = c.substring(1,c.length);
	    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	  }
	  return null;
	}



	function js_init() {
		if($debug_this_script){  alert('window.onload firing');}

			cookie = readCookie("pageWidth");
			currentStyle = cookie ? cookie : "Fixed";
			setWidth(currentStyle);


			var cookie = readCookie("style");
			var title = (cookie) ? cookie : getPreferredStyleSheet();
			setActiveStyleSheet(title);

			cookie = readCookie("fontSize");
			currentFontSize = (cookie) ? cookie : defaultFontSize;
			setFontSize(currentFontSize);

			do_template_specific_stuff();

		if($debug_this_script){alert('window.onload is exiting');}
	}




	window.onunload = function(e) {
		if($debug_this_script){ alert('onunload is firing'); }

		  createCookie("pageWidth", currentStyle, 365);

		var title = getActiveStyleSheet();
		createCookie("style", title, 365);

		createCookie("fontSize", currentFontSize, 365);

		if($debug_this_script){ alert('onunload is firing'); }
	};





	var myimages=new Array();
	function preloadimages(){
		for (i=0;i<preloadimages.arguments.length;i++){
			myimages[i]=new Image();
			myimages[i].src=preloadimages.arguments[i];
		}
	}
