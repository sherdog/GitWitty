<?php
/**
* Author: Dioscouri Design - www.dioscouri.com
* @package JUGA - Joomla User Group Access
* @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

//registers function as event
$mainframe->registerEvent( 'onAfterInitialise', 'jugaPlugin' );

function jugaPlugin () {
	global $mosConfig_absolute_path, $mosConfig_live_site, $mainframe, $database;
	
	$my 		= $mainframe->getUser();	
	$option 	= JRequest::getVar( "option", JRequest::getVar( "option", "", "GET" ), "POST");
	$section 	= JRequest::getVar( "section", JRequest::getVar( "section", "", "GET" ), "POST");
	$view	 	= JRequest::getVar( "view", JRequest::getVar( "view", "", "GET" ), "POST");
	$task 		= JRequest::getVar( "task", JRequest::getVar( "task", "", "GET" ), "POST");
	$id		 	= JRequest::getInt( "id", JRequest::getVar( "id", "", "GET" ), "POST");

	// Load the JUGA class
	require_once($mosConfig_absolute_path.'/administrator/components/com_juga/juga.class.php');

	// **********************************************************************
	// check user's rights to be on selected page with option, section, task
	$details["user"] 	= $my; 
	$details["option"] 	= $option; 
	$details["section"] = $section; 
	$details["view"] 	= $view; 
	$details["task"] 	= $task; 
	$details["site"]	= $mainframe->_name;
	// pass the id variable only if jugaPlugin is being called from the front-end
	// otherwise we're restricting all the way down to the ID in the back-end
	// and bloating our database table
	if ($mainframe->_name == "site") {
		$details["id"]		= $id;
	} else {
		unset( $details["id"] );
	}
	
	// check access rights
	$access = jugaRightsCheck( $details );

	// if user has access, load page
	if ($access["access"]) {
		return true;
	} else { // user doesn't have access	
		// if error_url_published, redirect there, else go to homepage
		if ( ($access["error_url_published"] == '1') && ($access["error_url"]) ) {
			// redirect to custom error URL
			mosRedirect( $access["error_url"] );
			exit();
		} else {
			// redirect to the home page w/ a notice saying ERROR: Unauthorized Access.
			// mosRedirect( $mosConfig_live_site, "ERROR: Unauthorized Access." );
			$mainframe->redirect( 'index.php', JText::_('ALERTNOTAUTH') );
			exit();
		}
	} // end if no access	
} // end function botJuga
?>