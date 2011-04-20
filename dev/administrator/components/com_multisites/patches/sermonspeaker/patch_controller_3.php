//_jms2win_begin v1.2.13
if ( defined( 'MULTISITES_ID')) {
	   $cf = "components/com_sermonspeaker/sermoncastconfig.sermonspeaker." .MULTISITES_ID. ".php";
	   if ( file_exists( __FILE__.DS.$cf)) {
   	   $permission = is_writable($cf);
	   }
	   if (!$permission) {
		   $this->setRedirect('index.php?option='.$option.'&task=config', MULTISITES_ID . " SermonCast configuration file not writeable!");
		   return;
	   }
}
//_jms2win_end
