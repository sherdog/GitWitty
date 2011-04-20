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
var DBSharing = {

    initialize: function()
    {
        this.tree = new InputTreeControl({ 
               div: 'dbsharing-tree_tree', 
               mode: 'folders', 
               grid: true, 
               imgDir: 'components/com_multisites/patches/sharing/'
            }, { 
                text: 'DB Sharing',
                icon: 'dbsharing.gif#3', 
                openicon: 'dbsharing.gif#8',
                open: true
            });
        this.tree.adopt('dbsharing-tree');
    }
};

window.addEvent('domready', function(){
    // Added to populate data on iframe load
    if ( $('dbsharing-tree_tree') != null) {
        DBSharing.initialize();
        DBSharing.trace = 'start';
    }
});
