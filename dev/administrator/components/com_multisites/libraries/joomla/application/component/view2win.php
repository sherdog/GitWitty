<?php
/**
 * @file       view2win.php
 * @brief      Wrapper to JView to add Administrator Toolbar rendering
 * @version    1.2.27
 * @author     Edwin CHERONT     (cheront@edwin2win.com)
 *             Edwin2Win sprlu   (www.edwin2win.com)
 * @copyright  (C) 2008-2010 Edwin2Win sprlu - all right reserved.
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
 * - V1.2.14 05-DEC-2009: Add Joomla 1.6 alpha 2 compatibility.
 * - V1.2.27 23-APR-2010: Add generic "isSuperAdmin" function.
 */


// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

jimport('joomla.application.component.view');

// ===========================================================
//             JView2Win class
// ===========================================================
/**
 * @brief This class extend the Joomla View with functions to allow a front-end use the administrator "toolbar"
 */
if ( !class_exists( 'JView2Win')) {
class JView2Win extends JView
{
   //------------ execute ---------------
	/**
	 * Wrapper to standard Controller Execute and in aim to parse the task 
	 * and extract sub Controller name when present in the task.
	 * A sub Controler name is the word before the dot.
	 *
	 * Syntax:
	 * SubControler.task
	 */
	function renderToolBar()
	{
   	$option = JRequest::getCmd('option');
		
		$mainframe	= &JFactory::getApplication();
      // Display the ToolBar into the template
      jimport('joomla.html.toolbar');
      $bar =& JToolBar::getInstance('toolbar');
      $toolbarContent = $bar->render('toolbar');
		$this->assignRef('toolbarContent',   $toolbarContent);
		
      $toolbarTitle = $mainframe->get('JComponentTitle');
		$this->assignRef('toolbarTitle',   $toolbarTitle);

		$document = & JFactory::getDocument();
		$document->addStyleSheet( JURI::base() . "administrator/components/$option/css/toolbar.css");
      if ( version_compare( JVERSION, '1.6') >= 0) {
         $document->addStyleSheet( JURI::base() . "administrator/components/$option/css/toolbar16.css");
      }
      else {
   		$document->addStyleSheet( JURI::base() . 'administrator/templates/khepri/css/icon.css');
      }
	}
	
	function getTemplateToolbar()
	{
	}

   //------------ assignAds ---------------
   /**
    * @brief Call the registered website to get ads information to display
    */
	function assignAds()
	{
      jimport('joomla.filesystem.file');
	   $ads = '';
      if ( !defined('_EDWIN2WIN_'))    { define('_EDWIN2WIN_', true); }
      if ( JFile::exists( JPATH_COMPONENT.DS.'classes'.DS.'http.php')) {
         require_once( JPATH_COMPONENT.DS.'classes'.DS.'http.php' );
      }
      else {
         if ( JFile::exists( JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'http.php')) {
            require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'http.php' );
         }
      }
      
      if ( JFile::exists( JPATH_COMPONENT.DS.'models'.DS.'registration.php')) {
         require_once( JPATH_COMPONENT.DS.'models'.DS.'registration.php' );
         
      	// Compute Ads
      	$isRegistered =& Edwin2WinModelRegistration::isRegistered();
      	if ( !$isRegistered)    { $ads =& Edwin2WinModelRegistration::getAds(); }
      	else                    { $ads = ''; }
      }
		$this->assignRef('ads', $ads);
	}

   //------------ _getPagination ---------------
   /**
    * @brief Get the pagination based on filters['limitstart'], filters['limit'] and total of records.
    */
	function &_getPagination( &$filters, $total=0)
	{
		jimport('joomla.html.pagination');
		$pagination = new JPagination( $total, $filters['limitstart'], $filters['limit'] );
		return $pagination;
	}

   //------------ isSuperAdmin ---------------
	/**
	 * @brief Check if this is a super administrator
	 */
	function isSuperAdmin()
	{
	   $user          = JFactory::getUser();
	   $isSuperAdmin  = false;
      if ($user->gid == 25) {
   	   $isSuperAdmin = true;
      }
		$this->assign('isSuperAdmin',   $isSuperAdmin);
		return $isSuperAdmin;
	}
	
   //------------ enableSortedHeader ---------------
	function enableSortedHeader()
	{
	   $this->_isSortedHeader = true;
	}
	
   //------------ displayListHeader ---------------
	function displayListHeader( &$rows, $fields = array(), $leadingspaces='')
	{
	   if ( empty( $fields)) {
	      return;
	   }
	   $isSortedHeader = false;
	   if ( !empty( $this->_isSortedHeader)) {
   	   $isSortedHeader = $this->_isSortedHeader;
	   }
	   
	   $isSuperAdmin = $this->isSuperAdmin();
	   
	   $this->_fieldcount = 0;
	   foreach( $fields as $fieldname => $properties) {
	      $show = true;
	      if ( !empty( $properties)) {
	         // If only displayed for a super admin
	         if ( !empty( $properties['isSuperAdmin'])) {
	            // and is not super Admin then hide the field
	            if ( !$isSuperAdmin) {
	               $show = false;
	            }
	         }
	      }
	      if ( $show) {
   	      echo $leadingspaces . '<th class="' .$fieldname. '">';
	         if ( !empty( $properties['input'])) {
	            $input = $properties['input'];
               if ( !empty( $input['type']) && $input['type'] == 'grid.id') {
   	            $nbrec = empty( $rows) ? 0 : count( $rows);
         			echo '<input type="checkbox" name="toggle" value="" onclick="checkAll(' .$nbrec. ');" />';
         		}
         		else {
         		   if ( $isSortedHeader) {
         		      echo JHTML::_('grid.sort', $fieldname, $fieldname, @$this->lists['order_Dir'], @$this->lists['order'] );
         		   }
         		   else {
         	         echo JText::_( $fieldname);
         		   }
         		}
   	      }
   	      else {
      		   if ( $isSortedHeader) {
      		      echo JHTML::_('grid.sort', $fieldname, $fieldname, @$this->lists['order_Dir'], @$this->lists['order'] );
      		   }
      		   else {
      	         echo JText::_( $fieldname);
      		   }
   	      }
   	      
   	      echo '</th>' . "\n";

   	      $this->_fieldcount++;
   	   }
	   }
	}
	
   //------------ getFieldCount ---------------
   /**
    * @brief Return the number of "columns" displayed
    */
	function getFieldCount()
	{
	   return $this->_fieldcount;
	}
	
   //------------ getFieldCount ---------------
	function displayListRows( $rows, $fields = array(), $leadingspaces='')
	{
	   if ( empty( $fields)) {
	      return;
	   }
	   
	   $isSuperAdmin = $this->isSuperAdmin();

      if ( !empty( $this->pagination) && !empty( $this->pagination->limitstart)) {
         $limitstart = $this->pagination->limitstart;
      }
      else {
         $limitstart = 0;
      }
      $i = 0; $k = 0;
      $hiddenField = '';
      foreach( $rows as $row) {
         // Start row
         echo $leadingspaces . '<tr class="row' .$k. '">'."\n";

   	   // Display the colums
   	   foreach( $fields as $fieldname => $properties) {
   	      $show = true;
   	      if ( !empty( $properties)) {
   	         // If only displayed for a super admin
   	         if ( !empty( $properties['isSuperAdmin'])) {
   	            // and is not super Admin then hide the field
   	            if ( !$isSuperAdmin) {
   	               $show = false;
   	            }
   	         }
   	      }
   	      if ( $show) {
      	      echo $leadingspaces .'   <td class="' .$fieldname. '">';
      	      echo $hiddenField; $hiddenField='';
      	     
      	      if ( !empty( $properties['output'])) {
      	         if ( $properties['output'] == 'recno') {
         				echo $limitstart + 1 + $i;
      	         }
      	      }
   	         else if ( !empty( $properties['input'])) {
   	            $input = $properties['input'];
   	            if ( !empty( $input['type']) && $input['type'] == 'grid.id') {
   	               if ( $input['fieldname']) {
   	                  $fieldname = $input['fieldname'];
   	               }
         				echo JHTML::_('grid.id', $i, $row->$fieldname );
   	            }
   	            else {
   	               $sizeStr = '';
      	            if ( !empty( $input['size'])) {
      	               $sizeStr = ' size="' .$input['size']. '"';
      	            }
   	               $maxlengthStr = '';
      	            if ( !empty( $input['maxlength'])) {
      	               $maxlengthStr = ' maxlength="' .$input['maxlength']. '"';
      	            }
   	               $valueStr = '';
      	            if ( !empty( $input['maxlength'])) {
      	               $valueStr = ' value="' .$input['value']. '"';
      	            }
            			echo '<input class="inputbox" type="text" name="' .$fieldname. '[]" id="' .$fieldname.$i. '"' .$sizeStr.$maxlengthStr.$valueStr. '" />';
            		}
   	         }
   	         // Display field
   	         else {
   	            $fieldvalue = '';
         	      if ( !empty( $row->$fieldname)) {
         	         $fieldvalue = $row->$fieldname;
         	      }
         	      if ( !empty( $properties['url'])) {
         	         $url = str_replace( '[_FIELDVALUE_]', $fieldvalue, $properties['url']);
         	         echo '<a href="' .$url. '">' .$fieldvalue. '</a>';
         	      }
         	      else {
         	         echo $fieldvalue;
         	      }
         	      if ( !empty( $properties['addHidden']) && $properties['addHidden']) {
            			echo '<input class="inputbox" type="hidden" name="' .$fieldname. '[]" id="' .$fieldname.$i. '" value="' .$fieldvalue. '" />';
         	      }
         	   }
         	   echo '</td>'."\n";
      	   }
      	   // If is not show but has a hidden field
      	   else {
	            $fieldvalue = '';
      	      if ( !empty( $row->$fieldname)) {
      	         $fieldvalue = $row->$fieldname;
      	      }
      	      $hiddenField = '<input class="inputbox" type="hidden" name="' .$fieldname. '[]" id="' .$fieldname.$i. '" value="' .$fieldvalue. '" />';
      	   }
   	   }
   	   
   	   // If there is still a hidden field to add in the row, then add a "hidden" column with the field.
   	   if ( !empty( $hiddenField)) {
   	      echo '<td style="display:none;">' . $hiddenField . '</td>';
   	   }
         // End row
         echo $leadingspaces . '</tr>';
		   $i++; 
		   $k = 1 - $k;
      }
	}
	
} // End class
} // End not exists
