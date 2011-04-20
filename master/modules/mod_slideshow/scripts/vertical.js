/***********************************************
* Horizontal SlideShow Module for Joomla! v1.5.x
* These script file was modified due to browsers needs
* JT.gen.tr - Joomla! Turkce Egitim ve Destek Sitesi
************************************************
* This script will be modified for multi-slideshow and no-break
************************************************
* Cross browser Marquee II- © Dynamic Drive (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit http://www.dynamicdrive.com/ for this script and 100s more.
***********************************************/

//Main codes

var copyspeed=marqueespeed
var pausespeed=(pauseit==0)? copyspeed: 0
var actualheight=''

function scrollmarquee(){
	if (parseInt(cross_marquee.style.top)>(actualheight*(-1)+8))
		cross_marquee.style.top=parseInt(cross_marquee.style.top)-copyspeed+"px"
	else
		cross_marquee.style.top=parseInt(marqueeheight)+8+"px"
	}

function initializemarquee(){
	cross_marquee=document.getElementById("vmarquee")
	cross_marquee.style.top=0
	marqueeheight=document.getElementById("marqueecontainer").offsetHeight
	actualheight=cross_marquee.offsetHeight
	if (window.opera || navigator.userAgent.indexOf("Netscape/7")!=-1){ //if Opera or Netscape 7x, add scrollbars to scroll and exit
		cross_marquee.style.height=marqueeheight+"px"
		cross_marquee.style.overflow="scroll"
		return
	}
	setTimeout('lefttime=setInterval("scrollmarquee()",30)', delayb4scroll)
}

if (window.addEventListener)
	window.addEventListener("load", initializemarquee, false)
else if (window.attachEvent)
	window.attachEvent("onload", initializemarquee)
else if (document.getElementById)
	window.onload=initializemarquee