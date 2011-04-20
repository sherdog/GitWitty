<?php
/**
 * Install File
 * Does the stuff for the specific extensions
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

$name = 'Add to Menu';
$alias = 'addtomenu';
$ext = $name.' (admin module)';

// MODULE
$states[] = installExtension( $alias, $name, 'module' );

// Stuff to do after installation / update
function afterInstall( &$db ) {
	$queries = array();

	$queries[] = "UPDATE `#__modules`
		SET `position` = 'status',
			`published` = 1,
			`access` = 2,
			`client_id` = 1
		WHERE `module` = 'mod_addtomenu'";

	foreach ( $queries as $query ) {
		$db->setQuery( $query );
		$db->query();
	}
}