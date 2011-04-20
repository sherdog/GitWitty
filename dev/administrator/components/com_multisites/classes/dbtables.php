<?php
/**
 * @file       dbtables.php
 * @version    1.2.30
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2009 Edwin2Win sprlu - all right reserved.
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
 * - V1.2.0 30-MAY-2009: Initial version
 * - V1.2.30 02-JUN-2010: Add the possibility to include contributors XML DB Table description
 *                        into the loaded XML. Call the multisites plugin onDBTableLoaded() function
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.filesystem.path');
require_once( dirname( __FILE__) .DS. 'treesearch.php');

// ===========================================================
//            Jms2WinDBTables class
// ===========================================================
/**
 * @brief This is a DBTables XML interface.
 */
class Jms2WinDBTables
{
   var $success         = false;    /**< Flag that is set to true when some information is loaded */
   var $_xml            = null;

   //------------------- Constructor ---------------
   function &getInstance()
   {
		static $instance;

		if (!is_object($instance))
		{
		   $instance = new Jms2WinDBTables();
		}
		
		return $instance;
   }

   //------------------- Constructor ---------------
   function Jms2WinDBTables()
   {
      $this->success  = false;
   }


   //------------ getConfigFilename ---------------
	function getConfigFilename()
	{
	   $filename = dirname( dirname( __FILE__))
	             .DS. 'patches'
	             .DS. 'sharing'
	             .DS. 'dbtables.xml'
	             ;
	   return $filename;
	}

   //------------------- Constructor ---------------
   function getXML()
   {
      return $this->_xml;
   }
   

   //------------------- _computeParents ---------------
   /**
    * @brief Scan all the nodes and save its parent to allow navigate easier in the tree
    */
   function _computeParents( &$node)
   {
      // If this is a table node,
      if ( $node->name() == 'table') {
         // Save the table name pattern in an index
         $name = $node->attributes( 'name');
         $this->_indexTablePatterns->add( $name, $node);
      }
      
      $option = $node->attributes( 'option');
      if ( !empty( $option)) {
         if ( !isset( $this->_extOptions[$option])) {
            $this->_extOptions[$option] = array();
         }
         $this->_extOptions[$option][] = $node;
      }
      
      if ( empty( $node->_children)) {
         return;
      }
		for ($i=count($node->_children)-1;$i>=0;$i--) {
		   $child = & $node->_children[$i];
		   if ( !isset( $child->_parent)) {
   		   $child->_parent = & $node;
		   }
         $this->_computeParents( $child);
	   }
   }


   //------------------- isLoaded ---------------
   function isLoaded()
   {
      if ( isset( $this->_xml)) {
         return true;
      }
      return false;
   }

   //------------------- load ---------------
   /**
    * @brief Load the DBTables configuration.
    */
   function load()
   {
      // If there is already an XML file loaded
      if ( isset( $this->_xml)) {
         $this->success  = true;
   		return $this->success;
      }
      
      $this->success = false;
		$this->_xml    = null;
	   $xmlpath = $this->getConfigFilename();

		// load the configuration
		if ( file_exists($xmlpath))
		{
			$xml =& JFactory::getXMLParser('Simple');
			if ($xml->loadFile($xmlpath)) {
			   // When the XML file is loaded, give the opportunity to a plugin add description inside.
            JPluginHelper::importPlugin('multisites');
      		$mainframe	= &JFactory::getApplication();
            $results = $mainframe->triggerEvent('onDBTableLoaded', array ( & $xml));
            
				$this->_xml =& $xml->document;

				$this->_extOptions = array();
				$this->_indexTablePatterns = new Jms2WinTreeSearch();
				$this->_computeParents( $this->_xml);
				
            $this->success  = true;
			}
		}
		return $this->success;
	}
	
	
   //------------------- getTable ---------------
   /**
    * @brief Get the table definition corresponding to the table pattern.
    */
   function &getTable( $aTablePattern)
   {
      if ( !$this->isLoaded()) {
         $this->load();
      }
      
      return $this->_indexTablePatterns->getKey( $aTablePattern);
   }
	
   //------------------- getMatchingKeys ---------------
   /**
    * @brief Return an array with the different keys that can be used to reach this solution.
    */
   function &getMatchingKeys( $aSolution)
   {
      if ( empty( $aSolution)) {
         return array();
      }
      
      return $this->_indexTablePatterns->getKeyString( $aSolution);
   }

   //------------------- getPath ---------------
   /**
    * @brief Retreive the whole path to arrive to this node.
    * It return an array with all the parent nodes
    */
   function & getPath()
   {
      $path = array();
      $node = $this;
      $path[] = $node;
      while( !empty( $node->_parent)) {
         array_unshift ($path, $node);
         $node = $node->_parent;
      }
      
      return $path;
   }


   //------------------- getPathName ---------------
   /**
    * @brief Retreive the whole path to arrive to this node.
    * It return a string with the concatenation of the names of each nodes
    */
   function getPathName( $sep = '/', $ignoreLeaf = false)
   {
      $pathStr = '';
      $node = $this;
      
      do {
         $name = $node->attributes( 'name');
         if ( empty( $name)) {
            $name = $node->name();
         }
         
         if ( $ignoreLeaf) {
            $ignoreLeaf = false;
         }
         else {
            $pathStr = $sep . $name . $pathStr;
         }

         $node = $node->_parent;
      } while( !empty( $node));
      
      return $pathStr;
   }




   //------------------- _computeParents ---------------
   /**
    * @brief Scan all the nodes and save its parent to allow navigate easier in the tree
    */
   function _collectTable( &$node, &$tables)
   {
      // If this is a table node,
      if ( $node->name() == 'table') {
         $tables[] = $node;
      }
      
      if ( empty( $node->_children)) {
         return;
      }
		for ($i=count($node->_children)-1;$i>=0;$i--) {
		   $child = & $node->_children[$i];
         $this->_collectTable( $child, $tables);
	   }
   }



   //------------------- getTablesInfos ---------------
   /**
    * @brief Retreive all <table> nodes that match the option.
    * It return an array of <table> nodes
    */
   function &getTablesInfos( $option)
   {
      $tables = array();
      
      if ( !$this->isLoaded()) {
         $this->load();
      }
      
      if ( isset( $this->_extOptions[$option])) {
         foreach( $this->_extOptions[$option] as $xml) {
            $this->_collectTable( $xml, $tables);
         }
      }
      
      return $tables;
   }

}

