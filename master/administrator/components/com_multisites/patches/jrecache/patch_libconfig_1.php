//_jms2win_begin v1.2.12
// If master website, add the wrapper to redirect to the appropriate config
if ( !defined( 'MULTISITES_ID')) {
$config .= "//_jms2win_begin v1.2.12\n";
$config .= "if ( defined( 'MULTISITES_ID')\n";
$config .= "  && file_exists( dirname(__FILE__) .DS. 'jrecache.config.' .MULTISITES_ID. '.php')) {\n";
$config .= "   require_once(  dirname(__FILE__) .DS. 'jrecache.config.' .MULTISITES_ID. '.php');\n";
$config .= "} else  {\n";
$config .= "//_jms2win_end\n";
}
//_jms2win_end
