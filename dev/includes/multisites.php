<?php
if ( !defined('_JEXEC') && !defined('_EDWIN2WIN_') ) die( 'Restricted access' );
/**
 * @file       multisites.php
 * @brief      Check the URI and compute the site_id, and the 'configuration.php' path
 * @version    1.2.47
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
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
 * - V1.1.0 29-SEP-2008: Creation of the source
 *          12-NOV-2008: Change to make the source totally independent and avoid any call to Joomla code.
 *                       This is required when configuration.php is called by external program such as
 *                       paypal notification for virtuemart.
 * - V1.1.9 29-DEC-2008: Put debug traces into comment to avoid using some variables that will produce a warning.
 * - V1.1.17 17-FEB-2009: Avoid using DS to completly work as standalone and reduce Joomla dependencies.
 * - V1.2.00 16-JUN-2009: Add expiration URL redirection processing when a site is expired.
 * - V1.2.06 18-SEP-2009: Add cookie domain to allow single sign-in sub-domains.
 * - V1.2.21 07-FEB-2010: Add parsing of the port in an URL.
 * - V1.2.29 20-MAY-2010: Add possibility to execute the Multisites Affiliate User Exit to process additional URL parsing.
 *                        This can be used to speedup the processing and sometimes bypass the Master Index creation.
 *                        Add letter tree directory processing to improve OS file system access and reduce the number of files in a directory.
 * - V1.2.33 29-JUN-2010: Fix call to the letter tree when directly called from a "configuration.php" file.
 * - V1.2.35 27-JUL-2010: Add enableStandalone debug interface to avoid call JLog when it is not yet initialized.
 * - V1.2.47 02-FEB-2011: Fix warning message.
 */

if ( !defined( 'JMS2WIN_VERSION')) define( 'JMS2WIN_VERSION', '1.2.29');

if ( file_exists( dirname( dirname(__FILE__)).DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_multisites' .DIRECTORY_SEPARATOR. 'classes' .DIRECTORY_SEPARATOR. 'lettertree.php')) { 
	@include_once( dirname( dirname(__FILE__)).DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_multisites' .DIRECTORY_SEPARATOR. 'classes' .DIRECTORY_SEPARATOR. 'lettertree.php');
}

// ===========================================================
//             Jms2Win class
// ===========================================================
if ( !class_exists( 'Jms2Win')) {
class Jms2Win {   
	// =====================================
	//             DUPLCIATED FUNCTIONS
	// =====================================
	/**
	 * Following functions are duplicated from other source to make this source totally independent.
	 */

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

   //------------ _enableStandalone ---------------
   function _enableStandalone()
   {
      if ( class_exists( 'Debug2Win')) {
         Debug2Win::enableStandalone();
      }
   }

   //------------ _disableStandalone ---------------
   function _disableStandalone()
   {
      if ( class_exists( 'Debug2Win')) {
         Debug2Win::disableStandalone();
      }
   }

   //------------ _redirect ---------------
   /**
    * @note Redirect to url
    */
   function _redirect( $url)
   {
		/*
		 * If the headers have been sent, then we cannot send an additional location header
		 * so we will output a javascript redirect statement.
		 */
		if (headers_sent()) {
			echo "<script>document.location.href='$url';</script>\n";
		} else {
			//@ob_end_clean(); // clear output buffer
			header( 'HTTP/1.1 301 Moved Permanently' );
			header( 'Location: ' . $url );
		}
	   exit();
   }

	
   //------------ _isExpired ---------------
   /**
    * @note Very similar to site.php function
    */
   function _isExpired( $site_def)
   {
      if ( empty( $site_def['expiration'])) {
         return false;
      }
      $expiration = strtotime( $site_def['expiration']);
      $now        = strtotime( 'now');

      $expiration_str = strftime( '%Y-%m-%d', $expiration);
      $now_str        = strftime( '%Y-%m-%d', $now);

      if ( $expiration_str < $now_str) {
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
			$theURI = 'http' . $https . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

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


	// =====================================
	//             FUNCTIONS
	// =====================================
	
   //------------ _calcConfigPath ---------------
   function _calcConfigPath( $site_id, $site_dir=null)
   {
// Jms2Win::_debug_start( ">> _calcConfigPath( site_id=[$site_id], site_dir=[$site_dir]) - START");

      define( 'MULTISITES_ID', $site_id);
      $filename = JPATH_MULTISITES.DIRECTORY_SEPARATOR.MULTISITES_ID.DIRECTORY_SEPARATOR.'config_multisites.php';
      // If the configuration file is not present in a flat structure
      if ( !file_exists( $filename)) {
// Jms2Win::_debug( "Default [$filename] does NOT exists");
         // If there is a specific site directory path present (provided or computed)
         if( !empty( $site_dir)) {
// Jms2Win::_debug( "Use site_dir[$site_dir]");
            // Try to compute a path using the letter tree
            $filename = $site_dir.DIRECTORY_SEPARATOR.'config_multisites.php';
            define( 'MULTISITES_ID_PATH', $site_dir);
         }
         // Else when there is no specific directory defined, 
         else {
// Jms2Win::_debug( "Retry using letter tree");
            // Try to compute the letter tree directory path
            if ( class_exists( 'MultisitesLetterTree')) {
               $site_dir = MultisitesLetterTree::getLetterTreeDir( MULTISITES_ID);
               if( !empty( $site_dir)) {
// Jms2Win::_debug( "Use letter tree directory [$site_dir]");
                  // Try to compute a path using the letter tree
                  $filename = JPATH_MULTISITES.DIRECTORY_SEPARATOR.$site_dir.DIRECTORY_SEPARATOR.'config_multisites.php';
                  if ( file_exists( $filename)) {
                     define( 'MULTISITES_ID_PATH', JPATH_MULTISITES.DIRECTORY_SEPARATOR.$site_dir);
                  }
                  else {
                     define( 'MULTISITES_ID_PATH', JPATH_MULTISITES.DIRECTORY_SEPARATOR.MULTISITES_ID);
                  }
// Jms2Win::_debug( "Assigned Multisites ID path = " . MULTISITES_ID_PATH);
               }
            }
         }
      }
      else {
         define( 'MULTISITES_ID_PATH', JPATH_MULTISITES.DIRECTORY_SEPARATOR.MULTISITES_ID);
      }
// Jms2Win::_debug( "Including file: [$filename]");
      @include($filename);
// Jms2Win::_debug( "Processing");
      if ( isset( $config_dirs) && !empty( $config_dirs)) {
         if ( !empty( $config_dirs['deploy_dir'])) {
            define( 'MULTISITES_CONFIG_PATH', $config_dirs['deploy_dir']);
         }
         else {
            define( 'MULTISITES_CONFIG_PATH', MULTISITES_ID_PATH);
         }
         
         // If Unknown (assume front-end) or front-end.
         // Otherwise, this is admin (back-end) and we don't used specific template and cache
         if ( !defined( 'MULTISITES_ADMIN')
            && ( (!defined( 'JPATH_ROOT') && !defined( 'JPATH_BASE'))
              || ( defined( 'JPATH_ROOT') &&  defined( 'JPATH_BASE') && JPATH_ROOT == JPATH_BASE)
               )
            ) {
            if ( !empty( $config_dirs['templates_dir'])) {
               if ( !defined( 'JPATH_THEMES')) {
                  define( 'JPATH_THEMES',	$config_dirs['templates_dir']);
                  // If the new theme directory is different of the master website theme directory
                  if ( defined( 'MULTISITES_MASTER_ROOT_PATH')) {
                     if ( JPATH_THEMES != MULTISITES_MASTER_ROOT_PATH.DIRECTORY_SEPARATOR.'templates') {
                        // Flag that the site can install templates
                        define( 'MULTISITES_THEMES_SPECIFIC',	true);
                     }
                  }
               }
            }
            if ( !empty( $config_dirs['cache_dir'])) {
               if ( !defined( 'JPATH_CACHE')) {
                  define( 'JPATH_CACHE', $config_dirs['cache_dir']);
               }
            }
         }
      }
      else {
         define( 'MULTISITES_CONFIG_PATH', MULTISITES_ID_PATH);
      }
// Jms2Win::_debug_stop( '<< _calcConfigPath() - STOP');
   }
   
   
   //------------ _getRootURL ---------------
   /**
    * @brief In case usage of _host_=xxxx param, retreive the ROOT URL that can be composed of a partial path
    * (Case of http://localhost/....../administrator/...)
    */
   function _getRootURL()
   {
      $myUrl = Jms2Win::_getCurrentURL();
      $pos1 = strpos( $myUrl, '://');
      if ( $pos1 > 0) {
         $pos2 = strpos( $myUrl, '/', $pos1+3);
         if ( $pos2 > 0) {
            $myUrl = substr( $myUrl, 0, $pos2);
         }
      }
      else {
         $pos2 = strpos( $myUrl, '/', $pos1+3);
         if ( $pos2 > 0) {
            $myUrl = 'http://' . substr( $myUrl, 0, $pos2);
         }
      }
      $prefix = rtrim( $myUrl, '/\\');
      
		if (strpos(php_sapi_name(), 'cgi') !== false && !empty($_SERVER['REQUEST_URI'])) {
			//Apache CGI
			$path =  rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
		} else {
			//Others
			$path =  rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
		}

		$parts = split ('[/\\]', $path);
		while( count($parts)>0) {
		   if ( $parts[ count($parts)-1] == 'administrator') {
		      if ( count($parts) > 0) {
      		   array_pop( $parts );
		      }
		      $path = implode( '/', $parts );
		      break;
		   }
		   array_pop( $parts );
		}

		
		$rootURL = $prefix . $path;
		return $rootURL;
   }
   
   //------------ _getMultisitesHost ---------------
   /**
    * A multisites host is an URL with out the leading http(s://
    * @remark
    * It can contain a port when present in the url.
    * ie. http://www.domain.com:8080/toto
    * returns "www.domain.com:8080"
    */
   function _getMultisitesHost( $URL)
   {
		$url     = rtrim( $URL, '/\\');
      $result  = $url;
      $pos1    = strpos( $url, '://');
      if ( $pos1 > 0) {
         $pos2 = strpos( $url, '/');
         if ( $pos2 == ($pos1+1)) {
            $result = substr( $url, $pos1+3);
         }
      }
		return $result;
   }


   //------------ matchSlaveSite ---------------
   /**
    * @param $is_host_  False that must be true when _host_=xxx is used in the URL.
    *                   This allow to use the current URL as host instead of the host
    */
   function matchSlaveSite( $hostInfo=null, $is_host_=false)
   {
//Jms2Win::_enableStandalone();
//Jms2Win::_debug_start( '>> matchSlaveSite() - START');
      
      $DS = DIRECTORY_SEPARATOR;
      if ( empty( $hostInfo)) {
         if ( !isset($_SERVER["HTTP_HOST"])) {
            return false;
         }
         $hostInfo = Jms2Win::_getHostInfo( Jms2Win::_getCurrentURL());
      }
      $host = $hostInfo['host'];
      
      // Retreive the multisites Master Index
      @include( JPATH_MULTISITES.$DS.'config_multisites.php');
      if ( !defined( 'MULTISITES_MASTER_ROOT_PATH')) {
         if ( defined( 'JPATH_ROOT')) {
            define( 'MULTISITES_MASTER_ROOT_PATH', JPATH_ROOT);
         }
         else {
            $filename = __FILE__;
            $parts = explode( $DS, dirname(__FILE__)  );
            if ( $parts[count($parts)-1] == 'includes') {
               array_pop( $parts );
            }
            $JPATH_ROOT  = implode( $DS, $parts );
            define( 'MULTISITES_MASTER_ROOT_PATH', $JPATH_ROOT);
         }
      }
      
      @include( MULTISITES_MASTER_ROOT_PATH.$DS.'components'.$DS.'com_multisitesaffiliate'.$DS.'multisites_userexit.php');

// Jms2Win::_debug( 'md_hostalias: ' . var_export($md_hostalias, true) );
      
      // If an entry exists for this domain name
      if ( isset($md_hostalias) && isset( $md_hostalias[ $host])) {
         $def = $md_hostalias[ $host];
//Jms2Win::_debug( 'Entry found. Def =' . var_export($def, true) );
         // If the definition of the domain is a string (JMS 1.0.x)
         if ( is_string($def)) {
            // This mean that the definition contain the site ID
            Jms2Win::_calcConfigPath( $md_hostalias[ $host]);
            if ( !defined( 'MULTISITES_HOST')) {
               // If _host_=xxx is used in URL
               if ( $is_host_) {
                  define( 'MULTISITES_HOST', Jms2Win::_getMultisitesHost( Jms2Win::_getRootURL()));
               }
               else {
                  define( 'MULTISITES_HOST', Jms2Win::_getMultisitesHost( $host));
               }
            }
            return true;
         }
         // Verify that the definition is an array ( >= JMS 1.1)
         if ( is_array($def)) {
            $url = $hostInfo['URL'];
            $url_len = strlen( $url);
            
//Jms2Win::_debug( 'Array entry. URL =' . var_export($url, true) );
            
            // For each definition
            foreach( $def as $values) {
               $cur_url     = strtolower( $values['url']);
               $cur_url_len = strlen( $cur_url);
//Jms2Win::_debug( 'Compare with Current URL =' . var_export($cur_url, true) );
               if ( $cur_url_len < $url_len) {
                  if ( strncmp( $url, $cur_url .'/', $cur_url_len+1) == 0) {
//Jms2Win::_debug( 'Condition 1 - OK');
                     if ( Jms2Win::_isExpired( $values)) {
                        if ( !empty( $values[ 'expireurl'])) {
                           Jms2Win::_redirect( $values[ 'expireurl']);
                        }
                        return false;
                     }
                     if ( !empty( $values[ 'site_dir'])) { $site_dir = $values[ 'site_dir']; }
                     else                                { $site_dir = null; }
                     Jms2Win::_calcConfigPath( $values[ 'site_id'], $site_dir);
                     if ( !defined( 'MULTISITES_HOST')) {
                        // If _host_=xxx is used in URL
                        if ( $is_host_) {
                           define( 'MULTISITES_HOST', Jms2Win::_getMultisitesHost( Jms2Win::_getRootURL()));
                        }
                        else {
                           define( 'MULTISITES_HOST', Jms2Win::_getMultisitesHost( $cur_url));
                        }
                     }
                     
                     if ( !defined( 'MULTISITES_COOKIE_DOMAINS') && !empty( $values[ 'cookie_domains'])) {
                        define( 'MULTISITES_COOKIE_DOMAINS', implode( '|', $values[ 'cookie_domains']));
                     }
                     
                     return true;
                  }
               }
               else if ( $cur_url_len == $url_len) {
//Jms2Win::_debug( 'Condition 1 - OK');
                  if ( strcmp( $url, $cur_url) == 0) {
                     if ( Jms2Win::_isExpired( $values)) {
                        if ( !empty( $values[ 'expireurl'])) {
                           Jms2Win::_redirect( $values[ 'expireurl']);
                        }
                        return false;
                     }
                     if ( !empty( $values[ 'site_dir'])) { $site_dir = $values[ 'site_dir']; }
                     else                                { $site_dir = null; }
                     Jms2Win::_calcConfigPath( $values[ 'site_id'], $site_dir);
                     if ( !defined( 'MULTISITES_HOST')) {
                        // If _host_=xxx is used in URL
                        if ( $is_host_) {
                           define( 'MULTISITES_HOST', Jms2Win::_getMultisitesHost( Jms2Win::_getRootURL()));
                        }
                        else {
                           define( 'MULTISITES_HOST', Jms2Win::_getMultisitesHost( $cur_url));
                        }
                     }
                     
                     if ( !defined( 'MULTISITES_COOKIE_DOMAINS') && !empty( $values[ 'cookie_domains'])) {
                        define( 'MULTISITES_COOKIE_DOMAINS', implode( '|', $values[ 'cookie_domains']));
                     }
                     return true;
                  }
               }
            }
         }
      }
      return false;
   } // End Function isSlaveSite
} // End class Jms2Win
} // End if class exists

?>