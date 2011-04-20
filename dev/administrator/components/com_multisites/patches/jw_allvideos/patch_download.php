//_jms2win_begin v1.2.49
// If this is a slave site and the path where the "configuration.php" is present then use this path as document root of the sitePath
if ( defined( 'MULTISITES_ID') && defined( 'MULTISITES_CONFIG_PATH')) { $sitePath = MULTISITES_CONFIG_PATH; }
//_jms2win_end
