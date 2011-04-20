<?php
/**
* @copyright (C) 2008 Dioscouri Design
* @author Dioscouri Design
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Inspired by RokAccess
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

// $mainframe->registerEvent( 'onBeforeDisplayContent', 'plgJugaAccess' );
$mainframe->registerEvent( 'onPrepareContent', 'plgJugaAccess' );


/**
* Access Plugin
*
* <b>Usage:</b>
* <code>{jugaaccess [!]group[,group]}...some content...{/jugaaccess}</code>
*
* One ore more group name should be passed, you can expressly deny access by putting a '!'
* before the group name in question.  It will keep looping through the list provided until
* it finds a 'true' value so strange results may occur with conflicting group access. 
*
* Examples:
*
*		{jugaaccess Public Access}shows only to Public Access JUGA Group members{/jugaaccess}
*		{jugaaccess !Public Access}shows to all users who are not a member of JUGA Group Public Access{/jugaaccess}
*		{jugaaccess Restricted,!Public Access}shows to all Restricted JUGA Group members members who are NOT members of the Public Access Group{/jugaaccess}
*		{jugaaccess Restricted,Public Access}shows to all members of both JUGA Groups{/jugaaccess}
*
*/

/**
* Process the plugin
*/
// ************************************************************************/
function plgJugaAccess( &$row, $params='', $page=0 ) {
	//Access the database
	$db =& JFactory::getDBO();
		
	//Check whether the bot should process or not
	if ( JString::strpos( $row->text, 'jugaaccess' ) === false ) {
		return true;
	}
	 
	//Get plugin info
	$plugin = &JPluginHelper::getPlugin('content', 'jugaaccess');
	 
	//Search for this tag in the content
	$regex = "#{jugaaccess(.*?)}(.*?){/jugaaccess}#s";
	 
	//Access the parameters
	$pluginParams = new JParameter( $plugin->params );
	 
	//Check whether plugin has been unpublished
	if ( !$pluginParams->get( 'enabled', 1 ) ) {
		$row->text = preg_replace( $regex, '', $row->text );
		return true;
	}
	 
	//Find all instances of plugin and put in $matches
	preg_match_all( $regex, $row->text, $matches );
	
	// echo "<pre>"; print_r($matches); echo "</pre>";
	// Returns: 
	//Array
	//(
	//    [0] => Array
	//        (
	//            [0] => {jugaaccess Public Access}This shows to Public Access group {/jugaaccess}
	//            [1] => {jugaaccess Administrator}This only shows to Administrator group {/jugaaccess}
	//            [2] => {jugaaccess Test,!Administrator}This only shows to Test group who are not Administrators {/jugaaccess}
	//        )
	//
	//    [1] => Array
	//        (
	//            [0] =>  Public Access
	//            [1] =>  Administrator
	//            [2] =>  Test,!Administrator
	//        )
	//
	//    [2] => Array
	//        (
	//            [0] => This shows to Public Access group 
	//            [1] => This only shows to Administrator group 
	//            [2] => This only shows to Test group who are not Administrators 
	//        )
	//
	//)
	
	//Number of plugin instances
	$count = count( $matches[0] );

	 
	//Plugin only processes if there are any instances of the plugin in the text
	if ( $count ) {
			plgJugaAccessProcessPositions( $row, $matches, $count, $regex );
	}
} // end plgJugaAccess
// ************************************************************************/

/**
* Process the positions
*/
// ************************************************************************/
function plgJugaAccessProcessPositions ( &$row, &$matches, $count, $regex, $style='' ) {
 	for ( $i=0; $i < $count; $i++ ) {   
		//Load position
		unset($thisposition);
		$thisposition[0] = $matches[0][$i];
		$thisposition[1] = $matches[1][$i];
		$thisposition[2] = $matches[2][$i];		
		$replace	= plgJugaAccess_replacer( $thisposition );

		// $row->text 	= preg_replace( $thisposition, $replace, $row->text );
		if ($replace) { $row->text = preg_replace( '{'. $matches[0][$i] .'}', $replace, $row->text ); }
		else { $row->text = preg_replace( '{'. $matches[0][$i] .'}', '', $row->text ); }
	}
	 
	//Remove tags without matching module positions
	// $row->text = preg_replace( $regex, '', $row->text );
}
// ************************************************************************/

/**
* Replaces the matched positions
*/
// ************************************************************************/
function plgJugaAccess_replacer( &$matches ) {
	global $my;
	
	$return = "";
	$negated = "";

	$text = $matches[2];
	if (@$matches[1]) {
		$accessLevels = explode(",", trim($matches[1]));
		foreach ($accessLevels as $access) {
			$negoffset = strpos($access, '!');
			if ($negoffset === false) {
				// if no "!" found, simply check if the user is a member of this group
				if (hasAccessLevel(trim($access), $negoffset)) { $return = $text; }	
			} else {
				// if "!" found, check if the user is a member of this group, and if not, return empty string
				$access = substr($access, $negoffset+1);
				if (hasAccessLevel(trim($access), false)) { $negated = true; }
			}

					
		}

	}
	
	if (!$negated) { return $return; } else { return ''; }
} // end plgJugaAccess_replacer
// ************************************************************************/

/**
* Replaces the matched positions
*/
// ************************************************************************/
function hasAccessLevel($level, $operator=true) {
	global $database, $my;
	
	$good = "";
	$group_match = false;
	$ugroupa = array();

	if ( $my->id != 0 ) {
		// first check user's group
		$query = "SELECT group_id FROM #__juga_u2g WHERE `user_id` = '$my->id' ";
		$database->setQuery($query);
		$ugroups = $database->loadObjectList();
	} else {
		// if it doesn't exist, then grab default 
    	$query = "SELECT `value` FROM #__juga WHERE `title` = 'default_juga' ";
		$database->setQuery($query);
		$default_juga = $database->loadResult();
		
		$ugroups = array( 'group_id'=>$default_juga );
    }

	// get user groups and compare to level
	$query = "SELECT * FROM #__juga_groups";
	$database->setQuery($query);
	$jugaGroups = $database->loadObjectList();

	// for each JUGA group in database
	foreach ( $jugaGroups as $jugaGroup ) {

		// for each group the user is in
		foreach ( $ugroups as $ugroup ) {

			//if the ids match
			if ($ugroup->group_id == $jugaGroup->id | $ugroups['group_id'] == $jugaGroup->id ) {

				if ($operator === false) {

					//if the level desired matches the group title
					if ($level==$jugaGroup->title) {

						//set flag
						$good=1;
					}

				} else {

					//if the level desired doesn't match the group title
					if ($level!=$jugaGroup->title) {

						//set flag
						$good=1;
					}
				}
			}
		}
	}

	// calculate result
	if ($good) {
		return true;
	}else{
		return false;
	}
} // end hasAccessLevel
// ************************************************************************/
?>