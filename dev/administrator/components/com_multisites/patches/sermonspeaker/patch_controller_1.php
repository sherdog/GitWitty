//_jms2win_begin v1.2.13
if ( defined( 'MULTISITES_ID')) {
	   $configfile = "components/com_sermonspeaker/config.sermonspeaker." .MULTISITES_ID. ".php";
	   if ( file_exists( __FILE__.DS.$configfile)) {
   	   $permission = is_writable($configfile);
	   }
	   if (!$permission) {
		   $this->setRedirect('index.php?option='.$option.'&task=config', MULTISITES_ID . " Configuration file not writeable!");
		   return;
	   }
}
//_jms2win_end
