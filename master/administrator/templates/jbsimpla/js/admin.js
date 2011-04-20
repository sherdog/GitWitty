// commands to run when domready
window.addEvent('domready', function()
{	
	if ($('menu')) {
		
		// Check if any menu items are set as current, if not default to JS search
		if ($$('#menu a.opened').getText() == '') { setCurrent();  }

		// Event listener for toggle sidebar
		$('toggleSideBar').addEvent('click', function(e){
			if (readCookie('sidebarToggle') == 'false') {
				$$('body').removeClass('full');
				$$('#toggleSideBar').removeClass('closed');
				if ($('position')) $('position').setStyle('left', wideX + "px");
				createCookie('sidebarToggle','true',7);
			}
			else {
				createCookie('sidebarToggle','false',7);
				$$('body').addClass('full');
				$$('#toggleSideBar').addClass('closed');
				if ($('position')) $('position').setStyle('left', thinX + "px");
			}
		});
		
		// Check if sidebar should be hidden, and hide it if necessary
		if (readCookie('sidebarToggle') == 'false') { 
				$$('body').addClass('full');
				$$('#toggleSideBar').addClass('closed');
		}
	
		if ($$('#module-menu').hasClass('autohide') == 'true') {
			 if ($$('ul#menu').hasClass('hide') == 'true') {
				$$('body').addClass('full');
				$$('#toggleSideBar').addClass('closed');
			 }
		}
	}
	
	// Initialize mootips
	var myTips = new MooTips($$('.toolTipImg'), {maxTitleChars: 100});// long caption
	
	// Check for system messages, and append the close button and slide feature
	/*
	var message = $('system-message');
	if (message) { 
		addMessageClose(message); 
		$('closeMessage').addEvent('click',function(e) {
			var mySlider = new Fx.Slide('system-message', {duration: 500});
   			mySlider.slideOut() //toggle the slider up and down.
		});
	}*/

	// Fix for module position box
	var poscheck = $('position');
	if (poscheck) {
		document.combobox = null;
		var combobox = new JCombobox();
		document.combobox = combobox;
		thinX = 0;
		wideX = 0;
		thinX = Number($('position').getStyle('left').substring(0, $('position').getStyle('left').indexOf('p')));
		wideX = Number(thinX + 190);
		window.onresize = setPosition;
		thinX = findPosX(document.getElementById('combobox-position-select'));
	}

});

function setPosition() {
	comboX = findPosX(document.getElementById('combobox-position-select'));
	comboY = findPosY(document.getElementById('combobox-position-select'));
	thinX = comboX;
	wideX = thinX + 190;
	$('position').setStyle('left', comboX + "px");
	$('position').setStyle('top', comboY + "px");
	
}

function findPosX(obj)
{
var curleft = 0;
if(obj.offsetParent)
	while(1) 
	{
	  curleft += obj.offsetLeft;
	  if(!obj.offsetParent)
		break;
	  obj = obj.offsetParent;
	}
else if(obj.x)
	curleft += obj.x;
return curleft;
}
function findPosY(obj)
{
var curtop = 0;
if(obj.offsetParent)
	while(1)
	{
	  curtop += obj.offsetTop;
	  if(!obj.offsetParent)
		break;
	  obj = obj.offsetParent;
	}
else if(obj.y)
	curtop += obj.y;
return curtop;
}



window.addEvent('load', function()
{	
	if ($('menu')) {
		// Gets the current menu node by its number to set as active for sidebar menu
		var current = 0;
		var x = 0;
		$$('ul#menu li.node').each(function(i) {
			var li = i.getFirst();
			if (li.hasClass('opened')) {
				current = x;
			}
			x++;
		});
		
		// Sidebar menu code, sets up mootools accordion
		var togglers = $$('ul#menu .node'),
		elements = $$('ul#menu .node ul'),
		sidebarAccordion = new Fx.Accordion(togglers, elements,
		{
			display: current
		});
		
		var list = $$('#menu li.node a.top');
		list.each(function(element) {
		
		// Add Event listeners for menu
		var fx = new Fx.Styles(element, {duration:250, wait:false});
			element.addEvent('mouseenter', function(){
				fx.start({'padding-right': 25});
			});
		 
			element.addEvent('mouseleave', function(){
				fx.start({'padding-right': 15});
			});
		 
		});
	}
});


// Basic create cookie function
function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

// Basic read cookie function
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

// Adds the close option to system messages
function addMessageClose(message) {
	var closeMessage = '<a id="closeMessage"> </a>';
	var messageContent = document.getElementById('system-message').getElementsByTagName('li').item(0).innerHTML;
	$$('dl#system-message dd.message ul').getFirst().setHTML(messageContent + closeMessage);
}

// Navigation function for the dropdown menu
function nav() {
	var goto=document.getElementById('dropdownnav').value;
	if (goto !== 'false') location=goto;
}

// Backup function to attempt to find current link if PHP fails
function setCurrent() {
	var page = location.href;
	var uri = page.substring(page.indexOf("index.php"));
	if (page.indexOf("index2.php")) { 
		var option = $('element-box').getElement('input[name=option]').getProperty('value');
		var current = $$('#menu a[href^=index.php?option='+option+']');
	}
	else {
		var current = $$('#menu a[href="'+uri+'"]');
	}
	
	current.addClass('current');
	$$('#menu a.current').getParent().getParent().getParent().getElement('a').addClass('opened');
	
	/*current.getParent().getParent().getParent().getParent().addClass('parent');

	if (current.getParent().getParent().getParent().hasClass('.node')) {
		
		if (document.getElementById('menu').className == 'parent') {
			$$('.parent').removeClass('parent');
			$$('#menu a.current').getParent().getParent().getParent().getElement('a').addClass('opened');
			$$('#menu a.opened').getParent().addClass('opened');
		}
		else {
			alert('else');
			//$$('.parent').removeClass('parent');
			//current.getParent().getParent().getParent().getElement('a').addClass('current');
			//current.removeClass('current');
			if (!$('.current')) {
				var option = uri.substring(0,uri.indexOf("&"));
				current = $$('#menu a[href="'+option+'"]');
				current.addClass('current');
			}
			$$('#menu a.current').getParent().getParent().getParent().getElement('a').addClass('opened');
			//$$('#menu a.opened').getParent().addClass('opened');
		}
	}*/
	
}