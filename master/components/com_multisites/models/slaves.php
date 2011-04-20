<?php
/**
 * @file       slaves.php
 * @brief      Interface between the front-end and back-end multisites.
 * @version    1.1.8
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008 Edwin2Win sprlu - all right reserved.
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
 * - V1.1.0 11-OCT-2008: File creation
 * - V1.1.8 18-OCT-2008: Add the redirection to the administation createMasterIndex() function.
 *                       This function is required when deleting a slave site.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

jimport('joomla.filesystem.path');
require_once( JPath::clean( JPATH_COMPONENT_ADMINISTRATOR.'/libraries/joomla/application/component/model2win.php'));

require_once( JPath::clean( JPATH_COMPONENT_ADMINISTRATOR.'/classes/site.php'));
require_once( JPath::clean( JPATH_COMPONENT_ADMINISTRATOR.'/models/manage.php'));

// ===========================================================
//             MultisitesModelSlaves class
// ===========================================================
class MultisitesModelSlaves extends JModel2Win
{
   var $id     = null;

	// Private members
	var $_modelName = 'slaves';

	var $_site     = null;
   
   //------------ getUserSlaveSites ---------------
   /**
    * @return the list of slave sites created by the user_id.
    */
   function &getUserSlaveSites( $user_id = null)
   {
      $manage = new MultisitesModelManage();
	   $filters = $this->getState( 'filters');
		$user =& JFactory::getUser();

		// Check if this is a super administrator or an administrator
		$user = JFactory::getUser();
	   $isSuperAdmin = false;
      if ($user->authorize( 'com_multisites', 'edit')) {
   	   $isSuperAdmin = true;
      }

      // If Super Admin or Admin
      if ( $isSuperAdmin) {
         // If can see all the users
      }
      // If registered user
      else {
   		$filters['owner_id'] = $user->id;
      }
	   

	   $manage->setState( 'filters', $filters);
	   
	   $rows = $manage->getSites();
	   $this->_countAll = count( $rows);
      return $rows;
   }

   //------------  getCurrentRecord ---------------
   /**
    * @brief Return a single record Site coresponding to the id. Null when does not exists.
    */
	
   function getCurrentRecord( $site_id=null)
	{
		if ($this->_site == null) {
			$this->_site = new Site();
			$id = JRequest::getString('id');
			if ( !empty( $id)) {
				$this->_site->load($id);
			}
		}
		return $this->_site;
	}

   //------------ getNewRecord ---------------
   /**
    * @brief Return a new record Site.
    */
	
   function getNewRecord()
	{
		if ($this->_site == null) {
			$this->_site = new Site();
		}
		return $this->_site;
	}

   //------------ canDelete ---------------
	/**
	 * Checks if the site can be deleted
	 * @return boolean
	 */
	function canDelete()
	{
      $manage = new MultisitesModelManage();
      $result = $manage->canDelete();
      $err    = $manage->getError();
      if ( !empty( $err)) {
			$this->setError( $err);
      }
      
      return $result;
   }

   //------------ delete ---------------
	/**
	 * Deletes the site directory.
	 * @return boolean
	 */
	function delete()
	{
      $manage = new MultisitesModelManage();
      $result = $manage->delete();
      $err    = $manage->getError();
      if ( !empty( $err)) {
			$this->setError( $err);
      }
      
      return $result;
   }
   
   //------------ getSiteID ---------------
   /**
    * @brief Get a Site ID number based on input values (template, site_prefix, ...)
    */
   function getSiteID( $enteredvalues)
   {
      return MultisitesModelManage::getSiteID( $enteredvalues);
   }
   
   //------------ createMasterIndex ---------------
   /**
    * @brief Create a file containing all the domain name and associated directories.
    * The 'config_multisites.php' files is created to speed-up the multisites normal processing.
    */
   function createMasterIndex()
   {
      $model = new MultisitesModelManage();
      return $model->createMasterIndex();
   }

}
