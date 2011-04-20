<?php
/**
 * @file       site.php
 * @version    1.2.44
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
 * - V1.1.0 24-SEP-2008: Add the Deploy Directory
 *                       Add the expiration date checking
 *                       Add the Master Index list of domains resolutions (indexDomains)
 * - V1.2.0 19-MAY-2009: Check website dependencies and add a cache on the template.
 * - V1.2.6 13-SEP-2009: Add interface to check if the "user sharing" is used.
 * - V1.2.7 22-SEP-2009: Implement an alternate algorithm to compute the "fromUserTableName"
 *                       when MySQL SHOW create VIEW is not allowed.
 *                       In this case, use the template ID to simulate the result of the SHOW CREATE VIEW.
 * - V1.2.9 22-SEP-2009: Fix a "syntax error" in strpos that may result by wrong getFromUserTablename
 * - V1.2.14 24-NOV-2009: Add the default FTP parameters
 * - V1.2.20 01-FEB-2010: Remove a PHP 5 warning
 * - V1.2.29 28-MAY-2010: Add letter tree directory processing to load a site information
 * - V1.2.30 01-JUN-2010: Fix letter tree directory processing to load a site information
 * - V1.2.33 13-JUL-2010: Joomla 1.6 beta 4 compatibility
 * - V1.2.44 03-DEC-2010: Allow possibility to disable the "refresh" icon computation that require
                          to count the number of table in each slave site.
                          This can be helpfull when a lot of slave sites must be defined in JMS Multisites.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

@include_once( JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_multisites' .DIRECTORY_SEPARATOR. 'classes' .DIRECTORY_SEPARATOR. 'lettertree.php');

// ===========================================================
//            Site class
// ===========================================================
/**
 * @brief This is a site record.
 *
 * Generally used in collection, this class contain all the information of a Site.\n
 * A site is defined by:
 */
class Site extends JObject
{
   var $id              = '';       /**< Identifier = Directory name */
   var $site_prefix     = '';       /**< Optional: Site Prefix specified by the front-end */
   var $site_alias      = '';       /**< Optional: Site Alias specified by the front-end */
   var $siteComment     = '';       /**< Optional: Site Alias specified by the front-end */
   var $status          = '';       /**< P=Pending, C=Confirmed, X=Cancel */
   var $payment_ref     = '';       /**< Optional: Payment reference */
   var $expiration      = '';       /**< Optional: Expiration date */
   var $owner_id        = '';       /**< Optional: This is the user_id of the user that has created the website */
   var $sitename        = '';       /**< Site name field defined in Joomla! 'configuration.php' */
   var $domains         = array();  /**< The list of domain name (stored in 'config_multisites.php') */
   var $indexDomains    = array();  /**< The list of domain name where the keywords are resolved and used to create the master index */
   var $fromTemplateID  = '';       /**< Optional: Template ID that must be used to create the new site */
   var $toSiteName      = '';       /**< Optional: New site title */
   var $shareDB         = '';       /**< Optional: Flag to request sharing the same DB parameters (all including the prefix) */
   var $toDBType        = '';       /**< Optional: New DB type (MySQL, MySQLI) */
   var $toDBHost        = '';       /**< Optional: New DB host name (server name) */
   var $toDBName        = '';       /**< Optional: New DB Name */
   var $toDBUser        = '';       /**< Optional: New DB User Name */
   var $toDBPsw         = '';       /**< Optional: New DB Password */
   var $toPrefix        = '';       /**< Optional: New DB prefix */
   var $newAdminEmail   = '';       /**< Optional: New administrator email */
   var $newAdminPsw     = '';       /**< Optional: New administrator password */
   var $deploy_dir      = '';       /**< Optional: For unix platform, it gives the path where the slave site is deployed  */
   var $deploy_create   = '';       /**< Optional: Flag that indicate if the deploy directory can be created or only use an exisitng directory  */
   var $alias_link      = '';       /**< Optional: For unix platform, it gives the path where an alias Symbolic Link can be created */
   var $media_dir       = '';       /**< Optional: Specify the path of the media folder that must be used by the slave site  */
   var $images_dir      = '';       /**< Optional: Specify the path of the images directory that must be used by the slave site  */
   var $templates_dir   = '';       /**< Optional: Specify the path of the front-end templates directory that must be used by the slave site  */
   var $tmp_dir         = '';       /**< Optional: Specify the path of the temporary folder  */
   var $host            = '';       /**< DB Server (host) defined in Joomla! 'configuration.php' */
   var $db              = '';       /**< DB name (db) defined in Joomla! 'configuration.php' */
   var $dbprefix        = '';       /**< DB prefix defined in Joomla! 'configuration.php' */
   var $user            = '';       /**< DB user name (user) defined in Joomla! 'configuration.php' */
   var $password        = '';       /**< DB password (password) defined in Joomla! 'configuration.php' */
   var $toFTP_enable    = '';       /**< Optional: New FTP enable (0=no, 1=yes, -1 or empty=default) */
   var $toFTP_host      = '';       /**< Optional: New FTP host name */
   var $toFTP_port      = '';       /**< Optional: New FTP port number */
   var $toFTP_user      = '';       /**< Optional: New FTP user name */
   var $toFTP_psw       = '';       /**< Optional: New FTP password */
   var $toFTP_rootpath  = '';       /**< Optional: New FTP root path */

   var $_success        = false;    /**< Flag that is set to true when some information is loaded */
   var $_template       = null;     /**< Cache to the template corresponding to the "fromTemplateID" */
   var $_newExtensions  = null;     /**< Flag that is set to true when new extension are present in the template site that potentially require a syncrhonization */


   //------------------- getInstance ---------------
   function &getInstance( $site_id)
   {
		static $instances;

		if (!isset( $instances )) {
			$instances = array();
		}

		if ( empty( $instances[$site_id]))
		{
   		$site = new Site();
   		if ( $site_id == ':master_db:') {
   		   $config           = & Jms2WinFactory::getMasterConfig();
   		   
   		   $site->id         = ':master_db:';
   		   $site->host       = $config->getValue( 'config.host');
   		   $site->db         = $config->getValue( 'config.db');
   		   $site->dbprefix   = $config->getValue( 'config.dbprefix');
   		   $site->user       = $config->getValue( 'config.user');
   		   $site->password   = $config->getValue( 'config.password');
   		}
   		else {
      		$site->load( $site_id);
   		}
		   $instances[$site_id] = & $site;
		}
		
		return $instances[$site_id];
	}

   //------------------- Constructor ---------------
   function Site()
   {
      $this->_success  = false;
   }
   
   //------------------- isExpired ---------------
   /**
    * @return true when the website contain an expiration date > now()
    */
   function isExpired()
   {
      // If no expiration then this is unlimited site
      if ( empty( $this->expiration)) {
         return false;
      }
      $expiration = strtotime( $this->expiration);
      $now        = strtotime( 'now');
      
      $expiration_str = strftime( '%Y-%m-%d', $expiration);
      $now_str        = strftime( '%Y-%m-%d', $now);

      if ( $expiration_str < $now_str) {
         return true;
      }
      return false;
   }

   //------------------- _countTables ---------------
   /**
    * @brief Compute the number of tables present in the site ID
    */
   function _countTables( $site_id)
   {
      // Get a temporary DB connection
      $db = & Jms2WinFactory::getMultiSitesDBO( $site_id, true);
      if ( empty( $db)) {
         return null;
      }

      $dbprefix      = str_replace('_' , '\_', $db->getPrefix());
      $db->setQuery( 'SHOW TABLES LIKE \''.$dbprefix.'%\'' );
      $tables = $db->loadResultArray();
      if ( empty( $tables)) {
         return null;
      }
      return count( $tables);
   }

   //------------------- _getTemplate ---------------
   /**
    * @return The template associated to the site or null when there is "fromTemplateID"
    */
   function &getTemplate( $templateID = null, $forceRefresh = false)
   {
	   static $none = null;
	   
      // If the template is already present and we don't want to refresh it
      if ( !empty( $this->_template) && !$forceRefresh) {
         // Use the current template
         return $this->_template;
      }
      
      // Otherwise, load the associated template using the fromTemplateID when there is no specific one.
      if ( empty( $templateID)) {
         $templateID = $this->fromTemplateID;
      }

      if ( empty( $templateID) || $templateID == ':master_db:') {
         return $none;
      }
      
      $this->_template = new Jms2WinTemplate();
		$this->_template->load( $this->fromTemplateID);
		
		return $this->_template;
   }
   
   //------------------- isNewExtensions ---------------
   /**
    * @return true when the website contain an expiration date > now()
    */
   function isNewExtensions( $forceCheck = false)
   {
      // If a result is already computed and not force to recheck the value
      if ( !is_null( $this->_newExtensions) && !$forceCheck) {
         return $this->_newExtensions;
      }
      
      // By default, we consider that there is no new extension
      $this->_newExtensions = false;
      
      // If the count of tables present in the slave site can be disabled.
      if ( defined( 'MULTISITES_REFRESH_DISABLED') && MULTISITES_REFRESH_DISABLED) {
         return $this->_newExtensions;   // False
      }
      
      // If there is no template
      if ( empty( $this->fromTemplateID)) {
         return $this->_newExtensions;   // False
      }
      
      if ( $this->fromTemplateID == ':master_db:') {
   		// Count the number of tables present in the Master Website
   		$fromCount = $this->_countTables( $this->fromTemplateID);
   		if ( empty( $fromCount)) {
            return $this->_newExtensions;   // False
   		}
      }
      else {
         $template = & $this->getTemplate();
   		
   		// Count the number of tables present in the website template
   		$fromCount = $this->_countTables( $template->fromSiteID);
   		if ( empty( $fromCount)) {
            return $this->_newExtensions;   // False
   		}
      }

		// Count the current number of tables
		$toCount   =  $this->_countTables( $this->id);
		
		if ( empty( $toCount) || $fromCount == $toCount) {
         return $this->_newExtensions;   // False
		}
		
      $this->_newExtensions = true;
      return $this->_newExtensions;
   }
   
   //------------------- load ---------------
   /**
    * @brief Load informatoin from Joomla 'configuration.php' and Multisites 'config_multisites.php'
    *
    * When the Joomla 'configuration.php' file is present, it loads:
    * - sitename;
    * - host;
    * - db;
    * - dbprefix;
    * - user;
    * - password.
    * When the Multisites 'config_multisites.php' file is present, it loads:
    * - domains.
    */
   function load( $sitename)
   {
      $this->_success = false;
      $this->id       = $sitename;
      $this->sitename = $sitename;

      // Check if there are domains associated to the ID (directory)
      $this->site_dir = JPATH_MULTISITES .DS. $sitename; 
      $filename = $this->site_dir .DS. 'config_multisites.php';
      if ( file_exists( $filename)) {}
      // Retry with letter tree directory structure
      else {
         if ( class_exists( 'MultisitesLetterTree')) {
            // Try to compute a path using the letter tree
            $lettertree_dir = MultisitesLetterTree::getLetterTreeDir( $sitename);
            if( !empty( $lettertree_dir)) {
               $site_dir = JPATH_MULTISITES.DIRECTORY_SEPARATOR.$lettertree_dir;
               $filename = $site_dir.DIRECTORY_SEPARATOR.'config_multisites.php';
               if ( file_exists( $filename)) {
                  $this->site_dir = $site_dir; 
               }
            }
         }
      }
      if ( file_exists( $filename)) 
      {
         include $filename;
         if ( isset( $domains)) {
            $this->domains = $domains;
         }
         if ( isset( $indexDomains)) {
            $this->indexDomains = $indexDomains;
         }
         // There is no "deploy_dir" variable in the config_multisites 
         // AND there is a config_dir array with an entry for 'deploy_dir'
         if ( empty( $deploy_dir) && !empty( $config_dirs['deploy_dir'])) {
            $deploy_dir = $config_dirs['deploy_dir'];
         }
         if ( isset( $deploy_dir)) {
            $this->deploy_dir = $deploy_dir;
         }
         if ( isset( $newDBInfo)) {
            foreach( $newDBInfo as $key => $value) {
               $this->$key = $value;
            }
         }
      }
      
      // If there is a deployment directory
      if ( !empty( $deploy_dir)) {
         // Use it to find the configuration file
         $c = substr( $deploy_dir, -1);
         if ( $c == '\\' || $c== '/') {
            $config = $deploy_dir . 'configuration.php';
         }
         else {
            $config = $deploy_dir .DS. 'configuration.php';
         }
      }
      // Use the multisite directory
      else {
         $config = $this->site_dir .DS. 'configuration.php';
      }
      
      if ( file_exists( $config)) {
         $handle = fopen( $config, "r");
         if ($handle) {
            while (!feof($handle)) {
               $line = fgets($handle, 4096);

               // Parse line like
               // var $name = 'value';
               
               // Remove var
               $line = trim( str_replace( "var", "", $line));
               $line = trim( str_replace( "public", "", $line));
               $arr = explode("=", $line);
               if ( count( $arr) == 2) {
                  $varname = trim( $arr[0]);
                  // Remove the ' and ;
                  $value = trim( $arr[1]);
                  $value = substr( $value, 1, strlen( $value) -3);
                  
                  if ( $varname == '$sitename') {
                     $this->sitename = $value;
                  } 
                  else if ( $varname == '$host') {
                     $this->host = $value;
                  } 
                  else if ( $varname == '$db') {
                     $this->db = $value;
                  }
                  else if ( $varname == '$dbprefix') {
                     $this->dbprefix = $value;
                  }
                  else if ( $varname == '$user') {
                     $this->user = $value;
                  }
                  else if ( $varname == '$password') {
                     $this->password = $value;
                  }
               }
            }
            fclose($handle);
            $this->_success  = true;
//            $this->isNewExtensions( true);
         }
      }
      
      
      return $this->_success;
   }

   //------------------- loadArray ---------------
   /**
    * @brief Convert all public properties of this class into an array.
    */
   function loadArray( $sitename)
   {
      if ( !$this->load( $sitename)) {
         return null;
      }
      
		// Convert public fields into an array and remove empty values
		$enteredvalues = $this->getProperties();
		foreach ( $enteredvalues as $key => $value) {
		   if ( empty( $value)) {
		      unset( $enteredvalues[$key]);
		   }
		}
      
      return $enteredvalues;
   }


   //------------  is_Site ---------------
   /**
    * @brief Check if the name correspond to a directory containing the "configuration.php" file
    *        or "config_multisites.php' or an "index.php" file with the define 'MUTLTISITES_DIR'.
    */
   function is_Site( $aName, $path=null)
   {
      $rc = false;
      if ( empty( $path)) {
         $path = JPATH_MULTISITES .DS. $aName;
      }
      if ( is_dir( $path))
      {
         // If there is a configuration file, this means this is site directory
         $config = $path .DS. 'configuration.php';
         if ( file_exists( $config)) {
            return true;
         }
         
         // If there is a multisites configuration file, this means this is site directory
         $config = $path .DS. 'config_multisites.php';
         if ( file_exists( $config)) {
            return true;
         }
         
         // When there is no configuration, perhaps it is not yet install
         // Therefore, check if MutliSites wrapper is present
         $index = $path .DS. 'index.php';
         if ( file_exists( $index)) {
            $str = file_get_contents( $index);
            // If the following line is present, this is a wrapper file
            // define( 'MULTISITES_DIR', dirname(__FILE__));
            if ( preg_match( '/MULTISITES_DIR/i', $str, $matches)) {
               return true;
            }
         }
      }
      
      return false;
   }
   
   //------------ getFromSiteID ---------------
   /**
    * @brief Compute the FromSiteID.
    */
   function getFromSiteID()
   {
      // If already compute, re-use the value.
      if ( isset( $this->fromSiteID)) {
         return $this->fromSiteID;
      }
      
      $fromSiteID = null;
      
	   // If there is a template used by the website to create the new website (prefix)
	   if ( !empty( $this->toPrefix) 
	     && !empty( $this->fromTemplateID)) {
	      // If this is directly the master website
	      if ( $this->fromTemplateID == ':master_db:') {
		      $fromSiteID = ':master_db:';
	      }
	      // Otherwise try load the associated template
	      else {
	         $template = & $this->getTemplate();
   		   // If the site uses a template
   		   if ( !empty( $template) && !empty( $template->fromSiteID)) {
   		      $fromSiteID = $template->fromSiteID;
   		   }
	      }
	   }
	   
	   $this->fromSiteID = $fromSiteID;
	   return $fromSiteID;
   }

   //------------ withUserSharing ---------------
   /**
    * @brief Test if the site has the joomla users table created as a View of a table.
    * When #__users is a view, return true;
    * Otherwise return false;
    */
   function withUserSharing()
   {
      // If not already computed
      if ( !isset( $this->_withUserSharing)) {
         // By default, the site does not share the users
         $this->_withUserSharing = false;

         // Get a temporary DB connection
         $db = & Jms2WinFactory::getMultiSitesDBO( $this->id, true);
         if ( !empty( $db)) {
            // Check if '#__users' is a VIEW;
            if ( MultisitesDatabase::_isView( $db, $db->getPrefix().'users')) {
               $this->_withUserSharing = true;
            }
         }
      }

      return $this->_withUserSharing;
   }

   //------------ getThisUserTablename ---------------
   /**
    * @brief return the normalized (dbname.dbtable) corresponding to '#__users".
    */
   function getThisUserTablename()
   {
      // If not already computed
      if ( !isset( $this->_thisUserTablename)) {
         // By default, the site does not share the users
         $this->_thisUserTablename = '';

         // Get a temporary DB connection
         $db = & Jms2WinFactory::getMultiSitesDBO( $this->id, true);
         if ( !empty( $db)) {
            $path = array( $db->_dbname, $db->getPrefix().'users');
            $path = MultisitesDatabase::backquote( $path);
            $this->_thisUserTablename = implode( '.', $path);
         }
      }

      return $this->_thisUserTablename;
   }

   //------------ getFromUserTablename ---------------
   /**
    * @brief return the normalized (dbname.dbtable) from whitch the  to '#__users" is cominng from.
    * When the table is a view, this search for the select statement in which the "from" DB is derived
    */
   function getFromUserTablename()
   {
      // If not already computed
      if ( !isset( $this->_fromUserTablename)) {
         // By default, the site does not share the users
         $this->_fromUserTablename = '';

         // Get a temporary DB connection
         $db = & Jms2WinFactory::getMultiSitesDBO( $this->id, true);
         if ( !empty( $db)) {
            // Check if '#__users' is a VIEW;
            $tablename = $db->getPrefix().'users';
            if ( MultisitesDatabase::_isView( $db, $tablename)) {
               $fromName = MultisitesDatabase::getViewFrom( $db, $tablename);
               if ( !empty( $fromName)) {
                  // If composed of a 'host'.'table' => check '`.`'
                  $pos = strpos( $fromName, '`.`');
                  // If not found host
                  if ( $pos === false) {
                     // This mean this is in the same DB
                     $path   = array( $db->_dbname);
                     $path   = MultisitesDatabase::backquote( $path);
                     $path[] = $fromName;
                     $this->_fromUserTablename = implode( '.', $path);
                  }
                  // If structured 'host'.'table'
                  else {
                     $this->_fromUserTablename = $fromName;
                  }
               }
               // If SHOW VIEWS does not exists, try using the template id to retreive the "from"
               else {
                  $fromSiteID = $this->getFromSiteID();
                  if ( !empty( $fromSiteID)) {
                     $fromdb = & Jms2WinFactory::getMultiSitesDBO( $fromSiteID, true);
                     if ( !empty( $fromdb)) {
                        $path   = array( $fromdb->_dbname);
                        $path   = MultisitesDatabase::backquote( $path);
                        $path[] = MultisitesDatabase::backquote( $fromdb->getPrefix().'users');
                        $this->_fromUserTablename = implode( '.', $path);
                     }
                  }
               }
            }
         }
      }

      return $this->_fromUserTablename;
   }
}

