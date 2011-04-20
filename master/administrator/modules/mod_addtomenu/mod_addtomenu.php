<?php
/**
 * Main Module File
 * Does all the magic!
 *
 * @package     Add to Menu
 * @version     1.6.0
 *
 * @author      Peter van Westen <peter@nonumber.nl>
 * @link        http://www.nonumber.nl
 * @copyright   Copyright © 2011 NoNumber! All Rights Reserved
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Module that adds menu items
*/

// return if NoNumber! Elements plugin is not installed
jimport( 'joomla.filesystem.file' );
if ( !JFile::exists( JPATH_PLUGINS.DS.'system'.DS.'nonumberelements.php' ) ) {
	return;
}

jimport( 'joomla.filesystem.folder' );
$option = JRequest::getCmd( 'option' );
$folder = dirname(__FILE__).DS.'addtomenu'.DS.'components'.DS.$option;
if ( !JFolder::exists( $folder ) ) {
	return;
}

// Include the syndicate functions only once
require_once dirname(__FILE__).DS.'addtomenu'.DS.'helper.php';

require JModuleHelper::getLayoutPath( 'mod_addtomenu'.DS.'addtomenu' );