<?php

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgContentAutoMenu_Magic extends JPlugin {
    
    const menuitem_link_format  = 'index.php?option=com_content&view=article&id=%d';
    const menualias_link_format = 'index.php?Itemid=%d';
    
    const all_menuitem_params = 'show_title,link_titles,show_intro,show_section,link_section,show_category,link_category,show_author,show_create_date,show_modify_date,show_item_navigation,show_readmore,show_vote,show_icons,show_pdf_icon,show_print_icon,show_email_icon,show_hits,feed_summary,page_title,show_page_title,pageclass_sfx,menu_image,secure';
    
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param object $params  The object that holds the plugin parameters
	 * @since 1.5
	 */
	function plgContentAutoMenu_Magic( &$subject, $params ) {        
		parent::__construct( $subject, $params );
	}


    private function fatalError($error_message) {
        global $mainframe;

        $mainframe->enqueueMessage(JText::sprintf($error_message), 'error');
        
        return false;
    }

    private function defaultComContentParams() {
        // Params Time!
        $menu_params = array();
        $all_menuitem_params = explode(',',self::all_menuitem_params);
        
        // First we load the user-defined defaults:
        foreach (explode(',', $this->params->get('menuitem_param_defaults')) as $param_default)
            if (preg_match('/^(.+)[\=](.+)$/', $param_default, $matches)) {
                $param = $matches[1];
                $value = $matches[2];
                
                $menu_params[] = sprintf("%s=%s",$param,$value);
                unset($all_menuitem_params[$param]);
            }
        
       // Now we'll declare all other menuitem params (that seem to be required) to NULL
        foreach ($all_menuitem_params as $param)
            $menu_params[] = sprintf("%s=",$param);

        return implode("\n",$menu_params);
    }

    private function &createMenuItem($linktype, $link, $name, $menutype, $published, $params, $componentid = 0, $menuparentid = 0, $menusublevel = 0) {
        $db   = &JFactory::getDBO();
        $menu = JTable::getInstance( 'menu');

        $menu->menutype           = $menutype;
        $menu->name               = $name;
        $menu->link               = $link;
        $menu->type               = $linktype;
        $menu->published          = $published;
        $menu->componentid        = $componentid;
        $menu->parent             = $menuparentid;
        $menu->sublevel           = $menusublevel;
        $menu->checked_out        = 0;
        $menu->checked_out_time   = 0;
        $menu->pollid             = 0;
        $menu->browserNav         = 0;
        $menu->access             = 0;
        $menu->utaccess           = 0;
        $menu->lft                = 0;
        $menu->rgt                = 0;
        $menu->home               = 0;
        
        $menu->params = $params;

        // Figure out the order (Just pop this article at the end of the list):
        $menu->ordering = $menu->getNextOrder(
            "menutype = ".$db->Quote($menu->menutype).
            " AND published >= 0 AND parent = ".(int) $menu->parent
        );

        // Validate:
        if (!$menu->check())
            return NULL;

        // Save:
        if (!$menu->store())
            return NULL;

        // Release any checkout status:
        $menu->checkin();
        
        // Compact the menu ordering:
        $menu->reorder( 'menutype='.$db->Quote( $menu->menutype ).' AND parent='.(int)$menu->parent );
        
        return $menu;
    }

    private function assignModulesToItemUsingTemplate($itemid, $templ_itemid) {
        $db = &JFactory::getDBO();
        
        $db->setQuery(
            sprintf('SELECT moduleid FROM #__modules_menu WHERE menuid = %d', (int) $templ_itemid)
        );
        
        $parents_modules = $db->loadObjectList();
    
        if ($parents_modules)
            foreach ($parents_modules as $module) {
                // Assign new module to menu alias
                $db->setQuery( 
                    sprintf( 
                    'INSERT INTO #__modules_menu SET moduleid = %d, menuid = %d',
                    (int) $module->moduleid,
                    (int) $itemid
                    )
                );
                
                if (!$db->query())
                    return false;
            }

        return true;
    }
    
    function onAfterContentSave( &$article, $isNew ) {

        // We only create menus if this is a new article. Otherwise our work is done.       
        if ($isNew) {
            $db = &JFactory::getDBO();
            $category = null;
            $new_menuitem = null;
            $new_menualiases = array();
            
            // First let's find out the category:
            if ($article->catid) {
            	$category = JTable::getInstance( 'category');
                $category->load( $article->catid );
            }       
    
            // Let's Find if any corresponding menutypes match:
            $menutype_title_matches = $this->params->get('menutype_title_matches');
            if ($menutype_title_matches) {
                $db->setQuery(
                    sprintf(
                        'SELECT * FROM #__menu_types WHERE title REGEXP %s',
                        $db->Quote( sprintf($menutype_title_matches,$category->title) )
                    )
                );
                $menutype = $db->loadObject();
                            
                if ($menutype) {
                    $com_articles = &JTable::getInstance( 'component');
                    $com_articles->loadByOption( 'com_content' );
                    if (!$com_articles)
                        return $this->fatalError('Unable to load com_content component');
                        
                	// Found a match - let's create a menuitem
                    $new_menuitem = &$this->createMenuItem(
                        'component',
                        sprintf(self::menuitem_link_format,$article->id), 
                        $article->title, 
                        $menutype->menutype,
                        $article->state,
                        $this->defaultComContentParams(),
                        $com_articles->id
                    );
                    if (!$new_menuitem)
                        return $this->fatalError('Unable to create article menu item');
                }
            }
            
            // Now let's find any eligible create_menulinks_in menu items!
            if ($new_menuitem && count($this->params->get('create_menulinks_in') > 0)) {           
            	$db->setQuery(
                    sprintf(
                        'SELECT * FROM #__menu WHERE name REGEXP %s AND menutype = %s',
                        $db->Quote( sprintf($this->params->get('menulinks_title_matches'), $category->title) ),
                        $db->Quote($this->params->get('create_menulinks_in'))
                    )
                );
                
                $parent_menuitems = $db->loadObjectList();
               
                if ($parent_menuitems)
                    foreach($parent_menuitems as $parent) {
                    	// Found a match - let's create a menuitem
                        $alias = &$this->createMenuItem(
                            'menulink',
                            sprintf(self::menualias_link_format,$new_menuitem->id), # Alias to the original MenuItem
                            $article->title, 
                            $parent->menutype,
                            $article->state,
                            sprintf('menu_item=%d',$new_menuitem->id), // There's really nothing to set here, so we don't fprovide a plugin param override
                            0, # componentid doesn't apply to aliases
                            $parent->id, 
                            ((int)$parent->sublevel+1)
                        );
                        
                        if (!$alias)
                            return $this->fatalError('Unable to create article menu alias under "'.$parent->name.'"');
                        
                        $new_menualiases[] = $alias;
                    }
            }
            
            // Item to Module Association. We load the first item in the menu and use it as an association 'template'
            if ($this->params->get('associate_modules_with_items')) {
                $db->setQuery( 
                    sprintf(
                    'SELECT* FROM #__menu WHERE menutype = %s AND published = 1 ORDER BY ordering ASC LIMIT 1',
                    $db->Quote($new_menuitem->menutype)
                    )
                );
                
                $menuitem_first = $db->loadObject();
                            
                if ($menuitem_first) 
                    if (!$this->assignModulesToItemUsingTemplate($new_menuitem->id, $menuitem_first->id))
                        return $this->fatalError('Unable to assign modules to menuitem "'.$db->getError().'"');
            }
            
            // Alias to Module Association:
            if ($this->params->get('associate_modules_with_aliases'))
                foreach( $new_menualiases as $alias )
                    if (!$this->assignModulesToItemUsingTemplate($alias->id, $alias->parent))
                        return $this->fatalError('Unable to assign modules to menualias "'.$db->getError().'"');
        }
        else if (class_exists('plgSystemAutoMenu_Magic')) {
            // Content publish status should match menuitem status and unpublish on detail submit.
            //
            // If the automenu system plugin is loaded - we can use this opportunity to set the appropriate 
            // menu publish state for this article

            $menu_ids = plgSystemAutoMenu_Magic::getMenusForArticles( array($article->id) );
            
            if ($menu_ids and count($menu_ids))
                plgSystemAutoMenu_Magic::setPublishOnMenus(
                    $menu_ids, 
                    ($article->state) ? 
                        plgSystemAutoMenu_Magic::menustate_published : 
                        plgSystemAutoMenu_Magic::menustate_unpublished
                 );
        }
        
        return true;
    }
    
}