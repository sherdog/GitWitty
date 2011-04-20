<?php
$user =&JFactory::getUser();
$doc = &JFactory::getDocument();
$url  = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

// set custom template theme for user
if( !is_null( JRequest::getCmd('templateTheme', NULL) ) ) {
$user->setParam($this->template.'_theme', JRequest::getCmd('templateTheme'));
$user->save(true);
}
if($user->getParam($this->template.'_theme')) {
$this->params->set('templateTheme', $user->getParam($this->template.'_theme'));
}
		
$logoFile = 'templates/'. $this->template .'/logo.png';

$profilelink = "<a href=\"" . $url . "administrator/index.php?option=com_users&view=user&task=edit&cid[]=" . $user->get('id') . "\">". JText::_( 'PROFILE' ) . " " . $user->username ."</a>";

$ap_task_set = JRequest::getCmd('ap_task') != null;
$ap_task     = JRequest::getCmd('ap_task');
$option      = JRequest::getCmd('option');
$task        = JRequest::getCmd('task');
$view        = JRequest::getCmd('view');
$client      = JRequest::getCmd('client');
$section     = JRequest::getCmd('section');
$scope       = JRequest::getCmd('scope');
$menutype    = JRequest::getCmd('menutype');

//Template Params
$templateTheme    = $this->params->get('templateTheme');

$shortHeader  = $this->params->get('shortHeader', 0);
$showSidebar  = $this->params->get('showSidebar', 0);
$showQuickAdd  = $this->params->get('showQuickAdd', 1);
$showComponentList  = $this->params->get('showComponentList', 1);
$switchSidebar  = $this->params->get('switchSidebar', 0);
$bottomStatus = $this->params->get('bottomStatus', 0);
$showBreadCrumbs = $this->params->get('showBreadCrumbs', 0);
$showChildren  = $this->params->get('showChildren', 0);
$showFootMods  = $this->params->get('showFootMods', 0);
$fontSize  = $this->params->get('fontSize');
$minWidth  = $this->params->get('minWidth');
$sidebarWidth  = $this->params->get('sidebarWidth');

$menusAcl = $this->params->get('menusAcl', 0);
$sectionsAcl = $this->params->get('sectionsAcl', 0);
$categoriesAcl = $this->params->get('categoriesAcl', 0);
$articlesAcl = $this->params->get('articlesAcl', 0);
$componentsAcl = $this->params->get('componentsAcl', 0);
$modulesAcl = $this->params->get('modulesAcl', 0);
$pluginsAcl = $this->params->get('pluginsAcl', 0);
$templatesAcl = $this->params->get('templatesAcl', 0);
$usersAcl = $this->params->get('usersAcl', 0);
$adminAcl = $this->params->get('adminAcl', 0);
$installAcl = $this->params->get('installAcl', 0);

$flexicontentAcl = $this->params->get('flexicontentAcl', 0);
$k2Acl = $this->params->get('k2Acl', 0);
$projectforkAcl = $this->params->get('projectforkAcl', 0);
$sobi2Acl = $this->params->get('sobi2Acl', 0);
$tiendaAcl = $this->params->get('tiendaAcl', 0);
$virtuemartAcl = $this->params->get('virtuemartAcl', 0);

$wideComponents = explode(',', $this->params->get('wideComponents'));
if (in_array($option, $wideComponents)) {
$showSidebar = 0;
} 
else if(($task =="edit") || ($task =="add")){
$showSidebar = 0;
}
if($option =="com_cpanel" && !$ap_task_set){
$showSidebar = 1;
}

if($option == "com_projectfork"){
$db =& JFactory::getDBO();
$sql = 'SELECT name FROM #__pf_themes WHERE is_default=1';
$db->setQuery($sql);
$pfTheme = $db->loadResult(); 
}else{
$pfTheme = "";
}

if(($pfTheme == "steel") && ($option == "com_projectfork")){
$steelActive = 1;
} else {
$steelActive = 0;
}
?>
