/**
 * Main JavaScript file
 *
 * @package     NoNumber! Elements
 * @version     2.2.2
 *
 * @author      Peter van Westen <peter@nonumber.nl>
 * @link        http://www.nonumber.nl
 * @copyright   Copyright © 2011 NoNumber! All Rights Reserved
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/* File moved... this is for backward compatibility */
var scripts = document.getElementsByTagName("script");
var nn_script_root = scripts[scripts.length-1].src.replace( 'script.js', '' );

document.write('<script src="'+nn_script_root+'../js/script.js" type="text/JavaScript"><\/script>');