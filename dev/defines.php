<?php
/**
 * @file       define.php
 * @brief      Create the "define.php" to replace the Joomla 1.6 one and allow calling the multisites.
 *
 * @version    1.2.52
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Jms Multi Sites
 *             Single Joomla! 1.6.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2010 Edwin2Win sprlu - all right reserved.
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
 * - V1.2.51 15-DEC-2010: File creation
 *                        Set the following keyword "MultisitesLetterTree::getLetterTreeDir"
 *                        MULTISITES_ID
 *                        in this comment section to be recognized by the patch definition
 *                        as compatible with the "multisites.php" file.
 * - V1.2.52 13-JAN-2010: Add possibility to change the JPATH_BASE root directory in case
 *                        Where a wildcard is used in a domain name.
 *                        This allow simulate specific directory event when the HTTP Server
 *                        is configured with a same directory.
 */

// No direct access.
defined('_JEXEC') or die;

//_jms2win_begin v1.2.52
if ( !defined( 'DS')) {
   define('DS', DIRECTORY_SEPARATOR);
}
// Try detect if this is a slave site and this should set the define MULTISITES_ID
if ( !defined( 'MULTISITES_ID')) {
   if ( !defined( 'JPATH_MULTISITES')) define( 'JPATH_MULTISITES', dirname(__FILE__) .DIRECTORY_SEPARATOR. 'multisites');
   if ( !defined( '_EDWIN2WIN_')) define( '_EDWIN2WIN_', true);
   @include( dirname(__FILE__) .DIRECTORY_SEPARATOR. 'includes' .DIRECTORY_SEPARATOR. 'multisites.php');
   if ( defined( 'JMS2WIN_VERSION')) {
      if ( !defined( 'MULTISITES_ADMIN')) define( 'MULTISITES_ADMIN', true);
      if ( class_exists( 'Jms2Win')) Jms2Win::matchSlaveSite();
   }
}

// If this is a slave site, check if it has a specific deploy directory (if YES, use its path to compute the JPATH_BASE)
if ( defined( 'MULTISITES_ID')) {
   if ( defined( 'MULTISITES_ID_PATH'))   { $filename = MULTISITES_ID_PATH.DIRECTORY_SEPARATOR.'config_multisites.php'; }
   else                                   { $filename = JPATH_MULTISITES.DS.MULTISITES_ID.DIRECTORY_SEPARATOR.'config_multisites.php'; }
   @include($filename);
   if ( isset( $config_dirs) && !empty( $config_dirs) && !empty( $config_dirs['deploy_dir'])) {
      define('JPATH_BASE', $config_dirs['deploy_dir']);
   }
   else {
      define('JPATH_BASE', dirname(__FILE__) );
   }
}
else {
   if ( !defined( 'DS'))         { define('DS', DIRECTORY_SEPARATOR); }
   if ( !defined( 'JPATH_BASE')) { define('JPATH_BASE', dirname(__FILE__)); }
}
//_jms2win_end


/**
 * Joomla! Application define.
 */

//Global definitions.
//Joomla framework path definitions.
$parts = explode(DS, JPATH_BASE);

//Defines.
define('JPATH_ROOT',			implode(DS, $parts));

define('JPATH_SITE',			JPATH_ROOT);
define('JPATH_ADMINISTRATOR',	JPATH_ROOT.DS.'administrator');
define('JPATH_LIBRARIES',		JPATH_ROOT.DS.'libraries');
define('JPATH_PLUGINS',			JPATH_ROOT.DS.'plugins'  );
@include_once ( JPATH_ROOT .DS. 'includes'.DS.'defines_multisites.php' );
if ( !defined( 'JPATH_CONFIGURATION'))  define( 'JPATH_CONFIGURATION', 	JPATH_ROOT );
if ( !defined( 'JPATH_INSTALLATION'))   define( 'JPATH_INSTALLATION',	JPATH_ROOT.DS.'installation' );
if ( !defined( 'JPATH_THEMES'))         define( 'JPATH_THEMES',			JPATH_BASE.DS.'templates' );
if ( !defined( 'JPATH_CACHE'))          define( 'JPATH_CACHE',			   JPATH_BASE.DS.'cache' );
if ( !defined( 'JPATH_MANIFESTS'))      define( 'JPATH_MANIFESTS',		JPATH_ADMINISTRATOR.DS.'manifests');

// Marked as defined
if (!defined('_JDEFINES')) {
   define('_JDEFINES', 1);
}
?>
