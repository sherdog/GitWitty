<?php
/**
 * @file       templates.php
 * @version    1.2.0 RC4
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
 * - V1.1.0 01-OCT-2008: Initial version
 * - V1.1.8 24-DEC-2008: Add a parameter in save to allow reset all field before save in aim
 *                       to perform an update of all the record. (This allow to remove fields)
 * - V1.2.0 RC4 10-JUL-2009: Add a check that the template is correctly written.
 *                           Otherwise, set an error message
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );
jimport('joomla.filesystem.file');

require_once( JPATH_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'site.php');
require_once( JPATH_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'template.php');
require_once( JPATH_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'utils.php');

if ( !defined( 'JPATH_MULTISITES')) {
   define( 'JPATH_MULTISITES', JPATH_ROOT.DS.'multisites');
}


// ===========================================================
//             MultisitesModelTemplates class
// ===========================================================
/**
 * @brief Is used to manage the 'Template websites'.
 */
class MultisitesModelTemplates extends JModel
{
	// Private members
	var $_modelName = 'templates';

	var $_template = null;
	var $_countAll = 0;
	
   //------------ getTemplateFilename ---------------
   /**
    * @return Return the template configuration file name.
    */
	function getTemplateFilename()
	{
	   $filename = JPATH_MULTISITES .DS. 'config_templates.php';
	   return $filename;
	}
	
   //------------ getTemplates ---------------
   /**
    * @brief Return a list of Templates.
    *
    * The templates can be filtered on :
    * - DB 'host' server name
    * - DB name
    */
	
   function &getTemplates()
	{
	   // Extract the filtering selection
     	$filters = $this->getState( 'filters');
	   if ( !is_null($filters)) {
	      // If there is a filtering on DB server
	      if ( isset($filters['hosts']) && $filters['host'] != '[unselected]') {
	         $filter_host = $filters['host'];
	      }
	      // If there is a filtering on DB name
	      if ( $filters['db'] != '[unselected]') {
	         $filter_db = $filters['db'];
	      }
	   }
 
	   /* Read the collection of templates */
	   $rows = array();
	   $filename = $this->getTemplateFilename();
	   @include( $filename);
      $loadExtraInfo = false;
	   if ( isset( $templates)) {
	      // If there is a filter
	      if ( !empty( $filter_host) || !empty( $filter_db)) {
	         foreach( $templates as $key => $template) {
      	      $site = new Site();
      	      $site->load( $template['fromSiteID']);
      	      $template['fromHost']    = $site->host;
      	      $template['fromDB']      = $site->db;
      	      $template['fromPrefix']  = $site->dbprefix;
      	      
	            if ( !empty( $filter_host) && !empty( $filter_db)){
	               if ( $template['fromHost'] == $filter_host
	                 && $template['fromDB'] == $filter_db
	                  )
	               {
	                  $rows[$key] = $template;
	               }
	            }
	            if ( !empty( $filter_host) && empty( $filter_db)){
	               if ( $template['fromHost'] == $filter_host)
	               {
	                  $rows[$key] = $template;
	               }
	            }
	            else if ( empty( $filter_host) && !empty( $filter_db)){
	               if ( $template['fromDB'] == $filter_db)
	               {
	                  $rows[$key] = $template;
	               }
	            }
	         }
	      }
	      else {
   	      $rows = $templates;
   	      $loadExtraInfo = true;
	      }
	   }
	   
	   $this->_countAll = count( $rows);

	   // Fill the ID with the key
	   foreach( $rows as $key => $row) {
	      $rows[$key]['id']      = $key;
	   }
	   
	   // If the user request ordering records
	   if ( !is_null($filters)) {
	      if ( !empty( $filters['order'])) {
	         $colname = $filters['order'];
	         $sortedrows = array();
	         $i = 0;
	         foreach( $rows as $row){
	            $colValue = isset( $row[$colname]) ? $row[$colname] : '';
	            $key = $colValue . '.' . substr( "00".strval($i++), -3);
	            $sortedrows[$key] = $row;
	         }
	         
	         // If requested a REVERSE ordering (descending)
	         if ( !empty( $filters['order_Dir']) && $filters['order_Dir'] =='desc') {
	            krsort($sortedrows);
	            $rows = $sortedrows;
	         }
	         // Ascending order
	         else {
	            ksort($sortedrows);
	            $rows = $sortedrows;
	         }
	      }
	   }
	   
	   
	   // If there is a limits specified
	   if ( !is_null($filters)) {
	      // If there is a limit specified (not ALL records)
	      if ( $filters['limit'] > 0) {
      		// slice out elements based on limits
      		$rows = array_slice( $rows, $filters['limitstart'], $filters['limit'] );
	      }
	   }
	   
	   // For each row, compute the additional information (fromDB, fromPrefix)
	   if ( $loadExtraInfo) {
   	   foreach( $rows as $key => $row) {
   	      $site = new Site();
   	      $site->load( $row['fromSiteID']);
   	      $rows[$key]['fromDB']      = $site->db;
   	      $rows[$key]['fromPrefix']  = $site->dbprefix;
   	   }
	   }
	   
	   return $rows;
	}

   //------------ getCountAll ---------------
   /**
    * @return the total number of records.
    */
   function getCountAll()
	{
	   return $this->_countAll;
	}

   //------------ setFilters ---------------
   function setFilters( &$filters)
	{
	   $this->setState( 'filters', $filters);
	}

   //------------ removeFilters ---------------
   function removeFilters()
	{
	   $this->setState( 'filters', null);
	}


   //------------  getCurrentRecord ---------------
   /**
    * @brief Return a single record Template coresponding to the id. Null when does not exists.
    */
	
   function getCurrentRecord()
	{
		if ($this->_template == null) {
			$this->_template = new Jms2WinTemplate();
			if ($id = JRequest::getVar('id', false, '', 'string')) {
				$this->_template->load($id);
			}
		}
		return $this->_template;
	}

   //------------ getNewRecord ---------------
   /**
    * @brief Return a new record template.
    */
	
   function getNewRecord()
	{
		if ($this->_template == null) {
			$this->_template = new Jms2WinTemplate();
		}
		return $this->_template;
	}
	
	
   //------------ write ---------------
	/**
	 * Write the template array into its configuration template file
	 * @return boolean
	 */
	function write( $templates)
	{
	   $filename = $this->getTemplateFilename();
	   
	   // Write the new template file
		$config = "<?php\n";
		$config .= "if( !defined( '_JEXEC' )) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); \n\n";

		$config .= '$templates = array();' 
		        .  "\n";
		foreach( $templates as $key => $values) {
		   $config .= "\$templates['$key'] = array(\n";
		   $leadingSpaces = '                           ';
		   $sep2 = $leadingSpaces;
		   foreach( $values as $key2 => $value) {
	         if ( is_array( $value)) {
         	   $config .= $sep2 . "'$key2' => array( " . MultisitesUtils::CnvArray2Str( $leadingSpaces.'     ', $value) . ")";
	         }
	         else {
   		      $config .= $sep2 . "'$key2' => '" . addslashes($value) ."'";
   		   }
   		   $sep2 = ",\n" . $leadingSpaces;
		   }
   		$config .= ");\n";
		}

      if ( !JFile::write( $filename, $config)) {
   		$this->setError( JText::sprintf( 'TEMPLATE_WRITE_ERR', $filename) );
   		return false;
      }
      return true;
	}
	

   //------------ save ---------------
	/**
	 * Save the template into the template collection and store the result on disk.
	 * @param reset   Flag that indicate if the record must be reset before save (unset the key ID)
	 * @return boolean
	 */
	function save( $enteredvalues, $reset=false)
	{
	   // Read the current template configuration file
	   $templates = array();
	   $filename = $this->getTemplateFilename();
	   @include( $filename);
	   
	   if ( $reset && !empty( $enteredvalues['id'])) {
	      unset( $templates[ $enteredvalues['id']]);
	   }
	   
	   // Update the template content
	   foreach( $enteredvalues as $key => $value) {
	      if ( strstr('*id*isnew*', $key)) {}
	      else {
      	   $templates[ $enteredvalues['id']][$key] = $value;
	      }
	   }
	   
	   ksort( $templates);
	   
	   return $this->write( $templates);
	}



   //------------ canDelete ---------------
	/**
	 * Checks if the template can be deleted
	 * @return boolean
	 */
	function canDelete()
	{
	   // Return TRUE If the template exists
		$template = new Jms2WinTemplate();
		$id = JRequest::getString('id');
		if (!empty( $id)) {
			if ( !$template->load($id)) {
      		$this->setError( JText::_( 'TEMPLATE_NOT_FOUND' ) );
      		return false;
			}
			return true;
		}
	   return false;
	}




   //------------ delete ---------------
	/**
	 * Deletes the template from the template collection.
	 * @return boolean
	 */
	function delete()
	{
		$id = JRequest::getString('id');
		if ( empty( $id)) {
		   return false;
		}

	   // Read the current template configuration file
	   $templates = array();
	   $filename = $this->getTemplateFilename();
	   @include( $filename);
	   
	   // If there is something in the templates collection for this id,
	   if ( isset( $templates[ $id])) {
	      // Delete it
         unset( $templates[ $id]);
         return $this->write( $templates);
	   }

      return true;
	}

} // End class
