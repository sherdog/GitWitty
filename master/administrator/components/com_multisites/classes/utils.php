<?php
/**
 * @file       utils.php
 * @version    1.2.32
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2010 Edwin2Win sprlu - all right reserved.
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
 * - V1.1.0 10-OCT-2008: File creation
 * - V1.1.20 19-APR-2009: Add escape character for the Key when converting an array to string.
 *                        This is to avoid the problem of apostrophe (') that may be present in the name of a directory
 * - V1.2.08 30-SEP-2009: Fix bug in updateSiteInfo that is called by plugin or other external extension to update the site info.
 * - V1.2.26 21-MAR-2010: Add the API to get the "Site Info" based on its "Site ID".
 * - V1.2.29 10-MAY-2010: Add the API to rebuild the master index.
 * - V1.2.32 20-JUN-2010: Add the possibility to UpdateStatus on All the websites.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

@include_once( JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_multisites' .DIRECTORY_SEPARATOR. 'classes' .DIRECTORY_SEPARATOR. 'lettertree.php');

// ===========================================================
//            MultisitesUtils class
// ===========================================================
/**
 * @brief Utility class used by MultiSites for generic purpose.
 */
class MultisitesUtils
{
   //------------ CnvArray2Str ---------------
   /**
    * Convert an array into a string that can be save in a configuration file. (valid PHP syntax).
    */
	function CnvArray2Str( $leadingSpaces, $arr)
	{
	   $result = '';
	   $sep = '';
	   $inline=true;
	   foreach( $arr as $key => $value) {
         if ( is_array( $value)) {
            $inline = false;
            if ( is_int( $key)) {
         	   $result .= $sep . "array( " . MultisitesUtils::CnvArray2Str( $leadingSpaces.'  ', $value) . ")";
            }
            else {
         	   $result .= $sep . "'" . addslashes($key) . "' => array( " . MultisitesUtils::CnvArray2Str( $leadingSpaces.'  ', $value) . ")";
            }
         }
         else {
            if ( is_int( $key)) {
		         $result .= $sep . "'" . addslashes($value) . "'";
		      }
		      else {
		         $result .= $sep . "'" . addslashes($key) . "' => '" . addslashes($value) . "'";
		      }
		   }
		   if ( $inline) {
   		   $sep = ", ";
		   }
		   else {
   		   $sep = ",\n" . $leadingSpaces;
		   }
	   }
	   return $result;
	}


   //------------ getSiteInfo ---------------
   /**
    * @brief Generic routine that allow to retreive the JMS website values from a site ID.
    */
	function &getSiteInfo( $site_id)
	{
      require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS. 'classes' .DS. 'site.php');
	   $site = & Site::getInstance( $site_id);
	   return $site;
	}

   //------------ updateSiteInfo ---------------
   /**
    * @brief Generic routine that allow to update some JMS website values using a site ID.
    *
    * This update the content of the file "config_multisites.php" file present in the site ID.\n
    * This routine start by reading the current configuration values. Next it update the DBInfo values
    * with new values parameter. Finally, it rewrite the configuration files with the new values.
    */
	function updateSiteInfo( $site_id, $values)
	{
	   $domains       = null;
	   // $deploy_dir    = null;
	   $newDBInfo     = null;
	   $config_dirs   = null;
	   
      // Load the configuration file if it exists
      $site_dir = JPATH_MULTISITES .DS. $site_id;
      $filename = $site_dir .DS. 'config_multisites.php';
      if ( !file_exists( $filename))
      {
         if ( class_exists( 'MultisitesLetterTree')) {
            // Try to compute a path using the letter tree
            $lettertree_dir = MultisitesLetterTree::getLetterTreeDir( $site_id);
            if( !empty( $lettertree_dir)) {
               $site_dir = JPATH_MULTISITES.DIRECTORY_SEPARATOR.$lettertree_dir;
               $filename = $site_dir.DIRECTORY_SEPARATOR.'config_multisites.php';
            }
         }
      }

      if ( file_exists( $filename))
      {
         include $filename;
         
         // Replace or Add the new DB Info
   		foreach( $values as $key => $value) {
   		   $newDBInfo[$key] = $value;
   		}
   		
   		// If the indexDomain that resolve the keyword in the domain is not present,
   		if ( empty( $indexDomains)) {
   		   // Consider the domain as already resolved
   		   $indexDomains = $domains;
   		}
   		
   		// Update the new configuration
         require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS. 'models' .DS. 'manage.php');
         return MultisitesModelManage::writeSite( $site_dir, $domains, $indexDomains, $newDBInfo, $config_dirs);
      }
	}


   //------------ updateStatus ---------------
   /**
    * @brief Update the status value based on a key and a value.
    * This function search for the site that has the key name = value expected.
    * This allow to use any key field in the scan. 
    * For example, VirtueMart use the 'order_id' field to identify the order 
    * and this information can be stored in the website info to later update the status
    *
    * This routine is used by billable website post processing to update an order status.\n
    * In case of VirtueMart, it is used by the plugin "onUpdateStatus"
    *
    * @par Implementation:
    * - For ALL websites;
    * - Check if the website match the 'key' with 'value' given in parameter
    * - For the website that match criteria, check if the status has changed.
    * - If the status has changed, update the site info with new status and recreate the master index to possibly change some information like 'expiration date'
    *
    * @param key     The Key name the must be used to retreive a website. (ie. 'order_id')
    * @param value   The value of the key. (ie 145  => 'order_id' = 145)
    * @param $newStatus An array with the list of new values to store in DBInfo.
    *                   (ie. array( 'status' => 'C'));
    * @return
    * - true  when update is OK
    * - false when an error occurs.
    */
   function updateStatus( $key, $value, $newStatus, $apply2AllSites = false)
   {
      $result        = false;
      $updateIndex   = false;
      
      // Get all website to retreive the one that correspond to the order_id
      require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS. 'models' .DS. 'manage.php');
      $model = new MultisitesModelManage();
      $sites = $model->getSites();
      
      // Search for the website that correspond to the order_id
      foreach( $sites as $site) {
         // If the Key match the value
         if ( isset( $site->$key) && !empty( $site->$key) && $site->$key == $value) {
            $site_id = $site->id;
            if ( class_exists( 'Debug2Win')) {
               Debug2Win::debug( "- $key [$value] found in site id [$site_id]");
            }
            $curStatus  = !empty( $site->status)
                        ? $site->status
                        : '';
            // If status has changed
            if ( $curStatus != $newStatus) {
               // Update the website status
               $values = array();
               $values['status'] = $newStatus;
               MultisitesUtils::updateSiteInfo( $site_id, $values);
               $updateIndex = true;
            }
            $result = true;
            if ( $apply2AllSites) {
               // continue
            }
            else {
               break;
            }
         }
      }
      
      // If finally the Master Index must also be updated.
      if ( $updateIndex) {
         // Update the master index to reflect the change
         $model->createMasterIndex();
      }
      
      return $result;
   }

   //------------ createMasterIndex ---------------
   /**
    *  @brief Refresh the master index
    */
   function createMasterIndex()
   {
      // Get all website to retreive the one that correspond to the order_id
      require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS. 'models' .DS. 'manage.php');
      $model = new MultisitesModelManage();
      $model->createMasterIndex();
   }
}

