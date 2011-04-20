<?php
/**
 * @package JUGA
 * @link 	http://www.dioscouri.com
 * @license GNU/GPLv2
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Include the syndicate functions only once
require_once( dirname(__FILE__).DS.'helper.php' );

$checker = modHiddenByJugaHelper::checker( $params );

if ( $checker ){
	$moduleposition = $params->get( 'moduleposition' );
	$modulestyle 	= $params->get( 'modulestyle' );
	$modules = JModuleHelper::getModules($moduleposition);	
	
	if ( $modules ) { foreach ( $modules as $mod ) {
		$attribs = array();		
		$attribs["style"] = $modulestyle;
		echo JModuleHelper::renderModule( $mod, $attribs ); 
	} }

}