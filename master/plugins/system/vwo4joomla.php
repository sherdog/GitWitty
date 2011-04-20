<?php
######################################################################
# Visual Website Optimizer For Jooma          	          	         #
# Copyright (C) 2011 by Analytics For Joomla   	   	   	   	   	   	 #
# Homepage   : www.AnalyticsForJoomla.com		   	   	   	   	   	 #
# Author     : Nico Kaag	    		   	   	   	   	   	   	   	 #
# Email      : info@analyticsforjoomla.com 	   	   	   	   	   	     #
# Version    : 1.0.0	                       	   	    	   	   	 #
# License    : http://www.gnu.org/copyleft/gpl.html GNU/GPL          #
######################################################################

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin');
jimport( 'joomla.filesystem.file' );
jimport( 'joomla.filesystem.path' );
jimport( 'joomla.client.helper' );


class plgSystemvwo4joomla extends JPlugin
{
	function plgSystemvwo4joomla(&$subject, $config)
	{
		parent::__construct($subject, $config);
		
		$this->_plugin = JPluginHelper::getPlugin( 'system', 'vwo4joomla' );
		$this->_params = new JParameter( $this->_plugin->params );
	}
	
	function onAfterRender()
	{
		global $mainframe;

		$vwo_id = $this->params->get('vwo_id', '');
			
		if($vwo_id == '' || $mainframe->isAdmin() || strpos($_SERVER["PHP_SELF"], "index.php") === false)
		{
			return;
		}
		
		$buffer = JResponse::getBody();

		$google_analytics_javascript = "
<!-- Start Visual Website Optimizer Code for Joomla by Analytics For Joomla v1.0 | http://www.analyticsforjoomla.com -->
<script type='text/javascript'>
var _vis_opt_account_id = ". $vwo_id .";
var _vis_opt_protocol = (('https:' == document.location.protocol) ? 'https://' : 'http://');
document.write('<s' + 'cript src=\"' + _vis_opt_protocol + 'dev.visualwebsiteoptimizer.com/deploy/js_visitor_settings.php?v=1&a='+_vis_opt_account_id+'&url='+encodeURIComponent(document.URL)+'&random='+Math.random()+'\" type=\"text/javascript\">' + '<\/s' + 'cript>');
</script>
<script type='text/javascript'>
if(typeof(_vis_opt_settings_loaded) == \"boolean\") { document.write('<s' + 'cript src=\"' + _vis_opt_protocol + 'd5phz18u4wuww.cloudfront.net/vis_opt.js\" type=\"text/javascript\">' + '<\/s' + 'cript>'); }
// if your site already has jQuery 1.4.2, replace vis_opt.js with vis_opt_no_jquery.js above 
</script>
<script type='text/javascript'>
if(typeof(_vis_opt_settings_loaded) == \"boolean\" && typeof(_vis_opt_top_initialize) == \"function\"){ _vis_opt_top_initialize(); 
vwo_$(document).ready(function() { _vis_opt_bottom_initialize(); }); }
</script>
<!-- End Visual Website Optimizer Code for Joomla by Analytics For Joomla v1.0-->
";
		
		$buffer = str_replace ("</head>", $google_analytics_javascript."</head>", $buffer);
		JResponse::setBody($buffer);
		
		return true;
	}
	
	function getDomain($url)
	{
	    if(filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED) === FALSE)
	    {
	        return false;
	    }
	    /*** get the url parts ***/
	    $parts = parse_url($url);
	    /*** return the host domain ***/
	    return $parts['scheme'].'://'.$parts['host'];
	}
}
?>