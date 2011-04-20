<?php
/**
* @package Admin-User-Access (mod_admin_user_access_backend)
* @version 2.1.1
* @copyright Copyright (C) 2007-2008 Carsten Engel. All rights reserved.
* @license GPL available versions: free, trial and pro
* @author http://www.pages-and-items.com
* @joomla Joomla is Free Software
*/

//no direct access
if(!defined('_VALID_MOS') && !defined('_JEXEC')){
	die('Restricted access');
}

global $pi_ua_config, $my, $database, $componentsAccessRights, $aua_usergroup, $option, $path_root;

//path to root
if( defined('_JEXEC') ){
	//joomla 1.5.x
	$path_root = dirname(__FILE__).'/../../..';
}else{
	//joomla 1.0.x
	$path_root = dirname(__FILE__).'/../..';
}

//get database for 1.5.x
if( defined('_JEXEC') ){
	//joomla 1.5
	$database = JFactory::getDBO();
}

//get usertype and id
if( defined('_JEXEC') ){
	//joomla 1.5
	$user =& JFactory::getUser();
	$user_type = $user->get('usertype');	
	$user_id = $user->get('id');	
}else{
	//joomla 1.0.x
	$user_type = $my->usertype;
	$user_id = $my->id;
}

//get option
if( defined('_JEXEC') ){
	//joomla 1.5
	$framework = '1.5.x';
	$option = JRequest::getVar('option', '');
	$task = JRequest::getCmd('task');
}else{
	//joomla 1.0.x
	$framework = '1.0.x';
	$option = mosGetParam( $_REQUEST, 'option', '' );
	$task = mosGetParam( $_REQUEST, 'task', '' );
}

//get config to see what needs to be done
if(file_exists($path_root.'/administrator/components/com_pi_admin_user_access/class.php')){
				
	$database->setQuery("SELECT config "
	."FROM #__pi_aua_config "
	."WHERE id='aua' "
	."LIMIT 1"
	);
	$temp = $database->loadObjectList();
	$temp = $temp[0];
	$raw = $temp->config;			
	
	$params = explode( "\n", $raw);
	
	for($n = 0; $n < count($params); $n++){		
		$temp = explode('=',$params[$n]);
		$var = $temp[0];
		$value = '';
		if(count($temp)==2){
			$value = trim($temp[1]);
			if($value=='false'){
				$value = false;
			}
			if($value=='true'){
				$value = true;
			}
		}							
		$pi_ua_config[$var] = $value;		
	}	
	
	//reformat array components
	$components = explode(',',$pi_ua_config['components']);
	$temp = array();		
	for($n = 0; $n < count($components); $n++){			
		$component = explode(';',$components[$n]);	
		$component = str_replace('[newline]','
',$component);
		$component = str_replace('[equal]','=',$component);			
		if(isset($component[4])){
			$component_array = array($component[0],$component[1],$component[2],$component[3], $component[4]);
		}else{				
			$component_array = array($component[0],$component[1],$component[2],$component[3], 'nothing');
		}
		array_push($temp,$component_array);
	}	
	$pi_ua_config['components'] = $temp;	
	
	//reformat array dropdown_buttons
	$dropdown_buttons = explode(',',$pi_ua_config['dropdown_buttons']);
	$temp = array();		
	for($n = 0; $n < count($dropdown_buttons); $n++){			
		$dropdown_button = explode(';',$dropdown_buttons[$n]);			
		$dropdown_button_array = array($dropdown_button[0],$dropdown_button[1]);
		array_push($temp,$dropdown_button_array);
	}	
	$pi_ua_config['dropdown_buttons'] = $temp;		
	
	//reformat array extra_buttons	
	$temp_extra_buttons = $pi_ua_config['extra_buttons'];		
	$temp_extra_buttons_array = explode('-||-', $temp_extra_buttons);			
	if(count($temp_extra_buttons_array)==1){
		$temp_extra_buttons_array = array($temp_extra_buttons);
	}	
	$temp_extra_buttons_array = str_replace('[newline]','
',$temp_extra_buttons_array);
	$temp_extra_buttons_array = str_replace('[equal]','=',$temp_extra_buttons_array);
	$pi_ua_config['extra_buttons'] = $temp_extra_buttons_array;			
					
}else{	
	echo '<span style="color: red;">component Admin-User-Access is not installed. module Admin-User-Access needs this component.</span>';	
}

//get class
$class_aua_module_superding = new class_aua_module_superding();

//redirect to Pages-and-Items	
if($pi_ua_config['redirect_to_pi'] && $class_aua_module_superding->check_trial_version_aua($pi_ua_config)){
	if( defined('_JEXEC') ){
		//joomla 1.5
		$control_panel = 'com_cpanel';
	}else{
		//joomla 1.0.x
		$control_panel = '';
	}
	if($option==$control_panel){
		$url = 'index2.php?option=com_pi_pages_and_items';		
		if(defined('_JEXEC')){
			//joomla 1.5
			$mainframe->redirect($url);
		}else{
			//joomla 1.0.x
			mosRedirect($url);
		}
	}
}

//get aua_usergroup if needed
if(($pi_ua_config['active_sections'] || $pi_ua_config['active_actions'] || $pi_ua_config['active_categories'] || $pi_ua_config['active_pagesaccess'] || $pi_ua_config['use_toolbar'] || $pi_ua_config['activate_modules'] || ($pi_ua_config['use_componentaccess'] && $user_type!='Super Administrator' && $option!='com_login' && $option!='com_cpanel')) && $user_type!='Super Administrator'){	

	//get aua_usergroup		
	$database->setQuery("SELECT group_id FROM #__pi_aua_userindex WHERE user_id='$user_id' LIMIT 1 ");
	$rows_userindex = $database->loadObjectList();
	if(count($rows_userindex)!=0){
		$row_userindex = $rows_userindex[0];	
		$aua_usergroup = $row_userindex->group_id;
	}else{
		$aua_usergroup = '';
	}
}

//get item_id	
if(JRequest::getVar('id')){
	$item_id = JRequest::getVar('id');
}else{
	$item_id = JRequest::getVar('cid', null, 'get', 'array');
	$item_id = intval($item_id[0]);		
}

//start content restrictions
if(($framework=='1.5.x' && !$item_id && $option=='com_content' && !$task) || ($framework=='1.0.x' && !$task)){
	$class_aua_module_superding->article_manager($user_type, $pi_ua_config, $aua_usergroup);
}

//if moving or copying an article
if($option=='com_content' && ($task=='movesect' || $task=='copy') && $user_type!='Super Administrator' && $pi_ua_config['active_categories']){
	$class_aua_module_superding->article_move_copy($pi_ua_config, $aua_usergroup);
}

$admin_page = false;
//if($framework=='1.5.x' && $item_id && $option=='com_content'){
if($framework=='1.5.x' && $task=='edit' && $option=='com_content'){
	$admin_page = 'edit';
}

//if workflow access restrictions are activated and editting item
if($pi_ua_config['active_actions'] && (($framework=='1.5.x' && ($task=='add' || $task=='edit')) || ($framework=='1.0.x' && ($task=='new' || $task=='edit'))) && $user_type!='Super Administrator' && $option=='com_content'){
	//$class_aua_module_superding->aua_disable_stuff();
}

//new item and section restrictions are active, so rip out sections out of section selector
if($pi_ua_config['active_sections'] && (($framework=='1.5.x' && $task=='add') || ($framework=='1.0.x' && $task=='new')) && $user_type!='Super Administrator' && $option=='com_content'){
	//$class_aua_module_superding->rip_sections_out($database, $item_id, $aua_usergroup);
}

//new item and section restrictions are active, so rip out sections out of section selector
if($pi_ua_config['active_categories'] && (($framework=='1.5.x' && $task=='add') || ($framework=='1.0.x' && $task=='new')) && $user_type!='Super Administrator' && $option=='com_content'){
	$class_aua_module_superding->rip_categories_out($database, $aua_usergroup);
}

//item access com_content backend
if((($framework=='1.5.x' && $admin_page=='edit') || ($framework=='1.0.x' && $option=='com_content' && ($task=='new' || $task=='edit'))) && $user_type!='Super Administrator' && $class_aua_module_superding->check_trial_version_aua($pi_ua_config)){	

	
	
	//if page access or category access restrictions are activated
	if($pi_ua_config['active_pagesaccess'] || $pi_ua_config['active_categories']){
	
		if($pi_ua_config['com_content_access']=='page_access' && $pi_ua_config['active_pagesaccess']){
		
			//get page access data
			$database->setQuery("SELECT pageid_usergroupid FROM #__pi_aua_access_pages");
			$pages_access_rights = $database->loadResultArray();		
				
			//make array of pages which user has access to		
			$user_page_access_array = '';
			$first = true;		
			for($n = 0; $n < count($pages_access_rights); $n++){
				$right = $pages_access_rights[$n];			
				$right = explode('_', $right);			
				if($right[1]==$aua_usergroup){				
					if(!$first){
						$user_page_access_array .= ',';				
					}
					$user_page_access_array .= $right[0];
					if($first){
						$first = false;					
					}
				}
			}	
			//print_r($user_page_access_array);
			//get page data
			$database->setQuery("SELECT id, link, componentid, type FROM #__menu WHERE id in ($user_page_access_array)");
			$all_menuitems = $database->loadObjectList();		
			
			$all_menuitems_with_categories = array();
			//make a new array from all categories which are used as category-blog-pages in menu
			foreach($all_menuitems as $menuitem){
				if(((strstr($menuitem->link, 'index.php?option=com_content&view=category&layout=blog') && $menuitem->type=='url') || !strstr($menuitem->link, 'index.php?option=com_content&view=category&layout=blog')) && $menuitem->type!='content_blog_category'){
					//something else	
				}else{
					//category-blog-page
					array_push($all_menuitems_with_categories, $menuitem->id);		
				}
			}		
			//print_r($all_menuitems_with_categories);
			
			//get categories from menuitems
			$all_categories_access = array();		
			foreach($all_menuitems as $menuitem){
				if(in_array($menuitem->id,$all_menuitems_with_categories)){			
					if(defined('_JEXEC')){
						//joomla 1.5
						$cat_id = str_replace('index.php?option=com_content&view=category&layout=blog&id=','',$menuitem->link);				
					}else{	
						//joomla 1.0.x
						$cat_id = $menuitem->componentid;				
					}
					array_push($all_categories_access, $cat_id);				
				}							
			}
			
			//print_r($all_categories_access);
			
			//get items cat id		
			$database->setQuery("SELECT catid FROM #__content WHERE id='$item_id' LIMIT 1 ");
			$rows = $database->loadResultArray();
			$cat_id = $rows[0];	
			
			
			//echo 'item_id='.$item_id;
			//echo 'catid='.$cat_id;
			//print_r($all_categories_access);
			if(!in_array($cat_id, $all_categories_access)){			
				$no_category_access = true;
			}
			
			//end if category access is checked with page-access
		}elseif($pi_ua_config['com_content_access']=='category_access' && $pi_ua_config['active_categories']){
			//category access is checked with category-access
			
			//get category access data
			$database->setQuery("SELECT category_groupid FROM #__pi_aua_categories");
			$category_access_rights = $database->loadResultArray();
			
			//get category from id
			//get items cat id		
			$database->setQuery("SELECT catid FROM #__content WHERE id='$item_id' LIMIT 1 ");
			$rows = $database->loadResultArray();
			$cat_id = $rows[0];
			
			$category_right = $cat_id.'__'.$aua_usergroup;				
		
						
			
			//get categories
			$database->setQuery("SELECT id FROM #__categories");
			$all_categories = $database->loadResultArray();	
						
			//make array from categories which user has access to
			$all_categories_access = array();		
			for($n = 0; $n < count($category_access_rights); $n++){
				$right = $category_access_rights[$n];			
				$right = explode('__', $right);			
				if($right[1]==$aua_usergroup){
					array_push($all_categories_access, $right[0]);
				}
			}
			
			//echo $category_right;
			if(!in_array($cat_id, $all_categories_access)){
				$no_category_access = true;
				//exit('no cat access- cats');
			}
				
			
			//print_r($all_categories_access);
			
			//end if category access is checked with category-access
		} 	
			
		echo '<script language="javascript" type="text/javascript">'."\n";			
		
		//make javascript array of categories which user has access to
		$javascript_array_category_access = 'var categories_with_access = new Array(';		
		$first = true;	
		foreach($all_categories_access as $category){
			if($first){
				$first = false;
			}else{
				$javascript_array_category_access .= ',';
			}			
			$javascript_array_category_access .= '"'.$category.'"';
		}		
		$javascript_array_category_access .= ');';
		echo $javascript_array_category_access."\n\n";		
			
		//javascript in_array function
		echo 'Array.prototype.in_array = function (element){'."\n";
			echo 'var retur = false;'."\n";
			echo 'for (var values in this){'."\n";
				echo 'if (this[values] == element){'."\n";
					echo 'retur = true;'."\n";
					echo 'break;'."\n";
				echo '}'."\n";
			echo '}'."\n";
			echo 'return retur;'."\n";
		echo '};'."\n\n"; 			
				
		//javascript function to rip categories out of category-array of dynamic select
		echo 'function filter_categories(){'."\n";			
			echo 'if(document.adminForm.sectionid && document.adminForm.catid){'."\n";	
			
				//if edit get selected cat
				if(($framework=='1.5.x' && $admin_page=='edit') || ($framework=='1.0.x' && $task=='edit')){					
					echo 'var selected_category = document.adminForm.catid.value;'."\n";	
					//echo 'alert(selected_category);'."\n";						
				}			
				
				//take categories out of array
				echo 'for (i = 0; i < sectioncategories.length; i++){'."\n";											
					echo 'if(!categories_with_access.in_array(sectioncategories[i][1])){'."\n";								
						echo 'sectioncategories.splice(i,1);'."\n";														
						echo 'i = i-1;'."\n";																	
					echo '}'."\n";																	
				echo '}'."\n";
				
				if(($framework=='1.5.x' && $admin_page=='edit') || ($framework=='1.0.x' && $task=='edit')){	
				
					//refresh category select by changing the section select and back again
					echo 'var selected_section = document.adminForm.sectionid.selectedIndex;'."\n";
					echo 'changeDynaList("catid", sectioncategories, document.adminForm.sectionid.options[0].value, 0, 0);'."\n";
					echo 'changeDynaList("catid", sectioncategories, document.adminForm.sectionid.options[selected_section].value, 0, 0);'."\n";				
					
					//select the category innitialy selected	
					echo 'if(selected_category){'."\n";								
						echo 'for(index = 0; index < document.adminForm.catid.length; index++){'."\n";	
							echo 'if(document.adminForm.catid[index].value == selected_category){'."\n";	
								echo 'document.adminForm.catid.selectedIndex = index;'."\n";	
							echo '}'."\n";	
						echo '}'."\n";							
					echo '}'."\n";
					
				}//end if edit page
				
			echo '}'."\n";//end if on new or edit page																
		echo '}'."\n\n";//end function
		
		//call onload event
		echo 'if(window.addEventListener)window.addEventListener("load",filter_categories,false);'."\n";
		echo 'else if(window.attachEvent)window.attachEvent("onload",filter_categories);'."\n";
		
		//if user has no category-access-permission
		if($no_category_access && (($framework=='1.5.x' && $admin_page=='edit') || ($framework=='1.0.x' && $task=='edit'))){			
			
			//include language, defaults to english
			if(file_exists($path_root.'/administrator/components/com_pi_admin_user_access/language/'.$pi_ua_config['language'].'.php')){
				require_once($path_root.'/administrator/components/com_pi_admin_user_access/language/'.$pi_ua_config['language'].'.php');
			}else{
				require_once($path_root.'/administrator/components/com_pi_admin_user_access/language/en.php');
			}
			//exit('cat_right='.$category_right);
				
							
			//do_alert			
			$message = _pi_ua_lang_no_category_item_access;
			echo 'function restrict_access(){'."\n";
				
				//echo 'alert("'.$message.'"); window.history.go(-1);'."\n";
				echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
				echo 'alert("'.addslashes(html_entity_decode($message)).'"); document.location.href=\'index2.php?option=com_pi_admin_user_access&task=unlock_item&item_id='.$item_id.'\';'."\n";
			echo '}'."\n";
			
			//call onload event
			echo 'if(window.addEventListener)window.addEventListener("load",restrict_access,false);'."\n";
			echo 'else if(window.attachEvent)window.attachEvent("onload",restrict_access);'."\n";				
		}
		
		echo '</script>';		
	
	}//end if page access
		
	
}//end item access com_content backend
	
//get component-access-data only if we need it
if($pi_ua_config['use_toolbar'] || ($pi_ua_config['use_componentaccess'] && $user_type!='Super Administrator' && $option!='com_login' && $option!='com_cpanel')){	

	//get component access data	for component access and toolbar	
	$database->setQuery("SELECT component_usergroupid FROM #__pi_aua_access_components ");
	$componentsAccessRights = $database -> loadResultArray();	
	
	//print_r($componentsAccessRights);		
	
}

//component-access-restriction
if($pi_ua_config['use_componentaccess'] && $user_type!='Super Administrator' && $option!='com_login' && $option!='com_cpanel' && $option!=''){

	$component_right = $option.'__'.$aua_usergroup;

	if(!in_array($component_right, $componentsAccessRights) && $class_aua_module_superding->check_trial_version_aua($pi_ua_config)){	
		
		//include language, defaults to english
		if(file_exists($path_root.'/administrator/components/com_pi_admin_user_access/language/'.$pi_ua_config['language'].'.php')){
			require_once($path_root.'/administrator/components/com_pi_admin_user_access/language/'.$pi_ua_config['language'].'.php');
		}else{
			require_once($path_root.'/administrator/components/com_pi_admin_user_access/language/english.php');
		}
		
		//do alert	
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';	
		echo "<script> alert('".addslashes(html_entity_decode(_pi_ua_lang_no_access_component))."'); window.history.go(-1); </script>";
		exit();
	}
}

//check if we need to show toolbar
$display_ua_toolbar = false;
$extra = false;
$display_joomla_toolbar = false;
if($pi_ua_config['use_toolbar']){
	if($pi_ua_config['activate_toolbars']){
		
		//if not administrator get display toolbars settings from this aua_usergroup
		if($user_type!='Super Administrator'){
			$database->setQuery("SELECT ua_toolbar, j_toolbar, extra FROM #__pi_aua_usergroups WHERE id='$aua_usergroup' LIMIT 1 ");
			$rows = $database->loadObjectList();
			if(count($rows)!=0){
				$row = $rows[0];			
				$display_ua_toolbar = $row->ua_toolbar;
				$display_joomla_toolbar = $row->j_toolbar;	
				$extra = $row->extra;
			}else{
				$display_ua_toolbar = 0;
				$display_joomla_toolbar = 0;	
				$extra = 0;
			}	
		}			
	}else{		
		$display_ua_toolbar = true;
		$display_joomla_toolbar = true;
	}
	if($user_type=='Super Administrator'){
		if($pi_ua_config['display_toolbar_superadmin']){
			$display_ua_toolbar = true;
			$display_joomla_toolbar = true;
		}else{
			$display_ua_toolbar = 0;
			$display_joomla_toolbar = 0;
		}		
	}
}


//toolbar
if($pi_ua_config['use_toolbar']){	

	if(!defined('_JEXEC')){
		//joomla 1.0.x wrapper opening tag
		echo '<div style="display: none" id="user_access_toolbar">';
	}		

	//display toolbar if usergoup has this set or if super administrator
	if(($display_ua_toolbar || ($user_type=='Super Administrator' && $pi_ua_config['display_toolbar_superadmin'])) && $class_aua_module_superding->check_trial_version_aua($pi_ua_config)){
	
		//get stylesheet for toolbar
		echo '<link rel="stylesheet" type="text/css" href="modules/mod_admin_user_access_backend/toolbar.css" />';
		echo '<script language="JavaScript" type="text/javascript" src="components/com_pi_admin_user_access/javascript/suckerfish_dropdowns.js"></script>';
		
		
		//make a new array with only components user has access to
		$components = array();
		foreach($pi_ua_config['components'] as $component){												
			$component_right = $component[0].'__'.$aua_usergroup;			
			if(in_array($component_right, $componentsAccessRights) || $user_type=='Super Administrator'){
				if(isset($component[4])){
					$component_array = array($component[0], $component[1], $component[2], $component[3], $component[4]);
				}else{
					$component_array = array($component[0], $component[1], $component[2], $component[3], 'nothing');
				}			
				array_push($components, $component_array);
			}
		}
		
		//print_r($components);
		
		//get backend template
		$database->setQuery("SELECT template FROM #__templates_menu WHERE client_id='1' "  );
		$backend_template = $database->loadResult();	
		
		$default_icon = 'templates'.'/'.$backend_template.'/'.'images'.'/'.'menu'.'/icon-16-component.png';
		
		//display toolbar
		$displayed_links_array = array();	
		echo '<div>';	
		echo '<ul id="pi_ua_menu">';	
		
		//first do dropdown menus
		for($b = 0; $b < count($pi_ua_config['dropdown_buttons']); $b++){
			$dropdown_has_children = false;
			for($c = 0; $c < count($components); $c++){
				if($components[$c][3]==$pi_ua_config['dropdown_buttons'][$b][0]){
					$dropdown_has_children = true;
				}
			}
			if($dropdown_has_children){
				echo '<li><a href="#" class="dropdown_button">';
				$temp = stripslashes($pi_ua_config['dropdown_buttons'][$b][1]);						
				echo str_replace('"','&quot;',$temp);	
				echo '</a>';
				echo '<ul>';
				for($c = 0; $c < count($components); $c++){					
					if($components[$c][3]==$pi_ua_config['dropdown_buttons'][$b][0]){					
						echo '<li><a href="index2.php?option='.$components[$c][2].'"';
						if($option==$components[$c][0]){
							echo ' class="active"';
						}
						if($components[$c][4]=='' || $components[$c][4]=='nothing'){
							echo ' style="background-image: url(\''.$default_icon.'\');"';							
						}else{
							echo ' style="background-image: url(\''.$components[$c][4].'\');"';	
						}					
						echo '>';
						$temp = stripslashes($components[$c][1]);						
						echo str_replace('"','&quot;',$temp);						 
						echo '</a></li>';
						array_push($displayed_links_array, $components[$c][2]);//using the link as the unique key			
					}					
				}
				echo '</ul>';
				echo '</li>';
			}
		}			
		
		//component links not assigned to any dropdown					
		foreach($components as $component){
			if(!in_array($component[2], $displayed_links_array)){
				echo '<li><a href="index2.php?option='.$component[2].'"';
				if($option==$component[0]){
					echo ' class="active"';
				}
				if($component[4]=='' || $component[4]=='nothing'){
					echo ' style="background-image: url(\''.$default_icon.'\');"';
				}else{					
					echo ' style="background-image: url(\''.$component[4].'\');"';		
				}
				
				echo '>';				
				$temp = stripslashes($component[1]);						
				echo str_replace('"','&quot;',$temp);	
				echo '</a></li>';
			}
		}
		
		//extra buttons
		if($extra){	
			$buttons = explode(',',$extra);
			foreach($buttons as $button){	
				$array_index_number = $button-1;
				if($array_index_number>=0){
					echo stripslashes($pi_ua_config['extra_buttons'][$array_index_number]);
				}
			}
		}
		
		echo '</ul>';
		echo '</div>';
		echo '<div id="pi_ua_clear_both">';
		echo '</div>';
				
	}//end display ua_toolbar		
	
	//if both toolbars are visible, put a line in between them
	if(($display_ua_toolbar && $display_joomla_toolbar) || ($user_type=='Super Administrator' && $pi_ua_config['display_toolbar_superadmin'])){
		echo '<div id="pi_ua_menu_line">&nbsp;</div>';
		echo '<link rel="stylesheet" type="text/css" href="components/com_pi_admin_user_access/css/toolbar_line.css" />';		
	}	
	
	//hide joomla toolbar
	if(!$display_joomla_toolbar && $user_type!='Super Administrator'){
			
		if(defined('_JEXEC')){
			//joomla 1.5			
			echo '<link rel="stylesheet" type="text/css" href="components/com_pi_admin_user_access/css/toolbar_hide_j-1-5-x.css" />';
			echo '<link rel="stylesheet" type="text/css" href="components/com_pi_admin_user_access/css/toolbar_padding_bottom_j-1-5-x.css" />';
		}else{
			echo '<link rel="stylesheet" type="text/css" href="components/com_pi_admin_user_access/css/toolbar_padding_bottom_j-1-0-x.css" />';
		}		
		echo '<link rel="stylesheet" type="text/css" href="components/com_pi_admin_user_access/css/toolbar_hide.css" />';
	}

	if(!defined('_JEXEC')){
		//joomla 1.0.x wrapper closing tag
		echo '</div>';		
	
	?>
	<script language="javascript" type="text/javascript">
	
	function uatoolbar_init(){
		joomla_toolbar = document.getElementById('myMenuID').innerHTML;	
		admin_user_access_toolbar = document.getElementById('user_access_toolbar').innerHTML;			
		document.getElementById('myMenuID').innerHTML = admin_user_access_toolbar+'<div id="joomla_toolbar">'+joomla_toolbar+'</div>';	
	}
	document.onload = uatoolbar_init();
	
	</script>
	<?php
	
	}//end joomla 1.0.x wrapper and script
}//end if toolbar



class class_aua_module_superding{
	
	//change in free version
	function check_trial_version_aua($aua_config){		
		
				$aua_key_checked_module = 1;
				
		return $aua_key_checked_module;
	}
	
	
	
	function filter_javascript($script_things){
		$there_are_script_restrictions = 0;
		for($n = 0; $n < count($script_things); $n++){
			if(!$script_things[$n][1]){
				$there_are_script_restrictions = 1;													
			}
		}
		if($there_are_script_restrictions){
			echo 'if(';										
			$first_script_thing = 1;
			for($n = 0; $n < count($script_things); $n++){							
				if(!$script_things[$n][1]){
					if(!$first_script_thing){
						echo ' && ';
					}
					echo 'task!=\''.$script_things[$n][0].'\'';
					$first_script_thing = 0;
				}
			}
			echo '){'."\n";						
		}
		if(defined('_JEXEC')){
			//joomla 1.5
			echo 'submitbutton(task);'."\n";
		}else{
			//joomla 1.0.x
			echo 'submitform(task);'."\n";
		}		
		if($there_are_script_restrictions){
			echo '}'."\n";
		}
	}
	
	
	
	
	
	
	
	function rip_sections_uncategorized_out(){				
		echo "var elSel = document.getElementById('sectionid');\n";
		echo 'var i;'."\n";		
		echo 'if(document.getElementById(\'sectionid\')!= null){'."\n";		
			echo 'for (i = elSel.length - 1; i>=0; i--){'."\n";									
				echo 'if (elSel.options[i].value == "0"){'."\n";						
					echo 'elSel.remove(i);'."\n";										
				echo '}'."\n";								
			echo '}'."\n";
		echo '}'."\n";		
	}
	
	
	
	function rip_categories_out($database, $aua_usergroup){
		//get category access data
		$database->setQuery("SELECT category_groupid FROM #__pi_aua_categories");
		$category_access_rights = $database->loadResultArray();			
		
		//get categories
		$database->setQuery("SELECT id FROM #__categories");
		$all_categories = $database->loadResultArray();	
					
		//make array from categories which user has access to
		$all_categories_access = array();		
		for($n = 0; $n < count($category_access_rights); $n++){
			$right = $category_access_rights[$n];			
			$right = explode('__', $right);			
			if($right[1]==$aua_usergroup){
				array_push($all_categories_access, $right[0]);
			}
		}		
		
		echo '<script language="javascript" type="text/javascript">'."\n";			
		
		//make javascript array of categories which user has access to
		$javascript_array_category_access = 'var categories_with_access = new Array(';		
		$first = true;	
		foreach($all_categories_access as $category){
			if($first){
				$first = false;
			}else{
				$javascript_array_category_access .= ',';
			}			
			$javascript_array_category_access .= '"'.$category.'"';
		}		
		$javascript_array_category_access .= ');';
		echo $javascript_array_category_access."\n\n";		
			
		//javascript in_array function
		echo 'Array.prototype.in_array = function (element){'."\n";
			echo 'var retur = false;'."\n";
			echo 'for (var values in this){'."\n";
				echo 'if (this[values] == element){'."\n";
					echo 'retur = true;'."\n";
					echo 'break;'."\n";
				echo '}'."\n";
			echo '}'."\n";
			echo 'return retur;'."\n";
		echo '};'."\n\n";
		
		echo 'function filter_categories(){'."\n";
			//take categories out of array			
			echo 'for (i = 0; i < sectioncategories.length; i++){'."\n";											
				echo 'if(!categories_with_access.in_array(sectioncategories[i][1])){'."\n";								
					echo 'sectioncategories.splice(i,1);'."\n";														
					echo 'i = i-1;'."\n";																	
				echo '}'."\n";																	
			echo '}'."\n";	
			// reset section select				
			echo 'document.getElementById("sectionid").options[0].selected = true;'."\n";
			// reset category select	
			echo 'changeDynaList(\'catid\', sectioncategories, 0, 0, 0);'."\n";																				
		echo '}'."\n\n";//end function
		
		//call onload event
		echo 'if(window.addEventListener)window.addEventListener("load",filter_categories,false);'."\n";
		echo 'else if(window.attachEvent)window.attachEvent("onload",filter_categories);'."\n";
		
		echo '</script>';	
		
	}
	
	
	
	function do_message($message){
		exit('<html><body>'.$message.' <a href="javascript:window.history.go(-1);">'._pi_ua_lang_back.'</a></body></html>');
	}
	
	function get_language(){
		global $pi_ua_config;
		
		if(defined('_JEXEC')){
			//joomla 1.5				
			$path_to_root = '/../../..';		
		}else{
			//joomla 1.0.x		
			$path_to_root = '/../..';
		}	
		
		if(file_exists(dirname(__FILE__).$path_to_root.'/administrator/components/com_pi_admin_user_access/language/'.$pi_ua_config['language'].'.php')){
			require_once(dirname(__FILE__).$path_to_root.'/administrator/components/com_pi_admin_user_access/language/'.$pi_ua_config['language'].'.php');
		}else{
			require_once(dirname(__FILE__).$path_to_root.'/administrator/components/com_pi_admin_user_access/language/english.php');
		}
	}
	
	
	function article_manager($user_type, $pi_ua_config, $aua_usergroup){
		global $database;
		
		if($user_type!='Super Administrator'){	
		
			echo '<script language="JavaScript" type="text/javascript" src="modules/';
			if(defined('_JEXEC')){
				//joomla 1.5
				echo 'mod_admin_user_access_backend/';	
			}
			echo 'jquery-1_3_2.js"></script>'."\n";				
			echo '<script language="javascript" type="text/javascript">'."\n";		
			echo 'var AUA_jQuery = jQuery.noConflict();';
			echo 'AUA_jQuery(document).ready(function() {'."\n";	
					
			echo 'AUA_jQuery("table.adminlist a").each(function (i) {'."\n";				
			echo 'if(this.href == "javascript:void(0);" || this.href == "javascript:%20void(0);" || this.href == "javascript: void(0);"){'."\n";
			echo 'temp_icon = this.innerHTML;'."\n";
			echo 'AUA_jQuery(this).replaceWith(temp_icon);	'."\n";
			echo '}'."\n";
			
			echo '});'."\n";
			
			echo '});'."\n";	
			echo '</script>'."\n";
						
			if($pi_ua_config['active_items'] || $pi_ua_config['active_categories'] || $pi_ua_config['active_sections'] || $pi_ua_config['active_pagesaccess']){	
									
				
				
				//print_r($access_array_items);
				
				//access for categories	or pages
				if($pi_ua_config['active_categories'] || $pi_ua_config['active_pagesaccess']){
					$access_array_categories = array();		
					if($pi_ua_config['com_content_access']=='category_access' && $pi_ua_config['active_categories']){					
						//access for categories							
						$database->setQuery("SELECT category_groupid FROM #__pi_aua_categories  " );
						$rows = $database->loadObjectList();
						foreach($rows as $row){						
							$pos = strpos($row->category_groupid, '__');						
							//$access_array_categories[] = substr($row->category_groupid, 0, $pos);
							$temp_category_id = substr($row->category_groupid, 0, $pos); 
							$temp_group_id = substr($row->category_groupid, $pos+2, 100); 
							if($temp_group_id==$aua_usergroup){
								$access_array_categories[] = $temp_category_id;
							}
						}				
					}elseif($pi_ua_config['com_content_access']=='page_access' && $pi_ua_config['active_pagesaccess']){											
						//get page access data
						$database->setQuery("SELECT pageid_usergroupid FROM #__pi_aua_access_pages");
						$pages_access_rights = $database->loadResultArray();		
							
						//make array of pages which user has access to		
						$user_page_access_array = '';
						$first = true;		
						for($n = 0; $n < count($pages_access_rights); $n++){
							$right = $pages_access_rights[$n];			
							$right = explode('_', $right);			
							if($right[1]==$aua_usergroup){				
								if(!$first){
									$user_page_access_array .= ',';				
								}
								$user_page_access_array .= $right[0];
								if($first){
									$first = false;					
								}
							}
						}	
						//print_r($user_page_access_array);
						//get page data
						$database->setQuery("SELECT id, link, componentid, type FROM #__menu WHERE id in ($user_page_access_array)");
						$all_menuitems = $database->loadObjectList();		
						
						$all_menuitems_with_categories = array();
						//make a new array from all categories which are used as category-blog-pages in menu
						foreach($all_menuitems as $menuitem){
							if(((strstr($menuitem->link, 'index.php?option=com_content&view=category&layout=blog') && $menuitem->type=='url') || !strstr($menuitem->link, 'index.php?option=com_content&view=category&layout=blog')) && $menuitem->type!='content_blog_category'){
								//something else	
							}else{
								//category-blog-page
								array_push($all_menuitems_with_categories, $menuitem->id);		
							}
						}		
						//print_r($all_menuitems_with_categories);
						
						//get categories from menuitems							
						foreach($all_menuitems as $menuitem){
							if(in_array($menuitem->id,$all_menuitems_with_categories)){			
								if(defined('_JEXEC')){
									//joomla 1.5
									$cat_id = str_replace('index.php?option=com_content&view=category&layout=blog&id=','',$menuitem->link);				
								}else{	
									//joomla 1.0.x
									$cat_id = $menuitem->componentid;				
								}
								array_push($access_array_categories, $cat_id);				
							}							
						}			
					}
								
				}
				
				//print_r($access_array_categories);							
				
							
						
				
				//make array of itemid's with access for this user
				//get all content data				
				$database->setQuery("SELECT id, catid FROM #__content  " );
				$rows = $database->loadObjectList();
				
				$item_access_array = array();
				//loop through array and check access for each of access types: item, cat and section		
				foreach($rows as $row){						
					if($pi_ua_config['active_categories'] || $pi_ua_config['active_pagesaccess']){
						$access_categories = false;
						if(in_array($row->catid, $access_array_categories)){
							$access_categories = true;
						}
					}else{
						$access_categories = true;
					}
					if($access_categories){
						$item_access_array[] = $row->id;
					}					
				}				
				
				//disable article-links and add no-access image
				echo '<script language="javascript" type="text/javascript">'."\n";		
				echo 'var AUA_jQuery = jQuery.noConflict();';
				echo 'AUA_jQuery(document).ready(function() {'."\n";
				//make article access array
				echo 'var items_with_access = [';
				for($n = 0; $n < count($item_access_array); $n++){
					if($n!=0){
						echo ',';
					}
					echo '"'.$item_access_array[$n].'"';	
				}
				echo '];'."\n";	
				
				echo 'AUA_jQuery("table.adminlist tr input").each(function (i) {'."\n";			
					//loop input elements within tr's
					echo 'if(this.name == "cid[]"){'."\n";
						//checkbox
						echo 'item_id = this.value;'."\n";
						echo 'if(AUA_jQuery.inArray(item_id, items_with_access)==-1){'."\n";						
							echo 'AUA_jQuery(this).attr({disabled: "true"});'."\n";
							echo 'td_element = AUA_jQuery(this).parent();'."\n";
							echo 'tr_element = AUA_jQuery(td_element).parent();'."\n";				
							echo 'AUA_jQuery(tr_element).addClass("aua_disable_link");'."\n";					
						echo '}'."\n";				
					echo '}'."\n";		
				echo '});'."\n";
				
				echo 'AUA_jQuery("table.adminlist .aua_disable_link a").each(function (i) {'."\n";
					echo 'href = this.href;'."\n";	
					echo 'pos = href.indexOf(\'index.php?option=com_content\');'."\n";	
					echo 'pos2 = href.indexOf(\'reorder\');'."\n";				
					echo 'if(pos!=\'-1\' && pos2==\'-1\'){'."\n";				
						echo 'link_string = AUA_jQuery(this).html();'."\n";				
						echo 'no_access_img = \'<img src="components/com_pi_admin_user_access/images/no_access.gif" alt="no access" /> \';'."\n";
						echo 'AUA_jQuery(this).replaceWith(no_access_img+link_string);'."\n";
					echo '}'."\n";
					
					echo '//take links on icons and access level out'."\n";	
					echo 'if(this.href == "javascript:void(0);"){'."\n";
					//echo 'if(this.href == "javascript:void(0);" || this.href == "javascript:%20void(0);" || this.href == "javascript: void(0);"){'."\n";
						echo 'temp_icon = this.innerHTML;'."\n";			
						echo 'AUA_jQuery(this).replaceWith(temp_icon);'."\n";		  
					echo '}'."\n"; 		
				echo '});'."\n";	
							
				echo '});'."\n";	
				echo '</script>'."\n";					
			}
				
					
		}
	}
	
	function article_move_copy($pi_ua_config, $aua_usergroup){
		$pi_sections_array = $this->get_sections();	
		$pi_category_array = $this->get_categories();
		
		$pages_array = array();
		foreach($pi_category_array as $pi_category){
			$category_section_id = $pi_category[2];
			foreach($pi_sections_array as $pi_sections){
				if($pi_sections[0]==$category_section_id){
					$pages_array[] = array('', $pi_category[1], $pi_category[0]);
				}
			}
		}		
		
		//make new array combining the 3 arrays and filtering for access
		$array_pages_sections_categories = array();
		for($n = 0; $n < count($pages_array); $n++){
			$page_id = $pages_array[$n][0];
			$page_title = $pages_array[$n][1];
			
			//get the section the pages category is linked to
			for($m = 0; $m < count($pi_category_array); $m++){
				if($pages_array[$n][2]==$pi_category_array[$m][0]){
					$page_cat_id = $pages_array[$n][2];
					$page_section_id = $pi_category_array[$m][2];
					break;
				}
			}
			
			//get section name from id
			for($s = 0; $s < count($pi_category_array); $s++){
				if($page_section_id==$pi_sections_array[$s][0]){
					$page_section_name = $pi_sections_array[$s][1];
					break;
				}
			}
			if($this->check_section_access($page_section_id, $aua_usergroup) && $this->check_category_access($page_cat_id, $aua_usergroup)){			
				$array_pages_sections_categories[] = array($page_id, $page_title, $page_section_name, $page_cat_id, $page_section_id);
			}
		}		
			
		//sort array by order
		$column = '';//reset column if you used this elsewhere
		$column = array();
		foreach($array_pages_sections_categories as $sortarray){
			$column[] = $sortarray[2];	
		}
		$sort_order = SORT_ASC;//define as a var or else ioncube goes mad
		array_multisort($column, $sort_order, $array_pages_sections_categories);	
		
		$sectcat_options = '';
		for($p = 0; $p < count($array_pages_sections_categories); $p++){
			$sectcat_options .= '<option value="'.$array_pages_sections_categories[$p][4].', '.$array_pages_sections_categories[$p][3].'">';				
			$sectcat_options .= $array_pages_sections_categories[$p][2].' / '.$array_pages_sections_categories[$p][1];
			$sectcat_options .= '</option>';
		}
		
		echo '<script language="javascript" type="text/javascript">'."\n";	
		
		//rip_cat_sec_out_of_select
		echo 'function replace_cats_and_sections(){'."\n";				
			echo "document.getElementById('sectcat').innerHTML = '".$sectcat_options."';\n";
		echo '}'."\n";	
		
		echo 'if(window.addEventListener)window.addEventListener("load",replace_cats_and_sections,false);'."\n";
		echo 'else if(window.attachEvent)window.attachEvent("onload",replace_cats_and_sections);'."\n";	
		echo '</script>';	
	}
	
	function get_categories(){
		static $pi_category_array;		
		
		if(!$pi_category_array){
			$database = JFactory::getDBO();
			$database->setQuery("SELECT id, title, section "
			."FROM #__categories "			
			);
			$pi_categories_object = $database->loadObjectList();
			
			$pi_category_array = array();
			foreach($pi_categories_object as $category){
				$pi_category_array[] = array($category->id, $category->title, $category->section);
			}
		}
		return $pi_category_array;
	}
	
	function get_sections(){
		static $pi_sections_array;
		
		if(!$pi_sections_array){
			$database = JFactory::getDBO();
			$database->setQuery("SELECT id, title "
			."FROM #__sections "			
			);
			$pi_sections_object = $database->loadObjectList();
			
			$pi_sections_array = array();
			foreach($pi_sections_object as $pi_section){
				$pi_sections_array[] = array($pi_section->id, $pi_section->title);
			}
		}
		return $pi_sections_array;
	}
	
	function check_section_access($section_id, $usergroup){			
		return true;		
	}	
	
	function get_section_access_rights(){
		static $section_access_rights;			
		$section_access_rights = array();				
		return $section_access_rights;
	}	
	
	function check_category_access($category_id, $usergroup){	
		$category_access_rights = $this->get_category_access_rights();			
		$category_access = $category_id.'__'.$usergroup;				
		$access = false;
		if(in_array($category_access, $category_access_rights)){
			$access = true;
		}		
		return $access;
	}	
	
	function get_category_access_rights(){
		static $category_access_rights;		
		if(!$category_access_rights){	
			$database = JFactory::getDBO();
			$database->setQuery("SELECT category_groupid FROM #__pi_aua_categories");
			$category_access_rights = $database->loadResultArray();			
		}		
		return $category_access_rights;
	}
	
	





}
		

			
	



?>