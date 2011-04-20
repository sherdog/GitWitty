//_jms2win_begin v1.1.9
if ( defined( 'MULTISITES_ID')) {
$sef_config_file  = JPATH_ADMINISTRATOR.'/components/com_sh404sef/config/config.sef.' .MULTISITES_ID. '.php';
} else {
    $config_data .= "if ( defined( 'MULTISITES_ID') && file_exists( dirname(__FILE__) .DS. 'config.sef.' .MULTISITES_ID. '.php')) {\n"
                 .  "   include( dirname(__FILE__) .DS. 'config.sef.' .MULTISITES_ID. '.php');\n"
                 .  "} else {\n"
                 ;
}
//_jms2win_end
