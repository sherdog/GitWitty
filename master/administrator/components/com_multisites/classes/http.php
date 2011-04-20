<?php
/**
 * @file       http.php
 * @brief      API that allow send HTTP POST or GET request.
 * @version    1.0.4
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
 * - V1.0.1 24-AUG-2008: In case of Safe Mode, the CURLOPT_FOLLOWLOCATION is forbidden.
 *                       So it is not possible to follow links.
 *                       Implement the Follow Link curl_redir_exec() sample from PHP 5.0 manual.
 * - V1.0.2 01-SEP-2008: Fix bugs when CURL extension is not present in PHP..
 * - V1.0.3 24-DEC-2008: Fix a buf when CURL is not present a GET request must be sent.
 *                       Remove the http://xxxxx that is present in GET command to be compliant with Unix
 *                       This is the value that is received in REQUEST_URI
 * - V1.0.4 17-FEB-2010: Hidden potential warning in the fsockopen() routine.
 */

// Check to ensure this file is included in Edwin2Win application
defined('_EDWIN2WIN_') or die( 'Restricted access' );


require_once( dirname( __FILE__) .DIRECTORY_SEPARATOR. 'debug.php');
Debug2Win::setFileName( 'http.error.php');

// ===========================================================
//             HTTP2Win class
// ===========================================================
class HTTP2Win
{
   //------------ getProxyInfo ---------------
   /**
    * @brief get the current Proxy information.
    * @return
    * Return a reference to a an array with proxy information.
    */
   function &getProxyInfo()
   {
      static $instance;
		if (!is_object( $instance )) {
			$instance = array();
		}
		return $instance;
   }

   //------------ setProxyInfo ---------------
   /**
    * @brief Set a new Proxy Information.
    */
   function setProxyInfo( $url, $user='', $password='')
   {
      $proxy = HTTP2Win::getProxyInfo();
      
		// Check proxy
		if( trim( $url) != '') {
			if( !stristr($url, 'http')) {
				$proxy['host']    = $url;
				$proxy['scheme']  = 'http';
				$proxy['port']    = 80;
			} else {
				$proxy = parse_url( $url);
      		if( !isset( $proxy['scheme'] ))  $proxy['scheme']  = 'http';
      		if( !isset( $proxy['port'] ))    $proxy['port']    = 80;
			}
		}
		else {
		   // ERROR
			$proxy = array();
			return;
		}
		
		$proxy['user']     = $user;
		$proxy['password'] = $password;
   }
   
   //------------ getLastHttpCode ---------------
   /**
    * @brief Return the http code of last request.
    */
   function &getLastHttpCode()
   {
      static $instance;
		if (!isset( $instance )) {
			$instance = '-1';
		}
		return $instance;
   }
   
   //------------ setLastHttpCode ---------------
   /**
    * @brief set a new status.
    */
   function setLastHttpCode( $newCode)
   {
      // Retreive the reference to the buffer
      $code =& HTTP2Win::getLastHttpCode();
      // Replace the code
      $code = (string)$newCode;
   }

   //------------ getLastResult ---------------
   /**
    * @brief get the last result.
    * @return
    * Return a reference to the result of last request.
    */
   function &getLastResult()
   {
      static $instance;
		if (!isset( $instance )) {
			$instance = '';
		}
		return $instance;
   }

   //------------ setLastResult ---------------
   /**
    * @brief set a new Last result.
    */
   function setLastResult( $newResult)
   {
      // Retreive the reference to the buffer
      $result =& HTTP2Win::getLastResult();
      // Replace the content
      $result = $newResult;
   }
   
   
   //------------ getLastData ---------------
   function getLastData()
   {
      // Retreive the reference to the buffer
      $result =& HTTP2Win::getLastResult();
      
      // Skip the header lines
      $len = strlen( $result);
      $nl = 0;
      $linelen = 0;
      for ( $i=0; $i<$len; $i++) {
         $c = substr( $result, $i, 1);
         if ( $c == "\r") { }
         else if ( $c == "\n") {
            if ( $linelen <= 0) {
               $nl++;
            }
            $linelen = 0;
         }
         else {
            // If there is a empty line, this means that the header is finished
            if ( $nl > 0) {
               // Therefore, the rest is the data
               return substr( $result, $i);
               
            }
            $linelen++;
         }
      }
      
      // If the data part is not found, return all the buffer
      return $result;
   }

   //------------ curl_redir_exec ---------------
   /**
    * curl_exec() function that follow links (redirect).
    * Code extracted from the PHP 5.0 manual.
    */
   function curl_redir_exec($ch)
   {
      static $curl_loops = 0;
      static $curl_max_loops = 20;
      if ($curl_loops++ >= $curl_max_loops)
      {
         $curl_loops = 0;
         return FALSE;
      }
      curl_setopt($ch, CURLOPT_HEADER, true);
      // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $data = curl_exec($ch);
      $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      if ($http_code == 301 || $http_code == 302)
      {
         // Try to separate the Header from the Data
         $header_and_data = explode("\n\n", $data, 2);
         $matches = array();
         preg_match('/Location:(.*?)\n/', $$header_and_data[0], $matches);
         $url = @parse_url(trim(array_pop($matches)));
         if (!$url)
         {
            //couldn't process the url to redirect to
            $curl_loops = 0;
            return $data;
         }
         $last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
         if (!$url['scheme'])    $url['scheme'] = $last_url['scheme'];
         if (!$url['host'])      $url['host']   = $last_url['host'];
         if (!$url['path'])      $url['path']   = $last_url['path'];
         $new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query']?'?'.$url['query']:'');
         curl_setopt($ch, CURLOPT_URL, $new_url);
         Debug2Win::debug('Redirecting to '. $new_url);
         return HTTP2Win::curl_redir_exec($ch);
      } else {
         $curl_loops=0;
         return $data;
      }
   }

   //------------ request ---------------
	/**
	 * This is a general function to safely open a connection to a server,
	 * post data when needed and read the result.
	 * Tries using cURL and switches to fopen/fsockopen if cURL is not available
	 * @param string $url
	 * @param string $postData
	 * @param array $headers
	 * @param resource $fileToSaveData
	 * @return mixed
	 */
	function request( $url, $vars=array(), $method='GET', $headers=array(), $fileToSaveData=null )
	{
      Debug2Win::debug_start( ">> request() - START");
      
		$urlParts = parse_url( $url );
		if( !isset( $urlParts['port'] ))    $urlParts['port'] = 80;
		if( !isset( $urlParts['scheme'] ))  $urlParts['scheme'] = 'http';

		// Get proxy URL
		$proxy =& HTTP2Win::getProxyInfo();

      // Convert the vars into a list of &key=value
   	$urlencoded = "";
   	while (list($key,$value) = each($vars))
   		$urlencoded.= urlencode($key) . "=" . urlencode($value) . "&";
   	$urlencoded = substr($urlencoded,0,-1);	
   	$content_length = strlen($urlencoded);
   	
   	if ( $method == 'POST') {
      	$postData = $urlencoded;
   	}
   	// Assume this is a GET
   	else {
	      // Append with the additional vars
   	   // If there is no ? in the url
   	   if ( !strstr( $url, '?')) {
   	      $url .= '?' . $urlencoded;
   	   }
   	   else {
   	      $url .= '&' . $urlencoded;
   	   }
   	}


		// When CURL function exists, use it to perform the request
		if( function_exists( "curl_init" ))
		{
			Debug2Win::debug( 'Using the cURL library for communicating with '.$urlParts['host'] );

			$CR = curl_init();
			curl_setopt($CR, CURLOPT_URL, $url);

			// just to get sure the script doesn't die
			curl_setopt($CR, CURLOPT_TIMEOUT, 30 );

			// Accept to follow Redirection location (max=10)
			@curl_setopt($CR, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($CR, CURLOPT_MAXREDIRS, 10);
			
			if( !empty( $headers )) {
				// Add additional headers if provided
				curl_setopt($CR, CURLOPT_HTTPHEADER, $headers);
			}
			curl_setopt($CR, CURLOPT_FAILONERROR, true);
			if( isset( $postData)) {
				curl_setopt($CR, CURLOPT_POSTFIELDS, $postData );
				curl_setopt($CR, CURLOPT_POST, 1);
			}
			if( is_resource($fileToSaveData)) {
				curl_setopt($CR, CURLOPT_FILE, $fileToSaveData );
			} else {
				curl_setopt($CR, CURLOPT_RETURNTRANSFER, 1);
			}
			// Do we need to set up the proxy?
			if( !empty($proxy) ) {
				Debug2Win::debug( 'Setting up proxy: '.$proxy['host'].':'.$proxy['port']);
				//curl_setopt($CR, CURLOPT_HTTPPROXYTUNNEL, true);
				curl_setopt($CR, CURLOPT_PROXY,     $proxy['host'] );
				curl_setopt($CR, CURLOPT_PROXYPORT, $proxy['port']);
				// Check if the proxy needs authentication
				if( trim( $proxy['user']) != '') {
					Debug2Win::debug( 'Using proxy authentication!' );
					curl_setopt($CR, CURLOPT_PROXYUSERPWD, $proxy['user'].':'.$proxy['password']);
				}
			}

			if( $urlParts['scheme'] == 'https') {
				// No PEER certificate validation...as we don't have
				// a certificate file for it to authenticate the host www.ups.com against!
				curl_setopt($CR, CURLOPT_SSL_VERIFYPEER, 0);
				//curl_setopt($CR, CURLOPT_SSLCERT , "/usr/locale/xxxx/clientcertificate.pem");
			}
			$result = HTTP2Win::curl_redir_exec( $CR );
			$error  = curl_error( $CR );
			if( !empty( $error ) && stristr( $error, '502') && !empty( $proxy)) {
				Debug2Win::debug( 'Switching to NTLM authenticaton.');
				curl_setopt( $CR, CURLOPT_PROXYAUTH, CURLAUTH_NTLM );
				$result = HTTP2Win::curl_redir_exec( $CR );
				$error  = curl_error( $CR );
			}
			
			$http_code = curl_getinfo ( $CR, CURLINFO_HTTP_CODE);
			// $info = curl_getinfo ( $CR);

			curl_close( $CR );

			if( !empty( $error )) {
				Debug2Win::debug( $error );
            Debug2Win::debug_stop( "<< request() - STOP");
				return false;
			}
			else {
   			HTTP2Win::setLastResult( $result);
            HTTP2Win::setLastHttpCode( $http_code);
            Debug2Win::debug_stop( "<< request() - STOP");
				return $result;
			}
		}
		// If CURL function does not exists, 
		else
		{
		   // ===================== Open the connection =============
		   // If HTTP POST or GET
		   // If there is a proxy, open it
			if( !empty( $proxy)) {
				// If we have something to post we need to write into a socket
				if( $proxy['scheme'] == 'https'){
					$protocol = 'ssl';
				}
				else {
					$protocol = 'http';
				}
				$fp = @fsockopen("$protocol://".$proxy['host'], $proxy['port'], $errno, $errstr, $timeout = 30);
			}
			// When there is no Proxy, open the URL
			else {
            $server  = $urlParts['host'];
            $port    = $urlParts['port'];
				// If we have something to post we need to write into a socket
				if( $urlParts['scheme'] == 'https' || $port == 443){
					$protocol = 'ssl';
         		$fp = @fsockopen("ssl://".$server, $port, $errno, $errstr, $timeout = 30);
				}
				else {
					$protocol = $urlParts['scheme'];
         		$fp = @fsockopen( $server, $port, $errno, $errstr, $timeout = 30);
				}
			}

			
		   // ===================== Check the connection =============
			if(!$fp){
				//error tell us
				Debug2Win::debug( "Possible server error! - $errstr ($errno)\n" );
            Debug2Win::debug_stop( "<< request() - STOP");
				return false;
			}
			else {
				Debug2Win::debug( 'Connection opened to '.$urlParts['host']);
			}

		   // ===================== Submit the request =============
			// If HTTP POST
			if( isset( $postData))
			{
				Debug2Win::debug('Now posting the variables.' );
				//send the server request
				if( !empty( $proxy)) {
					fputs($fp, "POST ".$urlParts['host'].':'.$urlParts['port'].$urlParts['path']." HTTP/1.0\r\n");
					fputs($fp, "Host: ".$proxyURL['host']."\r\n");

					if( trim( $proxy['user'])!= '') {
						fputs($fp, "Proxy-Authorization: Basic " . base64_encode ($proxy['user'].':'.$proxy['password']) . "\r\n\r\n");
					}
				}
				else {
					fputs($fp, 'POST '.$urlParts['path']." HTTP/1.0\r\n");
					fputs($fp, 'Host:'. $urlParts['host']."\r\n");
				}
				fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
				fputs($fp, "Content-length: ".strlen($postData)."\r\n");
				fputs($fp, "Connection: close\r\n\r\n");
				fputs($fp, $postData . "\r\n\r\n");
			}
			// If HTTP GET
			else {
				if( !empty( $proxy)) {
					fputs($fp, "GET ".$urlParts['host'].':'.$urlParts['port'].$urlParts['path']." HTTP/1.0\r\n");
					fputs($fp, "Host: ".$proxy['host']."\r\n");
					if( trim( $proxy['user'])!= '') {
						fputs($fp, "Proxy-Authorization: Basic " . base64_encode ($proxy['user'].':'.$proxy['password']) . "\r\n\r\n");
					}
				}
				else {
            	// $user_agent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
            	$user_agent = "http2win/1.0";
         	   $request_uri = $url;
            	$pos = strpos( $url, $urlParts['host']);
            	if ( $pos>0) {
            	   $pos += strlen( $urlParts['host']);
            	   $pos2 = strpos( $url, '/', $pos);
            	   if ( $pos2>0) {
            	      $request_uri = substr( $url, $pos2);
            	   }
            	}
            	$header_Line1 = "GET " . $request_uri . " HTTP/1.0";
            	$myHeaders = $header_Line1 . "\r\n"
                          . "Accept: text/*\r\n"
                          . "Accept-Language: en-us\r\n"
                          . "User-Agent: " . $user_agent . "\r\n"
                          . "Host: " . $urlParts['host'] . "\r\n"
                          . "Connection: Keep-Alive\r\n"
                          . "\r\n";
            	fputs($fp, $myHeaders);
				}
			}
			// Add additional headers if provided
			foreach( $headers as $header ) {
				fputs($fp, $header."\r\n");
			}
			$data = "";
			while (!feof($fp)) {
				$data .= @fgets ($fp, 4096);
			}
			fclose( $fp );

			// If didnt get content-lenght, something is wrong, return false.
			if ( trim($data) == '' ) {
				Debug2Win::debug('An error occured while communicating with the server '.$urlParts['host'].'. It didn\'t reply (correctly). Please try again later, thank you.' );
            Debug2Win::debug_stop( "<< request() - STOP");
				return false;
			}
			
			$result = trim( $data );
			HTTP2Win::setLastResult( $result);
      
         // Extract the HTTP line Code from the request (This is the first line)
         $lines = explode( "\n", $result);
         if ( $lines === false) {
            HTTP2Win::setLastHttpCode( -3);
         }
         else {
            // Extract the code
            $arr = explode( ' ', trim( $lines[0]));
            if ( $arr === false || count($arr) < 2) {
               HTTP2Win::setLastHttpCode( -2);
            }
            else {
               HTTP2Win::setLastHttpCode( intval($arr[1]));
            }
         }

			if( is_resource($fileToSaveData )) {
				fwrite($fileToSaveData, $result );
            Debug2Win::debug_stop( "<< request() - STOP");
				return true;
			} else {
            Debug2Win::debug_stop( "<< request() - STOP");
				return $result;
			}
		}
		
		Debug2Win::debug('FATAL ERROR - Check programmation - This statment should never be executed.' );
      Debug2Win::debug_stop( "<< request() - STOP");
		return false;
	} // End function
} // End Class

