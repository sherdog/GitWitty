<?php
/**
 * @copyright	Copyright (C) 2009 JoomlaPraise. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(dirname(__FILE__).DS.'helper.php');
AdminPraiseHelper::checkLogin();

require_once('assets/variables'.DS.'variables.php');

// gzip
//ob_start('ob_gzhandler');

//New Head
require_once(dirname(__FILE__).DS.'head.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo  $this->language; ?>" lang="<?php echo  $this->language; ?>" dir="<?php echo  $this->direction; ?>" id="minwidth" >
<head>
<?php if(!$safe){?>
<jdoc:include type="head" />
<?php } else {?>
<?php echo $buffer?>
<?php } ?>
<link href="templates/<?php echo  $this->template ?>/css/template.css" rel="stylesheet" type="text/css" />

<?php
require_once('assets/styles'.DS.'styles.php');
?>

<?php
// Fade out system messages
if ($this->getBuffer('message')) { ?>
<script type="text/javascript">
window.addEvent('domready',function() {
        var div = $('hiddenDiv').setStyles({
                display:'block',
                opacity: 1
        }); 
        new Fx.Style('hiddenDiv', 'opacity', {duration:2000, onComplete:
function() {
                
        new Fx.Style('hiddenDiv', 'opacity', {duration:2000}).start(0);
        }}).start(1);
}); 
</script>
<?php } ?>
</head>
<body id="minwidth-body" class="<?php echo $templateTheme. " " .$option. " " .$task. " " .$view. " " .$section. " " .$scope;if ($showSidebar){echo " minwidth";}?><?php if($shortHeader){ echo " ap-short";}?>">

<div class="ap-main">
<div id="ap-fixed">
<?php if($this->countModules('status') != 0) { ?>
<div id="module-status" class="ap-status <?php if ($bottomStatus){echo "status-bottom";}?>"><jdoc:include type="modules" name="status" /></div>
<?php } ?>
	<?php if(!$shortHeader){?>
	<div id="ap-header">
		<div id="ap-logo">
			<!--begin-->
			<?php
				if(file_exists($logoFile)) { ?>
					<a href="<?php echo $url;?>administrator"><img src="<?php echo $logoFile;?>" /></a>
				<?php } else { ?>
					<a href="<?php echo $url;?>administrator"><?php echo $mainframe->getCfg( 'sitename' ) . " " . JText::_( 'ADMIN' );?> </a>
				<?php }?>
			<!--end-->
		</div>		
		<div id="ap-topleft" class="left">
			<!--begin-->
			<ul>
				<li><a href="<?php echo JURI::root(); ?>" target="_blank"><?php echo JText::_( 'PREVIEW SITE' ); ?></a></li>
				<li class="last"><a href="<?php echo JURI::root(); ?>?tp=1" target="_blank"><?php echo JText::_( 'POSITIONS' ); ?></a></li>
			</ul>
			<!--end-->		
		</div>
		<div id="ap-topright" class="right">
			<!--begin-->
			
			<ul>
				<li><?php echo $profilelink; ?></li>
				<li class="last"><a href="index.php?option=com_login&task=logout"><?php echo JText::_( 'LOGOUT' );?></a></li>
			</ul>
			<!--end-->
		</div>
		<div class="clear"></div>
	</div>
	<?php } ?>
<div class="clear"></div>
<jdoc:include type="module" name="mod_sessionbar" />
<div id="ap-mainmenu">
	<!--begin-->
	<ul>
		<li class="home-item parent <?php if ($option == "com_cpanel" && $ap_task_set != "list_components") { echo "active";}?>"><a href="<?php echo $url;?>administrator" class="home-link"><?php echo JText::_( 'DASHBOARD' ); ?><span></span></a>	
			<ul class="submenu">
				<li><a href="index.php?option=com_login&task=logout"><?php echo JText::_( 'LOGOUT' );?></a>
				</li>
			</ul>
		</li>
		<?php if (($user->get('gid') >= $menusAcl) && $menusAcl != 0) { ?>
		<li class="parent <?php if ($option =="com_menus") { echo "active"; } ?>"><a href="index.php?option=com_menus"><?php echo JText::_( 'MENUS' ); ?><span></span></a>
		<?php require('assets/menus'.DS.'menus.php'); ?>
		</li>
		<?php } ?>
		<?php if (($user->get('gid') >= $sectionsAcl) && $sectionsAcl != 0) { ?>
		<li <?php if ($option =="com_sections") { echo "class=\"active\""; } ?>><a href="index.php?option=com_sections&scope=content"><?php echo JText::_( 'SECTIONS' );?></a></li><?php } ?>
		<?php if (($user->get('gid') >= $categoriesAcl) && $categoriesAcl != 0) { ?><li class="<?php if ($option =="com_categories" && $scope="content") { echo "active"; } ?>"><a href="index.php?option=com_categories&scope=content"><?php echo JText::_( 'CATEGORIES' );?></a></li><?php } ?>
		<?php if (($user->get('gid') >= $articlesAcl) && $articlesAcl != 0) { ?>
		<li class="parent <?php if ($option =="com_content" || $option == "com_sections" && $sectionsAcl == 0 || ($option =="com_categories" && $scope =="content") && $categoriesAcl == 0 || $option =="com_frontpage") { echo "active"; } ?>">
		<a href="index.php?option=com_content"><?php echo JText::_( 'ARTICLES' );?><span></span></a>
		<ul class="submenu">
			<li><a href="index.php?option=com_content"><?php echo JText::_( 'ARTICLES' );?></a>
				<ul>
					<li><a href="index.php?option=com_content&task=add"><?php echo JText::_( 'NEW ARTICLE' );?></a></li>
				</ul>
			</li>
			<li><a href="index.php?option=com_sections&scope=content"><?php echo JText::_( 'SECTIONS' );?></a>
				<ul>
					<li><a href="index.php?option=com_sections&scope=content&task=add"><?php echo JText::_( 'NEW SECTION' );?></a></li>
				</ul>
			</li>
			<li><a href="index.php?option=com_categories&scope=content"><?php echo JText::_( 'CATEGORIES' );?></a>
				<ul>
					<li><a href="index.php?option=com_categories&scope=content&task=add"><?php echo JText::_( 'NEW CATEGORY' );?></a></li>
				</ul>
			</li>
			<li><a href="index.php?option=com_frontpage"><?php echo JText::_( 'FRONTPAGE' );?></a></li>
		</ul>
		</li>
		<?php } ?>
		<?php if (($user->get('gid') >= $flexicontentAcl) && $flexicontentAcl != 0) { ?>
		<li class="parent <?php if ($option =="com_flexicontent") { echo "active"; } ?>"><a href="index.php?option=com_flexicontent">
		<?php echo JText::_( 'CONTENT' );?><span></span></a>
		<ul class="submenu">
			<li><a href="index.php?option=com_flexicontent&view=items"><?php echo JText::_( 'ITEMS' );?></a></li>
			<li><a href="index.php?option=com_flexicontent&view=types"><?php echo JText::_( 'TYPES' );?></a></li>
			<li><a href="index.php?option=com_flexicontent&view=categories"><?php echo JText::_( 'CATEGORIES' );?></a></li>
			<li><a href="index.php?option=com_flexicontent&view=fields"><?php echo JText::_( 'FIELDS' );?></a></li>
			<li><a href="index.php?option=com_flexicontent&view=tags"><?php echo JText::_( 'TAGS' );?></a></li>
			<li><a href="index.php?option=com_flexicontent&view=archive"><?php echo JText::_( 'ARCHIVE' );?></a></li>
			<li><a href="index.php?option=com_flexicontent&view=filemanager"><?php echo JText::_( 'FILES' );?></a></li>
			<li><a href="index.php?option=com_flexicontent&view=templates"><?php echo JText::_( 'TEMPLATES' );?></a></li>
			<li><a href="index.php?option=com_flexicontent&view=stats"><?php echo JText::_( 'STATISTICS' );?></a></li>
		</ul>
		</li>
		<?php } ?>
		<?php if (($user->get('gid') >= $k2Acl) && $k2Acl != 0) { ?>
		<li class="parent <?php if ($option =="com_k2") { echo "active"; } ?>"><a href="index.php?option=com_k2">
		<?php echo JText::_( 'CONTENT' );?><span></span></a>
		<ul class="submenu">
			<li><a href="index.php?option=com_k2&view=item"><?php echo JText::_( 'ADD NEW ITEM' );?></a></li>
			<li><a href="index.php?option=com_k2&view=items&filter_trash=0"><?php echo JText::_( 'ITEMS' );?></a></li>
			<li><a href="index.php?option=com_k2&view=items&filter_featured=1"><?php echo JText::_( 'FEATURED ITEMS' );?></a></li>
			<li><a href="index.php?option=com_k2&view=items&filter_trash=1"><?php echo JText::_( 'TRASHED ITEMS' );?></a></li>
			<li><a href="index.php?option=com_k2&view=categories&filter_trash=0"><?php echo JText::_( 'CATEGORIES' );?></a></li>
			<li><a href="index.php?option=com_k2&view=categories&filter_trash=1"><?php echo JText::_( 'TRASHED CATEGORIES' );?></a></li>
			<li><a href="index.php?option=com_k2&view=tags"><?php echo JText::_( 'TAGS' );?></a></li>
			<li><a href="index.php?option=com_k2&view=comments"><?php echo JText::_( 'COMMENTS' );?></a></li>
			<li><a href="index.php?option=com_k2&view=extraFields"><?php echo JText::_( 'EXTRA FIELDS' );?></a></li>
			<li><a href="index.php?option=com_k2&view=extraFieldsGroups"><?php echo JText::_( 'EXTRA FIELD GROUPS' );?></a></li>
		</ul>
		</li>
		<?php } ?>
		<?php if (($user->get('gid') >= $sobi2Acl) && $sobi2Acl != 0) { ?>
		<li class="parent <?php if ($option =="com_sobi2") { echo "active"; } ?>"><a href="index.php?option=com_sobi2">
		<?php echo JText::_( 'DIRECTORY' );?><span></span></a>
		<ul class="submenu">
			<li><a href="index.php?option=com_sobi2&task=listing&catid=-1"><?php echo JText::_( 'ALL ENTRIES' );?></a></li>
			<li><a href="index.php?option=com_sobi2&task=getUnapproved"><?php echo JText::_( 'ENTRIES AWAITING APPROVAL' );?></a></li>
			<li><a href="index.php?option=com_sobi2&task=genConf"><?php echo JText::_( 'GENERAL CONFIGURATION' );?></a></li>
			<li><a href="index.php?option=com_sobi2&task=editFields"><?php echo JText::_( 'CUSTOM FIELDS MANAGER' );?></a></li>
			<li><a href="index2.php?option=com_sobi2&task=addItem&returnTask="><?php echo JText::_( 'ADD ENTRY' );?></a></li>
			<li><a href="index2.php?option=com_sobi2&task=addCat&returnTask="><?php echo JText::_( 'ADD CATEGORY' );?></a></li>
			<li><a href="index2.php?option=com_sobi2&task=templates"><?php echo JText::_( 'TEMPLATE MANAGER' );?></a></li>
			<li><a href="index2.php?option=com_sobi2&task=pluginsManager"><?php echo JText::_( 'PLUGIN MANAGER' );?></a></li>
		</ul>
		</li>
		<?php } ?>
		<?php if (($user->get('gid') >= $virtuemartAcl) && $virtuemartAcl != 0) { ?>
		<li class="parent <?php if ($option =="com_virtuemart") { echo "active"; } ?>"><a href="index.php?option=com_virtuemart">
		<?php echo JText::_( 'SHOP' );?><span></span></a>
		<ul class="submenu">
			<li><a href="index.php?pshop_mode=admin&page=product.product_list&option=com_virtuemart"><?php echo JText::_( 'PRODUCT LIST' );?></a></li>
			<li><a href="index.php?pshop_mode=admin&page=product.product_category_list&option=com_virtuemart"><?php echo JText::_( 'CATEGORY TREE' );?></a></li>
			<li><a href="index.php?pshop_mode=admin&page=order.order_list&option=com_virtuemart"><?php echo JText::_( 'ORDERS' );?></a></li>
			<li><a href="index.php?pshop_mode=admin&page=store.payment_method_list&option=com_virtuemart"><?php echo JText::_( 'PAYMENT METHODS' );?></a></li>
			<li><a href="index.php?pshop_mode=admin&page=vendor.vendor_list&option=com_virtuemart"><?php echo JText::_( 'VENDORS' );?></a></li>
			<li><a href="index.php?pshop_mode=admin&page=admin.user_list&option=com_virtuemart"><?php echo JText::_( 'USERS' );?></a></li>
			<li><a href="index.php?pshop_mode=admin&page=admin.show_cfg&option=com_virtuemart"><?php echo JText::_( 'CONFIGURATION' );?></a></li>
			<li><a href="index.php?pshop_mode=admin&page=store.store_form&option=com_virtuemart"><?php echo JText::_( 'EDIT STORE' );?></a></li>
		</ul>
		</li>
		<?php } ?>
		<?php if (($user->get('gid') >= $tiendaAcl) && $tiendaAcl != 0) { ?>
		<li class="parent <?php if ($option =="com_tienda") { echo "active"; } ?>"><a href="index.php?option=com_tienda">
		<?php echo JText::_( 'SHOP' );?><span></span></a>
		<ul class="submenu">
			<li><a href="index.php?option=com_tienda&view=products"><?php echo JText::_( 'PRODUCTS' );?></a></li>
			<li><a href="index.php?option=com_tienda&view=categories"><?php echo JText::_( 'CATEGORIES' );?></a></li>
			<li><a href="index.php?option=com_tienda&view=orders"><?php echo JText::_( 'ORDERS' );?></a></li>
			<li><a href="index.php?pshop_mode=admin&page=store.payment_method_list&option=com_virtuemart"><?php echo JText::_( 'PAYMENT METHODS' );?></a></li>
			<li><a href="index.php?option=com_tienda&view=manufacturers"><?php echo JText::_( 'MANUFACTURERS' );?></a></li>
			<li><a href="index.php?option=com_tienda&view=users"><?php echo JText::_( 'USERS' );?></a></li>
			<li><a href="index.php?option=com_tienda&view=localization"><?php echo JText::_( 'LOCALIZATION' );?></a></li>
			<li><a href="index.php?option=com_tienda&view=reports"><?php echo JText::_( 'REPORTS' );?></a></li>
			<li><a href="index.php?option=com_tienda&view=config"><?php echo JText::_( 'CONFIGURATION' );?></a></li>
			
		</ul>
		</li>
		<?php } ?>
		<?php if (($user->get('gid') >= $projectforkAcl) && $projectforkAcl != 0) { ?>
		<li class="parent <?php if ($option =="com_projectfork") { echo "active"; } ?>"><a href="index.php?option=com_projectfork">
		<?php echo JText::_( 'PROJECTS' );?><span></span></a>
		<ul class="submenu">
			<li><a href="index.php?option=com_projectfork&amp;section=controlpanel">Control Panel</a></li>
			<li><a href="index.php?option=com_projectfork&amp;section=projects">Projects</span></a></li>
			<li><a href="index.php?option=com_projectfork&amp;section=tasks">Tasks</a></li>
			<li><a href="index.php?option=com_projectfork&amp;section=time">Time</a></li>
			<li><a href="index.php?option=com_projectfork&amp;section=filemanager">Files</a></li>
			<li><a href="index.php?option=com_projectfork&amp;section=calendar">Calendar</a></li>
			<li><a href="index.php?option=com_projectfork&amp;section=board">Messages</a></li>
			<li><a href="index.php?option=com_projectfork&amp;section=profile">Profile</a></li>
			<li><a href="index.php?option=com_projectfork&amp;section=users">Users</a></li>
			<li><a href="index.php?option=com_projectfork&amp;section=groups">Groups</a></li>
			<li><a href="index.php?option=com_projectfork&amp;section=config">Config</a></li>
		</ul>	
		</li>
		<?php } ?>
		<?php if (($user->get('gid') >= $componentsAcl) && $componentsAcl != 0) { ?>
		<li class="parent <?php if ($ap_task =="list_components") { echo "active"; } ?>"><a href="index.php?ap_task=list_components">
		<?php echo JText::_( 'COMPONENTS' );?><span></span></a>
		<?php require('assets/components'.DS.'components.php');?>
		</li>
		<?php } ?>
		<?php if (($user->get('gid') >= $modulesAcl) && $modulesAcl != 0) { ?>
		<li class="parent <?php if ($option =="com_modules"){ echo "active";}?>"><a href="index.php?option=com_modules"><?php echo JText::_( 'MODULES' );?><span></span></a>
		<ul class="submenu">
			<li><a href="index.php?option=com_modules"><?php echo JText::_( 'SITE MODULES' );?></a>
				<ul>
					<li><a href="index.php?option=com_modules&task=add"><?php echo JText::_( 'NEW MODULE' );?></a></li>
				</ul>
			</li>
			<li><a href="index.php?option=com_modules&client=1"><?php echo JText::_( 'ADMIN MODULES' );?></a>
				<ul>
					<li><a href="index.php?option=com_modules&client=1&task=add"><?php echo JText::_( 'NEW ADMIN MODULE' );?></a></li>
				</ul>
			</li>
			<li><a href="index.php?option=com_installer&task=manage&type=modules"><?php echo JText::_( 'MANAGE MODULES' );?></a></li>
			<li><a href="index.php?option=com_installer"><?php echo JText::_( 'INSTALL MODULES' );?></a></li>
		</ul>
		</li>
		<?php } ?>
		<?php if (($user->get('gid') >= $pluginsAcl) && $pluginsAcl != 0) { ?>
		<li class="parent <?php if ($option =="com_plugins"){ echo "active";}?>"><a href="index.php?option=com_plugins"><?php echo JText::_( 'PLUGINS' );?><span></span></a>
		<ul class="submenu">
			<li><a href="index.php?option=com_installer&task=manage&type=plugins"><?php echo JText::_( 'MANAGE PLUGINS' );?></a></li>
			<li><a href="index.php?option=com_installer"><?php echo JText::_( 'INSTALL PLUGINS' );?></a></li>
		</ul>
		</li><?php } ?>
		<?php if (($user->get('gid') >= $installAcl) && $installAcl != 0) { ?>
		<li class="parent <?php if ($option =="com_installer"){ echo "active";}?>"><a href="index.php?option=com_installer"><?php echo JText::_( 'INSTALLER' );?><span></span></a>
		<ul class="submenu">
			<li><a href="index.php?option=com_installer&task=manage&type=components"><?php echo JText::_( 'MANAGE COMPONENTS' );?></a></li>
			<li><a href="index.php?option=com_installer&task=manage&type=modules"><?php echo JText::_( 'MANAGE MODULES' );?></a></li>
			<li><a href="index.php?option=com_installer&task=manage&type=plugins"><?php echo JText::_( 'MANAGE PLUGINS' );?></a></li>
			<li><a href="index.php?option=com_installer&task=manage&type=languages"><?php echo JText::_( 'MANAGE LANGUAGES' );?></a></li>
			<li><a href="index.php?option=com_installer&task=manage&type=templates"><?php echo JText::_( 'MANAGE TEMPLATES' );?></a></li>
			
		</ul>
		</li><?php } ?>
		<?php
        for($x = 0; $x < 6; $x++)
        {
            $custom_main_acl  = $this->params->get('custom'.$x.'Acl', 0);
            $custom_main_name = $this->params->get('custom'.$x.'Name');
            $custom_main_link = $this->params->get('custom'.$x.'Link');
            if ($user->get('gid') >= $custom_main_acl && $custom_main_acl != 0) { ?>
                <li><a href="<?php echo $custom_main_link;?>"><?php echo htmlspecialchars($custom_main_name);?></a></li>
            <?php }
        }
        ?>
        <?php if (($user->get('gid') >= $usersAcl) && $usersAcl != 0) {?>
        <li class="parent <?php if ($option =="com_users") { echo "active"; } ?>"><a href="index.php?option=com_users"><?php echo JText::_( 'USERS' );?><span></span></a>
        <ul class="submenu">
        <!--Doesn't allow password to be filled
        	<li><a href="index.php?option=com_users&task=add"><?php echo JText::_( 'NEW USER' );?></a></li>
        -->
        	<li><a href="index.php?option=com_users&filter_logged=1"><?php echo JText::_( 'LOGGED IN USERS' );?></a></li>
        </ul>
        </li><?php } ?>
        <?php if (($user->get('gid') >= $templatesAcl) && $templatesAcl != 0) {?>
        <li class="parent <?php if ($option =="com_templates") { echo "active"; } ?>"><a href="index.php?option=com_templates"><?php echo JText::_( 'TEMPLATES' );?><span></span></a>
        <ul class="submenu">
        	<li><a href="index.php?option=com_templates"><?php echo JText::_( 'SITE TEMPLATES' );?></a></li>
        	<li><a href="index.php?option=com_templates&client=1"><?php echo JText::_( 'ADMIN TEMPLATES' );?></a></li>
        	<li><a href="index.php?option=com_installer&task=manage&type=templates"><?php echo JText::_( 'MANAGE TEMPLATES' );?></a></li>
        	<li><a href="index.php?option=com_installer"><?php echo JText::_( 'INSTALL TEMPLATES' );?></a></li>
        </ul>
        </li><?php } ?>
        <?php if (($user->get('gid') >= $adminAcl) && $adminAcl != 0) { ?>
        <li class="admin-item parent <?php if ($ap_task =="admin") { echo "active";}?>"><a href="index.php?ap_task=admin" class="admin-link"><?php echo JText::_( 'ADMIN' );?><span></span></a>
        <ul class="submenu">
       		<li><a href="index.php?option=com_templates&task=edit&cid[]=stainless&client=1"><?php echo JText::_( 'ADMIN OPTIONS' );?></a></li>
        	<li><a href="index.php?option=com_config"><?php echo JText::_( 'GLOBAL CONFIG' );?></a></li>
        	<li><a href="index.php?option=com_admin&task=sysinfo"><?php echo JText::_( 'SYSTEM INFO' );?></a></li>
        	<li><a href="index.php?option=com_checkin"><?php echo JText::_( 'CHECKIN' );?></a></li>
        	<li><a href="index.php?option=com_cache"><?php echo JText::_( 'CACHE' );?></a></li>
        	<li><a href="index.php?option=com_templates&client=1"><?php echo JText::_( 'ADMIN TEMPLATES' );?></a></li>
        	<li><a href="index.php?option=com_modules&client=1"><?php echo JText::_( 'ADMIN MODULES' );?></a></li>
        	<li><a href="index.php?option=com_login&task=logout"><?php echo JText::_( 'LOGOUT' );?></a></li>
        </ul>
        </li><?php } ?>
	</ul>
	<!--end-->
	<div class="clear"></div>
</div>


	<div id="ap-submenu">
		<?php if (!JRequest::getInt('hidemainmenu')) { ?>		
		<jdoc:include type="modules" name="submenu" id="submenu-box" />
		<?php } ?>
		<?php if ($option == "com_content" || $option == "com_sections" || ($option == "com_categories" && $scope =="content") || $option =="com_frontpage"){ ?>
		<ul class="submenu">
			<li><a href="index.php?option=com_sections&scope=content"><?php echo JText::_( 'SECTIONS' );?></a></li>
			<li><a href="index.php?option=com_categories&scope=content"><?php echo JText::_( 'CATEGORIES' );?></a></li>
			<li><a href="index.php?option=com_frontpage"><?php echo JText::_( 'FRONTPAGE' );?></a></li>
		</ul>
		<?php } ?>
		<?php if ($option =="com_menus") { ?>
			<?php require('assets/menus'.DS.'menus.php');?>
		<?php } else if ($option =="com_templates") { ?>
		<ul class="submenu">
			<li><a href="index.php?option=com_templates&task=edit&cid[]=stainless&client=1"><?php echo JText::_( 'ADMIN TEMPLATE PARAMS' );?></a></li>
			<li><a href="index.php?option=com_installer&task=manage&type=templates"><?php echo JText::_( 'MANAGE TEMPLATES' );?></a></li>
			<li><a href="index.php?option=com_installer"><?php echo JText::_( 'INSTALL TEMPLATES' );?></a></li>
		</ul>	
		<?php } else if ($option =="com_modules") { ?>
		<ul class="submenu">
			<li><a href="index.php?option=com_installer&task=manage&type=modules"><?php echo JText::_( 'MANAGE MODULES' );?></a></li>
			<li><a href="index.php?option=com_installer"><?php echo JText::_( 'INSTALL MODULES' );?></a></li>
		</ul>	
		<?php } else if ($option =="com_plugins") { ?>
		<ul class="submenu">
			<li><a href="index.php?option=com_installer&task=manage&type=plugins"><?php echo JText::_( 'MANAGE PLUGINS' );?></a></li>
			<li><a href="index.php?option=com_installer"><?php echo JText::_( 'INSTALL PLUGINS' );?></a></li>
		</ul>	
		<?php } else if ($ap_task == "list_components") { ?>
		<ul class="submenu">
			<li><a href="index.php?option=com_installer&task=manage&type=components"><?php echo JText::_( 'MANAGE COMPONENTS' );?></a></li>
			<li><a href="index.php?option=com_installer"><?php echo JText::_( 'INSTALL COMPONENTS' );?></a></li>
		</ul>			
		<?php } else if ($option =="com_users") { ?>
		<ul class="submenu">
			<li><a href="index.php?option=com_users&filter_logged=1"><?php echo JText::_( 'LOGGED IN USERS' );?></a></li>
		</ul>	
		<?php } else if (($ap_task == "admin") && ($user->get('gid') > 24) || ($option =="com_cpanel") && ($ap_task_set !="list_components") && ($user->get('gid') > 24)) { ?>
		<ul class="submenu">
			<li><a href="index.php?option=com_config"><?php echo JText::_( 'GLOBALS' );?></a></li>
			<li><a href="index.php?option=com_admin&task=sysinfo"><?php echo JText::_( 'SYSTEM INFO' );?></a></li>
			<li><a href="index.php?option=com_templates&client=1"><?php echo JText::_( 'ADMIN TEMPLATES' );?></a></li>
			<li><a href="index.php?option=com_templates&task=edit&cid[]=stainless&client=1"><?php echo JText::_( 'ADMIN TEMPLATE PARAMS' );?></a></li>
			<li><a href="index.php?option=com_modules&client=1"><?php echo JText::_( 'ADMIN MODULES' );?></a></li>
			<li><a href="index.php?option=com_checkin"><?php echo JText::_( 'CHECKIN' );?></a></li>
			<li><a href="index.php?option=com_cache"><?php echo JText::_( 'CACHE' );?></a></li>
			<li><a href="index.php?option=com_plugins"><?php echo JText::_( 'PLUGINS' );?></a></li>
			<li><a href="index.php?option=com_installer"><?php echo JText::_( 'INSTALLER' );?></a></li>
		</ul>
		<?php } else if ($option =="com_projectfork") { ?>
		<ul class="submenu">
			<li class="pf_button_controlpanel"><a href="index.php?option=com_projectfork&amp;section=controlpanel"><span>Control&nbsp;Panel</span></a></li>
			<li class="pf_button_projects"><a href="index.php?option=com_projectfork&amp;section=projects"><span>Projects</span></a></li>
			<li class="pf_button_tasks"><a href="index.php?option=com_projectfork&amp;section=tasks"><span>Tasks</span></a></li>
			<li class="pf_button_time"><a href="index.php?option=com_projectfork&amp;section=time"><span>Time</span></a></li>
			<li class="pf_button_filemanager"><a href="index.php?option=com_projectfork&amp;section=filemanager"><span>Files</span></a></li>
			<li class="pf_button_calendar"><a href="index.php?option=com_projectfork&amp;section=calendar"><span>Calendar</span></a></li>
			<li class="pf_button_board"><a href="index.php?option=com_projectfork&amp;section=board"><span>Messages</span></a></li>
			<li class="pf_button_profile"><a href="index.php?option=com_projectfork&amp;section=profile"><span>Profile</span></a></li>
			<li class="pf_button_users"><a href="index.php?option=com_projectfork&amp;section=users"><span>Users</span></a></li>
			<li class="pf_button_groups"><a href="index.php?option=com_projectfork&amp;section=groups"><span>Groups</span></a></li>
			<li class="pf_button_config"><a href="index.php?option=com_projectfork&amp;section=config"><span>Config</span></a></li>
		</ul>	
		<?php }	?>

		<div class="clear"></div>
	</div>
	<div class="clear"></div>
</div>
	<div id="<?php if($steelActive){?>pf-margintop<?php } else { ?>ap-mainbody<?php } ?>" <?php if($this->countModules('status') != 0) { ?>class="ap-tall"<?php } ?>>
	<div id="ap-myeditor" class="dr20">
	<jdoc:include type="module" name="mod_myeditor" />
	</div>
	<?php if(!$steelActive){?>
	<div id="ap-title" class="mr20 fluid">

		<?php
		// Get the component title div
		$title = $mainframe->get('JComponentTitle');
		// Create component title
		if ($ap_task == "list_components"){
		$title = "<div class=\"header\">" . JText::_( 'COMPONENTS' ) . "</div>";
		} else if ($ap_task == "admin"){
		$title = "<div class=\"header\">" . JText::_( 'ADMINISTRATION' ) . "</div>";
		} 
		// Echo title if it exists
		if ($title) {
			echo $title;
		} else {
		  echo "<div class=\"header\">" .$mainframe->getCfg( 'sitename' ). "</div>";
		}
		?>
		
				<?php if($showQuickAdd) { ?>
				<div id="ap-quicklink">
					<div id="ap-quickadd">
						<form id="add_form" name="add_form" action="index.php" method="get">
							<select onchange="location 
		= document.add_form.add_select.options [document.add_form.add_select.selectedIndex].value;" name="add_select" id="filter_menutype">
								<option> - <?php echo JText::_( 'QUICK ADD' );?> - </option>
								<option value="index.php?option=com_content&task=add"><?php echo JText::_( 'NEW ARTICLE' );?></option>
								<option value="index.php?option=com_sections&scope=content&task=add"><?php echo JText::_( 'NEW SECTION' );?></option>
								<option value="index.php?option=com_categories&scope=content&task=add"><?php echo JText::_( 'NEW CATEGORY' );?></option>
								<?php if($user->get('gid') > 23){?>
								<option value="index.php?option=com_menus&task=addMenu"><?php echo JText::_( 'NEW MENU' );?></option>
								<option value="index.php?option=com_modules&task=add"><?php echo JText::_( 'NEW MODULE' );?></option>
								<option value="index.php?option=com_users&task=add"><?php echo JText::_( 'NEW USER' );?></option>
								<option value="index.php?option=com_installer"><?php echo JText::_( 'NEW EXTENSION' );?></option>
								<?php } ?>
							</select>
						</form>
					</div>
				</div>
				<?php } ?>
		<div class="clear"></div>
	</div>
	<?php } ?>
		<div class="clear"></div>
		<?php // if(($task !="edit") && ($task !="add") && ($showSidebar)){ ?>
		<?php if($showSidebar){ ?>
		<div id="ap-sidebar" class="<?php if($switchSidebar){?>dl20<?php } else { ?>dr20<?php } ?>">
			
			<!--begin-->
			<jdoc:include type="module" name="mod_ualog" />
			<jdoc:include type="modules" name="apside" style="xhtml" />
			<!--<div class="pane-sliders">-->
			<!--
				<div class="panel">
					<h3 class="jpane-toggler"><?php echo JText::_( 'TOOLBAR' );?></h3>
					<div class="jpane-slider">
						<jdoc:include type="modules" name="toolbar" />
					</div>
				</div>
			-->
				<?php if($showComponentList) { ?>
				<div class="panel">
					<h3><?php echo JText::_( 'COMPONENTS' );?></h3>
					<div>
					<?php if($ap_task != "list_components" && ($user->get('gid') >= $componentsAcl) && $componentsAcl != 0) {?>
					
					<?php require('assets/components'.DS.'components.php');?>
					<?php } ?>
							
					</div>
				</div>
				<?php } ?>
				
				<?php if (($user->get('gid') >= $flexicontentAcl) && $flexicontentAcl != 0) { ?>
				<div class="panel">
					<h3><span><?php echo JText::_( 'CONTENT' );?></span></h3>
					<div>
						<ul class="submenu">
							<li><a href="index.php?option=com_flexicontent&view=items"><?php echo JText::_( 'ITEMS' );?></a></li>
							<li><a href="index.php?option=com_flexicontent&view=types"><?php echo JText::_( 'TYPES' );?></a></li>
							<li><a href="index.php?option=com_flexicontent&view=categories"><?php echo JText::_( 'CATEGORIES' );?></a></li>
							<li><a href="index.php?option=com_flexicontent&view=fields"><?php echo JText::_( 'FIELDS' );?></a></li>
							<li><a href="index.php?option=com_flexicontent&view=tags"><?php echo JText::_( 'TAGS' );?></a></li>
							<li><a href="index.php?option=com_flexicontent&view=archive"><?php echo JText::_( 'ARCHIVE' );?></a></li>
							<li><a href="index.php?option=com_flexicontent&view=filemanager"><?php echo JText::_( 'FILES' );?></a></li>
							<li><a href="index.php?option=com_flexicontent&view=templates"><?php echo JText::_( 'TEMPLATES' );?></a></li>
							<li><a href="index.php?option=com_flexicontent&view=stats"><?php echo JText::_( 'STATISTICS' );?></a></li>
						</ul>
					</div>
				</div>
				<?php } ?>

				<?php if ($option =="com_content"){?>
				<div class="panel">
					<h3 class="jpane-toggler"><?php echo JText::_( 'LATEST' );?></h3>
					<div class="jpane-slider">
						<jdoc:include type="module" name="mod_latest" />
					</div> 
				</div>
				<div class="panel"> 
					<h3 class="jpane-toggler"><?php echo JText::_( 'POPULAR' );?></h3>
					<div class="jpane-slider">
						<jdoc:include type="module" name="mod_popular" />
					</div>
				</div>
				<?php } ?>
			<!--</div>-->
		</div>
		<?php } ?>	
		<div id="<?php if($steelActive){?>pf-apcontent<?php } else { ?>ap-content<?php } ?>" class="<?php if(($switchSidebar) && ($showSidebar)){?>ml20<?php } else if($showSidebar) { ?>mr20<?php } ?> <?php if($showSidebar){?>fluid<?php } ?>">	
			<div id="ap-content-inner">	
			<?php if ($option != "com_cpanel"){?>
			<jdoc:include type="modules" name="toolbar" />
			<?php } ?>
			<jdoc:include type="modules" name="aptop" />
			<?php if ($option =="com_cpanel" && !$ap_task_set){?>
			<jdoc:include type="modules" name="icon" /><jdoc:include type="modules" name="cpanel" style="xhtml" />
			<?php } else if ($option =="com_cpanel" && !$ap_task_set && $user->get('gid') > 24){?>
			<jdoc:include type="modules" name="apsuperadmin" />
			<?php } else if($ap_task == "list_components" && ($user->get('gid') >= $componentsAcl) && $componentsAcl != 0) {?>
			<?php require('assets/components'.DS.'components.php');?>
			<?php } else if($ap_task == "admin") {?>
			<jdoc:include type="modules" name="apadmin" /><jdoc:include type="module" name="mod_menu" />
			<?php } else if ($option !="com_cpanel" && !$ap_task_set){?><jdoc:include type="component" /><?php } ?>
			<jdoc:include type="modules" name="apbottom" />
			<?php if(($showBreadCrumbs) && ($pfTheme != "steel")) { ?>
			<div id="ap-crumbs">
			<!--Begin Crumbs-->
			<?php
				require_once('html'.DS.'mod_breadcrumbs'.DS.'mod_breadcrumbs.php');
				breadcrumbs(); 
			?>
			<!--End Crumbs-->
			</div>
			<?php } ?>
			<div class="clear"></div>
		</div>
		<div class="clear"></div>
		</div>
	</div>
	<?php if(($showFootMods) && (!$steelActive)) { ?>
	<div id="ap-footmods">
		<table>
			<tbody>
				<tr>
					<td>
						<div class="ap-foot1">
						<?php if($this->countModules('apfoot1')) { ?>
						<jdoc:include type="modules" name="apfoot1" />
						<?php } else { ?>
							<h3><?php echo JText::_( 'CREATE NEW' );?></h3>
							<ul>
								<li><a href="index.php?option=com_content&task=add"><?php echo JText::_( 'NEW ARTICLE' );?></a></li>
								<li><a href="index.php?option=com_modules&task=add"><?php echo JText::_( 'NEW MODULE' );?></a></li>
								<li><a href="index.php?option=com_sections&scope=content&task=add"><?php echo JText::_( 'NEW SECTION' );?></a></li>
								<li><a href="index.php?option=com_categories&scope=content&task=add"><?php echo JText::_( 'NEW CATEGORY' );?></a></li>
							</ul>
						<?php } ?>
						</div>
					</td>
					<td>
					<div class="ap-foot2">
					<?php if($this->countModules('apfoot2')) { ?>
					<jdoc:include type="modules" name="apfoot2" />
					<?php } else { ?>
						<h3><?php echo JText::_( 'MANAGERS' );?></h3>
						<ul>
							<li><a href="index.php?option=com_content"><?php echo JText::_( 'ARTICLE MANAGER' );?></a></li>
							<li><a href="index.php?option=com_sections&scope=content"><?php echo JText::_( 'SECTION MANAGER' );?></a></li>
							<li><a href="index.php?option=com_categories&scope=content"><?php echo JText::_( 'CATEGORY MANAGER' );?></a></li>
							<li><a href="index.php?option=com_users"><?php echo JText::_( 'USER MANAGER' );?></a></li>
						</ul>	
					<?php } ?>	
					</div>
					</td>
					<td>
					<div class="ap-foot3">
					<?php if($this->countModules('apfoot3')) { ?>
					<jdoc:include type="modules" name="apfoot3" />
					<?php } else { ?>
						<h3><?php echo JText::_( 'EXTEND' );?></h3>
						<ul>
							<li><a href="index.php?option=com_installer"><?php echo JText::_( 'INSTALL/UNINSTALL' );?></a></li>
							<li><a href="index.php?option=com_installer&task=manage&type=components"><?php echo JText::_( 'MANAGE COMPONENTS' );?></a></li>
							<li><a href="index.php?option=com_installer&task=manage&type=modules"><?php echo JText::_( 'MANAGE MODULES' );?></a></li>
							<li><a href="index.php?option=com_installer&task=manage&type=plugins"><?php echo JText::_( 'MANAGE PLUGINS' );?></a></li>
						</ul>		
					<?php } ?>
					</div>
					</td>
					<td>
					<div class="ap-foot4">
					<?php if($this->countModules('apfoot4')) { ?>
					<jdoc:include type="modules" name="apfoot4" />
					<?php } else { ?>
						<h3><?php echo JText::_( 'ADMIN TOOLS' );?></h3>
						<ul>
							<li><a href="index.php?option=com_config"><?php echo JText::_( 'GLOBAL CONFIGURATION' );?></a></li>
							<li><a href="index.php?option=com_admin&task=sysinfo"><?php echo JText::_( 'SYSTEM INFORMATION' );?></a></li>
							<li><a href="index.php?option=com_checkin"><?php echo JText::_( 'GLOBAL CHECKIN' );?></a></li>
							<li><a href="index.php?option=com_cache"><?php echo JText::_( 'CACHE MANAGER' );?></a></li>
						</ul>		
					<?php } ?>
					</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<?php } ?>
	<div class="clear"></div>
	<?php if(!$steelActive){?>
	<div id="ap-footer">
		<jdoc:include type="modules" name="apfooter" />
		<!--begin-->
		<span id="ap-copyright">
		<a target="_blank" href="http://www.adminpraise.com/joomla/admin-templates.php">Joomla! Admin Templates</a>
		&amp; <a target="_blank" href="http://www.adminpraise.com/joomla/admin-extensions.php">Extensions</a>
		by <a target="_blank" href="http://www.adminpraise.com/" class="ap-footlogo">AdminPraise</a>.
		</span>
		<span id="ap-version">
		<a target="_blank" href="http://www.joomla.org">Joomla!</a> 
		<?php 
		if(($user->usertype) == "Super Administrator") {
		echo "<span class=\"version\">" . JText::_('Version') . " " . JVERSION . "</span> ";
		}
		?>
		</span>
		<!--end-->
		<div class="clear">&nbsp;</div>
	</div>
	<?php } ?>
</div>
<?php echo $scriptbuffer?>
<script type="text/javascript" src="templates/<?php echo  $this->template ?>/js/stainless.js"></script>

<div id="hiddenDiv"><jdoc:include type="message" />
</div>

<div id="ap-toolbar">
	<div class="rsfinder">
	<jdoc:include type="module" name="mod_rsfinder" />
	</div>
	<jdoc:include type="modules" name="toolbar" />
</div>

</body>
</html>