/**
 * @file       dbsharing.js
 * @version    1.2.0
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2009 Edwin2Win sprlu - all right reserved.
 * @license    This program is free software; you can redistribute it and/or
 *             modify it under the terms of the GNU General Public License
 *             as published by the Free Software Foundation; either version 2
 *             of the License, or (at your option) any later version.
 *             This program is distributed in the hope that it will be useful,
 *             but WITHOUT ANY WARRANTY; without even the implied warranty of
 *             MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU General Public License for more details.
 *             You should have received a copy of the GNU General Public License
 *             along with this program; if not, write to the Free Software
 *             Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *             A full text version of the GNU GPL version 2 can be found in the LICENSE.php file.
 */


/**
 * JMS / templates / DB Sharing Panel
 */
var TreeSites = {

    initialize: function()
    {
        this.tree = new InputTreeControl({ 
               div: 'treesites_tree', 
               mode: 'folders', 
               grid: true, 
               onSelect: function(node, state) {
                  // If not selected
                  if ( state) {
   	                TreeSites.selectedNode( node, state);
                  }
               },
               imgDir: 'components/com_multisites/patches/sharing/'
            }, { 
                text: 'Websites',
                icon: 'dbsharing.gif#12', 
                openicon: 'dbsharing.gif#11',
                open: true
            });
        this.tree.adopt('treesites');
    },
    
    selectedNode: function( node) {
         var ajax;
         var x;
         var save_style;
         
			x = node.div.icon;
			node.empty(x);
			node.getImg( 'loading.gif', x );
         
         // $('treesite_message').innerHTML = 'Refreshing ...';
         try {  ajax = new ActiveXObject('Msxml2.XMLHTTP');   }
         catch (e)
         {
           try {   ajax = new ActiveXObject('Microsoft.XMLHTTP');    }
           catch (e2)
           {
             try {  ajax = new XMLHttpRequest();     }
             catch (e3) {  ajax = false;   }
           }
         }
      
         ajax.onreadystatechange  = function()
         {
            if(ajax.readyState  == 4)
            {
               if(ajax.status  == 200) {
                  $('treesite_detail').innerHTML  = ajax.responseText;
                  $$('dl.tabs').each(function(tabs){ new JTabs(tabs, {}); });
                  $('treesite_message').innerHTML = '';
               }
               else {
                  $('treesite_detail').innerHTML  = '';
                  $('treesite_message').innerHTML = "Error code " + ajax.status;
               }
      			node.update();
            }
            // Build the dynamic tooltips
            var JTooltips = new Tips($$('.hasDynTip'), { maxTitleChars: 50, fixed: false});
         };
      
         ajax.open( "GET", "index.php?option=com_multisites&task=ajaxToolsGetSite&"+g_curtoken+"&site_id="+node.data['href'].substring( 1),  true);
         ajax.send(null);
   }
};

window.addEvent('domready', function(){
    // Added to populate data on iframe load
        TreeSites.initialize();
        TreeSites.trace = 'start';
});
