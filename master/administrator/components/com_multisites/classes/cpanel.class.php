<?php

/**
 * @author W-Shadow
 * @copyright 2008
 */
 
/**
 * cPanelAPI
 *
 * @package 
 * @author 
 * @copyright 2008
 * @version $Id$
 * @access public
 */
class cPanelAPI {
	var $host = '';
	var $username='';
	var $password='';
	var $skin = 'x3';
	var $port = 2082;
	var $hc = null;
	
  /**
   * cPanelAPI::cPanelAPI()
   *
   * @param mixed $cpanel_host
   * @param mixed $cpanel_username
   * @param mixed $cpanel_password
   * @param string $cpanel_skin
   * @return
   */
	function cPanelAPI($cpanel_host, $cpanel_username, $cpanel_password, 
		$cpanel_skin='x3', $cpanel_port=2082)
	{ 
		$this->host = $cpanel_host;
		$this->username = $cpanel_username;
		$this->password = $cpanel_password;
		$this->skin = $cpanel_skin;
		$this->port = $cpanel_port;
		
		//Initialize cURL
		$this->hc = new eHttpClient();
		$this->hc->configCurl(CURLOPT_USERPWD, "$cpanel_username:$cpanel_password");
		$this->hc->configCurl(CURLOPT_SSL_VERIFYPEER, 0);
		if (defined('CURLOPT_HTTPAUTH')){
			//optional - this is the default setting in cURL anyway.
			$this->hc->configCurl(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		}
	}
	
  /**
   * cPanelAPI::base()
   *
   * @return string
   */
	function base(){
		return "http://".$this->host.':'.$this->port.'/frontend/'.$this->skin;
	}
	
  /**
   * cPanelAPI::fetch()
   * Get a page from cPanel
   *
   * @param string $url
   * @return string
   */
	function fetch($page){
		if ($page[0]=='/'){
			return $this->hc->get("http://".$this->host.':'.$this->port.$page);
		} else {
			return $this->hc->get($this->base().'/'.$page);
		}
	}
	
  /**
   * cPanelAPI::mysql_create_db()
   * Create a MySQL database with the provided name.   
   *
   * @param string $db_name
   * @return boolean
   */
	function mysql_create_db($db_name){
		//WARNING : it's "adddb.html" in skin x, and "addb.html" in x3
		if ('x3' == $this->skin){
			$func = 'addb.html';
		} else {
			$func = 'adddb.html';
		}
		$html = $this->fetch("sql/$func?db=".urlencode($db_name));
		if ( $this->hc->httpCode() != 200 ){
			//An error occured.
			return false;
		}
		return true;
	}
	
  /**
   * cPanelAPI::mysql_create_db_user()
   * Creates a MySQL user. Note : the username is likely to be truncated
   * to 8 characters by cPanel. This function *does not* take that into account.
   *
   * @param string $username
   * @param string $password Defaults to random 8 character password if omitted.
   * @return boolean
   */
	function mysql_create_db_user($username, $password=''){
		if (empty($password)){
			$password = $this->random_password(8);
		}
		$html = $this->fetch("sql/adduser.html?user=".
			urlencode($username)."&pass=".urlencode($password));
		if ( $this->hc->httpCode() != 200 ) return false;
		return true;
	}
	
  /**
   * cPanelAPI::mysql_add_user_to_db()
   *
   * @param string $username
   * @param string $db_name
   * @param string $priv_string Defaults to all privileges.
   * @return boolean
   */
	function mysql_add_user_to_db($username, $db_name, $priv_string='ALL=ALL'){
		$html = $this->fetch(
			"sql/addusertodb.html?".
		    "user=".urlencode($username)."&db=".urlencode($db_name)."&".$priv_string);
		//verify that it worked
		if ( $this->hc->httpCode() != 200 ) return false;
		return true;
	}
	
  /**
   * cPanelAPI::mysql_easy_create()
   * Add a MySQL database, a user, and add the user to the DB.
   *
   * @param string $db_name Database name. 
   * @param string $db_user Database username. Equal to the database name by default.
   * @param string $db_password DB user password. Randomly generated by default.
   * @param string $priv_string User's privileges. Default is all privileges.
   * @return array
   */
	function mysql_easy_create($db_name='', $db_user='gitwitty_dbadmin', $db_password='db@dm1n', $priv_string='ALL=ALL'){
		//Create the database
		if (!$this->mysql_create_db($db_name)) return false;
		
		//If no username given use the DB name
		if (empty($db_user)){
			$db_user = $db_name;
		}
		
		//If password is empty generate a random password.
		if (empty($db_password)){
			$db_password = $this->random_password();
		}
		
		//Create the user
		if (!$this->mysql_create_db_user($db_user, $db_password)) return false;
		
		//Add the user to the database
		if ( !$this->mysql_add_user_to_db(
				$this->username.'_'.$db_user, $this->username.'_'.$db_name, $priv_string)
			) return false;

		return array(
			'db_name' => $db_name,
			'db_user' => $db_user, 
			'db_password' => $db_password,
		);
 	}
 	
  /**
   * cPanelAPI::mysql_list_databases()
   * List all MySQL databases. 
   *
   * @return array with the following elements : database - DB name, size - DB size as reported by cPanel, users - users added to this database 
   */
	function mysql_list_databases(){
		$html = $this->fetch("sql/index.html");
		if ( $this->hc->httpCode() != 200 ) return false;
		
		//Extract the relevant code block
		if (!preg_match('/<h\d>Current Databases<\/h\d>(.+?)<h\d>MySQL Users<\/h\d>/si', 
			$html, $matches)) return array();
		$html = $matches[1];
		//echo $html;
		
		//Extract all table rows
		$pattern = '/<tr\s+class="[^"]+">.*?<td[^>]*>(.+?)<\/td>.*?<td[^>]*>(.+?)<\/td>'.
			'.*?<td[^>]*>(.+?)<\/table>\s*<\/td>/si';
		if (!preg_match_all($pattern,$html, $matches, PREG_SET_ORDER)) 
			return array(); //No databases found
		
		//Process the rows
		$databases = array();
		foreach($matches as $match){
			$database = array('database'=>trim($match[1]), 'size'=>trim($match[2]));
			//Get users assigned to this database.
			$users = html_entity_decode(strip_tags($match[3]));
			$users = preg_split('/[^a-z_0-9]+/i', $users, -1, PREG_SPLIT_NO_EMPTY);
			$database['users'] = $users;
			//Append to the result array
			$databases[] = $database;
		}
		
		return $databases;
	} 	
	
	
  /**
   * cPanelAPI::fetchRawLogs()
   * Fetch a list of domains that have logs available for them
   *
   * @return array 
   */
    function fetch_raw_logs(){
        $html = $this->fetch("raw/index.html");
        if(!preg_match_all('/"\/getaccesslog\/accesslog_([^_]+)_[^.]+.gz"/i',$html,$pcs))
            return false;
        return $pcs[1];
    }
    
  /**
   * cPanelAPI::fetchRawLog()
   * Download the log for a given domain and a date
   *
   * @param string $domain 
   * @param integer $tm Timestamp indicating the log date. The default is the current date. 
   * @return
   */
    function fetch_raw_log($domain, $tm = null){
        if(!isset($tm)) $tm=time();
        $gzlnk="/getaccesslog/accesslog_".$domain."_".
            str_replace("_0","_",strftime("_%m_%d_%Y",$tm)).".gz";
        $gz=$this->fetch($gzlnk);
        $logtxt=gzdecode($gz);
        return $logtxt;
    }
	
  /**
   * cPanelAPI::random_password()
   *
   * @param integer $length
   * @return
   */
	function random_password($length = 8) { 
	    $chars = "abcdefghijklmnopqrstuvwxyzQWERTYUIOPASDFGHJKLZXCVBNM0123456789"; 
	    srand(microtime(true)*1000000); 
	    $i = 0; 
	    $pass = '' ; 
	
	    while ($i < $length) { 
	        $pass = $pass . $chars[rand(0,strlen($chars)-1)];
	        $i++; 
	    } 
	
	    return $pass; 
	}
}

if (!function_exists('gzdecode')) {
	function gzdecode($data,&$filename='',&$error='',$maxlength=null) 
	{
	    $len = strlen($data);
	    if ($len < 18 || strcmp(substr($data,0,2),"\x1f\x8b")) {
	        $error = "Not in GZIP format.";
	        return null;  // Not GZIP format (See RFC 1952)
	    }
	    $method = ord(substr($data,2,1));  // Compression method
	    $flags  = ord(substr($data,3,1));  // Flags
	    if ($flags & 31 != $flags) {
	        $error = "Reserved bits not allowed.";
	        return null;
	    }
	    // NOTE: $mtime may be negative (PHP integer limitations)
	    $mtime = unpack("V", substr($data,4,4));
	    $mtime = $mtime[1];
	    $xfl   = substr($data,8,1);
	    $os    = substr($data,8,1);
	    $headerlen = 10;
	    $extralen  = 0;
	    $extra     = "";
	    if ($flags & 4) {
	        // 2-byte length prefixed EXTRA data in header
	        if ($len - $headerlen - 2 < 8) {
	            return false;  // invalid
	        }
	        $extralen = unpack("v",substr($data,8,2));
	        $extralen = $extralen[1];
	        if ($len - $headerlen - 2 - $extralen < 8) {
	            return false;  // invalid
	        }
	        $extra = substr($data,10,$extralen);
	        $headerlen += 2 + $extralen;
	    }
	    $filenamelen = 0;
	    $filename = "";
	    if ($flags & 8) {
	        // C-style string
	        if ($len - $headerlen - 1 < 8) {
	            return false; // invalid
	        }
	        $filenamelen = strpos(substr($data,$headerlen),chr(0));
	        if ($filenamelen === false || $len - $headerlen - $filenamelen - 1 < 8) {
	            return false; // invalid
	        }
	        $filename = substr($data,$headerlen,$filenamelen);
	        $headerlen += $filenamelen + 1;
	    }
	    $commentlen = 0;
	    $comment = "";
	    if ($flags & 16) {
	        // C-style string COMMENT data in header
	        if ($len - $headerlen - 1 < 8) {
	            return false;    // invalid
	        }
	        $commentlen = strpos(substr($data,$headerlen),chr(0));
	        if ($commentlen === false || $len - $headerlen - $commentlen - 1 < 8) {
	            return false;    // Invalid header format
	        }
	        $comment = substr($data,$headerlen,$commentlen);
	        $headerlen += $commentlen + 1;
	    }
	    $headercrc = "";
	    if ($flags & 2) {
	        // 2-bytes (lowest order) of CRC32 on header present
	        if ($len - $headerlen - 2 < 8) {
	            return false;    // invalid
	        }
	        $calccrc = crc32(substr($data,0,$headerlen)) & 0xffff;
	        $headercrc = unpack("v", substr($data,$headerlen,2));
	        $headercrc = $headercrc[1];
	        if ($headercrc != $calccrc) {
	            $error = "Header checksum failed.";
	            return false;    // Bad header CRC
	        }
	        $headerlen += 2;
	    }
	    // GZIP FOOTER
	    $datacrc = unpack("V",substr($data,-8,4));
	    $datacrc = sprintf('%u',$datacrc[1] & 0xFFFFFFFF);
	    $isize = unpack("V",substr($data,-4));
	    $isize = $isize[1];
	    // decompression:
	    $bodylen = $len-$headerlen-8;
	    if ($bodylen < 1) {
	        // IMPLEMENTATION BUG!
	        return null;
	    }
	    $body = substr($data,$headerlen,$bodylen);
	    $data = "";
	    if ($bodylen > 0) {
	        switch ($method) {
	        case 8:
	            // Currently the only supported compression method:
	            $data = gzinflate($body,$maxlength);
	            break;
	        default:
	            $error = "Unknown compression method.";
	            return false;
	        }
	    }  // zero-byte body content is allowed
	    // Verifiy CRC32
	    $crc   = sprintf("%u",crc32($data));
	    $crcOK = $crc == $datacrc;
	    $lenOK = $isize == strlen($data);
	    if (!$lenOK || !$crcOK) {
	        $error = ( $lenOK ? '' : 'Length check FAILED. ') . ( $crcOK ? '' : 'Checksum FAILED.');
	        return false;
	    }
	    return $data;
	}
}

?>