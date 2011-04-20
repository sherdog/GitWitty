<?php
/**
 * @file       template.php
 * @version    1.2.13
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2009 Edwin2Win sprlu - all right reserved.
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
 * - V1.1.0 24-SEP-2008: Initial version
 * - V1.2.0 28-APR-2009: Add the DB Sharing parameters
 * - V1.2.13 18-NOV-2009: Add the FTP parameters
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );


// ===========================================================
//            Jms2WinTemplate class
// ===========================================================
/**
 * @brief This is a Template record.
 *
 * Generally used in collection, this class contain all the information of a Template.\n
 * A site is defined by:
 */
class Jms2WinTemplate
{
   var $id              = '';       /**< Identifier = Template name */
   var $groupName       = '';       /**< Group name that can be used to filter template shown to the front-end users */
   var $sku             = '';       /**< Optional reference to a eCommerce product number (ie SKU of VirtueMart) */
   var $title           = '';       /**< Template title shown to the front-end users */
   var $description     = '';       /**< Template description shown to the front-end users */
   var $validity        = '';       /**< Optional: Validity duration */
   var $validity_unit   = '';       /**< Optional: Validity duration Unit (days, months, years) */
   var $maxsite         = '';       /**< Optional: Maximum number of slave sites that can be created for the ower. */
   var $expireurl       = '';       /**< Optional: URL where must be redirected the user when the validity period is expired. */
   var $fromSiteID      = '';       /**< Optional: Site ID that must be used as the reference DB */
   var $fromDB          = '';       /**< Computed: reference DB retreive from Site ID*/
   var $toSiteID        = '';       /**< Optional: New Site ID naming convention. Is used to generate a site id for the front-end */
   var $toDomains       = array();  /**< Optional: Generic domain name to used when creating a new site */
   var $toSiteName      = '';       /**< Optional: New site title */
   var $adminUserID     = '';       /**< Optional: Administrator User ID where the password can be modified */
   var $shareDB         = '';       /**< Optional: Flag to request sharing the same DB parameters (all including the prefix) */
   var $toDBType        = '';       /**< Optional: New DB type (MySQL, MySQLI) */
   var $toDBHost        = '';       /**< Optional: New DB host name (server name) */
   var $toDBName        = '';       /**< Optional: New DB Name */
   var $toDBUser        = '';       /**< Optional: New DB User Name */
   var $toDBPsw         = '';       /**< Optional: New DB Password */
   var $toPrefix        = '';       /**< Optional: New DB table prefix */
   var $deploy_dir      = '';       /**< Optional: For unix platform, it gives the path where the slave site is deployed  */
   var $deploy_create   = '';       /**< Optional: Flag that indicate if the deploy directory can be created or only use an exisitng directory  */
   var $alias_link      = '';       /**< Optional: For unix platform, it gives the path where an alias Symbolic Link can be created */
   var $media_dir       = '';       /**< Optional: Specify the path of the media directory that must be used by the slave site  */
   var $images_dir      = '';       /**< Optional: Specify the path of the images directory that must be used by the slave site  */
   var $templates_dir   = '';       /**< Optional: Specify the path of the front-end templates directory that must be used by the slave site  */
   var $tmp_dir         = '';       /**< Optional: Specify the path of the temporary folder  */
   var $toFTP_enable    = '';       /**< Optional: New FTP enable (0=no, 1=yes, -1 or empty=default) */
   var $toFTP_host      = '';       /**< Optional: New FTP host name */
   var $toFTP_port      = '';       /**< Optional: New FTP port number */
   var $toFTP_user      = '';       /**< Optional: New FTP user name */
   var $toFTP_psw       = '';       /**< Optional: New FTP password */
   var $toFTP_rootpath  = '';       /**< Optional: New FTP root path */

   var $symboliclinks   = array();  /**< Define the new directory structure (Symbolic Links, empty dir, copy, unzip */
   var $dbsharing       = array();  /**< Define the DB Sharing parameters */

   var $success         = false;    /**< Flag that is set to true when some information is loaded */

   //------------------- Constructor ---------------
   function Jms2WinTemplate()
   {
      $this->success  = false;
   }


   //------------ getTemplateFilename ---------------
	function getTemplateFilename()
	{
	   $filename = JPATH_MULTISITES .DS. 'config_templates.php';
	   return $filename;
	}

   //------------------- load ---------------
   /**
    * @brief Load the template collection.
    */
   function load( $id)
   {
      $this->success  = false;
      
	   $templates = array();
	   $filename = Jms2WinTemplate::getTemplateFilename();
	   @include( $filename);
	   
	   // If the template exists
	   if ( isset( $templates[$id])) {
	      $this->id = $id;
	      // Transfert the values into the class
         foreach( $templates[$id] as $key => $value) {
            $this->$key = $value;
         }
         $this->success  = true;
	   }
      return $this->success;
   }
}

