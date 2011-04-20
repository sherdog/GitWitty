<?php
/**
* @package content-plugin for Pages-and-Items (com_pi_pages_and_items)
* @version 1.4.3
* @copyright Copyright (C) 2006-2008 Carsten Engel. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author http://www.pages-and-items.com
* @joomla Joomla is Free Software
*/

//no direct access
if(!defined('_VALID_MOS') && !defined('_JEXEC')){
	die('Restricted access');
}

//register mambots
if( defined('_JEXEC') ){
	//joomla 1.5	
	$mainframe->registerEvent( 'onPrepareContent', 'pages_and_items' );
}else{
	//joomla 1.0.x
	$_MAMBOTS->registerFunction( 'onPrepareContent', 'pages_and_items_j1_0_x' );
}

function pages_and_items_j1_0_x($published, &$row, &$params, $page=0) {
	pages_and_items($row, $params, $page);
}

function pages_and_items( &$row, &$params, $page=0 ){	

	global $database;

	if( defined('_JEXEC') ){
		//joomla 1.5	
		$database = JFactory::getDBO();	
	}	
	
	if(isset($row->id)){
		$id = $row->id;		
		$database->setQuery("SELECT show_title, itemtype FROM #__pi_item_index WHERE item_id='$id' LIMIT 1");
		$rows = $database->loadObjectList();
		$item_type = false;
		if($rows){
			$itemrow = $rows[0];			
			$item_type = $itemrow->itemtype;	
		}
		
		
		
		//hide title, but only if item has been made with PI because if it is not, it won't be in the item index
		if($item_type!=''){
			$show_title = $itemrow->show_title;		
			if($show_title==0){	
				//do the replacement
				$row->title = '';			
			}		
		}	
		
		//insert anchor link		
		echo '<a name="item'.$id.'"></a>';
		
		//if item is not text or html or other_item or any customitemtype then get itemtype-plugin-specific output		
		if($item_type!='' && $item_type!='text' && $item_type!='html' && $item_type!='other_item' && file_exists(dirname(__FILE__).'/../pages_and_items/itemtypes/'.$item_type.'/item_frontend.php')){				 
			include(dirname(__FILE__).'/../pages_and_items/itemtypes/'.$item_type.'/item_frontend.php');	
		}	
		
		//if customitemtype
		if(strpos($item_type, 'ustom_')){
		
			//get option and view
			if( defined('_JEXEC') ){
				//joomla 1.5
				$view = JRequest::getVar('view', '');	
				$option = JRequest::getVar('option', '');		
			}else{
				//joomla 1.0.x
				$option = mosGetParam( $_REQUEST, 'option', '' );
				$task = mosGetParam( $_REQUEST, 'task', '' );
				$view = '';
				if($task=='view'){
					$view = 'article';
				}						
			}
			
			if($option=='com_content' || $option=='com_frontpage'){			
				if($view=='article'){
					//full item view, so take out any content which is has bot-code to take out in full view
					$regex = "/{hide_in_full_view}(.*?){\/hide_in_full_view}/is";
					$row->text = preg_replace($regex, '', $row->text);
					$regex = "/{hide_in_intro_view}/is";
					$row->text = preg_replace($regex, '', $row->text);
					$regex = "/{\/hide_in_intro_view}/is";
					$row->text = preg_replace($regex, '', $row->text);
				}else{
					//intro item view, so take out any bot-code
					$regex = "/{hide_in_full_view}/is";
					$row->text = preg_replace($regex, '', $row->text);
					$regex = "/{\/hide_in_full_view}/is";
					$row->text = preg_replace($regex, '', $row->text);
					$regex = "/{hide_in_intro_view}(.*?){\/hide_in_intro_view}/is";
					$row->text = preg_replace($regex, '', $row->text);
					$regex = "/{hide_in_intro_view}/is";
					$row->text = preg_replace($regex, '', $row->text);
					$regex = "/{\/hide_in_intro_view}/is";
					$row->text = preg_replace($regex, '', $row->text);
				}			
			}
		}
		
		//show hits		
		if(strpos($row->text, '{article_hits}')){ 
			$regex = "/{article_hits}/is";
			$row->text = preg_replace($regex, $row->hits, $row->text);
		}
		
		//process dynamic fields (like the custom-itemtype-field item_hits, which need to be generated on the fly)
		$regex = "/{pi_dynamic_field (.*?) (.*?)}/is";	
		preg_match_all($regex, $row->text, $matches); 		
		for($n = 0; $n < count($matches[1]); $n++){		
			$class_name = 'class_fieldtype_'.$matches[1][$n].'_dynamic_output';
			if(!class_exists($class_name)){
				if($matches[1][$n]=='php'){
					require_once(dirname(__FILE__).'/../../administrator/components/com_pi_pages_and_items/dynamic_output_php_fieldtype.php');
				}else{
					require_once(dirname(__FILE__).'/../pages_and_items/fieldtypes/'.$matches[1][$n].'/dynamic_output.php');
				}			
			}
			$class_plugin = new $class_name();
			$dynamic_field_params = $matches[2][$n];
			$output = $class_plugin->display_dynamic_field($row, $params, $dynamic_field_params);		
			$code_to_replace = $matches[0][$n];		
			$row->text = str_replace($code_to_replace, $output, $row->text);
		}
	}
}

?>