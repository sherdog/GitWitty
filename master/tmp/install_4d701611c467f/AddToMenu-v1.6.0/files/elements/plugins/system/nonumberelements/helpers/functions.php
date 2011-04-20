<?php
/**
 * NoNumber! Elements Helper File: Functions
 *
 * @package     NoNumber! Elements
 * @version     2.2.2
 *
 * @author      Peter van Westen <peter@nonumber.nl>
 * @link        http://www.nonumber.nl
 * @copyright   Copyright © 2011 NoNumber! All Rights Reserved
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Functions
*/

class NNFunctions
{
	function &getFunctions()
	{
		static $instance;
		if ( !is_object( $instance ) ) {
			$instance = new NoNumberElementsFunctions;
		}
		return $instance;
	}
}
class NoNumberElementsFunctions
{
	function getJSVersion()
	{
		if (	defined( 'JVERSION' )
			&&	version_compare( JVERSION, '1.5', '>=' )
			&&	version_compare( JVERSION, '1.6', '<' )
		) {
			$app = JFactory::getApplication();
			if ( $app->get( 'MooToolsVersion', '1.11' ) != '1.11' ) {
				return '_1.2';
			} else {
				return '';
			}
		} else {
			return '';
		}
	}

	function dateToStrftimeFormat( $dateFormat ) {
		$caracs = array(
			// Day - no strf eq : S
			'd' => '%d', 'D' => '%a', 'j' => '%#d', 'l' => '%A', 'N' => '%u', 'w' => '%w', 'z' => '%j',
			// Week - no date eq : %U, %W
			'W' => '%V',
			// Month - no strf eq : n, t
			'F' => '%B', 'm' => '%m', 'M' => '%b',
			// Year - no strf eq : L; no date eq : %C, %g
			'o' => '%G', 'Y' => '%Y', 'y' => '%y',
			// Time - no strf eq : B, G, u; no date eq : %r, %R, %T, %X
			'a' => '%P', 'A' => '%p', 'g' => '%l', 'h' => '%I', 'H' => '%H', 'i' => '%M', 's' => '%S',
			// Timezone - no strf eq : e, I, P, Z
			'O' => '%z', 'T' => '%Z',
			// Full Date / Time - no strf eq : c, r; no date eq : %c, %D, %F, %x
			'U' => '%s'
		);
		return strtr( (string) $dateFormat, $caracs );
	}

	function html_entity_decoder( $given_html, $quote_style = ENT_QUOTES, $charset = 'UTF-8' )
	{
		if ( is_array( $given_html ) ) {
			foreach( $given_html as $i => $html ) {
				$given_html[$i] = html_entity_decoder( $html );
			}
			return $given_html;
		}
		if ( phpversion() < '5.0.0' ) {
			$trans_table = array_flip( get_html_translation_table( HTML_SPECIALCHARS, $quote_style ) );
			$trans_table['&#39;'] = "'";
			return ( strtr( $given_html, $trans_table ) );
		} else {
			return html_entity_decode( $given_html, $quote_style, $charset );
		}
	}
}

// PHP4 compatiblility for cloning objects
if ( phpversion() < '5.0.0' && function_exists( 'clone' ) == false ) {
	eval('
		function clone( $object )
		{
			return $object;
		}
	');
}
// PHP4 compatiblility for strripos
if ( phpversion() < '5.0.0' &&  function_exists('strripos') == false ) {
    eval('
		function strripos( $haystack, $needle )
		{
			$pos = strlen( $haystack ) - strpos( strrev( $haystack ), strrev( $needle ) );
			if ( $pos == strlen( $haystack ) ) { $pos = 0; }
			return $pos;
		}
	');
}
