<?php
/**
 * @file       settings.php
 * @version    1.1.0
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
 * - V1.1.0 20-OCT-2008: Initial version
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );
jimport('joomla.filesystem.file');

if ( !defined( 'JPATH_MULTISITES_COMPONENT_ADMINISTRATOR')) {
   define( 'JPATH_MULTISITES_COMPONENT_ADMINISTRATOR',
            JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites');
}
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'models' .DS. 'registration.php');


// ===========================================================
//             MultisitesModelSettings class
// ===========================================================
/**
 * @brief Is used to manage the Settings.
 */
class MultisitesModelSettings extends JModel
{
	// Private members
	var $_modelName = 'settings';
	
	// Public members
	var $id              = 'fake';
	var $product_id      = null;
	var $website_count   = null;
	var $website_quota   = null;
	
   //------------ getSettings ---------------
   /**
    * @brief return "this" object with website quota and count.
    */
	
   function &getSettings()
	{
      $this->quota_url  =& Edwin2WinModelRegistration::getURL();
      $regInfo          =  Edwin2WinModelRegistration::getRegistrationInfo();
      if ( empty( $regInfo) || empty( $regInfo['product_id'])) {
	      $this->setError( JText::_( 'You must register the product to access the settings'));
         return false;
      }
      $this->product_id    = $regInfo['product_id'];
      $result = $this->getWebsiteQuota( $this->product_id);
      if ( !empty( $result) && $result !== false && is_array( $result)) {
         $this->website_count = $result['website_count'];
         $this->website_quota = $result['website_quota'];
      }
      else {
         $this->website_count = 0;
         $this->website_quota = 0;
      }
      
	   return $this;
	}


   //------------ getWebsiteQuota ---------------
   function getWebsiteQuota( $product_id)
   {
      // Request a Website ID
      $vars = array( 'option'          => 'com_pay2win',
                     'task'            => 'jms.getWebSiteQuota',
                     'product_id'      => $product_id
                   );
      
      $data = '';
      $url =& Edwin2WinModelRegistration::getURL();
      if ( empty( $url)) {
	      $this->setError( JText::_( 'Unable to know where to get a Website Quota'));
         return false;
      }
      $result = HTTP2Win::request( $url, $vars);
      if ( $result === false) {
	      $this->setError( JText::_( 'The Website Quota cannot be retreived'));
      }
      else {
         $status = HTTP2Win::getLastHttpCode();
         // If HTTP OK
         if ( $status == '200') {
            $data =& HTTP2Win::getLastData();
   	      if ( strncmp( $data, '[OK]', 4) == 0) {
   	         // Retreive the website_id
   	         $arr        = explode( '|', $data);
   	         $result = array();
   	         $result['website_count'] = $arr[1];
   	         $result['website_quota'] = $arr[2];
               return $result;
            }
   	      else if ( strncmp( $data, '[ERR]', 5) == 0) {
   	         // Extract error info
   	         $arr = explode( '|', $data);
   	         $err_level  = $arr[1];
   	         $website_id = $arr[2];
   	         $err_code   = $arr[3];
   	         // Translate the Return Code into a Front-End message
   	         $err_code_key = 'JMS2WIN_ERR_WQ_'.$err_code;
   	         $user_msg = JText::_( $err_code_key);
   	         if ( $user_msg == $err_code_key && !empty($arr[4]) ) {
      	         $msg        = $arr[4];
   	         }
   	         else {
      	         $msg        = $user_msg;
   	         }
   	         if ( !empty( $msg)) {
            		$this->setError( $msg);
   	         }
               // Fatal error
               return array();
            }
            else {
         		$this->setError( "Unexpected reply when getting the Website Quota. Returned data=[".$data."]");
            }
         }
      }
      return false;
   }

} // End class
