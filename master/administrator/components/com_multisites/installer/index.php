<?php
/**
 * @file       index.php
 * @brief      Installer for the JMS front-end layouts (templates).
 *
 * @version    1.2.20
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2010 Edwin2Win sprlu - all right reserved.
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
 * @par History:
 * - V1.2.20 29-JAN-2010: Initial version
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// If Joomla 1.6
if ( version_compare( JVERSION, '1.6') >= 0) { 
   require_once( dirname( __FILE__) .DS. 'j1.6' .DS. 'index.php' );
   
}
// Else: Default Joomla 1.5
else {
   require_once( dirname( __FILE__) .DS. 'j1.5' .DS. 'index.php' );
}