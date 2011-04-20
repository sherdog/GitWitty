/**
 * $Id: editor_plugin_src.js 201 2007-02-12 15:56:56Z spocke $
 *
 * @author Moxiecode
 * @copyright Copyright © 2004-2008, Moxiecode Systems AB, All rights reserved.
 */

(function() {
	tinymce.create('tinymce.plugins.ReadMorePlugin', {
		init : function(ed, url) {
			var pb = '<img src="' + url + '/img/trans.gif" class="mceReadMore mceItemNoResize" />', cls = 'mceReadMore', sep = ed.getParam('readmore_separator', '<hr id="system-readmore" />'), pbRE;

			pbRE = new RegExp(sep.replace(/[\?\.\*\[\]\(\)\{\}\+\^\$\:]/g, function(a) {return '\\' + a;}), 'g');

			// Register commands
			ed.addCommand('mceReadMore', function() {
				var content = ed.getContent();
				if(/<hr id="system-readmore" \/>/i.test(content)){
					return false;
				}
				ed.execCommand('mceInsertContent', 0, pb);
			});

			// Register buttons
			ed.addButton('readmore', {title : 'readmore.readmore_desc', cmd : cls, image : url + '/img/readmore_16x16.png'});

			ed.onInit.add(function() {
				ed.dom.loadCSS(url + "/css/content.css");
				if(ed.settings.language != 'en'){
					ed.dom.loadCSS(url + "/css/content_"+ ed.settings.language +".css");
				}

				if (ed.theme.onResolveName) {
					ed.theme.onResolveName.add(function(th, o) {
						if (o.node.nodeName == 'IMG' && ed.dom.hasClass(o.node, cls))
							o.name = 'readmore';
					});
				}
			});

			ed.onClick.add(function(ed, e) {
				e = e.target;

				if (e.nodeName === 'IMG' && ed.dom.hasClass(e, cls))
					ed.selection.select(e);
			});

			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('readmore', n.nodeName === 'IMG' && ed.dom.hasClass(n, cls));
			});

			ed.onBeforeSetContent.add(function(ed, o) {
				o.content = o.content.replace(pbRE, pb);
			});

			ed.onPostProcess.add(function(ed, o) {
				if (o.get)
					o.content = o.content.replace(/<img[^>]+>/g, function(im) {
						if (im.indexOf('class="mceReadMore') !== -1)
							im = sep;

						return im;
					});
			});
		},
		// Minor adaptions for Read More - Ryan Demmer
		getInfo : function() {
			return {
				longname : 'Readmore',
				author : 'Moxiecode Systems AB',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/pagebreak',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('readmore', tinymce.plugins.ReadMorePlugin);
})();