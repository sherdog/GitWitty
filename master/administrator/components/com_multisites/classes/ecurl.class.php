<?php
//-----------------------------------------------------------------
//-- Copyright 5ubliminal 2008. (5ubliminal.com)
// http://www.tellinya.com/art2/39/
// You can do anything with it except selling it or claiming it.
// Copyright notice must remain intact and links are appreciated!
//
// Many small modifications added by W-Shadow (http://w-shadow.com/)
//-----------------------------------------------------------------
class eHttpClient{
    //--
    var $httpRecvHeaders    = "";
    var $authUserName        = "";
    var $authPassword        = "";
    var $cookieJar            = "";
    var $selfDestroyCookies    = true;
    var $curl;
    //--
    function eHttpClient(){ $this->__construct(); }
    function __construct() {
        $this->curl = curl_init();
        $this->cookieJar = tempnam('/tmp','cookie');
        //-- If you decide to use persistent cookies UNCOMMENT next line
        //$this->cookieJar = str_replace('\\','/',dirname(__FILE__).'/cookies.curl.txt');
        $this->_initCurl();
    }
    function __destruct() {
        curl_close($this->curl);
        //-- Comment next line to keep cookies between sessions
        unlink($this->cookieJar);
    }
    function getCookieJar(){ return $this->cookieJar; }
    //-- Add your own setting to internal cURL instance
    function configCurl($option, $value){
        return curl_setopt ($this->curl, $option, $value);
    }
    //-- Enable of disable redirects
    function setRedirects($follow=true){
        return $this->configCurl(CURLOPT_FOLLOWLOCATION, $follow);
    }
    function setTimeout($timeout=30){
        return $this->configCurl(CURLOPT_TIMEOUT, $timeout);
    }
    //-- Explicitly ask cURL you don't need the body
    function getBody($enable=1){
        return $this->configCurl(CURLOPT_NOBODY, !$enable);
    }
    //-- Set the refererURL ... referer spam :)
    function setReferer($referer=false){
        return $this->configCurl(CURLOPT_REFERER, $referer);
    }
    //-- Internal ... DO NOT USE
    function _initCurl(){
        $this->configCurl(CURLINFO_HEADER_OUT, 1);
        $this->configCurl(CURLOPT_SSL_VERIFYPEER, 0);
        $this->configCurl(CURLOPT_SSL_VERIFYHOST, 0);
        $this->configCurl(CURLOPT_RETURNTRANSFER, 1);
        $this->configCurl(CURLOPT_HEADER, 1);
        //$this->configCurl(CURLOPT_MUTE, 0);
        $this->configCurl(CURLOPT_AUTOREFERER, 1);
        $this->configCurl(CURLOPT_FORBID_REUSE, 1);
        $this->configCurl(CURLOPT_FRESH_CONNECT, 1);
        $this->configCurl(CURLOPT_COOKIEFILE, $this->cookieJar);
        $this->configCurl(CURLOPT_COOKIEJAR, $this->cookieJar);
        $this->setUserAgent("ie");
        $this->setRedirects();
    }
    //-- Predefined values of your own
    function setUserAgent($ua){
        if($ua=="gg")
            $httpUserAgent = "Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)";
        elseif($ua=="ms")
            $httpUserAgent = "msnbot/1.0 (+http://search.msn.com/msnbot.htm)";
        elseif($ua=="yh")
            $httpUserAgent = "Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)";
        elseif($ua=="ie")
            $httpUserAgent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; en)";
        elseif($ua=="ff")
            $httpUserAgent = "Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US; rv:1.8.1.8) Gecko/20071008 Firefox/2.0.0.8";
        else
            $httpUserAgent = $ua;
        return $this->configCurl(CURLOPT_USERAGENT, $httpUserAgent);
    }
    //-- Private ... Do Not Use!
    function _prepare($verb,$url,$headers=null,$sysheaders=null){
        //--
        if((isset($sysheaders) && !is_array($sysheaders)) || !isset($sysheaders)){
            $sysheaders=array();
        }
        if((isset($headers) && !is_array($headers)) || !isset($headers)){
            $headers=array();
        }
        //--
        if(is_array($headers) && count($headers)){
            foreach($headers as $key => $header){
                if(preg_match("/([^:]+):\s?(.+)?/i",$header,$pcs)){
                    $headers[$pcs[1]]=$pcs[2];
                    unset($headers[$key]);
                }
            }
            unset($headers["Content-Type"]);
            unset($headers["Content-Length"]);
        }
        //--
        $this->configCurl(CURLOPT_CUSTOMREQUEST, $verb);
        $this->configCurl(CURLOPT_URL, $url);
        if(strlen($this->authPassword) && strlen($this->authPassword)){
            $loginInfo = sprintf("%s:%s",$this->authUserName,$this->authPassword);
            $this->configCurl(CURLOPT_HTTPAUTH, CURLAUTH_BASIC); 
            $this->configCurl(CURLOPT_USERPWD, $loginInfo);
        }
        $sendHeaders = $headers;
        if(count($sysheaders)){
            $sendHeaders = array_merge($sendHeaders, $sysheaders);
        }
        $rawHeaders = array("Connection: close");
        if(count($sendHeaders)){
            foreach($sendHeaders as $key => $vals){
                if(!is_array($vals)){
                    array_push($rawHeaders,$key.": ".$vals);
                    continue;
                }
                $vals = array_unique($vals);
                foreach($vals as $val){
                    array_push($rawHeaders,$key.": ".$val);
                }
            }
        }
        $this->configCurl(CURLOPT_HTTPHEADER, $rawHeaders);
        $this->httpRecvHeaders = array();
    }
    //-- Get a URL. $getdata is an associated array that contains query string variables
    //-- used with http_build_query
    function get($url, $getdata=null, $headers=null){
        if((isset($getdata) && !is_array($getdata)) || !isset($getdata)){
            $getdata=array();
        }
        if(count($getdata) && ($getdata!=false)){
            $getdata=http_build_query($getdata);
            $url.=(!strchr($url,'?') ? "?" : "&").$getdata;
        }
        //-- nogenius pointed out a bug that was fixed here
        //-- I forgot to clear content-length after using a post and future gets failed
        $this->configCurl(CURLOPT_POSTFIELDS,null);
        //$this->configCurl(CURLOPT_POSTFIELDSIZE,0);
        $this->_prepare("GET",$url,$headers);
        return $this->_fetchHtml();
    }
    //-- Get a URL. $getdata is an associated array that contains query string variables
    //-- used with http_build_query. We use HEAD verb here.
    function head($url, $getdata=null, $headers=null){
        if((isset($getdata) && !is_array($getdata)) || !isset($getdata)){
            $getdata=array();
        }
        if(count($getdata) && ($getdata!=false)){
            $getdata=http_build_query($getdata);
            $url.=(!strchr($url,'?') ? "?" : "&").$getdata;
        }
        $this->_prepare("HEAD",$url,$headers);
        return $this->_fetchHtml();
    }
    //-- Get a URL. $getdata is an associated array that contains query string variables
    //-- used with http_build_query. We use POST verb here.
    function post($url, $postdata='', $headers=null, $type="application/x-www-form-urlencoded"){
        $sendHeaders = array();
        if(is_array($postdata)){
            $postdata = http_build_query($postdata,'','&');
            $contentType = "application/x-www-form-urlencoded";
        }else{
            $contentType = $type;
        }
        //--
        $this->configCurl(CURLOPT_POSTFIELDS, $postdata);
        //$this->configCurl(CURLOPT_POSTFIELDSIZE, strlen($postdata));
        //--
        $sendHeaders["Content-Type"]   = $contentType;
        $sendHeaders["Content-Length"]   = strlen($postdata);
        //-- The Class is right not the user!
        $this->_prepare("POST",$url,$headers,$sendHeaders);
        return $this->_fetchHtml();
    }
    //-- Internal ... DO NOT USE!
    function _parseHeaders($headers){
        if(is_string($headers)) $headers = preg_split("/[\r\n]+/",$headers);
        $hdret        = array("Raw" => array());
        $httpinf    = $headers[0];
        $hdret['HTTP'] = $httpinf;
        array_splice($headers,0,1);
        foreach($headers as $hdr){
            $hdr=trim($hdr);
            if(!strlen($hdr)) continue;
            if(!preg_match("/([^:]+):(.*)/",$hdr,$pcs)){
                array_push($hdret['Raw'],$hdr);
                continue;
            }
            $key = trim($pcs[1]);
            $val = trim($pcs[2]);
            if(isset($hdret[$key])){
                if(!is_array($hdret[$key]))
                    $hdret[$key]=array($hdret[$key]);
                array_push($hdret[$key],$val);
            }else{
                $hdret[$key]=$val;
            }
        }
        if(!count($hdret['Raw'])) unset($hdret['Raw']);
        return $hdret;
    }
    //-- Internal ... DO NOT USE!
    function _fetchHtml(){
        $html    = curl_exec ($this->curl);
        $inf        = $this->getInfo();
        $reqsize    = $this->getInfo(CURLINFO_HEADER_SIZE);
        $req        = substr($html,0,$reqsize);
        $req        = str_replace("\r","",$req);
        $lines    = explode("\n",$req);
        $pos        = 0;
        $reqbkt    = array();
        $this->httpRecvHeaders = array();
        while(count($lines)){
            $line = $lines[0];
            array_splice($lines,0,1);
            if(strlen($line)==0){
                if(!count($reqbkt)) continue;
                array_push($this->httpRecvHeaders,$reqbkt);
                $reqbkt=array();
                continue;
            }
            if(!count($reqbkt)){
                if(preg_match("/^[^\s]+\s([0-9]+)\s.*$/i",$line,$pcs)){
                    $reqbkt['HTTP']=$pcs[1];
                    continue;
                }
            }
            if(!preg_match("/^([^\s:]+):\s?(.*)$/i",$line,$pcs)) continue;
            $reqbkt[trim($pcs[1])]=trim($pcs[2]);
        }
        return substr($html,$reqsize);
    }
    //-- Get the CURL info
    function getInfo($cfg=null){
        if(!isset($cfg)) return curl_getinfo($this->curl);
        return curl_getinfo($this->curl,$cfg);
    }
    
    //Returns the latest HTTP response code
    function httpCode(){
		return curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
	}
    
    //-- Get Received headers. In case of redirects you will find more then one.
    function getHeaders(){
        return $this->httpRecvHeaders;
    }
    //-- Get last header. In case of redirects you will find last one.
    function getHeader(){
        $hdr=array_pop($this->httpRecvHeaders);
        array_push($this->httpRecvHeaders,$hdr);
        return $hdr;
    }
    //-- Get the exact sent headers ... good for debugging
    function getSentHeaders(){
        $sentHeaders = curl_getinfo($this->curl, CURLINFO_HEADER_OUT);
        return $this->_parseHeaders($sentHeaders);
    }
    //-- HTTP AUTH data for ... cPanel or WordTracker Login :)
    function setAuth($user,$pass){
        if(func_num_args()==0){
            return $this->resetAuth();
        }
        if(!strlen($user) || !strlen($pass)) return;
        $this->authUserName = $user;
        $this->authPassword = $pass;
    }
    //-- Clear HTTP Auth Data
    function resetAuth(){
        $this->authUserName = "";
        $this->authPassword = "";
    }
    //-- For more info comment form is below. If you use this heavily ... link back :)
};
//---------------------------------------------------

?>