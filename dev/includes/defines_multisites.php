<?php
/**
 * @file       defines_multisites.php
 * @brief      Multisite definition.
 *             Single installation, multiple configuration and therefore, multiple database, multiple prefix, ...
 * @version    1.2.47
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  JMS Multisite
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2010 Edwin2Win sprlu - all right reserved.
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
 * - V1.1.0 29-SEP-2008: Add sub-directory parsing and full URI detection (http(s), port, ...)
 * - V1.1.5 21-DEC-2008: Disable the session reading when not on a localhost
 * - V1.1.8 24-DEC-2008: Fix the getCurrentURL when receiving request for some Windows platform
 *                       that may repeat the HTTP_HOST in front of REQUEST_URI.
 * - V1.1.14 28-JAN-2009: Fix the test on localhost detection to avoid using the session that may
 *                        reject the login of the administration when FTP Layer is enabled.
 * - V1.2.06 20-SEP-2009: Add Single Sign In sub-domain processing.
 *                        Process 'cookie_domain[S]' information.
 * - V1.2.21 07-FEB-2010: Add parsing of the port in an URL.
 * - V1.2.27 26-APR-2010: Add a cross-check when working on a localhost to ensure that joomla fix
 *                        is present after they have introduced a bug in joomla 1.5.16.
 * - V1.2.30 02-JUN-2010: Add the following keyword "MultisitesLetterTree::getLetterTreeDir"
 *                        in this comment section to be recognized by the patch definition
 *                        as compatible with the "multisites.php" file.
 * - V1.2.47 01-FEB-2011: Add a potential security fix to filter the "com_install" parameters.
 *                        Add Joomla 1.6 "Install/Uninstall" filtering compatibility.
 */

/**
 * Define the relation between 'host' name present in the header of the page
 * and subdirectory name of the configuration.
 *
 * For example, this allow to assign the URL :
 *    www.domain1.com to directory multisites/domain1/configuration.php
 *    www.domain2.com              multisites/domain2/configuration.php
 */
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

// If the file '/includes/multisites_disable.php' is present, this means that we want to use
// a rescue procedure to restore the access to the administration.
// This can be required when a syntax error appears in a files (example a check for update that corrupt a file)
if ( file_exists( dirname( __FILE__) .DIRECTORY_SEPARATOR. 'multisites_disable.php')) {}
else {
//============= HERE BEGIN THE MULTISITES =================
// define( 'MD_HOST_PARAM', true);

if ( !defined( 'JPATH_MULTISITES')) {
       define( 'JPATH_MULTISITES', JPATH_ROOT.DS.'multisites');
}
if ( !defined( 'JPATH_MUTLISITES_COMPONENT')) {
   define( 'JPATH_MUTLISITES_COMPONENT', JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites');
}

// Don't report error in case where the configuration file is not present
@include( JPATH_MULTISITES .DS. 'config_multisites.php');
@include( dirname( __FILE__) .DS. 'multisites.php');

@include_once( JPATH_MUTLISITES_COMPONENT .DS. 'classes' .DS. 'debug.php');
if ( class_exists( 'Debug2Win')) {
   Debug2Win::enableStandalone();      // Write the log in administrator/components/com_multisites/classes/logs
   Debug2Win::setFileName( 'multisites.log.php');
   // Debug2Win::enableDebug();        // Remove the comment to enable the debugging
}

// ===========================================================
//            MultiSites class
// ===========================================================
class MultiSites
{
   //------------ _debug_start ---------------
   function _debug_start( $msg)
   {
// debug_msg( $msg);
      if ( class_exists( 'Debug2Win')) {
         Debug2Win::debug_start( $msg);
      }
   }
   //------------ _debug ---------------
   function _debug( $msg)
   {
// debug_msg( $msg);
      if ( class_exists( 'Debug2Win')) {
         Debug2Win::debug( $msg);
      }
   }
   //------------ _debug_stop ---------------
   function _debug_stop( $msg)
   {
// debug_msg( $msg);
      if ( class_exists( 'Debug2Win')) {
         Debug2Win::debug_stop( $msg);
      }
   }

   //------------ isLocalHost ---------------
   function isLocalHost()
   {
      $host = isset($_SERVER["HTTP_HOST"])
            ? $_SERVER["HTTP_HOST"]
            : '';
      if ( $host=='localhost' || $host=='127.0.0.1') {
         return true;
      }
      return false;
   }


   //------------ _getCurrentURL ---------------
   /**
    * This code is extracted from JURI::getInstancefunction.
    * The Query String is ignored
    *
    * @note this function must be IDENTICAL in the source
    * - /include/defined_multisites.php
    * - /include/multisites.php
    */
   function _getCurrentURL()
   {
		// Determine if the request was over SSL (HTTPS)
		if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) {
			$https = 's://';
		} else {
			$https = '://';
		}

		/*
		 * Since we are assigning the URI from the server variables, we first need
		 * to determine if we are running on apache or IIS.  If PHP_SELF and REQUEST_URI
		 * are present, we will assume we are running on apache.
		 */
		if (!empty ($_SERVER['PHP_SELF']) && !empty ($_SERVER['REQUEST_URI'])) {

			/*
			 * To build the entire URI we need to prepend the protocol, and the http host
			 * to the URI string.
			 */
			// If the HTTP_HOST is present in front of REQUEST_URI then ignore the HTTP_HOST
			// Otherwise, concatenate the HTTP_HOST and REQUEST_URI
			$host   = rtrim( 'http' . $https . $_SERVER['HTTP_HOST'], '/');
			$len = strlen( $host);
			if ( strlen( $_SERVER['REQUEST_URI']) == $len) {
			   if ( strtolower( $_SERVER['REQUEST_URI']) == strtolower( $host)) {
			   // Ignore the HTTP_HOST
      			$theURI =  $_SERVER['REQUEST_URI'];
			   }
			   else {
					$theURI = 'http' . $https . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			   }
			}
			else if ( strncmp( strtolower( $_SERVER['REQUEST_URI']), strtolower( $host) . '/', $len+1) == 0) {
			   // Ignore the HTTP_HOST
   			$theURI =  $_SERVER['REQUEST_URI'];
			}
			else {
				$theURI = 'http' . $https . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			}

		/*
		 * Since we do not have REQUEST_URI to work with, we will assume we are
		 * running on IIS and will therefore need to work some magic with the SCRIPT_NAME and
		 * QUERY_STRING environment variables.
		 */
		}
		 else
		 {
			// IIS uses the SCRIPT_NAME variable instead of a REQUEST_URI variable... thanks, MS
			$theURI = 'http' . $https . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
		}

		// Now we need to clean what we got since we can't trust the server var
		$theURI = urldecode($theURI);
		$theURI = str_replace('"', '&quot;',$theURI);
		$theURI = str_replace('<', '&lt;',$theURI);
		$theURI = str_replace('>', '&gt;',$theURI);
		$theURI = preg_replace('/eval\((.*)\)/', '', $theURI);
		$theURI = preg_replace('/[\\\"\\\'][\\s]*javascript:(.*)[\\\"\\\']/', '""', $theURI);

		$result = rtrim( strtolower( $theURI), '/');
		return $result;
   }

   //------------ _getHostInfo ---------------
   /**
    * @note this function must be IDENTICAL in the source
    * - /include/defined_multisites.php
    * - /include/multisites.php
    */
   function _getHostInfo( $URL)
   {
		$posBegin = strpos( $URL, '://');
		if ( $posBegin > 0) {
		   $posBegin += 3;
		   $posEnd = strpos( $URL, '/', $posBegin);
		   if ( $posEnd > 0) {
		      $host = substr( $URL, $posBegin, $posEnd-$posBegin);
		   }
		   else {
		      $host = substr( $URL, $posBegin);
		   }
		}
		// If http(s):// is missing in front of the URL,
		else {
		   $posEnd = strpos( $URL, '/');
		   if ( $posEnd > 0) {
		      $host = substr( $URL, 0, $posEnd);
		   }
		   else {
		      $host = $URL;
		   }
		   // Add a http in front of the URL
		   $URL = 'http://' . $URL;
		}
		
		// Compute the port
		// If a port in present in the host
		$posPort = strpos( $host, ':');
		if ( $posPort>0) {
		   $port = substr( $host, $posPort+1);    // Get the port
		   $host = substr( $host, 0, $posPort);   // remove the port from the host
		}
		else {
		   if ( substr( $URL, 0, 6) == 'https:') {
		      $port = 443;
		   }
		   else if ( substr( $URL, 0, 6) == 'http:') {
		      $port = 80;
		   }
		}
		
		
		// Build the results
		$results          = array();
		$results['URL']   = rtrim( strtolower( $URL), '/');                 // The Full url EXCLUDING the parameters
		$results['host']  = strtolower( $host);
		if (!empty( $port)) {
   		$results['port']  = strtolower( $port);
		}
      return $results;
   }


   //------------  getConfigPath ---------------
   /**
    * @brief Compute the configuration path.
    *
    * - When the wrapper is present (MULTISITES_DIR), use its path;
    * - If there is no wrapper, try to use the HTTP_HOST variable
    *   as key of an array providing the associated multi-site directory names;
    * - If the configuration path still not found and development flag is ON, use a the "host" parameter in the URL
    *   as key of an array providing the associated multi-site directory names;
    */
   function getConfigPath()
   {
      global $md_hostalias;
      $configdir = "";

      MultiSites::_debug_start( '>> getConfigPath() - START');
      
      // If the wrapper is present, use its path
      if ( defined( 'MULTISITES_DIR'))  $configdir = MULTISITES_DIR;

      // If there is no multi-site wrapper,
      // Try to use the HTTP_HOST variable to retreive the associated multi-site directory
      if ( strlen( $configdir) <= 0
        && isset($_SERVER["HTTP_HOST"])
        && isset($md_hostalias))
      {
         // The the current URL
         $hostInfo = MultiSites::_getHostInfo( MultiSites::_getCurrentURL());
         $host     = $hostInfo['host'];
      }

      // If the host is not found or empty or does not correspond to any slave sites,
      // try the alternate one using the host parameter in the URL (if this is allowed)
      if ( (!isset($host) || empty($host) || !isset($md_hostalias) || !isset( $md_hostalias[ $host]))
        && ( MultiSites::isLocalHost()
          || (defined( 'MD_HOST_PARAM') && MD_HOST_PARAM)
           )
         )
      {
         // Check it there is a '_host_' parameter in the URL
         if (isset( $hostInfo))  unset( $hostInfo);
         $host = "";
         unset( $host);
         if ( isset( $_REQUEST['_host_']))
         {
            $_host_ = $_REQUEST['_host_'];
            // Save host in the registry
            require_once( JPATH_LIBRARIES.DS.'joomla'.DS.'import.php');
      		jimport( 'joomla.user.user' );
      		$session =& JFactory::getSession();
      		$session->set( 'host', $_host_, 'multisites');

            // Parse the _host_ URL and return the host part
            $hostInfo = MultiSites::_getHostInfo( $_host_);
            $host     = $hostInfo['host'];
            
            MultiSites::_debug( 'Step 2 : _Host = ' . $host . ' Host info: '. var_export($hostInfo, true) );
         }
      }

      // If the host is not found or empty or does not correspond to any slave sites,
      // Try to retreive it from the registry
      if ( (!isset($host) || empty($host) || !isset($md_hostalias) || !isset( $md_hostalias[ $host]))
        && ( MultiSites::isLocalHost()
          || (defined( 'MD_HOST_PARAM') && MD_HOST_PARAM)
           )
         )
      {
         require_once( JPATH_LIBRARIES.DS.'joomla'.DS.'import.php');
   		jimport( 'joomla.user.user' );
   		$canReadSession = true;
   		// If Joomla 1.5.16 or higher
   		if ( version_compare( JVERSION, '1.5.16') >= 0) {
   		   // Check that joomla has fixed the bug that they introduced in version 1.5.16
            $str = file_get_contents( JPATH_LIBRARIES.DS.'joomla'.DS.'application'.DS.'application.php');
            $sessions_fork = strpos( $str, 'session->fork');
            if ( $sessions_fork === false) {}
            else {
               // Check if the $this->_createSession($session->getId()); is NOT present just after the fork
               $posCreateSess = strpos( $str, 'this->_createSession', $sessions_fork);
               if ($posCreateSess === false || ($posCreateSess-$sessions_fork) > 100) {
                  // Then does not read the session as this could avoid any login
                  $canReadSession = false;
               }
            }
   		}
   		if ( $canReadSession) {
      		$session =& JFactory::getSession();
      		$_host_ = $session->get( 'host', false, 'multisites');
      	}
   		if ( $_host_ === false || empty( $_host_)) {
   		   unset( $_host_);
   		}
   		else {
            // Parse the _host_ URL and return the host part
            $hostInfo = MultiSites::_getHostInfo( $_host_);
            $host     = $hostInfo['host'];
   		}
      }

      if ( empty( $hostInfo)) {
         MultiSites::_debug( 'Calling matchSlaveSite Host info <empty>');
      }
      else {
         MultiSites::_debug( 'Calling matchSlaveSite Host info: '. var_export($hostInfo, true));
      }
      
      // Finally, convert the host name into a directory path where it is stored the specific configuration
      if ( !empty( $hostInfo) && class_exists( 'Jms2Win') && Jms2Win::matchSlaveSite( $hostInfo, isset($_host_)))
      {
         if ( defined( 'MULTISITES_CONFIG_PATH')) {
            $configdir = MULTISITES_CONFIG_PATH;
         }
      }

      MultiSites::_debug( 'Config Directory: ' . $configdir);
      MultiSites::_debug_stop( '<< getConfigPath() - STOP');
      return $configdir;  // Use the default installation configuration
   }


   //------------ isSlaveSite ---------------
   /**
    * Return TRUE when:
    * - there is multisites directory defined (case of unix)
    * - or alternate URL parameter _host_ is authorized and present
    * Otherwise, return false.
    */
   function isSlaveSite()
   {
      if ( defined( 'MULTISITES_ID')) return true;
      return false;
   }


   //------------ _loadConfiguration ---------------
	function _loadConfiguration()
	{
      MultiSites::_debug_start( '>> _loadConfiguration() - START');
      // Pre-Load configuration
      require_once( JPATH_CONFIGURATION	.DS.'configuration.php' );

		jimport( 'joomla.registry.registry' );

		// Create the JConfig object
		$config = new JConfig();

		// Get the global configuration object
		$registry =& JFactory::getConfig();

		// Load the configuration values into the registry
		$registry->loadObject($config);

      MultiSites::_debug_stop( '<< _loadConfiguration() - STOP');
		return $config;
	}

   //------------ onInstallSetOverwrite ---------------
   /**
    * Trap the Install/Uninstall extension that is called by a slave site.
    * If Install extension is requested, check that extension has exactly the same version than the one installed on the master site.
    * If Uninstall extension is requested, filter the processing to avoid removing files. Only the master site can remove files.
    * To perform those operation, a special Multisites Installer is wrapper over the standard Intall/Uninstall class.
    */
   function onInstallSetOverwrite()
   {
      MultiSites::_debug_start( '>> onInstallSetOverwrite() - START');
      // If this is a slave site sharing the master site
      if ( MultiSites::isSlaveSite())
      {
         MultiSites::_debug( 'This is the slave site ID: [' . MULTISITES_ID . ']' );
         if ( isset($_REQUEST['option']) && isset($_REQUEST['task']))
         {
            // Filter the input parameters to reduce risk of hacking.
            $value   = trim( stripslashes( $_REQUEST['option']));
            $option  = (string) preg_replace( '/[^A-Za-z0-9_\.]/i', '', $value);  // Alpha numeric + underscore + dot (.)
            $value   = trim( stripslashes( $_REQUEST['task']));
            $task    = (string) preg_replace( '/[^A-Za-z0-9_\-\.]/i', '', $value);  // Alpha numeric + underscore + dash (-) + dot (.)

            @include( JPATH_MUTLISITES_COMPONENT .DS.'patches' .DS.'patch_installer.php');
            // If this is the standard Joomla 1.5 Install/Uninstall operation
            // Or standard Joomla 1.6 Install/Uninstall operation
            //install.install
            // Or an Install/Uninstall operation for a specific component (ie. xmap install extension)
            if ( ($option == 'com_installer' && ($task=='doInstall' || $task=='remove')) // J1.5
              || ($option == 'com_installer' && ($task=='install.install' || $task=='manage.remove')) // J1.6
              || ( isset( $multisites_Installer) 
                && isset( $multisites_Installer[$option]) 
                && in_array( $task, $multisites_Installer[$option])
                 )
               )
            {
               MultiSites::_debug( 'Install/Uninstall command detected');
               MultiSites::_debug( 'PHP Version ' . phpversion());
               // Replace the Joomla Installer by the Multisites Installer one.
               require_once( JPATH_LIBRARIES.DS.'joomla'.DS.'import.php');
               require_once( JPATH_MUTLISITES_COMPONENT
                         .DS.'libraries'
                         .DS.'joomla'
                         .DS.'installer'
                         .DS.'installer_multisites.php');
               MultiSites::_loadConfiguration();
            	$installer =& JInstallerMultisites::getInstance();
            }
         }
      }
      MultiSites::_debug_stop( '<< onInstallSetOverwrite() - STOP');
   }

   //------------  deploy ---------------
   function main()
   {
      if ( !defined( 'JPATH_CONFIGURATION'))
      {
         // If the multi-site configuration path can be retreived
         $configPath = MultiSites::getConfigPath();
         if ( strlen( $configPath) > 0) {
            // Assign new configuration path
            define( 'JPATH_CONFIGURATION', $configPath);

            // If the configuration file does not exist or is too small
            if ( !file_exists( JPATH_CONFIGURATION . DS . 'configuration.php' )
              || (filesize( JPATH_CONFIGURATION . DS . 'configuration.php' ) < 10))
            {
               // Let use the standard joomla installation directory
            }
            else
            {
               // Set the path to an invalid directory to simulate that the directory is removed
               define( 'JPATH_INSTALLATION',	JPATH_ROOT.DS.'installation_deleted' );
            }
         }
         else {
            define( 'JPATH_CONFIGURATION', 	JPATH_ROOT);
            // Set the path to an invalid directory to simulate that the directory is removed
            define( 'JPATH_INSTALLATION',	JPATH_ROOT.DS.'installation_deleted' );
         }

         MultiSites::onInstallSetOverwrite();
         
         // Process special cookie domain value to accept single sign-in on sub-domain.
         // If master website (not slave site) and a specific cookie domain (sub-domain) is defined for the single sign-in
         if ( !defined( 'MULTISITES_ID')) {
            if ( !defined( 'MULTISITES_COOKIE_DOMAINS')) {
               if ( defined( 'MULTISITES_MASTER_COOKIE_DOMAINS')) {
                  // Apply the special cookie domain value
                  define( 'MULTISITES_COOKIE_DOMAINS', MULTISITES_MASTER_COOKIE_DOMAINS);
               }
            }
         }
      }
   }
} // End class

// ================== MAIN =================
MultiSites::main();

if ( class_exists( 'Debug2Win')) {
   // Now that Multisite filtering is performed, accept again to use the Joomla JLog reporting.
   Debug2Win::disableStandalone();
}
//============= END THE MULTISITES =================
} // End disable multisites
