<?php

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgSystemAutoMenu_Magic extends JPlugin {
    
    # These are the menu published-field state codes:
    const menustate_intrash     = -2;
    const menustate_unpublished = 0;
    const menustate_published   = 1;
    
    // Not too DRY - but, we'll trry to keep this object self-encapsulated
    const menuitem_link_format  = 'index.php?option=com_content&view=article&id=%d';
    const menualias_link_format = 'index.php?Itemid=%d';

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
	function plgSystemAutoMenu_Magic( &$subject, $params ) {
		parent::__construct( $subject, $params );
	}

    private function fatalError($error_message) {
        global $mainframe;

        $mainframe->enqueueMessage(JText::sprintf($error_message), 'error');
                
        return false;
    }
    
    private function deleteMenus($menu_ids) {
        $db = &JFactory::getDBO();
        
        // Delete module associations first:
        $db->setQuery( 
            sprintf(
                'DELETE FROM #__modules_menu WHERE (%s)',
                $this->formatWhereEqualsOr('menuid', $menu_ids)
            )
        );
        
        if (!$db->query())
            return $this->fatalError('Error while deleting database module associations');
        
        // Now delete the menus:
        $db->setQuery( 
            sprintf(
                'DELETE FROM #__menu WHERE (%s)',
                $this->formatWhereEqualsOr('id', $menu_ids)
            )
        );
        
        if (!$db->query())
            return $this->fatalError('Error while deleting menu items in database');
    }

    function formatWhereEqualsOr($where_field, $where_values, $type = '%d') {
        $db = &JFactory::getDBO();
        
        $ret = array();
        
        foreach($where_values as $val) {
            switch($type) {
                case '%s': 
                   $val = $db->Quote($val);
                   break;
                case '%d':
                    $val = (int) $val;
                    break;
            }
            $ret[] = sprintf( "%s = $type", $where_field, $val);
        }

        return implode(" OR ",$ret);
    }

    function getMenusForArticles($cids) {
        $ret = array();
        
        $db = &JFactory::getDBO();

        $item_links = array();
        
        // Remember that cids is an array.
        // So let's get them ready for query-ing:
        foreach($cids as $cid)
            $item_links[] = sprintf(self::menuitem_link_format, (int) $cid);

        // So now we find all the menu items:
        $db->setQuery(
            sprintf(
                'SELECT id FROM #__menu WHERE (%s) AND type = %s',
                self::formatWhereEqualsOr('link', $item_links, '%s'),
                $db->Quote('component')
            )
        );
        
        $menuitems = $db->loadObjectList();

        if ($db->getErrorNum())
            return false;

        if (!$menuitems) 
            return $ret; // Basically, nothing was found.

        // Now let's find all out aliases
        $alias_links = array();
        foreach($menuitems as $menuitem)
            $alias_links[] = sprintf(self::menualias_link_format, (int) $menuitem->id);

        $db->setQuery(
            sprintf(
                'SELECT id FROM #__menu WHERE (%s) AND type = %s',
                self::formatWhereEqualsOr('link', $alias_links, '%s'),
                $db->Quote('menulink')
            )
        );

        $menualiases = $db->loadObjectList();

        if ($db->getErrorNum())
            return false;
        
        // Add these aliases to the collection
        if($menualiases)
            $menuitems = array_merge($menuitems,$menualiases);
        
        // Now ready them for $ret
        foreach($menuitems as $menuitem)
            $ret[] = (int) $menuitem->id;
        
        return $ret;
    }

    function setPublishOnMenus($menu_ids, $publish_code) {
        $db = &JFactory::getDBO();
                        
        $db->setQuery(
            sprintf(
                'UPDATE #__menu SET published = %d WHERE (%s)',
                $publish_code,
                self::formatWhereEqualsOr('id', $menu_ids)
            )
        );
        
        if (!$db->query())
            return $this->fatalError('Error while trashing menus in the database');
    }

    function onAfterRoute() {
        global $mainframe;
        
        if (!$mainframe->isAdmin()) 
            return;

        $cids = JRequest::getVar('cid', array(), 'post', 'array');

        if ($cids) {
        	$option = JRequest::getCmd('option');
            $task   = JRequest::getCmd('task');
            $type   = JRequest::getCmd('type');

            if (
                ( $option == 'com_content' && $task == 'remove' ) or
                ( $option == 'com_content' && $task == 'publish'  ) or
                ( $option == 'com_content' && $task == 'unpublish'  ) or
                ( $option == 'com_trash'   && $task == 'restore' && $type == 'content' ) or
                ( $option == 'com_trash'   && $task == 'delete'  && $type == 'content' )
            ) {
                $menu_ids = $this->getMenusForArticles($cids);

                if ($menu_ids === false)
                    return $this->fatalError('Unable to retrieve article menus from the database');
                elseif(count($menu_ids))
                    switch($task) {
                    	case 'remove':
                            return $this->setPublishOnMenus($menu_ids, self::menustate_intrash);
                            break;
                        case 'restore':
                            return $this->setPublishOnMenus($menu_ids, self::menustate_unpublished);
                            break;
                        case 'publish':
                            return $this->setPublishOnMenus($menu_ids, self::menustate_published);
                            break;
                        case 'unpublish':
                            return $this->setPublishOnMenus($menu_ids, self::menustate_unpublished);
                            break;
                        case 'delete':
                            return $this->deleteMenus($menu_ids);
                            break;
                    }
            }
            
        }
        
        return;
    }

}