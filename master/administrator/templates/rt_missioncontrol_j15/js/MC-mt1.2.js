/**
 * @package MissionControl Admin Template - RocketTheme
 * @version 1.5.0 September 1, 2010
 * @author RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2010 RocketTheme, LLC
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 */

(function(){
	
	var MC = this.MC = {
		
		init: function(){
			if (this.MC.Notice) this.MC.Notice.shake.delay(500, this.MC.Notice.shake, 3);
			SelectBoxes.init();
			MC.fixIOS();
		},
		
		fixIOS: function(){
			var menu = document.id('mctrl-menu');
			if (menu){
				var children = menu.getElements('li');
				if (children.length){
					children.addEvent('mouseenter', function(e){ new Event(e).stop(); });
				}
			}
		}
		
	};
	
	
	var SelectBoxes = this.MC.SelectBoxes = {
		
		init: function(){
			this.selects = $$('.dropdown select');
			
			this.selects.each(function(sel){
				sel.getParent().addEvent('mouseenter', function(e) {e.stop();});
				this.build(sel);
			}, this);
		},
		
		build: function(sel){
			var selected = new Element('a', {'class': 'mc-dropdown-selected'}).inject(sel, 'before');
			var list = new Element('ul', {'class': 'mc-dropdown'}).inject(selected, 'after');
			
			sel.setStyle('display', 'none');
			
			sel.getChildren().each(function(option, i){
				var active = option.get('selected') || false;
				var lnk = new Element('a', {'href': '#'}).set('text', option.get('text'));
				var opt = new Element('li').inject(list).adopt(lnk);
				
				opt.addEvent('click', function(e){
					e.stop();

					sel.selectedIndex = i;
					selected.getFirst().set('text', option.get('text'));
					
					sel.fireEvent('change');
				});
				
				opt.store('selected', active);
				opt.store('value', option.get('value') || '');
				
				if (active) selected.set('html', '<span class="select-active">' + option.get('text') + '</span>');
			});
			
			var arrow = new Element('span', {'class': 'select-arrow'}).set('html', '&#x25BE;').inject(selected);
		}
		
	};
	

	window.addEvent('domready', MC.init);
	
})();