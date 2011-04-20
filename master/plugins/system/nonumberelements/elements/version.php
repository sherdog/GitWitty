<?php
/**
 * Element: Version
 * Displays the version check
 *
 * @package     NoNumber! Elements
 * @version     2.2.2
 *
 * @author      Peter van Westen <peter@nonumber.nl>
 * @link        http://www.nonumber.nl
 * @copyright   Copyright © 2011 NoNumber! All Rights Reserved
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// Ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Version Element
 *
 * Available extra parameters:
 * xml			The title
 * description		The description
 */
class JElementVersion extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Version';

	function fetchTooltip()
	{
		return;
	}

	function fetchElement( $name, $value, &$node )
	{
		$xml =			$node->attributes( 'xml' );
		$extension =	$node->attributes( 'extension' );

		$user = JFactory::getUser();

		if( !strlen( $extension ) || !strlen( $xml ) || ( $user->usertype != 'Super Administrator' && $user->usertype != 'Administrator' ) ) {
			return;
		}

		// Import library dependencies
		require_once JPATH_PLUGINS.DS.'system'.DS.'nonumberelements'.DS.'helpers'.DS.'versions.php';
		$versions = NNVersions::instance();

		return $versions->getMessage( $extension, $xml );
	}
}