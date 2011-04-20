<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );

	// Retreive the contract information present in the session
	$session       =& JFactory::getSession();
	$site_id       = $session->get( 'site_id',      null, 'contracts');
	$payment_ref   = $session->get( 'payment_ref',  null, 'contracts');
	$validity      = $session->get( 'validity',     null, 'contracts');
	$vality_unit   = $session->get( 'vality_unit',  null, 'contracts');
	// If there is no session information
	if ( empty( $site_id) || empty( $payment_ref)) {
	   // This is the normal case. As this function is ALWAYS called (event when there is no contract)
	   // Skip the recording of contract information. 
	}
	else {
	   // Here we are in the case of a contract payment (this is a special order).
	   // Save the order ID into the site info to later allow check the payment and change the status of the site into confirmed or cancel
      $values = array();
      $values['order_id']     = $db->f("order_id");
      $values['order_number'] = $db->f("order_number");
      
      if ( !empty( $validity) && (int)$validity>0 && !empty( $vality_unit)) {
         $values['inc_validity']    = $validity;
         $values['inc_vality_unit'] = $vality_unit;
      }
      else {
         $values['inc_validity']    = 0;
         $values['inc_vality_unit'] = 'days';
      }
      
      require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'classes'.DS.'utils.php');
      MultisitesUtils::updateSiteInfo( $site_id, $values);
	}
?>
