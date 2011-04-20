//_jms2win_begin v1.2.13
if ( !defined( 'MULTISITES_ID')) {
	   $config .= "//_jms2win_begin v1.2.13\n";
	   $config .= "if ( defined( 'MULTISITES_ID')\n";
	   $config .= "  && file_exists( dirname(__FILE__) .DS. 'config.sermonspeaker.' .MULTISITES_ID. '.php')) {\n";
	   $config .= "   require_once(  dirname(__FILE__) .DS. 'config.sermonspeaker.' .MULTISITES_ID. '.php');\n";
	   $config .= "} else  {\n";
	   $config .= "//_jms2win_end\n";
}
//_jms2win_end
