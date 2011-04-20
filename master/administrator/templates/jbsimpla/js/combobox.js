/**
* @version		$Id: modal.js 5263 2006-10-02 01:25:24Z webImagery $
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * JCombobox javascript behavior
 *
 * Used for transforming <input type="text" ... /> tags into combobox dropdowns with appropriate <noscript> tag following
 * for dropdown list information
 *
 * @package		Joomla
 * @since		1.5
 * @version     1.0
 */
var JCombobox = function() { this.constructor.apply(this, arguments);}
JCombobox.prototype = {

	constructor: function()
	{
		var ie7 = false;
		var agt = navigator.userAgent.toLowerCase();
		this.is_ie = (agt.indexOf("msie") != -1);
		this.is_opera = (agt.indexOf("opera") != -1);
		this.is_safari = (agt.indexOf("safari") != -1);
		var boxes = document.getElements('.combobox');
		if (this.is_ie) { this.is_ie7 = (agt.indexOf("msie 7.0") != -1); }
		for ( var i=0; i < boxes.length; i++) {
			if (boxes[i].tagName == 'INPUT' && boxes[i].type == 'text') {
				this.populate(boxes[i]);
			}
		}
	},

	populate: function(element)
	{
		var list = document.getElementById('combobox-'+element.id).getElementsByTagName('LI');
		var select = document.createElement("select");
		select.setAttribute('id','combobox-'+element.id+'-select');
		for ( var i=0; i < list.length; i++) {
			// Do population bit here
			var o = document.createElement('option');
			o.value = list[i].innerHTML;
			o.innerHTML = list[i].innerHTML;
			if (o.value == element.value) {
				o.selected = selected;
			}
			select.appendChild(o);
		}
		select.inputbox = element.id;
		select.onchange = function(){ var input = document.getElementById(this.inputbox); input.value = this.options[this.selectedIndex].value; }
		element.parentNode.insertBefore(select, element);

		var coords = this.getCoords(select);
		var widthOffset = 35;
		var heightOffset = 13;
		if (this.is_ie) {
			if (this.is_ie7) {
				coords.x = coords.x + 2;
				coords.y = coords.y + 1;
				widthOffset = 27;
				heightOffset = 6;
			}
			else {
				coords.y = coords.y + 2;
				widthOffset = 33;
				heightOffset = 12;
			}
		}
		if (this.is_opera) {
			widthOffset = 23;
			heightOffset = 4;
		}
		if (this.is_safari) {
			if (navigator.appVersion.indexOf("Win")!=-1) {
				coords.y = coords.y - 1;
				widthOffset = 32;
				heightOffset = 10;
			}
			else {
				coords.y = coords.y - 1;
				widthOffset = 32;
				heightOffset = 10;
			}
		}

		// Set text field properties based on the select box
		element.style.position = 'absolute';
		element.style.top = coords.y + 'px';
		element.style.left = coords.x + 'px';
		element.style.width = select.offsetWidth - widthOffset + 'px';
		element.style.height = select.offsetHeight - heightOffset + 'px';
		element.style.zIndex = 1000;

		// Add iFrame for IE
		if (this.is_ie7) {
			var iframe = document.createElement('iframe');
			iframe.src = 'about:blank';
			iframe.scrolling = 'no';
			iframe.frameborder = '0';
			iframe.style.position = 'absolute';
			iframe.style.top = coords.y + 'px';
			iframe.style.left = coords.x + 'px';
			iframe.style.width = element.offsetWidth + 'px';
			iframe.style.height = element.offsetHeight + 'px';
			element.parentNode.insertBefore(iframe, element);
		}
	},

	getCoords: function(el) {
		var coords = { x: 0, y: 0 };
		while (el) {
			coords.x += el.offsetLeft;
			coords.y += el.offsetTop;
			el = el.offsetParent;
		}
		return coords;
	}
}


