<?php
/**
 * @file       vm_utils.php
 * @brief      Interface to VirtueMart that allow to add an item into the shopping cart based on its SKU.
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
 * - V1.1.0 11-OCT-2008: File creation
 */


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );


class Jms2Win_VM_Utils
{

   //------------ getVMProduct_id ---------------
   /**
    * @brief Return the product identifier corresponding to the VirtueMart SKU product in aim to display a "add cart button"
    */
	function getVMProduct_id( $sku)
	{
      require_once( JPath::clean( JPATH_ADMINISTRATOR.'/components/com_virtuemart/virtuemart.cfg.php'));
      require_once( CLASSPATH .'ps_database.php' );
      
      $db = new ps_DB;
		$db->query("SELECT product_id FROM #__{vm}_product WHERE product_sku='$sku' LIMIT 1" );
		$obj = null;
		$product_id = $db->loadResult( $obj);
	   return $product_id;
	}

   //------------ addItem_Cart ---------------
   /**
    * @brief Add an Item into VirtueMart shopping cart
    */
	function addItem_Cart( $Itemid, $site_id, $payment_ref, $sku, $validity=0, $vality_unit=null, $quantity=1)
	{
      /*** VirtueMart part ***/
        global $db, $my, $auth, $sess;
        require_once( JPath::clean( JPATH_ADMINISTRATOR.'/components/com_virtuemart/virtuemart.cfg.php'));
        include_once( ADMINPATH.'/compat.joomla1.5.php' );
        require_once( ADMINPATH. 'global.php' );
        require_once( CLASSPATH. 'ps_main.php' );
        require_once( CLASSPATH. 'ps_database.php' );
        require_once( CLASSPATH. 'ps_cart.php' );
        require_once( CLASSPATH. 'ps_checkout.php' );
   	  $GLOBALS['vmInputFilter'] = vmInputFilter::getInstance();
      /*** END VirtueMart part ***/

      // Retreive the VM product ID based on the SKU name
      $product_id = Jms2Win_VM_Utils::getVMProduct_id( $sku);
      

		// Save some information in the session in aim to finalize the transaction (to change the status in Confirmed or Cancelled)
		$session =& JFactory::getSession();
		$session->set( 'site_id',        $site_id,      'contracts');
		$session->set( 'payment_ref',    $payment_ref,  'contracts');
		if ( $validity > 0 && !empty( $vality_unit)) {
   		$session->set( 'validity',    $validity,     'contracts');
   		$session->set( 'vality_unit', $vality_unit,  'contracts');
		}

		// Add the product to the shopping cart
		$cart = new ps_cart();
		$cart->initCart();
		$d = array( 'Itemid'        => $Itemid,
		            'category_id'   => '',
		            'quantity'      => $quantity,
		            'product_id'    => $product_id,
		            'prod_id'       => $product_id,
		            'vendor_id'     => 1
		          );
		$cart->add( $d);
	}

   //------------ redirect_CheckOut ---------------
   /**
    * @brief Go to VirtueMart Check-out to proceed with payment
    */
	function redirect_CheckOut( $Itemid, $enteredvalues)
	{
	   global $mainframe;
	   
		// Redirect to the check out
	   $root_url = JURI::base();
		$mainframe->redirect( "$root_url/index.php?page=checkout.index&ssl_redirect=1&option=com_virtuemart&Itemid=$Itemid");
	}

} // End class
