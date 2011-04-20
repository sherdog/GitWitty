/**
 * $Id: editor_plugin_src.js 520 2008-01-07 16:30:32Z spocke $
 *
 * @author Moxiecode
 * @copyright Copyright © 2004-2008, Moxiecode Systems AB, All rights reserved.
 */

(function() {
	tinymce.create('tinymce.plugins.FloatImagePlugin', {

		init : function(ed, url) {
			// Register commands
			ed.addCommand('mceFloatImage', function() {
				var n = ed.selection.getNode();
				if (n.nodeName=="IMG"){
					var fl = ed.dom.getStyle(n,"float");
					var nfl = "";
					if (fl=="") nfl = "left";
					ed.dom.setStyle(n,"float",nfl);
					tinyMCE.execCommand("mceRepaint");
				}
			});

			// Register buttons
			ed.addButton('floatimage', {title : 'floatimage.floatimage_desc', cmd : 'mceFloatImage'});
			ed.onNodeChange.add(this._nodeChanged);
		},
		_nodeChanged : function(ed, cm, n, co) {
			var c, fl="";
			if (n.nodeName=="IMG") {
				var fl = ed.dom.getStyle(n,"float");
			}
			c = cm.get('floatimage');
			c.setActive(fl!="");
		},
		
		getInfo : function() {
			return {
				longname : 'FloatImage',
				author : 'RK',
				authorurl : '',
				infourl : '',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('floatimage', tinymce.plugins.FloatImagePlugin);
})();