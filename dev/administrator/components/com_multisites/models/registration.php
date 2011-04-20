<?php
/**
 * @file       registration.php
 * @version    1.0.14
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
 * - 27-JUN-2008 V1.0.0 : File creation
 * - 26-JUL-2008 V1.0.1 : Add Joomla 1.0.x compatibility.
 * - 30-AUG-2008 V1.0.2 : Add Joomla 1.0.x compatibility.
 * - 11-SEP-2008 V1.0.3 : Add copy detection to regenerated a product id.
 * - 21-SEP-2008 V1.0.4 : Add compatibility with osCommerce
 * - 18-OCT-2008 V1.0.5 : Add customisable default URL with usage of registration_inc.php file
 * - 22-DEC-2008 V1.0.6 : Add alternate solution to the registration when product_id is empty.
 *                        Also retry to get a product-ID in case where the registration return a "missing info".
 *                        This may be due to invalid product-id.
 * - 23-DEC-2008 V1.0.7 : Remove the trailing space into the resuce product ID
 * - 04-JAN-2009 V1.0.8 : Force resend all the registration information when the communication layer
 *                        fail to update the JMS version and Joomla Version.
 * - 14-MAR-2009 V1.0.9 : Fix problem in RSA decrypt linked to a bug in the PHP floor function in PHP 5.2.5
 *                        that return unpredictable result.
 *                        Implement specific multiply function and modulus function to work on big number.
 *                        Also remove the usage of floor function.
 *                        Bug in PHP 5.2.5
 *                          $result = floor( 144623676 / 10);
 *                          echo "[$result] = floor( 144623676 / 10)<br/>";
 *                        The expected result is "14462367"
 * - 02-AUG-2009 V1.0.10 : Add the possibility to get the latest version of a product.
 * - 05-DEC-2009 V1.0.11 : Add Joomla 1.6 alpha 2 compatibility.
 * - 17-JAN-2010 V1.0.12 : Add product_id information in the "ads" protocol..
 * - 26-APR-2010 V1.0.13 : Add PHP 5.3 compatibility (remove split function)
 * - 09-JUN-2010 V1.0.14 : Add with Joomla 1.6
 */

// Check to ensure this file is included in Joomla!
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC') && !defined( '_EDWIN2WIN_') ) {
	die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
}

if ( !defined( 'DS')) define('DS', DIRECTORY_SEPARATOR);

// If Joomla 1.5.x
if ( function_exists('jimport')) {
   jimport( 'joomla.application.component.model' );
}
// Else Joomla 1.0.x, osCommerce
else {
   // declare a fake model
   class JModel { var $fake= true; }
}

if ( !class_exists( 'HTTP2Win')) {
   if ( !defined('_EDWIN2WIN_'))    { define('_EDWIN2WIN_', true); }
   if ( file_exists( dirname( __FILE__) .DS. 'http.php')) {
      @include( dirname( __FILE__) .DS. 'http.php');
   }
   else {
      @include( dirname( __FILE__) .DS. '..' .DS. 'classes' .DS. 'http.php');
   }
}


// ===========================================================
//             Edwin2WinModelRegistration class
// ===========================================================
class Edwin2WinModelRegistration extends JModel
{

   //------------ getURL ---------------
   /**
    * @brief Return the URL in charge to manage the registration.
    *
    */
   function &getURL()
   {
		static $instance;

		if (!isset( $instance )) {
		   $filename = dirname( __FILE__) .DS. 'registration_inc.php';
		   @include_once( $filename);
         if ( defined( 'EDWIN2WIN_REGISTRATION_URL')) {
            $instance = EDWIN2WIN_REGISTRATION_URL;
         }
         else {
            $instance = 'http://www.2win.lu/index.php';
         }
		}
		
		return $instance;
   }

   //------------ setURL ---------------
   /**
    * @brief Set the new URL in charge to manage the registration
    */
   function setURL( $newUrl)
   {
      $url =& Edwin2WinModelRegistration::getURL();
      $url = $newUrl;
   }


   //------------ _getExtVers ---------------
   /**
    * @brief Return the local extension version.
    *
    */
   function &_getExtVers()
   {
		static $instance;

		if (!isset( $instance )) {
			$instance = '';
		}
		
		return $instance;
   }

   //------------ _getExtName ---------------
   /**
    * @brief Return the local extension name.
    *
    */
   function &_getExtName()
   {
		static $instance;

		if (!isset( $instance )) {
			$instance = '';
		}
		
		return $instance;
   }

   //------------ setURL ---------------
   /**
    * @brief Set the new URL in charge to manage the registration
    */
   function setExtensionInfo( $newName, $newVersion)
   {
      $name =& Edwin2WinModelRegistration::_getExtName();
      $name = $newName;

      $vers =& Edwin2WinModelRegistration::_getExtVers();
      $vers = $newVersion;
   }



   //------------ _getRegInfo_Filename ---------------
   function _getRegInfo_Filename()
   {
      return dirname(__FILE__).DS.'info'.DS.'data.php';
   }
   

   //------------ writeRegistrationInfo ---------------
   function writeRegistrationInfo( $values)
   {
      $filename = Edwin2WinModelRegistration::_getRegInfo_Filename();
      $dir = dirname( $filename);
      // If Joomla 1.5
      if ( function_exists('jimport')) {
         jimport('joomla.filesystem.folder');
         if ( !JFolder::exists( $dir)) {
            JFolder::create( $dir, 0755);
         }
      }
      else {
         if ( !is_dir( $dir)) {
            mkdir( $dir, 0755);
         }
      }

		$config = "<?php\n";
      $config .= "if ( !defined( '_JEXEC') && !defined( '_VALID_MOS') && !defined( '_EDWIN2WIN_')) die( 'Restricted access' ); \n\n";
		$config .= "\$data = array( ";
		$sep='';
	   foreach( $values as $key => $value) {
   		$config .= $sep . "'$key' => '$value'" ;
   		$sep = ",\n               ";
	   }
		$config .= ");\n";
		$config .= "?>";
		
      // If Joomla 1.5
      if ( function_exists('jimport')) {
         jimport('joomla.filesystem.file');
         return JFile::write( $filename, $config);
      }
      else {
   	   $fp = fopen( $filename, "w");
   	   if ( $fp)
   	   {
   			fputs($fp, $config, strlen($config));
   			fclose ($fp);
   			return true;
   	   }
      }

	   return false;
   }


   //------------ getRegistrationInfo ---------------
   function getRegistrationInfo( $prev_product_id = null)
   {
      $data = array();
      $filename = Edwin2WinModelRegistration::_getRegInfo_Filename();
      if ( !file_exists($filename)) {
         // Try to create the file with a product ID only
         Edwin2WinModelRegistration::_createRegistrationInfo( $prev_product_id);
      }
      // If registration file still does not exists (event when tried to create it);
      if ( !file_exists($filename)) {
         // Return an empty array
         return $data;
      }
      
      // Otherwise, try to open it and retreive the data it contains.
      @include( $filename);
      if ( !empty( $data) && !isset($data['dir'])) {
          $data['dir'] = dirname( __FILE__);
      }
      // If the product ID is NOT found
      if ( !isset( $data['product_id']) || empty($data['product_id'])) {
         // Try to get a new product ID
         $data['product_id'] = Edwin2WinModelRegistration::_getProductID( $prev_product_id);
         // If finally we have a product id
         if ( isset($data['product_id']) && !empty($data['product_id'])) {
            // Update the registration info file
            Edwin2WinModelRegistration::writeRegistrationInfo( $data);
            // Reset the data
            $data = array();
            // And reload it - Just to ensure that the registration file is correctly written
            @include( $filename);
         }
      }
      
      return $data;
   }
   
   //------------ _removeFile ---------------
   function _removeFile( $filename)
   {
      // IF Joomla 1.5
      if ( function_exists('jimport')) {
         jimport('joomla.filesystem.file');
   		if ( JFile::exists( $filename)) {
      		JFile::delete( $filename);
   		}
      }
      // IF Joomla 1.0.x
      else {
         if ( file_exists($filename)) {
            unlink( $filename);
         }
      }
   }
   
   //------------ _movedInstall ---------------
   function _movedInstall()
   {
      // Save current registration info
      $data = Edwin2WinModelRegistration::getRegistrationInfo();
      $filename = Edwin2WinModelRegistration::_getRegInfo_Filename();
      Edwin2WinModelRegistration::_removeFile( $filename);
      
      // Rebuild the registration information with the previous 
      $cur_product_id = isset( $data['product_id']) 
                      ? $data['product_id']
                      : null;
      Edwin2WinModelRegistration::getRegistrationInfo( $cur_product_id);
   }

   //------------ _sendUpdateVersion ---------------
   function _sendUpdateVersion( $product_key, $version)
   {
      $vars = array( 'option'          => 'com_pay2win',
                     'task'            => 'updproductvers',
                     'product_key'     => $product_key,
                     'productversion'  => $version
                   );
      
      $data = '';
      $url =& Edwin2WinModelRegistration::getURL();
      $result = HTTP2Win::request( $url, $vars);
      if ( $result === false) {
      }
      else {
         $status = HTTP2Win::getLastHttpCode();
         // If HTTP OK
         if ( $status == '200') {
            $data =& HTTP2Win::getLastData();
            return $data;
         }
      }

      // Return rescue ID
      return null;
   }

   //------------ _sendUpdateJoomlaVersion ---------------
   function _sendUpdateJoomlaVersion( $product_key, $jvers)
   {
      $vars = array( 'option'          => 'com_pay2win',
                     'task'            => 'updjoomlavers',
                     'product_key'     => $product_key,
                     'joomlaversion'   => $jvers
                   );
      
      $data = '';
      $url =& Edwin2WinModelRegistration::getURL();
      $result = HTTP2Win::request( $url, $vars);
      if ( $result === false) {
      }
      else {
         $status = HTTP2Win::getLastHttpCode();
         // If HTTP OK
         if ( $status == '200') {
            $data =& HTTP2Win::getLastData();
            return $data;
         }
      }

      // Return rescue ID
      return null;
   }


   //------------ getForceRegistration ---------------
   /**
    * @brief Return the flag that indicate if a full registration information must be sent to the server.
    * It is used in case where the communication layer does not work due to the security.
    * In this case, resend all the information with the registration button.
    *
    */
   function &getForceRegistration()
   {
		static $instance;

		if (!isset( $instance )) {
			$instance = false;
		}
		
		return $instance;
   }

   //------------ setForceRegistration ---------------
   /**
    * @brief Set the flag to force resend full registration information
    */
   function setForceRegistration( $newValue)
   {
      $value = &Edwin2WinModelRegistration::getForceRegistration();
      $value = $newValue;
		
		return $value;
   }

   //------------ _isRegistered ---------------
   /**
    * @brief Check if the component is registered
    */
   function _isRegistered()
   {
      $data = Edwin2WinModelRegistration::getRegistrationInfo();
      if ( !isset($data['dir'])
        || (isset($data['dir']) && $data['dir'] != dirname( __FILE__))) { 
         Edwin2WinModelRegistration::_movedInstall();
         return false;
      }
         
      if ( isset($data['product_id']))    { $product_id = $data['product_id'];}
      else                                { $product_id = ''; }
      if ( isset($data['product_key']))   { $product_key = $data['product_key'];}
      else                                { $product_key = ''; }
      
      if ( !empty( $product_id) && !empty( $product_key)) {
         $decoded = RSA::decrypt( $product_key, 7877, 56360411);
         if ( $decoded == $product_id) {
            $rc = true;
            Edwin2WinModelRegistration::setForceRegistration( false);
            
            // When the product is registered, check if the version numher is the same
            // Otherwise, update the registration info with the new version number
            // This help to communicate the maintenance distribution
            if ( isset($data['product_version']))  { $reg_version = $data['product_version'];}
            else                                   { $reg_version = ''; }
            $cur_version = Edwin2WinModelRegistration::getExtensionVersion();
            if ( $cur_version != $reg_version) {
               // Send the new Version number
               $reply = Edwin2WinModelRegistration::_sendUpdateVersion( $product_key, $cur_version);
               if ( !empty( $reply) && $reply == '[OK]') {
                  // Register the version locally
                  $data['product_version'] = $cur_version;
                  Edwin2WinModelRegistration::writeRegistrationInfo( $data);
               }
               else {
                  Edwin2WinModelRegistration::setForceRegistration( true);
                  $rc = false;
               }
            }

            // Idem with Joomla Version
            if ( isset($data['joomla_version']))  { $reg_version = $data['joomla_version'];}
            else                                  { $reg_version = ''; }
            $cur_version = Edwin2WinModelRegistration::getJoomlaVersion();
            if ( $cur_version != $reg_version) {
               // Send the new Version number
               $reply = Edwin2WinModelRegistration::_sendUpdateJoomlaVersion( $product_key, $cur_version);
               if ( !empty( $reply) && $reply == '[OK]') {
                  // Register the version locally
                  $data['joomla_version'] = $cur_version;
                  Edwin2WinModelRegistration::writeRegistrationInfo( $data);
               }
               else {
                  Edwin2WinModelRegistration::setForceRegistration( true);
                  $rc = false;
               }
            }

            return $rc;
         }
      }
      return false;
   }


   //------------ isRegistered ---------------
   /**
    * @brief On first call, this check if the component is registered.
    *
    */
   function &isRegistered()
   {
		static $instance;

		if (!isset( $instance )) {
			$instance = Edwin2WinModelRegistration::_isRegistered();
		}
		
		return $instance;
   }

   //------------ getProductID ---------------
   function _getProductID( $prev_product_id)
   {
      $name       = Edwin2WinModelRegistration::getExtensionName();
      $version    = Edwin2WinModelRegistration::getExtensionVersion();
      $jvers      = Edwin2WinModelRegistration::getJoomlaVersion();
      $clientInfo = Edwin2WinModelRegistration::getClientInfo();
      $vars = array( 'option'          => 'com_pay2win',
                     'task'            => 'getproductid',
                     'productname'     => $name,
                     'productversion'  => $version,
                     'joomlaversion'   => $jvers,
                     'clientinfo'      => $clientInfo);
      if ( !empty( $prev_product_id)) {
         $vars['prevproductid'] = $prev_product_id;
      }
      
      $data = '';
      $url =& Edwin2WinModelRegistration::getURL();
      if ( empty( $url)) {
         return false;
      }
      $result = HTTP2Win::request( $url, $vars);
      if ( $result === false) {
      }
      else {
         $status = HTTP2Win::getLastHttpCode();
         // If HTTP OK
         if ( $status == '200') {
            $data =& HTTP2Win::getLastData();
            return $data;
         }
      }

      // Return rescue ID
      return '1-2WIN-V4H-1PZB-T8B-CE8';
   }

   //------------ generateProductID ---------------
   function generateProductID( $prev_product_id)
   {
      $values = array();
      $values['product_id'] = Edwin2WinModelRegistration::_getProductID( $prev_product_id);
      if ( isset( $values['product_id'])) {
         $values['product_version'] = Edwin2WinModelRegistration::getExtensionVersion();
         $values['joomla_version']  = Edwin2WinModelRegistration::getJoomlaVersion();
         $values['dir']  = dirname( __FILE__);
         Edwin2WinModelRegistration::writeRegistrationInfo( $values);
      }
   }

   //------------ _createRegistrationInfo ---------------
   function _createRegistrationInfo( $prev_product_id)
   {
      Edwin2WinModelRegistration::generateProductID( $prev_product_id);
   }
   
   
   //------------ registerInfo ---------------
   /**
    * @brief Check the input values and when OK, update the regsitration info
    */
   function registerInfo( $inputValues)
   {
      // If the status is present and the product_key is present
      if ( isset( $inputValues['status'])      && !empty($inputValues['status'])
        && isset( $inputValues['product_key']) && !empty( $inputValues['product_key'])
         )
      {
         // If the status is OK
         if ( $inputValues['status'] == 'OK') {
            // update the registration info with the product_key
            $data = Edwin2WinModelRegistration::getRegistrationInfo();
            // If there is no registration info or there is a new product ID that is different of the current one
            if ( empty($data)
              || (!empty( $inputValues['product_id']) && $data['product_id'] != $inputValues['product_id'])) 
            {
               if ( !empty( $inputValues['product_id']) ) {
                  $data['product_id']      = $inputValues['product_id'];
                  $data['product_version'] = Edwin2WinModelRegistration::getExtensionVersion();
                  $data['joomla_version']  = Edwin2WinModelRegistration::getJoomlaVersion();
                  $data['dir']             = dirname( __FILE__);
               }
               else {
                  $this->setError( JText::_('Unable to retreive the registration information'));
                  return false;
               }
            }
            $data['product_key'] = $inputValues['product_key'];
            if ( !Edwin2WinModelRegistration::writeRegistrationInfo( $data)) {
               $this->setError( JText::_('Unable to update the registration information'));
               return false;
            }
            
            // Update the registered instance
            $instance =& Edwin2WinModelRegistration::isRegistered();
   			$instance =  Edwin2WinModelRegistration::_isRegistered();
   			if ( $instance) {
   			   return true;
   			}
   			
            $this->setError( JText::_('Invalid product key received! Please contact your distributor or re-saler.'));
   			return false;
         }
      }
      
      
      // In case of error, modify the Directory path to force request a new product ID.
      $data = Edwin2WinModelRegistration::getRegistrationInfo();
      $data['dir'] = '-retry-';
      if ( !Edwin2WinModelRegistration::writeRegistrationInfo( $data)) {}
      else {
         // Update the registered instance and request a new product ID
         $instance =& Edwin2WinModelRegistration::isRegistered();
   		$instance =  Edwin2WinModelRegistration::_isRegistered();
      }
      
      $this->setError( JText::_('Missing registered information. Retry and if the problem continue, contact the support'));
      return false;
   }

   //------------ getClientInfo ---------------
   /**
    * @brief Return the client information
    */
   function &getClientInfo()
   {
      $data = array();
      
      $host =  (isset($_SERVER["HTTP_HOST"])) ? $_SERVER["HTTP_HOST"] : '';
         
      // If Joomla 1.5
      if ( class_exists( 'JFactory')) {
         $user = JFactory::getUser();
         $name  = $user->name;
         $email = $user->email;
      }
      // If Joomla 1.0.x
      else if ( class_exists( 'joomlaVersion')){
         global $my, $mosConfig_mailfrom;
         $name  = $my->username;
         $email = $mosConfig_mailfrom;
      }
      // If osCommerce
      else if ( defined( 'STORE_OWNER_EMAIL_ADDRESS')) {
         $name  = STORE_OWNER;
         $email = STORE_OWNER_EMAIL_ADDRESS;
      }
      $msg = "<root>"
           . "<host>$host</host>"
           . "<name>$name</name>"
           . "<email>$email</email>"
           . "</root>";
      
      // $encoded = RSA::encrypt( $msg, 7877, 56360411);
      $encoded = '[B64]'.base64_encode($msg);
      
      return $encoded;
   }


   //------------ getExtensionName ---------------
   /**
    * As this view can be called from the installer, we can not use $option to retreive the extension name.
    * This routine will use the directory name to retreive the extension name.
    */
	function getExtensionName()
	{
	   $extName = Edwin2WinModelRegistration::_getExtName();
	   if ( !empty( $extName)) {
	      return $extName;
	   }
      // If osCommerce
      if ( defined( 'DIR_WS_INCLUDES')) {
   	   $dir = dirname( __FILE__ );
         $parts = explode( DS, $dir);
         $modulename = $parts[count($parts)-1];
         return $modulename;
      }
	   
	   // If Joomla
	   $dir = dirname( __FILE__ );
      $parts = explode( DS, $dir);
      
      // Search for the directory name called 'components'
      $previousName = '';
      for ( $i=count($parts)-1; $i>=0; $i--) {
         if ( $parts[$i] == 'components' || $parts[$i] == 'modules') {
            if ( !empty($previousName)) {
               return $previousName;
            }
            break;
         }
         $previousName = $parts[$i];
      }
      
      // Resuce: Return the current component name.
   	$option = JRequest::getCmd('option');
      return $option;
	}


   //------------ getExtensionPath ---------------
   /**
    * As this view can be called from the installer, we can not use $option to retreive the extension path.
    * This routine will use the directory name to retreive the extension path.
    */
	function getExtensionPath()
	{
	   $dir = dirname( __FILE__ );
      $parts = explode( DS, $dir);
      
      // Search for the directory name called 'components'
      $path = '';
      for ( $i=count($parts)-1; $i>=0; $i--) {
         if ( $parts[$i] == 'components' || $parts[$i] == 'modules') {
            if ( !empty($path)) {
               return $path;
            }
            break;
         }
         $path = implode( DS, $parts);
         array_pop( $parts );
      }
      
      // ERROR, path not found.
      return null;
	}


   //------------ getExtensionVersion ---------------
   /**
    * As this function can be called from the installer, we can not use $option to retreive the extension path.
    * This routine will use the directory name to retreive the extension name.
    * When the path of the component is found, the manfiest (install.xml) is loaded to retreive the version number
    */
	function getExtensionVersion()
	{
	   $version = "unknown";

      // If osCommerce
      if ( defined( 'DIR_WS_INCLUDES')) {
         $vers = Edwin2WinModelRegistration::_getExtVers();
         if ( !empty( $vers)) {
            $version = $vers;
         }
         else {
      	   $dir = dirname( __FILE__ );
            $parts = explode( DS, $dir);
            $modulename = $parts[count($parts)-1];
            if (method_exists( $modulename, 'getVersion')) {
               $fn = $modulename . '::getVersion';
               $version = $fn();
            }
         }
         
         return $version;
      }
      
      // If Joomla

	   $path     = Edwin2WinModelRegistration::getExtensionPath();
	   $filename = $path .DS. 'install.xml';
      // If Joomla 1.5 or 1.6
      if ( function_exists('jimport')) {
         // If the install.xml does not exists
         if ( !file_exists( $filename)) {
            // Assume this is a Joomla 1.6 manifest file
            $filename = $path .DS. 'extension.xml';
         }
         
   	   jimport( 'joomla.application.helper');
   		$data = null;
   		if ( file_exists( $filename)) {
   		   $data = JApplicationHelper::parseXMLInstallFile($filename);
   		}
   		if ( !empty( $data)) {
   		   // If the version is present
   		   if (isset($data['version']) && !empty($data['version'])) {
   		      $version = $data['version'];
   		   }
   		}
   		else {
      	   // When this function is called during the installation, then the install.xml files is not yet present
      	   // Therefore, use the version of the manifest files that is stored in a temporary directory and collected
      	   // during the installation (see install.xxx.php)
      	   if ( isset( $GLOBALS['installManifestVersion'])) {
      	      return $GLOBALS['installManifestVersion'];
      	   }
   		}
      }
      // If Joomla 1.0.x
      else if ( class_exists( 'DOMIT_Lite_Document')){
			// Read the file to see if it's a valid component XML file
			$xmlDoc = new DOMIT_Lite_Document();
			$xmlDoc->resolveErrors( true );

			// If the Manifest files is present and can be read
			if ( file_exists( $filename) && $xmlDoc->loadXML( $filename, false, true )) {
			   // Retreive the version number
   			$root    = &$xmlDoc->documentElement;
   			$element = &$root->getElementsByPath('version', 1);
   			$version = $element ? $element->getText() : '';
			}
			else {
      	   // When this function is called during the installation, then the install.xml files is not yet present
      	   // Therefore, use the version of the manifest files that is stored in a temporary directory and collected
      	   // during the installation (see install.xxx.php)
      	   if ( isset( $GLOBALS['installManifestVersion'])) {
      	      return $GLOBALS['installManifestVersion'];
      	   }
			}
      }
	   
		return $version;
	}


   //------------ getLatestVersion ---------------
   /**
    * Retreive the latest version number corresponding to the extension and its version/release number.
    * In fact, it retreive the latest build number.
    */
   function getLatestVersion()
   {
      $name       = Edwin2WinModelRegistration::getExtensionName();
      $version    = Edwin2WinModelRegistration::getExtensionVersion();
      
      $vers = explode( '.', $version);
      if ( count( $vers) >= 2) {
         $product = $name
                  . '_v' . $vers[0] .'_'. $vers[1];
      }
      else {
         $product = $name;
      }
      
      $url = "http://update.jms2win.com/latestversion_$product.xml";
      $data = '';
      
      $results = array();
      
      //try to connect via cURL
      if(function_exists('curl_init') && function_exists('curl_exec')) {
      	$ch = @curl_init();
      	
      	@curl_setopt($ch, CURLOPT_URL, $url);
      	@curl_setopt($ch, CURLOPT_HEADER, 0);
      	//http code is greater than or equal to 300 ->fail
      	@curl_setopt($ch, CURLOPT_FAILONERROR, 1);
      	@curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      	//timeout of 5s just in case
      	@curl_setopt($ch, CURLOPT_TIMEOUT, 5);
      				
      	$data = @curl_exec($ch);
      				
      	@curl_close($ch);
      }
      
      //try to connect via fsockopen
      if(function_exists('fsockopen') && $data == '') {
      
      	$errno = 0;
      	$errstr = '';
      
      	//timeout handling: 5s for the socket and 5s for the stream = 10s
      	$fsock = @fsockopen("update.jms2win.com", 80, $errno, $errstr, 5);
      
      	if ($fsock) {
      		@fputs($fsock, "GET /latestversion_$product.xml HTTP/1.1\r\n");
      		@fputs($fsock, "HOST: update.jms2win.com\r\n");
      		@fputs($fsock, "Connection: close\r\n\r\n");
        
      		//force stream timeout...
      		@stream_set_blocking($fsock, 1);
      		@stream_set_timeout($fsock, 5);
      		 
      		$get_info = false;
      		while (!@feof($fsock))
      		{
      			if ($get_info)
      			{
      				$data .= @fread($fsock, 1024);
      			}
      			else
      			{
      				if (@fgets($fsock, 1024) == "\r\n")
      				{
      					$get_info = true;
      				}
      			}
      		}        	
      		@fclose($fsock);
      		
      		//need to check data cause http error codes aren't supported here
      		if(!strstr($data, '<?xml version="1.0" encoding="utf-8"?><update>')) {
      			$data = '';
      		}
      	}
      }
      
      //try to connect via fopen
      if (function_exists('fopen') && ini_get('allow_url_fopen') && $data == '') {
      
      	//set socket timeout
      	ini_set('default_socket_timeout', 5);
      	
      	$handle = @fopen ($url, 'r');
      	
      	//set stream timeout
      	@stream_set_blocking($handle, 1);
      	@stream_set_timeout($handle, 5);
      	
      	$data	= @fread($handle, 1000);
      	
      	@fclose($handle);
      }
      				
      // Fill the result
      if( $data && strstr($data, '<?xml version="1.0" encoding="utf-8"?><update>') ) {
      	$xml = & JFactory::getXMLparser('Simple');
      	$xml->loadString($data);
      	
      	foreach( $xml->document as $key => $value) {
      	   if ( substr( $key, 0, 1) == '_') {}
      	   else {
      	      $node            = & $xml->document->$key;
            	$results[$key]   = & $node[0]->data();
      	   }
      	}
      }
      
      return $results;
   }




   //------------ getJoomlaVersion ---------------
   /**
    * @return Return the Joomla Version
    */
	function getJoomlaVersion()
	{
	   $joomlaversion = '';
	   
		// If Joomla 1.5
		if ( class_exists( 'JVersion')) {
   		$version       = new JVersion();
   		$joomlaversion = $version->getShortVersion();
		}
		// Joomla 1.0.x
		else if ( class_exists( 'joomlaVersion')) {
   		$version       = new joomlaVersion();
   		$joomlaversion = $version->getShortVersion();
		}
		// If osCommerce
		else if ( defined( 'DIR_WS_INCLUDES') && defined( 'PROJECT_VERSION')) {
         $joomlaversion = str_replace("osCommerce Online Merchant", "osc", PROJECT_VERSION);
		}
		return $joomlaversion;
	}

   //------------ _getAds ---------------
   /**
    * @brief Call the Edwin2Win website to retreive the code corresponding to this component
    *
    */
   function _getAds()
   {
      $name       = Edwin2WinModelRegistration::getExtensionName();
      $version    = Edwin2WinModelRegistration::getExtensionVersion();
      $jvers      = Edwin2WinModelRegistration::getJoomlaVersion();
      $clientInfo = Edwin2WinModelRegistration::getClientInfo();
      $vars = array( 'productname'     => $name,
                     'productversion'  => $version,
                     'joomlaversion'   => $jvers,
                     'clientinfo'      => $clientInfo);
      $regInfo    = Edwin2WinModelRegistration::getRegistrationInfo();
		if ( !empty( $regInfo['product_id'])) {
		   $product_id = trim( $regInfo['product_id']);
		   if ( !empty( $product_id)) {
   		   $vars['product_id'] = $product_id;
		   }
		}
      
      $data = '';
      $result = HTTP2Win::request( 'http://tools.2win.lu/ads/index.php', $vars);
      if ( $result === false) {
      }
      else {
         $status = HTTP2Win::getLastHttpCode();
         // If HTTP OK
         if ( $status == '200') {
            $data =& HTTP2Win::getLastData();
            return $data;
         }
      }
      
      // If the Ads code is not found
      if ( empty( $data)) {
         // Just use an hardcoded value to make the promotion of Edwin2Win
         $data = '<a href="http://www.edwin2win.com"><img src="http://tools.2win.lu/ads/images/edwin2win_banner.gif" border="0"></a>';
      }
      return $data;
   }

   //------------ getAds ---------------
   /**
    * @brief Return the Ads code that must be inserted into the View.
    *
    */
   function &getAds()
   {
		static $instance;

		if (!isset( $instance )) {
			$instance = Edwin2WinModelRegistration::_getAds();
		}
		
		return $instance;
   }


} // End class



/* 
*v.1.3 [2 Sep 2002]

9-8-2002: very simple conversion of example to class form <chucks@arizona.edu>

* Rivest/Shamir/Adelman (RSA) compatible functions
* to generate keys and encode/decode plaintext messages.  
* Plaintext must contain only ASCII(32) - ASCII(126) characters.

*Send questions and suggestions to Ilya Rudev <www@polar-lights.com> (Polar Lights Labs)

*most part of code ported from different
*C++, JS and Flash
*RSA examples found in books and in the net :)

*supplied with Hacker Hunter authentication system.
*http://www.polar-lights.com/hackerhunter/

*It is distributed in the hope that it will be useful, but
*WITHOUT ANY WARRANTY; without even the implied warranty of
*MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*See the GNU General Public License for more details.

*With a great thanks to:
*Glenn Haecker <ghaecker@idworld.net>
*Segey Semenov <sergei2002@mail.ru>
*Suivan <ssuuii@gmx.net>
*
* Adapted by Edwin2Win to allow static calls
* Also remove floor statement that has bug on some PHP version (ie. 5.2.5)
* Also use multiply function and another modulus function for big numbers
*/

class RSA {
   /*
   * ENCRYPT function returns
   *, X = M^E (mod N)
   * Please check http://www.ge.kochi-ct.ac.jp/cgi-bin-takagi/calcmodp
   * and Flash5 RSA .fla by R.Vijay <rveejay0@hotmail.com> at
   * http://www.digitalillusion.co.in/lab/rsaencyp.htm
   * It is one of the simplest examples for binary RSA calculations 
   *
   * Each letter in the message is represented as its ASCII code number - 30
   * 3 letters in each block with 1 in the beginning and end.
   * For example string
   *, AAA
   * will become
   *, 13535351 (A = ASCII 65-30 = 35)
   * we can build these blocks because the smalest prime available is 4507
   *, 4507^2 = 20313049 
   * This means that 
   *, 1. Modulo N will always be < 19999991
   *, 2. Letters > ASCII 128 must not occur in plain text message
   */
   
   function encrypt($m, $e, $n) {
   	$asci = array ();
   	for ($i=0; $i<strlen($m); $i+=3) {
   		$tmpasci="1";
   		for ($h=0; $h<3; $h++) {
   			if ($i+$h <strlen($m)) {
   				$tmpstr = ord (substr ($m, $i+$h, 1)) - 30;
   				if (strlen($tmpstr) < 2) {
   					$tmpstr ="0".$tmpstr;
   				}
   			} else {
   				break;
   			}
   			$tmpasci .=$tmpstr;
   		}
   		array_push($asci, $tmpasci."1");
   	}
   
   	//Each number is then encrypted using the RSA formula: block ^E mod N
   	$coded = '';
   	for ($k=0; $k< count ($asci); $k++) {
   		$resultmod = RSA::powmod($asci[$k], $e, $n);
   		$coded .= $resultmod." ";
   	}
   	return trim($coded);
   }
   
   /*Russian Peasant method for exponentiation */
   function powmod($base, $exp, $modulus) {
   	$accum = 1;
   	$i = 0;
   	$basepow2 = $base;
   	while (($exp >> $i)>0) {
   		if ((($exp >> $i) & 1) == 1) {
   			$accum = RSA::modulus( RSA::multiply($accum, $basepow2) , $modulus);
   		}
   		$basepow2 = RSA::modulus( RSA::multiply($basepow2, $basepow2) , $modulus);
   		$i++;
   	}
   	return $accum;
   }
   
   function multiply($a,$b){   
     $a = ''.$a;   
     $b = ''.$b;   
     $b_length = strlen($b);   
     $value = '';
     $temp=0;
     for($i=1;$i<=$b_length;$i++){   
       $b2 = $b[$b_length-$i];   
       $mul = $a*$b2+$temp;   
       $temp = substr( $mul, 0, strlen( $mul)-1);   
       $value = ($mul%10).$value;   
     }   
     $value = $temp.$value;   
     return $value;   
   }  

   function modulus( $g, $m) {
      $rem = 0;
      $div = 0;
      $s = ''.$g;
      for ( $i = 0; $i < strlen( $s); $i++) {
         $d = substr( $s, $i, 1);
         $div = ($rem * 10) + $d;
         $rem = $div % $m;
      }
      return $rem;
   }
   

   /*
   ENCRYPT function returns
   M = X^D (mod N)
   */
   function decrypt($c, $d, $n) {
   	//Strip the blank spaces from the ecrypted text and store it in an array
   	$decryptarray = explode(" ", $c);
   	for ($u=0; $u<count ($decryptarray); $u++) {
   		if ($decryptarray[$u] == "") {
   			array_splice($decryptarray, $u, 1);
   		}
   	}
   	//Each number is then decrypted using the RSA formula: block ^D mod N
   	$deencrypt = '';
   	for ($u=0; $u< count($decryptarray); $u++) {
   		$resultmod = RSA::powmod($decryptarray[$u], $d, $n);
   		//remove leading and trailing '1' digits
   		$deencrypt.= substr ($resultmod,1,strlen($resultmod)-2);
   	}
   	//Each ASCII code number + 30 in the message is represented as its letter
   	$resultd = '';
   	for ($u=0; $u<strlen($deencrypt); $u+=2) {
   	   $c = substr ($deencrypt, $u, 2);
   		$resultd .= chr($c + 30);
   	}

   	return $resultd;
   }


} // End Class
