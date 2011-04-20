<?php
/**
 * @file       layouts.php
 * @version    1.2.20
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2010 Edwin2Win sprlu - all right reserved.
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
 * - V1.2.20 01-FEB-2010: Initial version
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');



// ===========================================================
//             MultisitesModelLayouts class
// ===========================================================
/**
 * @brief Is used to manage the layouts.
 * In particular, it allows retreiving the parameters from a specific layout and that match a specific menu item.
 */
class MultisitesModelLayouts extends JModel
{
	// Private members
	var $_modelName = 'layouts';
	var $_table = null;

	/**
	 * Returns the internal table object
	 * @return JTable
	 */
	function &_getTable()
	{
		if ($this->_table == null) {
			$this->_table =& JTable::getInstance( 'menu');
		}
		return $this->_table;
	}

   //------------ getItem ---------------
   /**
    * @brief Return a menu item record corresponding to the parameter cid[].
    */
	function &getItem()
	{
		static $item;
		if (isset($item)) {
			return $item;
		}

		$table =& $this->_getTable();

		// Load the current item if it has been defined
		$cid = JRequest::getVar( 'cid', array(0), '', 'array' );
		JArrayHelper::toInteger($cid, array(0));
		$table->load($cid[0]);
		
		return $table;
	}



   //------------ getLayoutParams ---------------
   /**
    * @brief return a JParameters containing the parameters of the layout.
    * When there is an error, it can return a string instead of an object JParameter.
    */
	
   function &getLayoutParams( $enteredvalues)
	{
	   $result = '';
      $baseDir = JPATH_SITE .DS. 'components' .DS. 'com_multisites' .DS. 'templates';
	   if ( !empty( $enteredvalues['client'])) {
	      if ( $enteredvalues['client'] == 'admin') {
	         $baseDir = JPATH_COMPONENT_ADMINISTRATOR .DS. 'templates';
	      }
	   }
	   
	   if ( empty( $enteredvalues['layout'])) {
	      $result = JText::_( 'Layout is empty');
	      return $result;
	   }
	   
	   // Check if the layout folder exists
	   $fname = $baseDir .DS. $enteredvalues['layout'];
	   if ( !JFolder::exists( $fname)) {
	      $result = JText::sprintf( 'Layout [%s] does not exists', $enteredvalues['layout']);
	      return $result;
	   }
	   
	   // Check if the layout XML file exists
	   $fname = $baseDir .DS. $enteredvalues['layout'] .DS. 'templateDetails.xml';
	   if ( !JFile::exists( $fname)) {
	      $result = JText::sprintf( 'Layout file [%s] does not exists', $enteredvalues['layout'] .DS. 'templateDetails.xml');
	      return $result;
	   }

		// Read the Layout description
		$xml =& JFactory::getXMLParser('Simple');
		if ($xml->loadFile( $fname)) {
		   $document =& $xml->document;
		   $xmlParams =& $document->getElementByPath('params');
		   
		   if ( $xmlParams === false) {
   	      $result = '';
		      return $result;
		   }
		   else {
      		$item	=& $this->getItem();
      		$params	= new JParameter($item->params);
   			$params->setXML( $xmlParams);
   			return $params;
		   }

		}
      
	   $result = JText::sprintf( 'Unable to parse the layout [%s]', $enteredvalues['layout'] .DS. 'templateDetails.xml');
      return $result;
	}



} // End class
