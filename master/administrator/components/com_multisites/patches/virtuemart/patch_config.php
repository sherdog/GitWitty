//_jms2win_begin v1.2.14
		// Patch for VirtueMart 1.1.3 or lower
		// If slave site
		if ( defined( 'MULTISITES_ID')) {
		   $config_filename        = ADMINPATH .'virtuemart.' .MULTISITES_ID. '.cfg.php';
		   $master_wrapper         = '';
		   $master_wrapper_end     = '';
		   $master_url_wrapper     = '';
		   $master_url_wrapper_end = '';
		}
		else {
		   $config_filename        = ADMINPATH .'virtuemart.cfg.php';
		   $master_wrapper         = "if ( defined( 'MULTISITES_ID') && file_exists( dirname(__FILE__) .DS. 'virtuemart.' .MULTISITES_ID. '.cfg.php')) {\n"
		                           . "   include_once( dirname(__FILE__) .DS. 'virtuemart.' .MULTISITES_ID. '.cfg.php');\n"
		                           . "} else {"
		                           ;
		   $master_wrapper_end     = "}\n";
		   $master_url_wrapper     = "if ( defined( 'MULTISITES_HOST')) {\n"
                                 . "   define( 'URL', 'http://'.MULTISITES_HOST.'/' );\n"
                                 . "   define( 'SECUREURL', '". ((strncmp( $d['conf_SECUREURL'], 'https',5) == 0) ? 'https://' : 'http://')."'.MULTISITES_HOST.'/' );\n"
                                 . "}\n"
                                 . "else {"
                                 ;
		   $master_url_wrapper_end = "}";
		}
		if (!$fp = fopen( $config_filename, "w")) {			
			$vmLogger->err( $VM_LANG->_('VM_CONFIGURATION_CHANGE_FAILURE',false).' ('. $config_filename );
//_jms2win_end
/*_jms2win_undo
		if (!$fp = fopen(ADMINPATH ."virtuemart.cfg.php", "w")) {			
			$vmLogger->err( $VM_LANG->_('VM_CONFIGURATION_CHANGE_FAILURE',false).' ('. ADMINPATH ."virtuemart.cfg.php)" );
  _jms2win_undo */
