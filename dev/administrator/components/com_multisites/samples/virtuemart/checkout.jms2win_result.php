<?php 
/**
 * @file       checkout.jms2win_result.php
 * @brief      This script must be called when VirtueMart receive confirmation of payment
 * @version    1.1.0
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008 Edwin2Win sprlu - all right reserved.
 * @license    This program is free software; you can redistribute it and/or
 *             modify it under the terms of the GNU General Public License
 *             as published by the Free Software Foundation; either version 2
 *             of the License, or (at your option) any later version.
 *             This program is distributed in the hope that it will be useful,
 *             but WITHOUT ANY WARRANTY; without even the implied warranty of
 *             MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU General Public License for more details.
 *             You should have received a copy of the GNU General Public License
 *             along with this program; if not, write to the Free Software
 *             Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *             A full text version of the GNU GPL version 2 can be found in the LICENSE.php file.
 * @par History:
 * - V1.1.0 19-OCT-2008: File creation
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

/*
   This script must be called when the status of an order is changed in aim to propagate the status
   to the website.
   
   For PayPal, it can be called from administrator/components/com_virtuemart/notify.php
*/

@include_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS. 'classes' .DS. 'debug.php');
Debug2Win::enableStandalone();      // Write the log in administrator/components/com_multisites/classes/logs
Debug2Win::setFileName( 'checkout.jms2win_result.log.php');
Debug2Win::enableDebug();        // Remove the comment to enable the debugging


/*****************************************/
/*             MAIN                      */
/*****************************************/

Debug2Win::debug_Start( ">> checkout.jms2win_result.php() - START", "JMS_Result: ");

// ======== Read the input parameters from the calling script ==========
if ( empty( $order_id)) {
   if ( isset( $d['order_id'])) {
      $order_id = $d['order_id'];
   }
}

Debug2Win::debug( "ORDER ID: [" . $order_id . "]");

// ======== Process the information ==========
// Retreive the VirtueMart order status
$dbor = new ps_DB();
$q  = "SELECT * FROM #__{vm}_orders "
    . " WHERE (order_id = '" . $order_id . "')";
$dbor->query($q);
$dbor->next_record();
$order_status = $dbor->f( 'order_status');
Debug2Win::debug( "- Order Status Code: [" . $order_status . "]");

// Convert the VirtueMart status code into JMS status code
$statusCodes = array( 'P' => 'Pending',
                      'C' => 'Confirmed',
                      'X' => 'Cancelled',
                      'W' => 'Pending'    // Waiting for payment confirmation (case of ClearPark)
                    );
$newStatus = $statusCodes[ $order_status];

require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'classes'.DS.'utils.php');
MultisitesUtils::updateStatus( 'order_id', $order_id, $newStatus);

Debug2Win::debug_stop( "<< checkout.jms2win_result.php() - STOP");

?>
