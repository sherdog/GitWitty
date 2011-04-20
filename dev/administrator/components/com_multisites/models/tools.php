<?php
/**
 * @file       tools.php
 * @version    1.2.34
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2009 Edwin2Win sprlu - all right reserved.
 * @license    This program is free software; you can redistribute it and/or
 *             modify it under the terms of the GNU General Public License
 *             as published by the Free Software Foundation; either version 2
 *             of the License, or (at your option) any later version.
 *             This program is distributed in the hope that it will be useful,
 *             but WITHOUT ANY WARRANTY; without even the implied warranty of
 *             MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU General Public License for more details.
 *             You should have received a copy of the GNU General Public License
 *             along with this program; if not, write to the Free Software
 *             Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *             A full text version of the GNU GPL version 2 can be found in the LICENSE.php file.
 * @par History:
 * - V1.2.0 23-MAY-2009: Initial version
 * - V1.2.0 RC3 03-JUL-2009: remove some warning relative to deprecated syntax in PHP 5.0 when using the 
 *                           "&" for a reference.
 * - V1.2.0 06-AUG-2009: Remove some warning messages returned in PHP.
 * - V1.2.2 12-AUG-2009: Give the possibility to also install a "core" plugin that is defined in the "dbtable.xml".
 * - V1.2.4 23-AUG-2009: Remove some warning messages returned in PHP.
 *                       Start Joomla 1.6 compatibilty
 * - V1.2.5 06-SEP-2009: Fix missing data installation when not present.
 *                       When JMS Tools is used with "install", also copy the data present in the table
 *                       and not only the structure (When the data is not already present).
 * - V1.2.11 17-OCT-2009: Give the possibility to also install a "core" module when it is defined in the "dbtable.xml".
 * - V1.2.12 10-NOV-2009: Fix some "table" icon display when in the dbtable.xml it exists both definition with wildcard (%)
                          and without wildcard. (ie __comprofiler and __comprofiler% was not identified as identical).
                          So when a wildcard is present as last character a test is perform again without the wildcard
                          to check that table pattern match.
                          Also remove warning when it is not possible to retreive all the information of a module.
                          (Case where the XML manifest file is no more present on the disk or the module is deleted)
 * - V1.2.33 13-JUL-2010: Add Joomla 1.6 Beta 4 compatibility
 * - V1.2.34 24-JUL-2010: Fix bug when sharing the extension
 *                        Bug introduced in 1.2.33 when giving the possibility to exclude some tables
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );
jimport('joomla.filesystem.file');

require_once( JPATH_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'site.php');
require_once( JPATH_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'template.php');
require_once( JPATH_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'dbsharing.php');
require_once( JPATH_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'dbtables.php');
require_once( JPATH_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'utils.php');

require_once( dirname( __FILE__) .DS. 'manage.php');

if ( !defined( 'JPATH_MULTISITES')) {
   define( 'JPATH_MULTISITES', JPATH_ROOT.DS.'multisites');
}


// ===========================================================
//             MultisitesModelTemplates class
// ===========================================================
/**
 * @brief Is used to manage the 'Template websites'.
 */
class MultisitesModelTools extends JModel
{
   //------------ getSiteDependencies ---------------
   /**
    * @brief Return a tree array that give the Sites dependencies
    *
    * @param "with_parent_site_id"  Allow to retreive a tree based on a specific site_id.
    *                               When NULL, this return a tree based on all root nodes
    *                               When a site_id is specified, this return the children nodes of this site_id
    */
	
   function &getSiteDependencies( $with_parent_site_id = null)
	{
//Debug2Win::enableStandalone();      // Write the log in administrator/components/com_multisites/classes/logs
//Debug2Win::setFileName( 'multisites.tools.log.php');
//Debug2Win::enableDebug();        // Remove the comment to enable the debugging

//Debug2Win::debug_start( '>> getSiteDependencies() - START');

		$model = & JModel::getInstance( 'Manage', 'MultisitesModel');
		$sites = & $model->getSites();
		
		// Start to index all sites to have a direct access on them based on the site ID
		$indice = array();
		$sys_sites = array();
		// Create a special "master" website
		$master_site     = new Site();
		$master_site->id = ':master_db:';
      $sys_sites[]     = $master_site;
      $indice[ $master_site->id] = - count( $sys_sites);
		
		for( $i = 0; $i<count($sites); $i++) {
		   $indice[ $sites[$i]->id] = $i;
		}
		
		// Create the dependencies
		for( $i = 0; $i<count($sites); $i++) {
		   $site     = & $sites[$i];
//Debug2Win::debug( "$i: site_id=" . $site->id . (!empty($site->_treeChildren) ? ' Children count = ' . count($site->_treeChildren) : ''));
   		if ( isset( $fromSiteID))  { unset( $fromSiteID); }
   		if ( isset( $template))    { unset( $template); }
		   
		   // If there is a template used by the website
		   if ( !empty( $site->toPrefix) 
		     && !empty( $site->fromTemplateID)) {
		      // If this is directly the master website
		      if ( $site->fromTemplateID == ':master_db:') {
   		      $fromSiteID = ':master_db:';
//Debug2Win::debug( "  (1) fromSiteID = [$fromSiteID]");
		      }
		      // Otherwise try load the associated template
		      else {
		         $template = & $site->getTemplate();
//Debug2Win::debug( "  (1) template");
		      }
		   }
		   
		   // If the site uses a template
		   if ( !empty( $template) && !empty( $template->fromSiteID)) {
		      $fromSiteID = $template->fromSiteID;
//Debug2Win::debug( "  (2) fromSiteID = [$fromSiteID]");

		      // If the site reference a site that does not exists anymore
		      if ( !isset( $indice[ "$fromSiteID"])) {
		         $fromSiteID = ':orphan:';
		         // If the orphan entry does not exist in the index
		         if( !isset( $indice[ "$fromSiteID"])) {
		            // Create a special entry for the orphan
            		$orphan = new Site();
            		$orphan->id = $fromSiteID;
                  $indice[ $orphan->id] = - count( $sys_sites);
                  $sys_sites[] = $orphan;
		         }
		      }
		   }
		   
		   // If finally, we have found the "fromSiteID"
		   if ( !empty( $fromSiteID)) {
		      $x = $indice[ $fromSiteID];
		      if ( $x < 0) {
   		      $sites[$i]->_treeParentSite = & $sys_sites[ (- $x) - 1] ;
		      }
		      else {
   		      $sites[$i]->_treeParentSite = & $sites[ $x] ;
		      }
		      if ( !isset( $site->_treeParentSite->_treeChildren)) {
		         $site->_treeParentSite->_treeChildren = array();
		      }
		      $site->_treeParentSite->_treeChildren[$site->id] = & $site;
//Debug2Win::debug( '  parentSite = ' . $site->_treeParentSite->id);
//Debug2Win::debug( '  children count = ' . count( $site->_treeParentSite->_treeChildren));
		   }
		}
		
		// Search for all "root" sites (without parent sites)
		$tree  = array();
		foreach( $indice as $key => $i) {
		   if ( $i<0) {
		      $i = -$i;
		      $i--;
		      $site = & $sys_sites[$i];
		   }
		   else {
		      $site = & $sites[$i];
		   }
		   if ( empty( $with_parent_site_id)) {
   		   if ( empty( $site->_treeParentSite)) {
   		      $tree[ $site->id] = $site;
   		   }
		   }
		   else {
		      // If the parent match the selected site_id
   		   if ( !empty( $site->_treeParentSite) && $site->_treeParentSite->id == $with_parent_site_id ) {
   		      $tree[ $site->id] = $site;
   		   }
		   }
		}
      ksort( $tree);
      
      return $tree;
	}
	
   //------------ dumpTree ---------------
   /**
    * Display on screen the Tree and its dependencies
    */
	function dumpTree( $tree, $leadingspaces = '')
	{
	   if ( empty( $tree)) {
	      return;
	   }
	   foreach( $tree as $site) {
	      echo "\n<br />";
	      echo $leadingspaces . 'site : ' . $site->id;
	      if ( !empty( $site->_treeParentSite)) {
	         echo ' parentSiteID = ' .$site->_treeParentSite->id;
	      }
	      if ( !empty( $site->_treeChildren)) {
	         echo ' children:';
	         MultisitesModelTools::dumpTree( $site->_treeChildren, $leadingspaces . '&nbsp;');
	      }
	   }
	}

   //------------ _getComponent ---------------
   /**
    * @brief Return the list of component associated to the site.
    */
	function &_getComponent( $site_id, $option)
	{
	   static $none = array();
	   
		/* Get a database connector */
		$db =& Jms2WinFactory::getMultiSitesDBO( $site_id);
		if ( empty( $db)) {
		   return $none;
		}

      if ( version_compare( JVERSION, '1.6') >= 0) {
   		$query = "SELECT extension_id as id, name, element as 'option', client_id, protected as iscore"
   				 . ' FROM #__extensions'
				    . ' WHERE type = "component" AND protected=0 AND element='. $db->Quote( $option)
   				 . ' ORDER BY name'
   				 ;
   		$db->setQuery($query);
   		$rows = $db->loadObjectList();
      }
      else {
   		$query = 'SELECT *'
   				 . ' FROM #__components as c'
   				 . ' WHERE parent = 0 AND iscore = 0 AND c.option = ' . $db->Quote( $option)
   				 . ' ORDER BY name'
   				 ;
   		$db->setQuery($query);
   		$row = $db->loadObject();
   	}
		
		return $row;
	}

   //------------ _getComponents ---------------
   /**
    * @brief Return the list of component associated to the site.
    */
	function &_getComponents( $site_id)
	{
	   static $none = array();
		/* Get a database connector */
		$db =& Jms2WinFactory::getMultiSitesDBO( $site_id);
		if ( empty( $db)) {
		   return $none;
		}

      if ( version_compare( JVERSION, '1.6') >= 0) {
   		$query = "SELECT extension_id as id, name, element as 'option', client_id, protected as iscore"
   				 . ' FROM #__extensions'
				    . ' WHERE type = "component"'
   				 . ' GROUP BY name'
   				 . ' ORDER BY name'
   				 ;
   		$db->setQuery($query);
   		$rows = $db->loadObjectList();
      }
      else {
   		$query = 'SELECT *' .
   				' FROM #__components' .
   				' WHERE parent = 0 AND iscore = 0' .
//				   ' WHERE parent = 0' .
   				' ORDER BY name';
   		$db->setQuery($query);
   		$rows = $db->loadObjectList();
   	}
		
		return $rows;
	}
	
   //------------ _fillComponents ---------------
   /**
    * @brief Fill the components in the matrix with "column" number
    */
	function _fillComponents( &$components, $column, &$rows)
	{
	   for( $i = 0; $i<count($rows); $i++) {
	      $row  = & $rows[$i];
	      
	      if ( !isset( $components[$row->name])) {
	         $components[$row->name] = array();
	      }
	      $components[$row->name][$column] = $row;

	      if ( !isset( $components[$row->name][4])) {
   	      $shareInfos = & $this->dbsharing->getShareInfos( $row->option);
   	      if ( !empty( $shareInfos)) {
      	      $components[$row->name][4] = $shareInfos;
   	      }
	      }

	      if ( !isset( $components[$row->name][5])) {
   	      $tablesInfos = & $this->dbtables->getTablesInfos( $row->option);
   	      if ( !empty( $tablesInfos)) {
      	      $components[$row->name][5] = $tablesInfos;
   	      }
	      }
	   }
	}

   //------------ _getModule ---------------
   /**
    * @brief Return the module definition.
    */
	function &_getModule( $site_id, $module)
	{
	   static $none = array();
		/* Get a database connector */
		$db =& Jms2WinFactory::getMultiSitesDBO( $site_id);
		if ( empty( $db)) {
		   return $none;
		}


		$query = 'SELECT module, client_id'
				 . ' FROM #__modules'
				 . ' WHERE module = '  . $db->Quote( $module)
				 . ' AND iscore=0'
				 ;
		$db->setQuery($query, 0, 1);
		$rows = $db->loadObjectList();

		$n = count($rows);
		for ($i = 0; $i < $n; $i ++) {
			$row = & $rows[$i];

			// path to module directory
			if ($row->client_id == "1") {
				$moduleBaseDir = JPATH_ADMINISTRATOR.DS."modules";
			} else {
				$moduleBaseDir = JPATH_SITE.DS."modules";
			}

			// xml file for module
			$xmlfile = $moduleBaseDir . DS . $row->module .DS. $row->module.".xml";

			if (file_exists($xmlfile))
			{
				if ($data = JApplicationHelper::parseXMLInstallFile($xmlfile)) {
					foreach($data as $key => $value) {
						$row->$key = $value;
					}
				}
			}
		}
		
		if ( $n >0) {
   		return $rows[0];
		}
		$none = array();
	   return $none;
	}

   //------------ _getModules ---------------
   /**
    * @brief Return the list of modules associated to the site.
    */
	function &_getModules( $site_id)
	{
	   static $none = array();
		/* Get a database connector */
		$db =& Jms2WinFactory::getMultiSitesDBO( $site_id);
		if ( empty( $db)) {
		   return $none;
		}


      if ( version_compare( JVERSION, '1.6') >= 0) {
   		$query = 'SELECT extension_id as id, name, folder, element as module, client_id, protected as iscore'
   				 . ' FROM #__extensions'
				    . ' WHERE type = "module"'
   				 . ' GROUP BY name'
   				 . ' ORDER BY name'
   				 ;
   		$db->setQuery($query);
   		$rows = $db->loadObjectList();
      }
      else {
   		$query = 'SELECT module, client_id, iscore' .
   				' FROM #__modules' .
   				' WHERE module LIKE "mod_%" ' .
   				' GROUP BY module, client_id' .
   				' ORDER BY module, client_id';
   		$db->setQuery($query);
   		$rows = $db->loadObjectList();
   	}

		$n = count($rows);
		for ($i = 0; $i < $n; $i++) {
			$row = & $rows[$i];
			// If this is the same module than previous one, this mean it has different 'iscore'
			if ( $i>0 && $rows[$i-1]->module == $row->module) {
			   // If now this is a core module
			   if ( $row->iscore == 1) {
   			   // So skip it as we probably had another one that is not a core
   			   $row->todelete = true;
   			   continue;
			   }
			}

			// path to module directory
			if ($row->client_id == "1") {
				$moduleBaseDir = JPATH_ADMINISTRATOR.DS."modules";
			} else {
				$moduleBaseDir = JPATH_SITE.DS."modules";
			}

			// xml file for module
			$xmlfile = $moduleBaseDir . DS . $row->module .DS. $row->module.".xml";

			if (file_exists($xmlfile))
			{
				if ($data = JApplicationHelper::parseXMLInstallFile($xmlfile)) {
					foreach($data as $key => $value) {
						$row->$key = $value;
					}
				}
			}
			else {
			   $row->todelete = true;
			}
		}
		
		return $rows;
	}


   //------------ _fillModules ---------------
   /**
    * @brief Fill the modules in the matrix with "column" number
    */
	function _fillModules( &$modules, $column, &$rows)
	{
	   for( $i = 0; $i<count($rows); $i++) {
	      $row  = & $rows[$i];
	      if ( isset( $row->todelete) && $row->todelete) {
	         continue;
	      }
	      
	      $tablesInfos = & $this->dbtables->getTablesInfos( $row->module);
	      
	      // If the module is not yet present in the collection
	      if ( !isset( $modules[$row->name])) {
	         // If is NOT a core module (normal module) 
	         if ( $row->iscore != 1) {
   	         $modules[$row->name] = array();
	         }
	         // OR the module is defined in the dbtable.xml (case of core module that JMS want to manage).
	         else if ( !empty( $tablesInfos)) {
	            // Try to see if there is an identical module name in the modules
	            if ( $column <= 0) {
      	         $modules[$row->name] = array();
	            }
	            else {
	               // As the back-end module name can be different of the front-end module name
	               // Scan all modules to compare the system module name (not its label)
	               $foundName = '';
   	            foreach( $modules as $moduleArray) {
   	               if ( isset( $moduleArray[0])) {
   	                  if ( isset( $moduleArray[0]->module) && $moduleArray[0]->module == $row->module) {
   	                     $foundName = $moduleArray[0]->name;
   	                     break;
   	                  }
   	               }
   	            }
   	            // If not found a front-end module name
   	            if ( empty( $foundName)) {
   	               // Create a new entry
         	         $modules[$row->name] = array();
   	            }
   	            // reuse the front-end module name
   	            else {
      	            $row->name = $foundName;
   	            }
	            }
	         }
	         else {
	            // skip this module (don't publish it to the user)
	            continue;
	         }
	      }
	      $modules[$row->name][$column] = $row;

	      if ( !isset( $modules[$row->name][4])) {
   	      $shareInfo = & $this->dbsharing->getShareInfos( $row->module);
   	      if ( !empty( $shareInfos)) {
      	      $modules[$row->name][4] = $shareInfos;
   	      }
	      }

	      if ( !isset( $modules[$row->name][5])) {
//   	      $tablesInfos = & $this->dbtables->getTablesInfos( $row->module);
   	      if ( !empty( $tablesInfos)) {
      	      $modules[$row->name][5] = $tablesInfos;
   	      }
	      }
	   }
	}


   //------------ _getPlugins ---------------
   /**
    * @brief Return the list of plugins associated to the site.
    */
	function &_getPlugin( $site_id, $folder, $element = null)
	{
	   static $none = array();
	   // When $element is empty
	   if ( empty( $element)) {
	      // Consider the $folder as an option in format folder/element
	      $parts =  explode( '/', $folder);
	      if ( count( $parts) == 2) {
	         $folder  = $parts[0];
	         $element = $parts[1];
	      }
	   }
	   
		/* Get a database connector */
		$db =& Jms2WinFactory::getMultiSitesDBO( $site_id);
		if ( empty( $db)) {
		   return $none;
		}


      if ( version_compare( JVERSION, '1.6') >= 0) {
   		$query = 'SELECT extension_id as id, name, folder, element, client_id'
   				 . ' FROM #__extensions'
   				 . ' WHERE type = "plugin"'
      			 . '   AND state > -1'
   				 . '   AND folder ='  . $db->Quote( $folder)
   				 . '   AND element =' . $db->Quote( $element)
   				 ;
      }
      else {
   		$query = 'SELECT id, name, folder, element, client_id'
   				 . ' FROM #__plugins'
   				 . ' WHERE iscore=0'
   				 . '   AND folder ='  . $db->Quote( $folder)
   				 . '   AND element =' . $db->Quote( $element)
   				 ;
      }
		$db->setQuery($query);
		$rows = $db->loadObject();

		return $rows;
	}


   //------------ _getPlugins ---------------
   /**
    * @brief Return the list of plugins associated to the site.
    */
	function &_getPlugins( $site_id)
	{
	   static $none = array();
	   
		/* Get a database connector */
		$db =& Jms2WinFactory::getMultiSitesDBO( $site_id);
		if ( empty( $db)) {
		   return $none;
		}


      if ( version_compare( JVERSION, '1.6') >= 0) {
   		$query = 'SELECT extension_id as id, name, folder, element, client_id, 0 as iscore'
   				 . ' FROM #__extensions'
				    . ' WHERE type = "plugin"'
   				 . ' GROUP BY name'
   				 . ' ORDER BY name'
   				 ;
   		$db->setQuery($query);
   		$rows = $db->loadObjectList();
      }
      else {
   		$query = 'SELECT id, name, folder, element, client_id, iscore' .
   				' FROM #__plugins' .
   				' GROUP BY name' .
   				' ORDER BY name';
   		$db->setQuery($query);
   		$rows = $db->loadObjectList();
      }

		return $rows;
	}


   //------------ _fillPlugins ---------------
   /**
    * @brief Fill the plugins in the matrix with "column" number
    */
	function _fillPlugins( &$plugins, $column, &$rows)
	{
	   for( $i = 0; $i<count($rows); $i++) {
	      $row  = & $rows[$i];

	      $option = $row->folder .'/'. $row->element;
	      $tablesInfos = & $this->dbtables->getTablesInfos( $option);

	      // If the plugin is not yet present in the collection
	      if ( !isset( $plugins[$row->name])) {
	         // If is NOT a core plugin (normal plugin) 
	         // OR the plugin is defined in the dbtable.xml (case of core plugin that JMS want to manage).
	         if ( $row->iscore != 1 || !empty( $tablesInfos)) {
   	         $plugins[$row->name] = array();
	         }
	         else {
	            // skip this plugin (don't publish it to the user)
	            continue;
	         }
	      }
	      $plugins[$row->name][$column] = $row;
	      
	      if ( !isset( $plugins[$row->name][4])) {
   	      $shareInfos = & $this->dbsharing->getShareInfos( $option);
   	      if ( !empty( $shareInfos)) {
      	      $plugins[$row->name][4] = $shareInfos;
   	      }
	      }

	      if ( !isset( $plugins[$row->name][5])) {
//   	      $tablesInfos = & $this->dbtables->getTablesInfos( $option);
   	      if ( !empty( $tablesInfos)) {
      	      $plugins[$row->name][5] = $tablesInfos;
   	      }
	      }
	   }
	}

   //------------ _getExtName ---------------
   /**
    * @brief Return the name associated to the option.
    * Depending on the option, this query the #__components, #__modules or #__plugins
    */
	function &_getExtName( $site_id, $option)
	{
      $result = $option;
      
	   // Component
	   if ( strncmp( $option, 'com_', 4) == 0) {
	      $obj = $this->_getComponent( $site_id, $option);
	      if ( !empty( $obj)) {
	         $row->name = $obj->name;
	      }
	   }
	   // Module
	   else if ( strncmp( $option, 'mod_', 4) == 0) {
	      $obj = $this->_getModule( $site_id, $option);
	      if ( !empty( $obj)) {
	         $result = $obj->name;
	      }
	   }
	   // Plugin
	   else {
	      $obj = $this->_getPlugin( $site_id, $option);
	      if ( !empty( $obj)) {
	         $result = $obj->name;
	      }
	   }
	   
	   return $result;
	}

   //------------ getExtensions ---------------
   /**
    * @brief Get all extensions informations
    *
    * This return a matrix
    * ['Components'][col number]->Object
    * With Col number:
    * - 0 = Master webiste
    * - 1 = Template webstie (fromSiteID)
    * - 2 = Site ID
    * - 3 = List of Tables and Views
    * - 4 = DBSharing pattern reference
    * - 5 = DBTables info pattern reference
    */
	function &getExtensions( $site_id)
	{
	   $extensions = array();
	   
	   $components = array();
	   $modules    = array();
	   $plugins    = array();
	   
	   $this->dbsharing = & Jms2WinDBSharing::getInstance();
	   $this->dbtables  = & Jms2WinDBTables::getInstance();

	   // retreive Maste Website info
	   $components_master = & $this->_getComponents( ':master_db:');
   	$this->_fillComponents( $components, 0, $components_master);

	   $modules_master = & $this->_getModules( ':master_db:');
   	$this->_fillModules( $modules, 0, $modules_master);

	   $plugins_master = & $this->_getPlugins( ':master_db:');
   	$this->_fillPlugins( $plugins, 0, $plugins_master);

	   if ( $site_id != ':master_db:') {
   	   // retreive the "site_id"  info
   	   $components_site   = & $this->_getComponents( $site_id);
      	$this->_fillComponents( $components, 2, $components_site);

   	   $modules_site = & $this->_getModules( $site_id);
      	$this->_fillModules( $modules, 2, $modules_site);
   
   	   $plugins_site = & $this->_getPlugins( $site_id);
      	$this->_fillPlugins( $plugins, 2, $plugins_site);
      	
   	   // retreive the "fromSiteID" info (template)
   		$site = & Site::getInstance( $site_id);
   		$fromSiteID = $site->getFromSiteID();
   		if ( $fromSiteID != ':master_db:') {
      	   $components_templates   = & $this->_getComponents( $fromSiteID);
         	$this->_fillComponents( $components, 1, $components_templates);

      	   $modules_templates = & $this->_getModules( $fromSiteID);
         	$this->_fillModules( $modules, 1, $modules_templates);
      
      	   $plugins_templates = & $this->_getPlugins( $fromSiteID);
         	$this->_fillPlugins( $plugins, 1, $plugins_templates);
   		}
	   }
	   
	   $extensions['Components'] = & $components;
	   $extensions['Modules']    = & $modules;
	   $extensions['Plugins']    = & $plugins;
	   
	   return $extensions;
	}


   //------------ getSiteInfo ---------------
   /**
    * @brief Return the information related to the site_id.
    */
	function &getSiteInfo( $site_id)
	{
		$site = & Site::getInstance( $site_id);
		$site->fromSiteID = $site->getFromSiteID();

		$site->mysql_version = Jms2WinFactory::getDBOVersion( $site_id);
		$site->mysql_sharing = Jms2WinFactory::isCreateView( $site_id);

		return $site;
	}


   //------------ _getTablesInfo ---------------
   /**
    * @brief Update the "$matrix" on "$column' with the list of tables or views used by the website
    */
	function _getTablesInfo( & $matrix, $column, $site_id)
	{
		$db =& Jms2WinFactory::getMultiSitesDBO( $site_id);
		if ( empty( $db)) {
		   return;
		}

      $srcPrefix     = $db->getPrefix();
      $srcPrefix_len = strlen($srcPrefix);
      $dbprefix      = str_replace('_' , '\_', $srcPrefix);
      
      // Retreive all the table name present in the source database with corresponding prefix
      $db->setQuery( 'SHOW TABLES LIKE \''.$dbprefix.'%\'' );
      $rows = $db->loadResultArray();
      
      if ( empty( $rows)) {
         return;
      }

	   // For each table name
	   foreach( $rows as $table) {
	      // retreive the table info
	      $like = str_replace('_' , '\_', $table);
         $query = "SHOW TABLE STATUS LIKE '$like'";
         $db->setQuery( $query );
         $obj = $db->loadObject();
         if ( !empty( $obj)) {
            // If this is a View
            if ( !empty( $obj->Comment) && strtoupper( substr($obj->Comment, 0, 4)) == 'VIEW') {
               $obj->_isView = true;
               $obj->_viewFrom = MultisitesDatabase::getViewFrom( $db, $table);
            }
            // Else this is a table
            else {
               $obj->_isView = false;
            }
            
            $tablename = '#__' . substr($table, $srcPrefix_len);
            
            // Save the result into the matrix
   	      if ( !isset( $matrix[$tablename])) {
   	         $matrix[$tablename] = array();
   	      }
   	      $matrix[$tablename][$column] = $obj;
         }
	   }
	}
	
   //------------ getListOfTables ---------------
   /**
    * @brief Return a matrix with the list of tables and its type (table or view) for each websites
    *
    * [0] = List of :master_db: tables or view
    * [1] = List of "template" (fromSiteID) tables or view
    * [2] = List of the "site ID" tables or view
    * [3] = Reference to a "DBTables" <table> entry
    */
	function &getListOfTables( $site_id)
	{
		static $tables;
		
		if ( !isset( $tables)) {
   	   $tables = array();
   	   $this->_tablespattern = array();

   	   $this->_getTablesInfo( $tables, 0, ':master_db:');
   	   if ( $site_id != ':master_db:') {
      	   // retreive the "site_id"  info
      	   $this->_getTablesInfo( $tables, 2, $site_id);
   
      	   // retreive the "fromSiteID" info (template)
      		$site = & Site::getInstance( $site_id);
      		$fromSiteID = $site->getFromSiteID();
      		if ( $fromSiteID != ':master_db:') {
         	   $this->_getTablesInfo( $tables, 1, $fromSiteID);
      		}
      	}
      	
      	// Now associate each table entry with its definition
   	   $this->dbtables  = & Jms2WinDBTables::getInstance();
      	foreach( $tables as $key => $columns) {
      	   $tables[$key][3] = & $this->dbtables->getTable( $key);
      	   // If the table exists in the slave site
      	   if ( !empty($tables[$key][2])) {
         	   // If there is a table pattern corresponding to this table
         	   if ( !empty( $tables[$key][3])) {
         	      $tablepatterns = & $this->dbtables->getMatchingKeys( $tables[$key][3]);
         	      foreach( $tablepatterns as $tablepattern) {
            	      // Add the site table found into the pattern definition
            	      if ( !isset( $this->_tablespattern[$tablepattern])) {
            	         $this->_tablespattern[$tablepattern] = array();
            	      }
            	      $this->_tablespattern[$tablepattern][] = $tables[$key][2];
         	      }
         	   }
      	   }
      	}
		}
   	
   	return $tables;
	}
	   

   //------------ hasChildren ---------------
   /**
    * @brief Return true when a website has children websites
    */
	function hasChildren( $site_id)
	{
	   // Load all sites information
		$model = & JModel::getInstance( 'Manage', 'MultisitesModel');
		$sites = & $model->getSites();

		// Search for a website that have as parent (fromSiteID) = $site_id
		for( $i = 0; $i<count($sites); $i++) {
		   $site     = & $sites[$i];
   		if ( isset( $fromSiteID))  { unset( $fromSiteID); }
   		if ( isset( $template))    { unset( $template); }
		   
		   // If there is a template used by the website
		   if ( !empty( $site->toPrefix) 
		     && !empty( $site->fromTemplateID)) {
		      // If this is directly the master website
		      if ( $site->fromTemplateID == ':master_db:') {
   		      $fromSiteID = ':master_db:';
		      }
		      // Otherwise try load the associated template
		      else {
		         $template = & $site->getTemplate();
      		   // If the site uses a template
      		   if ( !empty( $template) && !empty( $template->fromSiteID)) {
      		      $fromSiteID = $template->fromSiteID;
      		   }
		      }
		   }

		   // If finally, we have found the "fromSiteID"
		   if ( !empty( $fromSiteID)) {
		      if ( $fromSiteID == $site_id) {
		         return true;
		      }
		   }
		}
		return false;
	}


   //------------ getTableUsingPattern ---------------
   /**
    * @brief Return a "SHOW TABLE" definition of NULL when the table is not defined.
    */
	function &getTableUsingPattern( $tablepattern)
	{
	   static $none = null;
	   if ( !empty( $this->_tablespattern[$tablepattern])) {
	      return $this->_tablespattern[$tablepattern];
	   }
	   // If the pattern ends with a "%", retry also without the wildcard.
	   if ( substr( $tablepattern, -1) == '%') {
	      $tablepattern = substr( $tablepattern, 0, strlen( $tablepattern)-1);
   	   if ( !empty( $this->_tablespattern[$tablepattern])) {
   	      return $this->_tablespattern[$tablepattern];
   	   }
	   }
	   
	   return $none;
	}
	
   //------------ convertTreeIntoList ---------------
	/**
	 * Convert a tree into a list.
	 * Each node of the tree is append to the $_list
	 */
	function convertTreeIntoList( $tree, &$_list) {
	   foreach ( $tree as $key => $node) {
	      $_list[$key] = & $tree[$key];
	      if ( !empty( $node->_treeChildren)) {
	         $this->convertTreeIntoList( $node->_treeChildren, $_list);
	      }
	   }
	}


   //------------ getActionsToDo ---------------
   /**
    * @brief Return an tree with all the actions to do.
    * The tree represent the dependencies between the websites
    *
    * [site_id] {
    *    - rows[] = { - Action (Drop table, create table, create view), Replace table data.
    *                 - table name
    *                 - from DB & table 
    *           }
    * }
    */
	function &getActionsToDo( $enteredvalues)
	{
	   $results = array();
	   
	   $site_id    = & $enteredvalues['site_id'];
      $site       = & Site::getInstance( $site_id);
		$fromSiteID = $site->getFromSiteID();           // The template site ID
		
	   $extName_site_id  = $fromSiteID;
		if ( empty( $fromSiteID)) {
		   $extName_site_id  = $site_id;
		}

	   // Retreive the tables and sharing replication rules
	   $this->dbsharing = & Jms2WinDBSharing::getInstance();
	   $this->dbtables  = & Jms2WinDBTables::getInstance();

	   // ===========  Sites ============
	   $rows = array();

	   // --- Components ---
	   // First analyze the actions to do and if they require to propagate to children websites
	   $actions = array();
	   if ( !empty( $enteredvalues['comActions'])) {
   	   $actions = & $enteredvalues['comActions'];
	   }
	   
	   // remove the "[unselected]" actions
	   for ( $i=count($actions)-1; $i>=0; $i--) {
	      if ( $actions[$i] == '[unselected]') {
	         unset( $actions[$i]);
	      }
	   }
	   
	   foreach( $actions as $action) {
	      $row  = new stdClass();
	      $list = explode( '|', $action);
	      $row->option      = $list[1];    // Option
	      if ( !empty( $site->sitename)) { $row->sitename = $site->sitename; }
	      $row->name = $this->_getExtName( $extName_site_id, $row->option);

	      $cmds =  explode( '.', $list[0]);
	      $row->action      = $cmds[0];          // uninstall, table, share (View)
	      $row->fromSiteID  = '';
	      $row->overwrite   = false;
	      if ( count( $cmds) >= 2) {
	         // If master
	         if ( $cmds[1] == 'master') {
         	   $row->fromSiteID  = ':master_db:';  // Based on the master website
	         }
	         // If template
	         else {
         	   $row->fromSiteID  = $fromSiteID;    // Based on the site ID defined in the template
	         }
	      }
	      
	      if ( $row->action == 'share') {
   	      $row->shareInfos  = & $this->dbsharing->getShareInfos( $row->option);
	      }
	      else {
      	   $row->tablesInfos = & $this->dbtables->getTablesInfos( $row->option);
	      }
	      
	      $rows[$row->option] = $row;
	   }

	   // --- Modules ---
	   $actions = array();
	   if ( !empty( $enteredvalues['modActions'])) {
   	   $actions = & $enteredvalues['modActions'];
	   }
	   // remove the "[unselected]" actions
	   for ( $i=count($actions)-1; $i>=0; $i--) {
	      if ( $actions[$i] == '[unselected]') {
	         unset( $actions[$i]);
	      }
	   }
	   
	   foreach( $actions as $action) {
	      $row  = new stdClass();
	      $list = explode( '|', $action);
	      $row->option      = $list[1];    // Option
	      if ( !empty( $site->sitename)) { $row->sitename = $site->sitename; }
	      $row->name = $this->_getExtName( $extName_site_id, $row->option);

	      $cmds =  explode( '.', $list[0]);
	      $row->action      = $cmds[0];          // uninstall, table, share (View)
	      $row->fromSiteID  = '';
	      $row->overwrite   = false;
	      if ( count( $cmds) >= 2) {
	         // If master
	         if ( $cmds[1] == 'master') {
         	   $row->fromSiteID  = ':master_db:';  // Based on the master website
	         }
	         // If template
	         else {
         	   $row->fromSiteID  = $fromSiteID;    // Based on the site ID defined in the template
	         }
	      }
	      
	      if ( $row->action == 'share') {
   	      $row->shareInfos  = & $this->dbsharing->getShareInfos( $row->option);
	      }
	      else {
      	   $row->tablesInfos = & $this->dbtables->getTablesInfos( $row->option);
	      }
	      
	      $rows[$row->option] = $row;
	   }

	   // --- Plugins ---
	   $actions = array();
	   if ( !empty( $enteredvalues['plgActions'])) {
   	   $actions = & $enteredvalues['plgActions'];
	   }
	   // remove the "[unselected]" actions
	   for ( $i=count($actions)-1; $i>=0; $i--) {
	      if ( $actions[$i] == '[unselected]') {
	         unset( $actions[$i]);
	      }
	   }
	   
	   foreach( $actions as $action) {
	      $row  = new stdClass();
	      $list = explode( '|', $action);
	      $row->option      = $list[1];    // Option
	      if ( !empty( $site->sitename)) { $row->sitename = $site->sitename; }
	      $row->name = $this->_getExtName( $extName_site_id, $row->option);
	         
	      $cmds =  explode( '.', $list[0]);
	      $row->action      = $cmds[0];          // uninstall, table, share (View)
	      $row->fromSiteID  = '';
	      $row->overwrite   = false;
	      if ( count( $cmds) >= 2) {
	         // If master
	         if ( $cmds[1] == 'master') {
         	   $row->fromSiteID  = ':master_db:';  // Based on the master website
	         }
	         // If template
	         else {
         	   $row->fromSiteID  = $fromSiteID;    // Based on the site ID defined in the template
	         }
	      }
	      
	      if ( $row->action == 'share') {
   	      $row->shareInfos  = & $this->dbsharing->getShareInfos( $row->option);
	      }
	      else {
      	   $row->tablesInfos = & $this->dbtables->getTablesInfos( $row->option);
	      }
	      
	      $rows[$row->option] = $row;
	   }

	   // If there is something to process in the current site ID
	   if ( !empty( $rows)) {
   	   $results[$site_id] = $rows;
	   }
	   
	   // ===========  Propagation in the children ============
	   // --- Components ---
	   // Convert the propagation flag into a list of sites ID and list of actions
	   $propagations = & $enteredvalues['comPropagates'];
	   $overwrites   = & $enteredvalues['comOverwrites'];
	   if ( !empty( $propagations)) {
	      // convert the children tree into a list of site ids
	      $childrenTree = & $this->getSiteDependencies( $site_id);
	      $sites = array();
	      $this->convertTreeIntoList( $childrenTree, $sites);
	      
	      // For each propagation flag
   	   foreach( $propagations as $indice => $option) {
   	      // If the propagation is associated to a site action
   	      if ( !empty( $rows[$option])) {
   	         // Propagate the site action
   	         $row  = $rows[$option];
   	      }
   	      else {
      	      $row  = new stdClass();
      	      $row->action      = 'table';     // Create table
         	   $row->fromSiteID  = $site_id;    // Based on this site ID
      	      $row->option      = $option;
      	      $row->name        = $this->_getExtName( $extName_site_id, $row->option);
         	   $row->tablesInfos = & $this->dbtables->getTablesInfos( $row->option);
   	      }
   	      if ( !empty( $overwrites[$indice]) && $overwrites[$indice] == $option) {
      	      $row->overwrite   = true;
   	      }
   	      else {
      	      $row->overwrite   = false;
   	      }
   	      
   	      // Propagate the action to all the sites
   	      foreach( $sites as $site) {
   	         if ( empty( $results[$site->id])) {
   	            $results[$site->id] = array();
   	         }
   	         $results[$site->id][$option] = $row;
   	      }
   	   }
	   }

	   // --- Modules ---
	   // Convert the propagation flag into a list of sites ID and list of actions
	   $propagations = & $enteredvalues['modPropagates'];
	   $overwrites   = & $enteredvalues['modOverwrites'];
	   if ( !empty( $propagations)) {
	      // convert the children tree into a list of site ids
	      $childrenTree = & $this->getSiteDependencies( $site_id);
	      $sites = array();
	      $this->convertTreeIntoList( $childrenTree, $sites);
	      
	      // For each propagation flag
   	   foreach( $propagations as $indice => $option) {
   	      // If the propagation is associated to a site action
   	      if ( !empty( $rows[$option])) {
   	         // Propagate the site action
   	         $row  = $rows[$option];
   	      }
   	      else {
      	      $row  = new stdClass();
      	      $row->action      = 'table';     // Create table
         	   $row->fromSiteID  = $site_id;    // Based on this site ID
      	      $row->option      = $option;
      	      $row->name        = $this->_getExtName( $extName_site_id, $row->option);
         	   $row->tablesInfos = & $this->dbtables->getTablesInfos( $row->option);
   	      }
   	      if ( !empty( $overwrites[$indice]) && $overwrites[$indice] == $option) {
      	      $row->overwrite   = true;
   	      }
   	      else {
      	      $row->overwrite   = false;
   	      }
   	      
   	      // Propagate the action to all the sites
   	      foreach( $sites as $site) {
   	         if ( empty( $results[$site->id])) {
   	            $results[$site->id] = array();
   	         }
   	         $results[$site->id][$option] = $row;
   	      }
   	   }
	   }

	   // --- Plugins ---
	   // Convert the propagation flag into a list of sites ID and list of actions
	   $propagations = & $enteredvalues['plgPropagates'];
	   $overwrites   = & $enteredvalues['plgOverwrites'];
	   if ( !empty( $propagations)) {
	      // convert the children tree into a list of site ids
	      $childrenTree = & $this->getSiteDependencies( $site_id);
	      $sites = array();
	      $this->convertTreeIntoList( $childrenTree, $sites);
	      
	      // For each propagation flag
   	   foreach( $propagations as $indice => $option) {
   	      // If the propagation is associated to a site action
   	      if ( !empty( $rows[$option])) {
   	         // Propagate the site action
   	         $row  = $rows[$option];
   	      }
   	      else {
      	      $row  = new stdClass();
      	      $row->action      = 'table';     // Create table
         	   $row->fromSiteID  = $site_id;    // Based on this site ID
      	      $row->option      = $option;
      	      $row->name        = $this->_getExtName( $extName_site_id, $row->option);
         	   $row->tablesInfos = & $this->dbtables->getTablesInfos( $row->option);
   	      }
   	      if ( !empty( $overwrites[$indice]) && $overwrites[$indice] == $option) {
      	      $row->overwrite   = true;
   	      }
   	      else {
      	      $row->overwrite   = false;
   	      }
   	      
   	      // Propagate the action to all the sites
   	      foreach( $sites as $site) {
   	         if ( empty( $results[$site->id])) {
   	            $results[$site->id] = array();
   	         }
   	         $results[$site->id][$option] = $row;
   	      }
   	   }
	   }


	   return $results;
	}


   //------------ doAction ---------------
   /**
    * @brief Execute the action.
    * @return true when success. Otherwise return false.
    */
	function &doAction( $enteredvalues)
	{
	   $errors     = array( 'Invalid action');
	   
	   $action     = $enteredvalues['action'];
	   $option     = $enteredvalues['option'];
	   $overwrite  = $enteredvalues['overwrite'];

	   $fromSiteID = $enteredvalues['fromSiteID'];
	   $toSiteID   = $enteredvalues['site_id'];

      if ( !empty( $fromSiteID)) {
         $fromDB  =& Jms2WinFactory::getMultiSitesDBO( $fromSiteID);
      }
      $toDB    =& Jms2WinFactory::getMultiSitesDBO( $toSiteID);
	   
	   if ( $action == 'table') {
	      // Get the list of tables to replicate
   	   $this->dbtables  = & Jms2WinDBTables::getInstance();
   	   $tablesInfos     = & $this->dbtables->getTablesInfos( $option);
   	   // Extract the patttern present in all <table> nodes.
   	   $tablePatterns = array();
   	   foreach( $tablesInfos as $xmlTable) {
   	      $name = $xmlTable->attributes( 'name');
   	      if ( !empty( $name)) {
   	         $tablePatterns[$name] = $name;
   	      }
   	   }
   	   $errors = MultisitesDatabase::copyDbTablePatterns( $fromDB, $toDB, $tablePatterns, true, $overwrite);
   	   if ( !empty( $errors)) {
   	      return $errors;
   	   }
   	   // Depending on the extension (option), also insert entry in the "table of content"
   	   $errors = MultisitesDatabase::installNewExtension( $fromDB, $toDB, $option, $overwrite);
	   }
	   else if ( $action == 'share' || $action == 'view') {
   	   $this->dbsharing = & Jms2WinDBSharing::getInstance();
	      $shareInfo = & $this->dbsharing->getShareInfos( $option);
   	   // Return an array with the list of <table> corresponding to the shared infos.
   	   $tablesInfos = & $this->dbsharing->getTables( $shareInfo);
   	   // Extract the patttern present in all <table> nodes.
   	   $tablePatterns = array();
   	   foreach( $tablesInfos as $xmlTable) {
   	      $name = $xmlTable->attributes( 'name');
   	      if ( !empty( $name)) {
   	         $tablePatterns[$name] = $name;
   	      }
   	   }
	      $toConfig =& Jms2WinFactory::getMultiSitesConfig( $toSiteID);
   	   $errors = MultisitesDatabase::createViews( $fromDB, $toDB, array( 'table' => $tablePatterns), $toConfig);
   	   if ( !empty( $errors)) {
   	      return $errors;
   	   }
   	   // Depending on the extension (option), also insert entry in the "table of content"
   	   $errors = MultisitesDatabase::installNewExtension( $fromDB, $toDB, $option, $overwrite);
	   }
	   else if ( $action == 'uninstall') {
	      // Get the list of tables to removed
   	   $this->dbtables  = & Jms2WinDBTables::getInstance();
   	   $tablesInfos     = & $this->dbtables->getTablesInfos( $option);
   	   // Extract the patttern present in all <table> nodes.
   	   $tablePatterns = array();
   	   foreach( $tablesInfos as $xmlTable) {
   	      $name = $xmlTable->attributes( 'name');
   	      if ( !empty( $name)) {
   	         $tablePatterns[] = $name;
   	      }
   	   }
   	   $errors = MultisitesDatabase::dropTablePatterns( $toDB, $tablePatterns);
   	   if ( !empty( $errors)) {
   	      return $errors;
   	   }
   	   $errors = MultisitesDatabase::uninstallExtension( $toDB, $option);
	   }
	   
      return $errors;
	}


   //------------ doActions ---------------
   /**
    * @brief Execute all the actions of a site.
    * @return an array of errors. When success, the array is empty.
    */
	function &doActions( $enteredvalues)
	{
	   $errors     = array( 'Invalid action');

	   $toSiteID   = $enteredvalues['site_id'];
	   $nbActions  = $enteredvalues['nbActions'];
	   
	   $actions     = $enteredvalues['actions'];
	   $options     = $enteredvalues['options'];
	   $overwrites  = $enteredvalues['overwrites'];
	   $fromSiteIDs = $enteredvalues['fromSiteIDs'];


      $toDB       =& Jms2WinFactory::getMultiSitesDBO( $toSiteID);
      $toConfig   =& Jms2WinFactory::getMultiSitesConfig( $toSiteID);
      
      // For each actions, collect the pattern to process (table, share, uninstall)
	   $this->dbtables      = & Jms2WinDBTables::getInstance();
	   $this->dbsharing     = & Jms2WinDBSharing::getInstance();
	   $uninstallPatterns   = array();  // Array as the fromSiteID is not relevant when uninstalling (always toSiteID is required)
	   $uninstallOptions    = array();  // Array of Options
	   $sharePatterns       = array();  // Matrix on fromSiteID (Key = Option / Value = Overwrite)
	   $tablePatterns       = array();  // Matrix on fromSiteID
	   $installOptions      = array();  // Matrix on fromSiteID
      for ( $i=0; $i<$nbActions; $i++) {
         $action = $actions[$i];
         $option = $options[$i];
         if ( empty( $fromSiteIDs[$i])) {
            $fromSiteID = ':master_db:';
         }
         else {
            $fromSiteID = $fromSiteIDs[$i];
         }
   	   if ( !empty( $overwrites[$i])) {
   	      $overwrite = $overwrites[$i];
   	   }
   	   else {
   	      $overwrite = false;
   	   }
         
   	   if ( $action == 'table') {
   	      // Get the list of tables to replicate
      	   $tablesInfos     = & $this->dbtables->getTablesInfos( $option);
      	   // Extract the patttern present in all <table> nodes.
      	   foreach( $tablesInfos as $xmlTable) {
      	      $name = $xmlTable->attributes( 'name');
      	      if ( !isset( $tablePatterns[$fromSiteID])) {
      	         $tablePatterns[$fromSiteID] = array();
      	      }
      	      if ( !empty( $name)) {
      	         $tablePatterns[$fromSiteID][$name]  = $name;
      	      }
      	   }
   	      if ( !isset( $installOptions[$fromSiteID])) {
   	         $installOptions[$fromSiteID] = array();
   	      }
	         $installOptions[$fromSiteID][$option] = $overwrite;
   	   }
   	   else if ( $action == 'share' || $action == 'view') {
   	      $shareInfo = & $this->dbsharing->getShareInfos( $option);
      	   // Return an array with the list of <table> corresponding to the shared infos.
      	   $tablesInfos = & $this->dbsharing->getTables( $shareInfo);
      	   // Extract the patttern present in all <table> nodes.
      	   foreach( $tablesInfos as $xmlTable) {
      	      $name = $xmlTable->attributes( 'name');
      	      if ( !isset( $sharePatterns[$fromSiteID])) {
      	         $sharePatterns[$fromSiteID] = array();
      	         $sharePatterns[$fromSiteID]['table'] = array();
      	      }
      	      if ( !empty( $name)) {
      	         $sharePatterns[$fromSiteID]['table'][$name]  = $name;
      	      }
      	   }
   	      if ( !isset( $installOptions[$fromSiteID])) {
   	         $installOptions[$fromSiteID] = array();
   	      }
	         $installOptions[$fromSiteID][$option] = $overwrite;
      	}
   	   else if ( $action == 'uninstall') {
   	      // Get the list of tables to removed
      	   $tablesInfos     = & $this->dbtables->getTablesInfos( $option);
      	   // Extract the patttern present in all <table> nodes.
      	   foreach( $tablesInfos as $xmlTable) {
      	      $name = $xmlTable->attributes( 'name');
      	      if ( !empty( $name)) {
         	      $drop = $xmlTable->attributes( 'drop');
         	      // If fake table
      	         if ( $name == '[none]') {}                                     // Ignore the table
      	         // If drop table is not allowed
      	         else if ( !empty( $drop) && strtolower( $drop) == 'no') {}     // Ignore the table
      	         else {
         	         $uninstallPatterns[$name]  = $name;
      	         }
      	      }
      	   }
	         $uninstallOptions[$option] = $option;
      	}
      } // For each actions
      
      // ------- Uninstall ------
      // Process the Uninstall patterns
      if ( !empty( $uninstallPatterns)) {
   	   $errors = MultisitesDatabase::dropTablePatterns( $toDB, $uninstallPatterns);
   	   if ( !empty( $errors)) {
   	      return $errors;
   	   }
   	}

   	// Now remove the extension in the TOC
   	if ( !empty( $uninstallOptions)) {
         $errors = array();
   	   foreach( $uninstallOptions as $option) {
      	   $results = MultisitesDatabase::uninstallExtension( $toDB, $option);
      	   $errors = array_merge( $errors, $results);
   	   }
   	   
   	   if ( !empty( $errors)) {
   	      return $errors;
   	   }
   	}
   	
      // ------- share ------
      // For each 'fromSiteID' that want share table patterns, create view on this "fromSiteID' table
      foreach( $sharePatterns as $fromSiteID => $fromSiteIDPatterns) {
         $fromDB  =& Jms2WinFactory::getMultiSitesDBO( $fromSiteID);
         
   	   $errors = MultisitesDatabase::createViews( $fromDB, $toDB, $fromSiteIDPatterns, $toConfig);
   	   if ( !empty( $errors)) {
   	      return $errors;
   	   }
      }

      // ------- table ------
      // For each 'fromSiteID', get the associated table patterns
      foreach( $tablePatterns as $fromSiteID => $fromSiteIDPatterns) {
         $fromDB  =& Jms2WinFactory::getMultiSitesDBO( $fromSiteID);
         
   	   $errors = MultisitesDatabase::copyDbTablePatterns( $fromDB, $toDB, $fromSiteIDPatterns, false, $overwrite);
   	   if ( !empty( $errors)) {
   	      return $errors;
   	   }
      }
      
      // ------- Intall ------
      // Install the options (table & share)
      foreach( $installOptions as $fromSiteID => $values) {
         $fromDB = & Jms2WinFactory::getMultiSitesDBO( $fromSiteID);
         $errors = array();
   	   foreach( $installOptions[$fromSiteID] as $option => $overwrite) {
      	   // Depending on the extension (option), also insert entry in the "table of content"
      	   $results = MultisitesDatabase::installNewExtension( $fromDB, $toDB, $option, $overwrite);
      	   $errors  = array_merge( $errors, $results);
   	   }
   	}
      
      return $errors;
	}

} // End class
