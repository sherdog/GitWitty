<?php
/**
 * @file       view.php
 * @version    1.0.9
 * @author     Edwin CHERONT     (cheront@edwin2win.com)
 *             Edwin2Win sprlu   (www.edwin2win.com)
 * @copyright  (C) 2008 Edwin2Win sprlu - all right reserved.
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
 * - V1.0.6 22-DEC-2008: Add alternate solution to the registration when product_id is empty.
 * - V1.0.7 23-DEC-2008: Trim the product ID in case of the 'default product ID' that my have spaces.
 * - V1.0.8 04-JAN-2009: Force resend all the registration information when the communication layer
 *                        fail to update the JMS version and Joomla Version.
 * - V1.2.42 05-NOV-2010: Add compatibility with Joomla 1.6 beta 13
 * - V1.0.9  05-NOV-2010: Just keep independent version number.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.view');

// ===========================================================
//            Edwin2WinViewRegistration class
// ===========================================================
/**
 * @brief Display registration or donation button.
 */
class Edwin2WinViewRegistration extends JView
{
   //------------ _getComponentName ---------------
   /**
    * As this view can be called from the installer, we can not use $option to retreive the component name.
    * This routine will use the directory name to retreive the component name.
    */
	function _getComponentName()
	{
      return $this->get('ExtensionName');
	}
   
   //------------ donateButton ---------------
   /**
    * @brief This display a 'donate' button.
    */
	function donateButton($redirect_url='', $tpl=null)
	{
		$this->setLayout( 'donate');

		$action     = $this->get( 'URL');
		$clientInfo = &$this->get('ClientInfo');

		// If there is no redirect url
		if ( empty($redirect_url)) {
		   // Redirect to this component using the task=registered
		   $redirect_url = JURI::base()."index.php?option=$option&task=donate";
		}
		
		// Assign view variable with will be used by the template
		$this->assignRef('action',       $action);
		$this->assign('message',         JText::_('EDWIN2WIN_DONATION'));
		$this->assign('option',          $this->_getComponentName());
		$this->assignRef('clientInfo',   $clientInfo);
		$this->assign('btnToolTipMsg',   JText::_('EDWIN2WIN_DONATION_BTN_TTMSG'));
		$this->assign('btnAltMsg',       JText::_('EDWIN2WIN_DONATION_BTN_ALTMSG'));

		JHTML::_('behavior.tooltip');

		// Display the template
		parent::display($tpl);
	}
	
   //------------ donate ---------------
	function donate($tpl=null)
	{
	}
	
   //------------ registrationButton ---------------
   /**
    * @brief This display a 'registration' button.
    * @param action The form action that must be called when user press the registration button.
    */
	function registrationButton($redirect_url='', $tpl=null)
	{
   	$option = JRequest::getCmd('option');
		$this->setLayout( 'registration');

		$action           =  $this->get( 'URL');
		$clientinfo       = &$this->get('ClientInfo');
      $productname      = $this->get('ExtensionName');
      $productversion   = $this->get('ExtensionVersion');
      $joomlaversion    = $this->get('JoomlaVersion');
		$regInfo          = &$this->get('RegistrationInfo');
		if ( Edwin2WinModelRegistration::getForceRegistration()) {
		   $product_id = '';
		}
		else if ( isset($regInfo['product_id'])) {
		   $product_id = trim( $regInfo['product_id']);
		}
		else {
		   $product_id = '';
		}
		
		// If there is no redirect url
		if ( empty($redirect_url)) {
		   // Redirect to this component using the task=registered
		   $redirect_url = JURI::base()."index.php?option=$option&task=registered";
		}
		
		// Assign view variable with will be used by the template
		$this->assignRef('action',          $action);
		$this->assign('message',            JText::_('EDWIN2WIN_REGISTRATION'));
		$this->assign('option',             $this->_getComponentName());
		$this->assignRef('clientinfo',      $clientinfo);
		$this->assignRef('productname',     $productname);
		$this->assignRef('productversion',  $productversion);
		$this->assignRef('joomlaversion',   $joomlaversion);
		$this->assignRef('regInfo',         $regInfo);
		$this->assignRef('product_id',      $product_id);
		$this->assignRef('redirect_url',    $redirect_url);
		$this->assign('btnToolTipMsg',      JText::_('EDWIN2WIN_REGISTRATION_BTN_TTMSG'));
		$this->assign('btnAltMsg',          JText::_('EDWIN2WIN_REGISTRATION_BTN_ALTMSG'));

		JHTML::_('behavior.tooltip');
		
		// Display the template
		parent::display($tpl);
	}


   //------------ registered ---------------
	function registered($displayForm=true,$tpl=null)
	{
		// Retreive Input Values
		$inputValues['status']        = JRequest::getString( 'status');
		$inputValues['product_key']   = JRequest::getString( 'product_key');
		
		if ( isset( $_REQUEST['product_id'])) {
   		$inputValues['product_id'] = JRequest::getString( 'product_id');
		}
		
		// Process the values
		$model = $this->getModel();
		$isOK  = $model->registerInfo( $inputValues);
		$error = $model->getError();
		
		if ( $isOK) {
		   $this->setLayout( 'registered_ok');
	      $msg = JText::_('EDWIN2WIN_REGISTERED_OK');
		}
		else {
		   $this->setLayout( 'registered_err');
	      $msg = JText::_('EDWIN2WIN_REGISTERED_ERR')
	           . $error;
		}
		
		if ( $displayForm) {
   		// Assign the template variables
   		$this->assignRef('inputValues',  $inputValues);
   		$this->assign('isOK',            $isOK);
   		$this->assignRef('msg',          $msg);
   		$this->assignRef('error',        $error);
   
   		JHTML::_('behavior.tooltip');
   		
   		// Display the template
   		parent::display($tpl);
   		return null;
		}
		else {
		   return $msg;
		}

	}
} // End class
