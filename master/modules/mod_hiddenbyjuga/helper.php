<?php
/**
 * @package JUGA
 * @link 	http://www.dioscouri.com
 * @license GNU/GPLv2
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class modHiddenByJugaHelper {
	/**
	* checker
	*/
	// ************************************************************************/
	function checker( $params ) {
		global $mainframe;
		
		$database = &JFactory::getDBO();
		$my 	= &JFactory::getUser();
		$jugagroups = $params->get( 'jugagroups' );
		
		$negated = false;

		if (!$jugagroups) {
			// if no jugagroups defined, hide the modules
			return false;
		}
	
		$accessLevels = explode(",", trim($jugagroups));

		foreach ($accessLevels as $access) {
			$negoffset = strpos($access, '!');
			if ($negoffset === false) {
				// if no "!" found, simply check if the user is a member of this group
				if (modHiddenByJugaHelper::hasJugaAccessLevel(trim($access), $negoffset)) { $hasaccess = true; }	
			} else {
				// if "!" found, check if the user is a member of this group, and if not, return empty string
				$access = substr($access, $negoffset+1);
				if (modHiddenByJugaHelper::hasJugaAccessLevel(trim($access), false)) { $negated = true; }
			}	
		}
		
		if ($negated) { return false; } 
		if ($hasaccess) { return true; }
		else { return false; }
		
	} // end checker

	/**
	* Checks JUGA Access Level
	*/
	// ************************************************************************/
	function hasJugaAccessLevel($level, $operator=true) {
		global $mainframe;
		
		$database = &JFactory::getDBO();
		$my 	= &JFactory::getUser();
	
		if ($my->id!=0) {
			// first check user's group
			$query = "SELECT group_id FROM #__juga_u2g WHERE `user_id` = '$my->id' ";
			$database->setQuery($query);
			$ugroups = $database->loadObjectList();
		} else {
			// if it doesn't exist, then grab default 
			$query = "SELECT value FROM #__juga WHERE `title` = 'default_juga' ";
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
}