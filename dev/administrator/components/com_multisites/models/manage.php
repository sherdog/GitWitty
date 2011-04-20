<?php
/**
 * @file       manage.php
 * @version    1.2.46
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
 * - V1.0.7 22-AUG-2008: Replace file_exists, unlink, rmdir Joomla JFolder and JFile in aim to reduce permission problems.
 * - V1.0.9 07-SEP-2008: Fix minor return code value when delete of a site.
 * - V1.1.0 24-SEP-2008: Add the Deploy Directory and symbolic link creation
 *                       Add replication of database (fromPrefix -> toPrefix)
 * - V1.1.1 20-NOV-2008: Add processing of Symbolic Link return code
 * - V1.1.2 29-NOV-2008: Clone the config because some system use a reference that make the "from" and "to" configuration identical
 * - V1.1.3 02-DEC-2008: Replace getString by getCmd when reading the site ID to avoid special characters and the spaces.
 *                       Some customer are using spaces in the name of a site id.
 *                       Add checking when creating Symbolic Links to verify when it already exists, it they correspond to the same path.
 *                       In this case, does not report an error when creating an Symbolic Links that already exists with the same parameters
 *                       Also add some error message in case of DeploySite failure.
 *                       Add a control that Image and Media folder exists during the "special copy".
 *                       When a slave sites is created with a deployment directory and NO DB, in this case,
 *                       the image and media folder are not copied because the "to db" parameters can not be written.
 *                       In this case, the special copy will create a Symbolic Link on the master directory.
 * - V1.1.4 09-DEC-2008: Fix problem when creating a slave site from the master DB.
 *                       Also fix a problem in "special copy" when working on Windows platform. Ignore the Symbolic Link creation.
 * - V1.1.6 23-DEC-2008: Add checks on "deploy_dir" to verify that folder exists and is not the "root" directory.
 * - V1.1.8 25-DEC-2008: Update error message.
 * - V1.1.13 12-JAN-2009: Force the compute expiration date in American format to avoid problem with french accentuated characters.
 *                        Replace the "include" present in the redirection "index.php" index files by a eval statement in aim to keep
 *                        the __FILE__ value unchanged (The behavior was different depending on PHP server platform)
 *                        With the eval statment, the __FILE__ remain with the path of the deploy directory (and not the master directroy)
 *                        This allow some components such as JCE to have correct path present in group or other panel.
 * - V1.1.16 04-FEB-2009: Compute the master root path only when the JMS is manage from the master website
 *                        Otherwise, keep the master root path returned by the slave site.
 * - V1.1.20 05-APR-2009: Add the possibility to use the keyword {site_id} into a domain name.
 *                        Also reset the configuration.php live_site field to ensure it is empty and avoid using
 *                        an existing value as Root URL that could redirect the slave site to a wrong URL.
 * - V1.1.21 20-APR-2009: Add the possibility to resolve the keyword {site_id} present into "website template" domain name list.
 * - V1.2.0  28-APR-2009: Add processing of DB Sharing parameters.
 *                        Add verification when creating the master index to avoid using empty URL
 * - V1.2.0 RC4 08-JUL-2009: Add a verification when a symbolic link can not be created.
 *                        On PHP 5.2.8 or perhaps related to a configuration of the hosting provider, it was
 *                        not possible to check a symbolic link when using a relative path.
 *                        So we have add a second check on a full path.
 * - V1.2.0 RC5 25-JUL-2009: Fix a problem when creating the DB dynamically.
 *                         In that case, the tables was not replicated due to a bug in processing of the return code
 *                         of the DB creation.
 *                         In fact JMS has processed a sucessfull DB creation like an error that had resulted
 *                         by a stop in creation of the tables.
 *                         Also create an empty index.html to hide the list of slave sites present in the /multisites directory
 * - V1.2.4 23-AUG-2009: Joomla 1.6! compatibility.
 * - V1.2.6 13-SEP-2009: - Add MULTISITES_COOKIE_DOMAIN computation to allow single sign-in for sub-domain.
 *                         The current basic implementation consists in the definition of a defined when there is at least 
 *                         a slave site that share users.
 *                         This define a "cookie sub-domain" that is computed based on master domain name.
 *                         The basic current implementation is generic for all the domains.
 *                       - Create a special direction "installation" replication to avoid using Symbolic Links.
 *                         This create a directory an put a special "index.php" file that read (include) the original index.php
 *                         to keep the correct directory path.
 *                       - Add the possibility to copy and unzip the templates directory.
 *                         This avoid the "un-install" / "re-install" of templates when a specific directory is specified.
 * - V1.2.10 14-OCT-2009: - Improve the "templates" directory copy in case where the directory to copy contains symbolic links.
 *                          In this case, copy each linked directory content (instead of the link).
 *                          Also fix a bug introduced during the implementation of the "copy" that does not allow create special copies.
 * - V1.2.13 17-NOV-2009: - Add new "RewriteBase" action for "htaccess.txt" and ".htaccess" when replicating a website.
 *                        - Also give the possibility to "copy" the images directory instead of special copy.
 *                        - Add the FTP parameter processing when creating the new "configuration.php" of a slave site.
 *                        - Also compute real path for the "logs and tmp" folders in case where they contain "../" in the path.
 * - V1.2.14 24-NOV-2009: - Add the FTP parameter processing at "site" level.
 *                          Also try using the new slave FTP parameters to deploy the slave site.
 * - V1.2.15 15-DEC-2009: - Add cross-check that MULTISITES_MASTER_ROOT_PATH directory exists and can retreive the master website
 *                          "configuration.php file".
 * - V1.2.20 01-FEB-2010: - Add possibility to filter the sites on the "group-name"
 * - V1.2.21 15-FEB-2010: - Resolve and save the FTP parameter that must be written in the new configuration file
 * - V1.2.23 07-MAR-2010: - Add the generation of a specific "secret" value in each configuration.php file to
 *                          allow specific "cache" files for each slave sites when the "cache" directory is shared.
 *                          This is also used by JReviews that store its configuration into a file that use the secret value
 *                          to make the file unique.
 *                          Also add a default "order by" site ID ascending when not specified.
 * - V1.2.25 19-MAR-2010: - Remove the new "secret" value generation introduced in version 1.2.23 that has a side
 *                          effect on the single sign-in.
 *                          When there are different "secret" value between the websites, it is not possible to retreive
 *                          the same sessions ID.
 * - V1.2.26 22-MAR-2010: - Add the "Share Whole" site value computed based on the templates when not present in the slave site definition.
 * - V1.2.27 26-APR-2010: - Add PHP 5.3 compatibility (remove split function)
 * - V1.2.29 10-MAY-2010: - Fix split warning message
 *                          Add possibility to save the Multisites configuration using a directory tree instead of using a flat "multisites"
 *                          directory.
 *                          This functionality is added when a huge number of slave site is expected (several thousand).
 * - V1.2.30 01-JUN-2010: - Give the possibility to force a flat directory structure on a specific slave site (event when letter tree is enabled).
 * - V1.2.32 07-JUN-2010: - When updating an existing slave site, resave it in the same format as originally (flat if it was flat).
 *                        - Fix the delete of a slave site when using the letter tree to avoid delete recursivelly all slave site under
 *                          a give letter tree entry.
 *                        - Fix the computation of the list of sites when "getSites" is called from a slave site where "/multisites" is a link.
 *                        - Fix a bug introduced when implementing the "letter tree" that display all the list of website event when a filter
 *                          is provided.
 * - V1.2.33 08-JUL-2010: - Report an error when it is not possible to create an alias for the slave site.
 *                          ie: Case of alias that already exists with another path.
 * - V1.2.35 27-JUL-2010: - Save the expiration information into the Master Index event when the slave site is already expired
 *                          to ensure that slave site deployed in a specific directory will not be displayed when the slave site is expired.
 *                          Previously, a slave site expired were not saved in the Master Index and therefore the current "configuration.php"
 *                          were used like if that was a master website. Now as the slave site remain in the index, it may be redirected
 *                          to the URL when it is present.
 * - V1.2.36 07-SEP-2010: - Fix the creation of the master index to avoid replace the URI/Host that is used by SEF.
 *                        - When an alias is defined for a website, also delete it when the website is deleted.
 *                        - Also manage the case when an alias is renamed. This delete the old one when the new one is created with success.
 * - V1.2.39 27-SEP-2010: - Fix PHP syntax error message when it is not possible to get a list of directory..
 * - V1.2.46 12-JAN-2011: - Add joomla 1.6.0 stable compatibility after that they change the JRegistry->toString() function 
 *                          to remove the second parameter. So in Joomla 1.5.x, this require 3 parameters and in 1.6.x only 2.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );
jimport( 'joomla.filesystem.path');
jimport( 'joomla.filesystem.archive');
jimport( 'joomla.filesystem.folder');
jimport( 'joomla.filesystem.file');
jimport( 'joomla.utility.string');


if ( !defined( 'JPATH_MULTISITES_COMPONENT_ADMINISTRATOR')) {
   define( 'JPATH_MULTISITES_COMPONENT_ADMINISTRATOR',
            JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites');
}
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'lettertree.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'site.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'multisitesdb.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'dbsharing.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'template.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'utils.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'tld2win.php');

require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'helpers' .DS. 'helper.php');

require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');

// When the patches are not yet installed, declare the directory to allow manage the sites without the installation
if ( !defined( 'JPATH_MULTISITES')) {
   define( 'MUTLISITES_PATCHES_NOTINSTALLED', true);
   define( 'JPATH_MULTISITES', JPATH_ROOT.DS.'multisites');
}

if ( !defined( 'MULTISITES_DIR_RIGHTS')) {
   define( 'MULTISITES_DIR_RIGHTS', 0755);
}

// Enable or disable the current Multisite FTP redirection.
// When false or not defined, this disable this class and replace it with "fake" implementation
if ( !defined( 'MULTISITES_REDIRECT_FTP')) {
   define( 'MULTISITES_REDIRECT_FTP', false);
}

require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'filesystem' .DS. 'jms2winfolder.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'filesystem' .DS. 'jms2winfile.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'filesystem' .DS. 'jms2winpath.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'client' .DS. 'jms2winftp.php');


// ===========================================================
//             MultisitesModelManage class
// ===========================================================
/**
 * @brief Is used to manage the 'Slave' sites.
 *
 * Management of the sites consists in:
 * - Retreiving the list of slave sites using the getSites() function;
 * - Create the master index where is defined all the domains name with their slave site directory.
 *   @see createMasterIndex().
 * - Create 'slave' site directory where will be written the 'slave' configuration files.
 *   @see deploySite().
 */
class MultisitesModelManage extends JModel
{
	// Private members
	var $_modelName = 'manage';

	var $_site = null;
	var $_countAll = 0;

   //------------ _getSites_Recursive ---------------
   /**
    * @brief Analyse the "/multisites" directory recursivelly to retreive all the "Slave site" definitions
    */
   function _getSites_Recursive( &$filters, $path, &$rows, $is_multisites_root, $site_id_prefix='')
	{
		// Check to make sure the path valid and clean
		$dir = JPath::clean($path);

		// Is the path is NOT a folder 
		// or (is a Symbolic Link and not the root multisites directory - means not yet found any answer when called from a multisites directory)
		if ( !is_dir($dir) || (is_link( $dir) && !$is_multisites_root)) {
			// Skyp it
			return false;
		}
		
      if ($handle = opendir( $dir)) {
         while (false !== ($file = readdir($handle))) {
             if ($file != "." && $file != "..") {
               $filename = $dir . DS. $file;
               // If this is a link, skip it
               if ( is_link( $filename)) {}
               // If this is a subdirectory
               else if ( is_dir( $filename)) {
                  // Check if this directory c
                  $site_id = $site_id_prefix . $file;
                  if ( Site::is_Site( '', $filename)) {
                     // Add the site
                     $site = new Site();
                     $site->load( $site_id);
                     // Filter the record
                     $selected = true;
                     if ( !empty($filters['host']) && $site->host != $filters['host']) {
                        $selected = false;
                     }
                     if ( !empty( $filters['db']) && $site->db != $filters['db']) {
                        $selected = false;
                     }
                     if ( !empty( $filters['status']) && $site->status != $filters['status']) {
                        $selected = false;
                     }
                     if ( !empty( $filters['owner_id']) && $site->owner_id != $filters['owner_id']) {
                        $selected = false;
                     }
                     if ( !empty( $filters['groupName'])) {
                        $template = & $site->getTemplate();
                        // If there is no template
                        // OR that the "groupName" is empty
                        // OR is different to the expected "groupName"
                        if ( empty( $template) || !isset( $template->groupName) || $template->groupName != $filters['groupName']) {
                           $selected = false;
                        }
                     }
   
                     // If specific search
                     if ( !empty( $filters['search'])) {
                        $search = $filters['search'];
                        $posFile          = strpos( JString::strtolower( $site_id), $search);
                        $posSitename      = strpos( JString::strtolower( $site->sitename), $search);
                        $posDomains       = strpos( JString::strtolower( implode( '|', $site->domains)), $search);
                        $posIndexDomains  = strpos( JString::strtolower( implode( '|', $site->indexDomains)), $search);
   
                        $posHost          = strpos( JString::strtolower( $site->host), $search);
                        $posDB            = strpos( JString::strtolower( $site->db), $search);
                        $posDbPrefix      = strpos( JString::strtolower( $site->dbprefix), $search);
   
                        if ( $posFile === false
                          && $posSitename === false
                          && $posSitename === false
                          && $posIndexDomains === false
                          && $posHost === false
                          && $posDB === false
                          && $posDbPrefix === false
                           )
                        {
                           $selected = false;
                        }
                     }
   
                     if ( $selected) {
                        $rows[] = $site;
                     }
                  } // End is_Site
                  
                  // If the letter tree directory structure is enabled, retry recursivelly
                  if ( defined( 'MULTISITES_LETTER_TREE') && MULTISITES_LETTER_TREE) {
                     $len = strlen( $file);
                     // If single letter directory
                     if ( $len == 1) {
                        $this->_getSites_Recursive( $filters, $filename, $rows, false, $site_id);
                     }
                     // If something like "a.b"
                     else if ( $len <= 3) {
                        // If it contains a dot then retry recursivelly - otherwise, ignore.
                        if ( strpos( $file, '.') === false) {}
                        else {
                           $this->_getSites_Recursive( $filters, $filename, $rows, false, $site_id);
                        }
                     }
                  }
               }
             }
         }
         closedir($handle);
      }
	}


   //------------  getSites ---------------
   /**
    * @brief Return a list of Sites.
    *
    * The website instance is the list of subdirectories present in the "multisites" directory of the joomla root directory.\n
    * For each website directory, the configuration file is read to extract:
    * - database server;
    * - database name;
    * - database prefix;
    * - Login name;
    * - Password;
    */

   function &getSites()
	{
	   $filters = $this->getState( 'filters');

	   $cleanFilter = array();

	   // Extract the filtering selection
	   if ( !is_null($filters)) {
	      // If there is a filtering on DB server
	      if ( !empty($filters['host']) && $filters['host'] != '[unselected]') {
	         $cleanFilter['host'] = $filters['host'];
	      }
	      // If there is a filtering on DB name
	      if ( !empty( $filters['db']) && $filters['db'] != '[unselected]') {
	         $cleanFilter['db'] = $filters['db'];
	      }
	      // If there is a filtering on Status
	      if ( !empty( $filters['status']) && $filters['status'] != '[unselected]') {
	         $cleanFilter['status'] = $filters['status'];
	      }
	      // If there is a filtering on Owner
	      if ( !empty( $filters['owner_id']) && $filters['owner_id'] != '[unselected]') {
	         $cleanFilter['owner_id'] = $filters['owner_id'];
	      }
	      // If there is a filtering on the GroupName
	      if ( !empty( $filters['groupName']) && $filters['groupName'] != '[unselected]') {
	         $cleanFilter['groupName'] = $filters['groupName'];
	      }

	      // If there is a "search" filtering
	      if ( !empty( $filters['search'])) {
   		   $cleanFilter['search'] = JString::strtolower( $filters['search']);
   		}

	   }

	   /* Collect all Site name */
	   $rows = array();
	   $dir = JPATH_MULTISITES;
	   if ( JFolder::exists( $dir))
	   {
	      $this->_getSites_Recursive( $cleanFilter, $dir, $rows, true);
	   }

	   $this->_countAll = count( $rows);

	   // If there called from a form (filter exists) and there is no order by the add a default order by site_id
	   if ( !empty($filters) && empty( $filters['order'])) {
	      $filters['order'] = 'id';
	      $filters['order_Dir'] = 'asc';
	   }

	   // If the user request ordering records
	   if ( !is_null($filters)) {
	      if ( !empty( $filters['order'])) {
	         $colname = $filters['order'];
	         $sortedrows = array();
	         $i = 0;
	         foreach( $rows as $row){
	            // If expiration date
	            if ( $colname == 'expiration') {
	               // Convert the format to produce YYYY-MM-DD
	               $expiration = strtotime( $row->$colname);
	               $expiration_str = strftime( '%Y-%m-%d', $expiration);
   	            $key = $expiration_str . '.' . substr( "00".strval($i++), -3);
	            }
	            else {
   	            $key = $row->$colname . '.' . substr( "00".strval($i++), -3);
	            }
	            $sortedrows[$key] = $row;
	         }

	         // If requested a REVERSE ordering (descending)
	         if ( !empty( $filters['order_Dir']) && $filters['order_Dir'] =='desc') {
	            krsort($sortedrows);
	            $rows = $sortedrows;
	         }
	         // Ascending order
	         else {
	            ksort($sortedrows);
	            $rows = $sortedrows;
	         }
	      }
	   }


	   // If there is a limits specified
	   if ( !is_null($filters)) {
	      // If there is a limit specified (not ALL records)
	      if ( !empty( $filters['limit']) && $filters['limit'] > 0) {
      		// slice out elements based on limits
      		$rows = array_slice( $rows, $filters['limitstart'], $filters['limit'] );
	      }
	   }

	   return $rows;
	}

   //------------ getCountAll ---------------
   /**
    * @return the total number of records.
    */
   function getCountAll()
	{
	   return $this->_countAll;
	}

   //------------ setFilters ---------------
   function setFilters( &$filters)
	{
	   $this->setState( 'filters', $filters);
	}

   //------------ removeFilters ---------------
   function removeFilters()
	{
	   $this->setState( 'filters', null);
	}
	
   //------------ getCookieSubdomain ---------------
   /**
    * @brief Return an array with the list of domain that share the users.
    * The first element of the array is the cookie sub-domain.
    * The other element are full domain name when different of the sub-domain (first element).
    * When the first elemenent = '' then there is no sub-domain.
    * When returned an empty array, this mean that neither there is no sub-domain corresponding
    * to the hosts.
    */
   function getCookieSubdomain( $hosts)
	{
	   $results        = array( '');
	   $reverse_cookie = array();
	   $match_depth    = 0;
	   $firstHost = true;
	   
      foreach( $hosts as $host) {
         // Get the Top Level Domains definition and try to split the host name into domain elements
         $tlds  = &TLD2Win::getInstance();
         $parts = $tlds->splitHost( $host);
         if ( !$firstHost) {
            // Check this is a real domain structure (domain.com) having at least 2 elements ('domain' and 'com')
            if ( count( $parts) < 2) {
               continue;
            }
            
            // If this is an IP address
            if ( count( $parts) == 4 
              && is_numeric( $parts[0]) 
              && is_numeric( $parts[1]) 
              && is_numeric( $parts[2]) 
              && is_numeric( $parts[3]) 
               )
            {
               // Skip it (as it does not contain a domain name)
               $results[] = $host; // Save this host as extrat host
               continue;
            }
         }
         $firstHost = false;
         $reverse_domain = array_reverse( $parts);
         
         if ( empty( $reverse_cookie)) {
            $reverse_cookie = $reverse_domain;
            $match_depth = count( $reverse_cookie);
         }
         else {
            // Compare current domain with refrence 'master' domain
            for ( $i=0; $i<$match_depth && $i<count($reverse_domain); $i++) {
               if ( $reverse_cookie[$i] == $reverse_domain[$i]) {}
               else {
                  if ( $i>1) {
                     if ( $i < $match_depth) {
                        $match_depth = $i;
                     }
                  }
                  break;
               }
            }
            // If domain is totally different
            if ( $i<2) {
               $results[] = $host; // Save this host as extrat host
            }
         }
      }
	   
	   // If there is a cookie sub-domain with a lenght greater than 2
	   if ( !empty( $reverse_cookie) && $match_depth >= 2) {
	      while( count( $reverse_cookie) > $match_depth) {
	         array_pop( $reverse_cookie);
	      }
	      $parts = array_reverse( $reverse_cookie);
	      // If this is an IP address
	      if ( count($parts) <= 4) {
	         $allNumeric = true;
	         for( $i=0;$i<count($parts); $i++) {
	            $allNumeric &= is_numeric( $parts[$i]);
	         }
	         if ( $allNumeric) {
	            if ( count( $results) <= 1) {
	               return array();
	            }
	            return $results;
	         }
	      }
	      $cookie_subdomain = '.' . implode( '.', $parts);
	      $results[0] = $cookie_subdomain;
	      return $results;
	   }

      if ( count( $results) <= 1) {
         return array();
      }
      return $results;
	}
	
   //------------ getCookieDomains ---------------
   /**
    * @brief In case of user sharing, try to open the cookies to the sub-domain 
    *        in aim to allow single sign-in.
    */
   function getCookieDomains( $master_domain, $site_dependencies, &$sites, $i, $aHost, $master_userTablename = null)
	{
      $shared_site = false;
      // If master
      if ( $i < 0) {
   	   $userTablename = $master_userTablename;
   	   if ( !empty( $userTablename)) {
      	   // If it is not possible to retreive the list of indice corresponding to this website.
      	   if ( isset( $site_dependencies[$userTablename])) {
         	   // If there is only one site that access this table
         	   if ( count( $site_dependencies[$userTablename]) > 1) {
         	      $shared_site = true;
         	   }
      	   }
   	   }
      }
      else {
         if ( !isset( $sites[$i])) {
   	      return '';
         }
   	   $site = & $sites[$i];
   	   // If this site is shared by other sites
   	   $userTablename = $site->getThisUserTablename();
   	   if ( !empty( $userTablename)) {
      	   // If it is not possible to retreive the list of indice corresponding to this website.
      	   if ( isset( $site_dependencies[$userTablename])) {
         	   // If there is only one site that access this table
         	   if ( count( $site_dependencies[$userTablename]) > 1) {
         	      $shared_site = true;
         	   }
      	   }
   	   }
   
   	   if ( !$shared_site) {
      	   // If this site has view on other sites
      	   $userTablename = $site->getFromUserTablename();
      	   if ( !empty( $userTablename)) {
         	   // If it is not possible to retreive the list of indice corresponding to this website.
         	   if ( isset( $site_dependencies[$userTablename])) {
            	   // If there is only one site that access this table
            	   if ( count( $site_dependencies[$userTablename]) > 1) {
            	      $shared_site = true;
            	   }
         	   }
      	   }
   	   }
      }
	   
	   if ( !$shared_site) {
	      return '';
	   }
	   
	   
	   // For each sites that share the same user's tables.
	   $hosts = array();
	   $hosts[] = $aHost;
	   foreach( $site_dependencies[$userTablename] as $indice) {
	      // If master
	      if ( $indice < 0) {
	         if ( !empty( $master_domain)) {
         	   $hosts[] = $master_domain;
	         }
	      }
	      // If slave
	      else {
	         $site = & $sites[ $indice];
	         
   	      // retreive the list of domains
   	      if ( !empty( $site->indexDomains)) {
   	         $domains = $site->indexDomains;
   	      }
   	      else {
   	         // Use the values entered by the user
   	         $domains = $site->domains;
   	      }
   	      foreach( $domains as $domain) {
               // If http(s):// is not present, add it
               $s = strtolower( $domain);
               if ( (strncmp( $s, 'http://', 7) == 0)
                 || (strncmp( $s, 'https://', 8) == 0)
                  ) {}
               else {
                  $domain = 'http://' . $domain;
               }
   	         $uri = new JURI( $domain);
               $myHost = $uri->getHost();
               if ( empty( $myHost)) {
                  $parts = explode( '/', $domain);
                  $myHost = $parts[0];
                  if ( !empty( $myHost)) {
                     $uri->setHost( $myHost);
                  }
               }
               // If the host is found,
               if ( !empty( $myHost)) {
                  $host = strtolower( $myHost);
            	   $hosts[] = $host;
               }
            }
	      }
	   }
	   
	   return MultisitesModelManage::getCookieSubdomain( $hosts);
	}


   //------------ createMasterIndex ---------------
   /**
    * @brief Create a file containing all the domain name and associated directories.
    * The 'config_multisites.php' files is created to speed-up the multisites normal processing.
    */

   function createMasterIndex()
	{
		jimport( 'joomla.environment.uri' );
		
		// If not called from a slave site
		$master_domain = '';
		$master_userTablename = null;
		$site_dependencies = array();
		if ( !defined( 'MULTISITES_ID')) {
		   // Save the 'master' domain as candidate for sharing
      	$uri = JFactory::getURI();
      	
      	$master_domain = $uri->getHost();

         $db = & Jms2WinFactory::getMasterDBO( true);
         if ( !empty( $db)) {
            $path = array( $db->_dbname, $db->getPrefix().'users');
            $path = MultisitesDatabase::backquote( $path);
            $master_userTablename = implode( '.', $path);
            $site_dependencies[$master_userTablename][] = -1;
         }
		}

	   /* Collect all Site name and domain names */
	   $sites = $this->getSites();

		// Compute the relationship between the websites
		// Use the #__users tables definition to know if there is a link (view)
		// or if this is a standalone table
	   for( $i=0; $i<count( $sites); $i++) {
	      $thisUserTablename = $sites[$i]->getThisUserTablename();
	      if ( !empty($thisUserTablename)) {
	         $site_dependencies[$thisUserTablename][] = $i;
	      }
	   }

	   // Analyse each sites to search if they contain view to another website
	   for( $i=0; $i<count( $sites); $i++) {
	      $fromUserTablename = $sites[$i]->getFromUserTablename();
	      if ( !empty( $fromUserTablename)) {
	         $site_dependencies[$fromUserTablename][] = $i;
	      }
	   }

	   $md_hostalias = array();
	   for( $i=0; $i< count( $sites); $i++) {
	      $site = & $sites[$i];
	      // Only create an index for website with Unknown Status
	      // Or status confirmed and not expired
	      // Otherwise, skip the site
	      if ( empty( $site->status)) {}
	      else if ($site->status == 'Confirmed') {
	         // If the site is expired and there is no expiration URL
	         if ( $site->isExpired() && empty( $site->expireurl)) {
   	         // Skip the website
   	         continue;
	         }
	         // Process the site to write it into the master index
	      }
	      else {
	         // Skip the website
	         continue;
	      }
	      // If there is a list of domains evaluated (where keywords are replaced by their values)
	      if ( !empty( $site->indexDomains)) {
	         $domains = $site->indexDomains;
	      }
	      else {
	         // Use the values entered by the user
	         $domains = $site->domains;
	      }
	      // Normalize each domain name with http(s)://....
	      foreach( $domains as $domain) {
            // If http(s):// is not present, add it
            $s = strtolower( $domain);
            if ( (strncmp( $s, 'http://', 7) == 0)
              || (strncmp( $s, 'https://', 8) == 0)
               ) {}
            else {
               $domain = 'http://' . $domain;
            }
	         $uri = new JURI( $domain);
            $myHost = $uri->getHost();
            if ( empty( $myHost)) {
               $parts = explode( '/', $domain);
               $myHost = $parts[0];
               if ( !empty( $myHost)) {
                  $uri->setHost( $myHost);
               }
            }
            // If the host is found,
            if ( !empty( $myHost)) {
               // Reformat domain URL
               $url = $uri->toString( array('scheme', 'user', 'pass', 'host', 'port', 'path'));
               // remove trailing '/'
               $url = rtrim( $url, '/');
               // If the URL is correctly reformatted
               if ( !empty( $url)) {
                  // Add the new URL into the JMS main index
                  $host = strtolower( $myHost);
                  if ( !isset( $md_hostalias[$host])) {
                     $md_hostalias[$host] = array();
                  }
                  
                  $cookie_domains = MultisitesModelManage::getCookieDomains( $master_domain, $site_dependencies, $sites, $i, $host);
                  
                  $site_detail = array( 'url' => $url, 'site_id' => $site->id);

                  if ( !empty( $site->expiration)) { $site_detail['expiration']     = $site->expiration; }
                  if ( !empty( $site->expireurl))  { $site_detail['expireurl']      = $site->expireurl; }
                  if ( !empty( $cookie_domains))   { $site_detail['cookie_domains'] = $cookie_domains; }
                  if ( !empty( $site->site_dir) && $site->site_dir != JPATH_MULTISITES .DS. $site->id) {
                     $site_detail['site_dir']      = $site->site_dir;
                  }
                  
                  $md_hostalias[$host][] = $site_detail;
               }
            }
	      } // End for domains
	   } // End for Sites
	   
	   // Sort the domain list in ASCENDING order that will be later write in REVERSE order
	   // This should avoid that partial URL have priority on a long path
	   foreach( $md_hostalias as $key =>$hostalias) {
	      $sortedDomain = array();
	      $i = 0;
	      foreach( $hostalias as $domains) {
	         // Compute the domain length to sort them in ascending length
	         $lenDom = substr( '000' . strlen( $domains['url']), -3);
	         $keyDom = $lenDom
	                 . $domains['url'] . '_' . substr( '0000'.$i, -4);
	         $sortedDomain[$keyDom] = $domains;
	         $i++;
	      }
	      ksort( $sortedDomain, SORT_STRING);
	      // Save the domain in reverse order and remove the temporary key
	      $md_hostalias[$key] = array_values( $sortedDomain);
	   }

      $master_root_path = JPATH_ROOT;
      // If slave site and a master root path was defined,
      if ( defined( 'MULTISITES_ID') && defined( 'MULTISITES_MASTER_ROOT_PATH' )) {
         // Keep the current master root path to avoid replace it by a slave directory path in case 
         // where the slave site is deployed into a specific directory 
         // and that JMS is managed from the slave site.
         // In that case, JPATH_ROOT contain the path of the slave site and not the one of the master website.
         
         // Cross-check that a "configuration.php" exists in the master root directory.
         if ( JFile::exists( MULTISITES_MASTER_ROOT_PATH .DS. 'configuration.php')) {
            $master_root_path = MULTISITES_MASTER_ROOT_PATH;
         }
      }
      
      // If there is sites with "user sharing", set the cookie for sub-domain
      $master_cookie_domains = MultisitesModelManage::getCookieDomains( $master_domain, $site_dependencies, $sites, -1, $master_domain, $master_userTablename);
      
	   // Save the list of domains into a special configuration files
		$config = "<?php\n";
		$config .= "if( !defined( '_EDWIN2WIN_' ) && !defined( '_JEXEC' )) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); \n\n";
		$config .= "if( !defined( 'MULTISITES_MASTER_ROOT_PATH' )) {\n"
		        .  "   define( 'MULTISITES_MASTER_ROOT_PATH', '" . $master_root_path . "');\n"
		        .  "}\n\n";
		if ( !empty( $master_cookie_domains)) {
   		$config .= "if( !defined( 'MULTISITES_MASTER_COOKIE_DOMAINS' )) {\n"
   		        .  "   define( 'MULTISITES_MASTER_COOKIE_DOMAINS', '" . implode( '|', $master_cookie_domains) . "');\n"
   		        .  "}\n\n";
		}
		$config .= "\$md_hostalias = array( ";
		$sep='';
	   foreach( $md_hostalias as $key => $domains) {
   		$config .= $sep . "'$key' => array( ";
   		if ( count( $domains) > 1) {
      		$sep2 = "\n                            ";
   		}
   		else {
      		$sep2 = '';
   		}
   		// Write the detail URL in the REVERSE order to allow patching test longer URL in priority
	      for( $i=count($domains)-1; $i>=0; $i--) {
	         $site = $domains[$i];
	         $domain  = $site['url'];
	         $site_id = $site['site_id'];

	         $site_dir_str = '';
	         if ( !empty( $site['site_dir'])) {
   	         $site_dir_str = ", 'site_dir' => '" . $site['site_dir'] ."'";
	         }
	         if ( !empty( $site['expiration'])) {
	            $expiration = $site['expiration'];
	            if ( !empty( $site['expireurl']))  { $expireurl_str = ", 'expireurl' => '" . $site['expireurl'] . "'"; }
	            else                               { $expireurl_str = ''; }
   	         if ( !empty( $site['cookie_domains'])) {
   	            $cookie_domains = $site['cookie_domains'];
            		$config .= $sep2 . "array( 'url' => '$domain', 'site_id' => '$site_id'$site_dir_str, 'expiration' => '$expiration' $expireurl_str, 'cookie_domains' => array( " . MultisitesUtils::CnvArray2Str( '', $cookie_domains) . ") )" ;
   	         }
   	         else {
            		$config .= $sep2 . "array( 'url' => '$domain', 'site_id' => '$site_id'$site_dir_str, 'expiration' => '$expiration' $expireurl_str)" ;
   	         }
	         }
	         else {
   	         if ( !empty( $site['cookie_domains'])) {
   	            $cookie_domains = $site['cookie_domains'];
            		$config .= $sep2 . "array( 'url' => '$domain', 'site_id' => '$site_id'$site_dir_str, 'cookie_domains' => array( " . MultisitesUtils::CnvArray2Str( '', $cookie_domains) . ") )" ;
   	         }
   	         else {
            		$config .= $sep2 . "array( 'url' => '$domain', 'site_id' => '$site_id'$site_dir_str)" ;
   	         }
	         }

      		$sep2 = ",\n                            ";
	      }
	      $config .= ')';
   		$sep = ",\n                       ";
	   }
		$config .= ");\n";
		$config .= "?>";


	   // Write the configuration
	   $filename = JPath::clean( JPATH_MULTISITES. '/config_multisites.php');
      JFile::write( $filename, $config);
      
	   // If the index.html files does not exist to hide the list of slave sites,
	   $filename = JPath::clean( JPATH_MULTISITES. '/index.html');
      if ( !JFile::exists( $filename)) {
         // Create the empty index.html to hide the list of slave sites.
         JFile::copy( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR.DS.'index.html', 
                      JPATH_MULTISITES .DS. 'index.html');
      }
	}


   //------------  getCurrentRecord ---------------
   /**
    * @brief Return a single record Site coresponding to the id. Null when does not exists.
    */

   function getCurrentRecord()
	{
		if ($this->_site == null) {
			$this->_site = new Site();
			if ($id = JRequest::getVar('id', false, '', 'cmd')) {
				$this->_site->load($id);
			}
		}
		return $this->_site;
	}

   //------------ getNewRecord ---------------
   /**
    * @brief Return a new record Site.
    */

   function getNewRecord()
	{
		if ($this->_site == null) {
			$this->_site = new Site();
		}
		return $this->_site;
	}


   //------------ canDelete ---------------
	/**
	 * Checks if the site can be deleted
	 * @return boolean
	 */
	function canDelete()
	{
		$site_dir = $this->getSiteDir();
		if ( !JFolder::exists( $site_dir)) {
		   // Reset the cache and retry with force flat directory structure
   		$site_dir = null;
   		$site_dir = $this->getSiteDir( '', true);
   		if ( !JFolder::exists( $site_dir)) {
   			$this->setError( JText::_( 'SITE_NOT_FOUND' ) );
   			return false;
   		}
		}
		return true;
	}


	/**
	 * Delete a folder
	 *
	 * @param string $path The path to the folder to delete
	 * @return boolean True on success
	 * @since 1.5
	 */
	function _deleteFolderLinks($path)
	{
		// Sanity check
		if ( ! $path ) {
			// Bad programmer! Bad Bad programmer!
			JError::raiseWarning(500, 'MultisitesModelManage::_deleteFolderLinks: '.JText::_('Attempt to delete base directory') );
			return false;
		}

		// Initialize variables
		jimport('joomla.client.helper');
		$FTPOptions = JClientHelper::getCredentials('ftp');

		// Check to make sure the path valid and clean
		$path = JPath::clean($path);

		// Is this really a folder?
		if (!is_dir($path)) {
			JError::raiseWarning(21, 'MultisitesModelManage::_deleteFolderLinks: '.JText::_('Path is not a folder').' '.$path);
			return false;
		}

		// Remove all the files in folder if they exist
		$files = JFolder::files($path, '.', false, true, array());
		if (count($files)) {
			jimport('joomla.filesystem.file');
			if (JFile::delete($files) !== true) {
				// JFile::delete throws an error
				return false;
			}
		}


		if ($FTPOptions['enabled'] == 1) {
			// Connect the FTP client
			jimport('joomla.client.ftp');
			$ftp = & JFTP::getInstance($FTPOptions['host'], $FTPOptions['port'], null, $FTPOptions['user'], $FTPOptions['pass']);
		}


		// Remove sub-folders of folder
		$folders = JFolder::folders($path, '.', false, true, array());
		foreach ($folders as $folder) {
		   $checkDeleted = true;
		   // If in fact the folder is a link to a folder
		   if ( is_link( $folder)) {
   			$file = $folder;
   			if (@unlink($file)) {
   				// Do nothing
   			} elseif ($FTPOptions['enabled'] == 1) {
   				$file = JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $file), '/');
   				if (!$ftp->delete($file)) {
   					// FTP connector throws an error
   					return false;
   				}
   			} else {
   				$filename	= basename($file);
   				JError::raiseWarning('SOME_ERROR_CODE', JText::_('Delete failed') . ": '$filename'");
   				return false;
   			}
		   }
		   else {
		      // In case of letter tree, do not delete the single letter and potential "a.b" directories
		      // to avoid delete other slave sites definitions
		      $foldername = basename( $folder);
            if ( defined( 'MULTISITES_LETTER_TREE') && MULTISITES_LETTER_TREE) {
               $len = strlen( $foldername);
               // If single letter directory
               if ( $len == 1) {
                  $checkDeleted = false;
               }
               // If something like "a.b"
               else if ( $len <= 3) {
                  // If it contains a dot then ignore deleting - otherwise, try delete recurviselly.
                  if ( strpos( $file, '.') === false) {
                     if (MultisitesModelManage::_deleteFolderLinks($folder) !== true) {
            				// JFolder::delete throws an error
            				return false;
            			}
                  }
                  else {
                     $checkDeleted = false;
                  }
               }
               // If more than 3 letters
               else if (MultisitesModelManage::_deleteFolderLinks($folder) !== true) {
      				// JFolder::delete throws an error
      				return false;
   		      }
            }
            // If flat directory structure
            else if (MultisitesModelManage::_deleteFolderLinks($folder) !== true) {
   				// JFolder::delete throws an error
   				return false;
		      }
			}
			
			clearstatcache();
			
			// Check that the folder is deleted
			if ( $checkDeleted && file_exists( $folder)) {
			   // When the file is still present, report an error.
				$filename	= basename($folder);
				JError::raiseWarning('SOME_ERROR_CODE', JText::_('Delete failed') . ": '$filename'");
			   return false;
			}
		}
		
		// Cross-check that all the content of the directory is removed before trying to delete the directory itself
		// If there are no more files or subdirectories present under the current directory
		// Then delete the current directory
		$files   = JFolder::files($path, '.', false, true, array());
		$folders = JFolder::folders($path, '.', false, true, array());
		if ( count( $files)   <= 0
		  && count( $folders) <= 0) {
   		// In case of restricted permissions we zap it one way or the other
   		// as long as the owner is either the webserver or the ftp
   		if (@rmdir($path)) {
   			$ret = true;
   		} elseif ($FTPOptions['enabled'] == 1) {
   			// Translate path and delete
   			$path = JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $path), '/');
   			// FTP connector throws an error
   			$ret = $ftp->delete($path);
   		} else {
   			JError::raiseWarning('SOME_ERROR_CODE', 'JFolder::delete: '.JText::_('Could not delete folder').' '.$path);
   			$ret = false;
   		}
   	}
   	else {
			$ret = true;
   	}

		return $ret;
	}


   //------------ _deleteDBTables ---------------
	/**
	 * Delete the DB content.
	 * @return boolean
	 */
	function _deleteDBTables()
	{
	   $site_id    = JRequest::getCmd('id');
	   $deleteDB   = JRequest::getBool('deleteDB');

	   if ( !$deleteDB) {
	      return true;
	   }

      $db =& Jms2WinFactory::getSlaveDBO( $site_id);
      if ( empty( $db)) {
         return false;
      }
      return MultisitesDatabase::deleteDBTables( $db);
	}


   //------------ delete ---------------
	/**
	 * Deletes the site directory.
	 *
	 * @par Implementation:
	 * - Check if the multisites/xxxx of the site id xxxx exists
	 * - If the site exists, start to delete all the DB tables (if the flag deleteDB is ON);
	 * - Delete the folder symbolic links to avoid the bug in JFolder delete (security implementation)
	 * - Logical Delete of the 'billable' websites ID in aim to manage the Website quota
	 * @return boolean
	 */
	function delete()
	{
		$err = array();
		$rc = true;
		
	   // Delete the tables present in the database
	   if ( !$this->_deleteDBTables()) {
         $err[] = JText::sprintf( 'SITE_DELETEDB_ERR');
      }

		$dir = getcwd();
		
		$deploy_dir = $this->getDeployDir();
		$alias_link = $this->getAliasLink();
		$site_dir   = $this->getSiteDir();
		
      $this->_deleteWebsiteID();
      
		// If there is a deployment directory that is not the multisites/site_id directory,
		//  Delete the deployment directory
		if ( !empty( $deploy_dir) && $deploy_dir != $site_dir) {
   		if ( JFolder::exists( $deploy_dir)) {
   		   // Delete Folders defined as Symbolic Links
   		   if ( !$this->_deleteFolderLinks( $deploy_dir)) {
               $err[] = JText::sprintf( 'SITE_DELETE_ERR', $deploy_dir);
   		   }
   		}
		}
		
		// Delete the multi sites directory
		if ( JFolder::exists( $site_dir)) {
		   // Delete Folders that can also contain Symbolic Links
		   if ( !$this->_deleteFolderLinks( $site_dir)) {
            $err[] = JText::sprintf( 'SITE_DELETE_ERR', $site_dir);
		   }
		}
		
		if ( !empty( $alias_link) && is_link( $alias_link)) {
		   // try delete the link directly
			if (@unlink($alias_link)) {
   				// Do nothing
			}
			// If it fails, report the error
			else {
            $err[] = JText::sprintf( 'SITE_DELETE_ERR', $alias_link);
			}
		}
		
      chdir( $dir);

      if ( !empty( $err)) {
			$this->setError( implode( '</li><li>', $err));
			return false;
      }

		return true;
	}

   //------------ getSiteDir ---------------
   /**
    * @return the path to the Site Directory based on its site ID.
    */
   var $_site_dir = null;
   function &getSiteDir( $id='', $force_flat_dir = false)
   {
      // If the site directory is not already compute or force to recompute using a flat directory structure
      if ( $this->_site_dir == null) {
   	   if ( $id == '') {
      	   $id = JRequest::getCmd('id');
   	   }

         // If letter tree directory structure and NOT force a flat directory computation
         if ( defined( 'MULTISITES_LETTER_TREE') && MULTISITES_LETTER_TREE && !$force_flat_dir) {
            // Then compute a letter tree directory structure
            $id_path = MultisitesLetterTree::getLetterTreeDir( $id);
      	   $this->_site_dir = JPath::clean( JPATH_MULTISITES .DS. $id_path);
         }
         // Otherwise compute the flat directory structure
         else {
      	   $this->_site_dir = JPath::clean( JPATH_MULTISITES .DS. $id);
      	}
      }
      return $this->_site_dir;
   }

   //------------ getDeployDir ---------------
   /**
    * @return the path to the Deployment Directory based on its site ID.
    *
    * This read the multisites/site_id configuration file in aim to retreive the deploy_dir parameter
    */
   var $_deploy_dir = null;
   function &getDeployDir( $id='')
   {
      if ( $this->_deploy_dir == null) {
   	   if ( $id == '') {
      	   $id = JRequest::getCmd('id');
   	   }
   	   
         $this->_deploy_dir = '';
   	   
   	   $site_dir =& $this->getSiteDir( $id); 
         $filename = $site_dir.DS.'config_multisites.php';
         @include($filename);
         if ( isset( $config_dirs) && !empty( $config_dirs)) {
            if ( !empty( $config_dirs['deploy_dir'])) {
               $this->_deploy_dir = JPath::clean( $config_dirs['deploy_dir']);
            }
         }
      }
      return $this->_deploy_dir;
   }

   //------------ getAliasLink ---------------
   /**
    * @return the path to the Alias Link based on its site ID.
    *
    * This read the multisites/site_id configuration file in aim to retreive the deploy_dir parameter
    */
   var $_alias_link = null;
   function &getAliasLink( $id='')
   {
      if ( $this->_alias_link == null) {
   	   if ( $id == '') {
      	   $id = JRequest::getCmd('id');
   	   }
   	   
         $this->_alias_link = '';
   	   
   	   $site_dir =& $this->getSiteDir( $id); 
         $filename = $site_dir.DS.'config_multisites.php';
         @include($filename);
         if ( !empty( $config_dirs) && !empty( $config_dirs['alias_link'])) {
            $this->_alias_link = JPath::clean( $config_dirs['alias_link']);
         }
      }
      return $this->_alias_link;
   }

   //------------ compute_default_links ---------------
   /**
    * On unix platform, also create symbolic links to allow creating a virtual host that can map the sub directory.
    */
   function compute_default_links()
	{
	   $site_links = array();
	   $master_dir = JPATH_ROOT;

	   // Search for all DIRECTORIES to include as Symbolic Links
	   foreach( JFolder::folders( $master_dir, '.', false, false, array('.svn', 'CVS', 'vssver.scc',
	                                                                    'cache',        // automatic mkdir
	                                                                    'images',       // Special media and image folder
   	                                                                 'installation', // Special Installation replication directory
	                                                                    'logs',         // automatic mkdir
	                                                                    'templates',    // Special themes folder
	                                                                    'tmp'           // automatic mkdir
	                                                                    )) as $folder)
	   {
	      $site_links[$folder] = array( 'action' => 'SL');
	   }

	   // Here some directories are empty directories
	   $site_links['cache']       = array( 'action' => 'mkdir',    'readOnly' => true);
	   $site_links['logs']        = array( 'action' => 'mkdir',    'readOnly' => true);
	   $site_links['images']      = array( 'action' => 'special');
	   $site_links['templates']   = array( 'action' => 'copy');
	   $site_links['tmp']         = array( 'action' => 'mkdir',    'readOnly' => true);

	   if ( JFolder::exists( $master_dir .DS. 'installation')) {
   	   $site_links['installation']  = array( 'action' => 'dirlinks');
	   }


	   ksort( $site_links);

	   // Search for all FILES to include as Symbolic Links
	   foreach( JFolder::files( $master_dir, '.', false, false, array('.svn', 'CVS', 'vssver.scc',
	                                                                  'CHANGELOG.php',
	                                                                  'configuration.php',
	                                                                  'configuration.php-dist',
	                                                                  'index.php',
	                                                                  'index2.php',
	                                                                  'COPYRIGHT.php',
	                                                                  'CREDITS.php',
	                                                                  'INSTALL.php',
	                                                                  'LICENSE.php',
	                                                                  'LICENSES.php'
	                                                                  )) as $file)
	   {
	      $site_links[$file] = array( 'action' => 'SL');
	   }

	   $site_links['index.php']   = array( 'action' => 'redirect',  'readOnly' => true);
	   $site_links['index2.php']  = array( 'action' => 'redirect',  'readOnly' => true);
	   
	   return $site_links;
	}


   //------------ symlink ---------------
   /**
    * @brief Platform independent Symbolic Links
    */
	function symlink( $target_path, $link)
	{
	   // On Windows platform, it is not possible to create symbolic links.
	   if ( !MultisitesHelper::isSymbolicLinks()) {
	      return false;
	   }
	   // If the link already exists
	   if ( is_link( $link)) {
	      // Check this is the same path
	      $cur_path = readlink( $link);
	      if ( $cur_path === false) {
	         // Retry using a full path to ensure it fails.
	         // One customer had a problem to retreive links with relative path but success with full path
   	      $full_path = getcwd() .DS. $link;
   	      $cur_path = readlink( $full_path);
   	      if ( $cur_path === false) {
   	         return false;
   	      }
	      }
	      if ( $cur_path == $target_path) {
	         return true;
	      }
	      return false;
	   }
	   if ( !function_exists( 'symlink')) {
	      return false;
	   }
      return symlink( $target_path, $link);
	}

   //------------ _getSourcePath ---------------
   /**
    * @brief Compute the Source Path
    */
	function _getSourcePath( $targetname, $sourcename, $site_id, $site_dir, $deploy_dir, $dbInfo)
	{
	   // If the source file name is not defined
	   if ( empty( $sourcename)) {
	      // Compute the filename based on the target name (source = target)
   	   $source = JPath::clean( JPATH_ROOT .DS. $targetname);
	   }
	   else {
	      $str = MultisitesDatabase::evalStr( $sourcename, $site_id, $site_dir, $deploy_dir, $dbInfo);
	      // If absolute path
	      $c = substr( $str, 0, 1);
         jimport( 'joomla.utilities.utility.php');
	      if ( $c == '\\' || $c == '/'
	         || (JUtility::isWinOS() && substr( $str, 1, 1) == ':')) {
      	   $source = JPath::clean( $str);
	      }
	      else {
	         // Relative path to the root
      	   $source = JPath::clean( JPATH_ROOT .DS. $str);
	      }
	   }
	   return $source;
	}

   //------------ _getTargetPath ---------------
   /**
    * @brief Compute the target Path
    */
	function _getTargetPath( $site_dir, $deploy_dir, $name)
	{
	   if ( !empty( $deploy_dir)) {
         $target = JPath::clean( $deploy_dir .DS. $name);
	   }
	   else {
         $target = JPath::clean( $site_dir .DS. $name);
      }

      return $target;
	}

   //------------ _deployLinks ---------------
   /**
    * possible actions:
    * - mkdir;
    * - copy;
    * - unzip;
    * - SL: Symbolic Links is only authorised in Unix. Otherwise, ignore;
    * - ignore
    * @remark
    * On unix platform, also create symbolic links to allow creating a virtual host that map the sub directory.
    */
   function _deployLinks( $config_dirs, $site_id, $site_dir, $deploy_dir, $dbInfo, $domains, $indexDomains)
	{
//      Debug2Win::debug_start( ">> _deployLinks() - START");

		$errors = array();
	   
	   // If there is a list of SymbolicLinks into the configuration
	   if ( isset( $config_dirs['symboliclinks']) && !empty( $config_dirs['symboliclinks'])) {
	      $site_links = $config_dirs['symboliclinks'];
	   }
	   else {
	      // If the user does not want to deploy the website into a specific directory
	      if ( empty( $config_dirs['deploy_dir'])) {
	         // Ignore the Symbolic Link creation
	         return $errors;
	      }
	      // Compute the default symbolic link list
	      $site_links = $this->compute_default_links();
	   }

	   $shell_script = "#!/bin/bash\n";

	   $sav_dir = getcwd();
	   if ( isset( $site_links)) {
         foreach( $site_links as $name => $site_link) {
            $action  = $site_link['action'];
            // ---- rewrite ----
            if ( $action == 'rewrite')
            {
               // Compute the new "RewriteBase" value.
               // Retreive the first target indexDomain to extract the new "RewriteBase"
               $targetDomain = '';
               if ( !empty( $indexDomains)) {
                  $targetDomain = $indexDomains[0];
               }
               if ( empty( $targetDomain) && !empty( $domains)) {
                  $targetDomain = $domains[0];
               }
               if ( empty( $targetDomain)) {
                  $action == 'copy';
               }
               else {
                  // remove the http(s)://
                  $pos = strpos( $targetDomain, '://');
                  if ( $pos === false) {
                     $rewriteBase = $targetDomain;
                  }
                  else {
                     $rewriteBase = substr( $targetDomain, $pos+3);
                  }
                  // Remove the host (domain) name when present
                  $pos = strpos( $rewriteBase, '/');
                  if ( $pos === false) {}
                  else {
                     $rewriteBase = substr( $rewriteBase, $pos);
                  }
                  
                  // Normalise the RewriteBase to be sure it does not contain a '/' at the end
                  $rewriteBase = rtrim( $rewriteBase, '/');
                  // Except when RewriteBase is empty. In this case you have to set '/'
                  if ( empty( $rewriteBase)) {
                     $rewriteBase = '/';
                  }
                  
                  // read source
         	      $srcfile = !empty( $site_link['file'])
         	               ? $site_link['file']
         	               : null;
                  $source  = $this->_getSourcePath( $name, $srcfile, $site_id, $site_dir, $deploy_dir, $dbInfo);
                  $content = JFile::read( $source);

            		// replace RewriteBase value
            		$content = preg_replace( "/RewriteBase [^\n]+/",
            		                         "RewriteBase ".$rewriteBase,
            		                         $content );

            		// Write the result into the target path
         	      $target = $this->_getTargetPath( $site_dir, $deploy_dir, $name);
            		JFile::write( $target, $content);
               }
               
            }
            // ---- mkdir ----
            if ( $action == 'mkdir')
            {
               if ( empty( $deploy_dir)) {
                  $dir = JPath::clean( $site_dir .DS. $name);
                  $shell_script .= "cd $site_dir\n"
                                .  "mkdir -p $name\n"
                                ;
               }
               else {
                  $dir = JPath::clean( $deploy_dir .DS. $name);
                  $shell_script .= "cd $deploy_dir\n"
                                .  "mkdir -p $name\n"
                                ;
               }
// Debug2Win::debug( "mkdir [$dir]");
         	   if ( !JFolder::exists( $dir)) {
         	      // Try to create the directory and check it really exists. Otherwise, report an error
            	   if ( ! JFolder::create( $dir, MULTISITES_DIR_RIGHTS)
            	     || ! JFolder::exists( $dir))
            	   {
         	         $errors[] = JText::sprintf( 'SITE_DEPLOY_MKDIR_ERR', $dir);
            	   }
            	}
            }
            // ---- Copy ----
            else if ( $action == 'copy')
            {
      	      $srcfile = !empty( $site_link['file'])
      	               ? $site_link['file']
      	               : null;
               $source = $this->_getSourcePath( $name, $srcfile, $site_id, $site_dir, $deploy_dir, $dbInfo);

      	      // Compute the extraction directory
      	      // This is one directory up the to name give
      	      $target = $this->_getTargetPath( $site_dir, $deploy_dir, $name);

         		$result = true;
         		if ( is_dir( $source)) {
         		   // If the target folder does not exists,
         		   if ( !JFolder::exists( $target)) {
                     $shell_script .= "cp $source $target\n";
// Debug2Win::debug( "cp1 $source $target");
               		$result = JFolder::copy( $source, $target);
         		   }
         		}
         		else {
         		   // If File does not exists
         		   if ( !JFile::exists( $target)) {
                     $shell_script .= "cp $source $target\n";
// Debug2Win::debug( "cp2 $source $target");
               		$result = JFile::copy( $source, $target);
         		   }
         		}
         		if ( $result === false ) {
      	         $errors[] = JText::sprintf( 'SITE_DEPLOY_COPY_ERR', $source);
         		   // As it was not possible to unzip the file, try to use a link
         		   // Continue with SL (Symbolic Link)
         		   $action = 'SL';
         		}
            }
            // ---- unzip ----
            else if ( $action == 'unzip')
            {
      	      $archivename = $this->_getSourcePath( $name, $site_link['file'], $site_id, $site_dir, $deploy_dir, $dbInfo);

      	      // Compute the extraction directory
      	      // This is one directory up the to name give
      	      $source = JPath::clean( $this->_getTargetPath( $site_dir, $deploy_dir, $name));
      	      $arr = explode( DS, $source);
      	      $link = $arr[count($arr)-1];
      	      array_pop( $arr);
      	      $source_dir = implode( DS, $arr);
      	      chdir( $source_dir);
         	   $dir = getcwd();

               $shell_script .= "cd $dir\n"
                             .  "rm -R $link\n"
                             .  "cp $archivename _tmp.tar.gz\n"
                             .  "gunzip _tmp.tar.gz\n"
                             .  "tar -xvf _tmp.tar\n"
                             .  "rm _tmp.tar\n"
                             ;

         		$result = JArchive::extract( $archivename, $dir);
         		if ( $result === false ) {
      	         $errors[] = JText::sprintf( 'SITE_DEPLOY_UNZIP_FILE_ERR', $archivename);
         		   // As it was not possible to unzip the file, try to use a link
         		   // Continue with SL (Symbolic Link)
         		   $action = 'SL';
         		}
            }
            // ---- redirect ----
            else if ( $action == 'redirect')
            {
               // Create a file that contain an include to the original file.
               $target_path = JPath::clean( JPATH_ROOT .DS. $name);
      	      // Extract the symbolic link name (this is the last word)
      	      $filename = $this->_getTargetPath( $site_dir, $deploy_dir, $name);
      	      $content = "<?php\n"
      	               . "// Don't use a Symbolic Link because that crash the website.\n"
      	               . "// Just include the original file to redirect the processing.\n"
      	               . "//include( '$target_path');\n"
      	               . "// Evaluate the original include file to redirect to keep the __FILE__ value.\n"
                        . "\$filename = '$target_path';\n"
                        . '$handle = fopen ($filename, "r");' . "\n"
                        . '$contents = fread ($handle, filesize ($filename));' . "\n"
                        . 'fclose ($handle);' . "\n"
                        . 'unset($handle);' . "\n"
                        . 'eval("?>" . $contents);' . "\n"
      	               ;
      	      JFile::write( $filename, $content);
            }
            // ---- special ----
            else if ( $action == 'special')
            {
               // If Symbolic Link does not exists
               if ( !MultisitesHelper::isSymbolicLinks()) { 
                  // This means that the user must map the slave sites on the master directory
                  // and we can not create a symbolic link to simulate the image and media folder present in another directory
               }
               // If Symbolic Link is available
               else {
                  // Check if the target path exists.
         	      $target = $this->_getTargetPath( $site_dir, $deploy_dir, $name);
         	      if ( JFolder::exists( $target)) {
         	         // If the directory exists, Do nothing
         	      }
                  // Otherwise, create a Symbolic Link
         	      else {
         	         // Create a Symbolic Link.
            		   $action = 'SL';
         	      }
         	   }
            }
            
            // ---- dirlinks (create Directory + SL inside) ----
            else if ( $action == 'dirlinks')
            {
               // If Symbolic Link does not exists
               if ( !MultisitesHelper::isSymbolicLinks()) { 
                  // This means that the user must map the slave sites on the master directory
                  // and we can not create a symbolic link to simulate the image and media folder present in another directory
               }
               // If Symbolic Link is available
               else {
                  // Check if the target path exists.
         	      $target = $this->_getTargetPath( $site_dir, $deploy_dir, $name);
         	      if ( !JFolder::exists( $target)) {
            	      // Try to create the target directory
               	   if ( ! JFolder::create( $target)
               	     || ! JFolder::exists( $target))
               	   {
            	         $errors[] = JText::sprintf( 'SITE_DEPLOY_DIRLINK_ERR', $target);
               	   }
         	      }

         	      // If the directory is created,
         	      if ( JFolder::exists( $target)) {
                     $source_dir = $this->_getSourcePath( $name, null, $site_id, $site_dir, $deploy_dir, $dbInfo);

         	         if ( !$this->_deployDirLinks( $source_dir, $target, array( 'index.php', 'index2.php', 'index3.php'))) {
            	         $errors[] = $this->getError();
         	         }
         	      }
         	   }
            }
            
            // ---- SL ----
            if ( $action == 'SL')
            {
               $target_path = JPath::clean( JPATH_ROOT .DS. $name);
      	      // Extract the symbolic link name (this is the last word)
      	      $source = JPath::clean( $this->_getTargetPath( $site_dir, $deploy_dir, $name));
      	      $arr = explode( DS, $source);
      	      $link = $arr[count($arr)-1];

      	      // Go up one directory
      	      array_pop( $arr);
      	      $source_dir = implode( DS, $arr);
      	      chdir( $source_dir);

               $shell_script .= "cd $source_dir\n"
                             .  "ln -s $target_path $link\n"
                             ;
      	      // If unable to create the symbolic link, report the error
      	      if ( !$this->symlink( $target_path, $link)) {
      	         $errors[] = JText::sprintf( 'SITE_DEPLOY_SYMLINK_ERR', $link, $target_path);
      	      }
      	   }
         } // End foreach site links
      }

	   // For Unix, Just in case, also write the shell_script.sh to give opportunity to launch the command manually
      jimport( 'joomla.utilities.utility.php');
	   if ( !JUtility::isWinOS()) {
         $filename = JPath::clean( $deploy_dir .DS. 'symbolic_links.sh');
         // Security Risk to write the shell script because it can reveal sensitive information
         // JFile::write( $filename, $shell_script);
	   }


	   chdir( $sav_dir);

	   return $errors;
	}

   //------------ _createEmptyFolder ---------------
   /**
    * @brief Create an empty folder and write an index.html file inside with an empty page.
    */
	function _createEmptyFolder( $dir)
	{
	   if ( !Jms2WinFolder::exists( $dir)) {
	      Jms2WinFolder::create( $dir);
	   }
	   $index_php  = $dir .DS. 'index.php';
	   $index_html = $dir .DS. 'index.html';
	   if ( !Jms2WinFile::exists( $index_php)
	     && !Jms2WinFile::exists( $index_html)) {
	      $content = '<html><body bgcolor="#FFFFFF"></body></html>';
	      Jms2WinFile::write( $index_html, $content);
	   }
	}

  //------------ _checkEmptyFolders ---------------
   /**
    * @brief Check that all the directories contain a 'index.html' file or a 'index.php' file
    * If this is not the case, add a 'index.html' file
    */
   function _checkEmptyFolders( $site_dir)
	{
	   $pathroot_len = strlen( JPATH_ROOT);

	   $path = $site_dir;
	   while( strlen( $path) > $pathroot_len) {
   	   $this->_createEmptyFolder( $path);
   	   $path = dirname( $path);
	   }
	   
	   // When there is both index.html and index.php in the site_dir, delete the "dummy" index.html (size < 50)
	   $index_php  = $site_dir.DS.'index.php';
	   $index_html = $site_dir.DS.'index.html';
	   if ( JFile::exists( $index_php) && JFile::exists( $index_html)) {
         // If "dummy" file to hide the directory
         $str = file_get_contents( $index_html);
         if ( strlen( $str) < 50) {
            // Remove the html file to let the index.php take the hand
            JFile::delete( $index_html);
         }
	   }
	}

	
	
   //------------ _real_path ---------------
   /**
    * @brief resolve the "./ and ../" present in an path
    */ 
	function _real_path( $path)
	{
	   $result = realpath( $path);
	   // If unable to resolve it with PHP routine
	   if ( $result === false) {
	      // Retry to do it manually
	      $parts = preg_split('/\/|\\\\/', $path);
	      $n = count( $parts);
	      for ( $i=0; $i<$n; ) {
	         if ( $parts[$i] == '..') {
	            if ( $i>0 && $parts[$i-1] != '..') {
	               // resolve the "../"
	               for ( $j=$i+1; $j<$n; $j++) {
	                  $parts[$j-2]=$parts[$j];
	               }
	               array_pop($parts);
	               array_pop($parts);
         	      $n = count( $parts);
         	      $i--;
	            }
	            else {
	               $i++;
	            }
	         }
	         else {
	            $i++;
	         }
	      }
	      $result = implode( DS, $parts);
	   }
	   
	   return $result;
	}



   //------------ duplicateDBandConfig ---------------
   /**
    * @brief This duplicate the DB and configuration file base on a "template" or a "from Site ID" or the master DB.
    *
    * This routine also create specific path for tmp, log
    *
    * @param dbInfo['fromTemplateID']  Template ID that specify the "from DB".
    *                                  When not defined, it uses the "fromSiteID"
    * @param dbInfo['fromSiteID']      Site ID to use as template to create the new DB.
    *                                  When not defined, it uses the Master Site
    * @param dbInfo['toSiteName']      New title of the site
    * @param dbInfo['toPrefix']        New table prefix
    *
    * @par Implementation:
    * - If "From Template ID" is present, load the configuration of the site ID defined in the template.
    * - If there is no "From Template ID", try to use the "From Site ID" to get its configuraton file;
    * - If there no template or site, use the master configuration file.
    *
    * When the "source" configuration is found, copy it to create the "to" configuration file.
    * - Fill the new "to" site name;
    * - Compute the "toPrefix".
    *   If there is a value in dbInfo['toPrefix'], use it.
    *   Otherwise compte the value based on the template.
    * - Write the new "to" configuration.php
    * - Copy the "From" DB into "To" DB.
    * - Update the "To" DB with specific configuration information such as media folder, image folder
    */
   function duplicateDBandConfig( $enteredvalues, $dbInfo, $site_id, $site_dir=null, $deploy_dir = null)
	{
//Debug2Win::enableStandalone();      // Write the log in administrator/components/com_multisites/classes/logs
//Debug2Win::setFileName( 'multisites.media.log.php');
//Debug2Win::enableDebug();        // Remove the comment to enable the debugging

//Debug2Win::debug_start( '>> duplicateDBandConfig() - START');

		$sharedTables = array();
		if ( empty( $site_dir)) {
   		$site_dir = JPATH_MULTISITES .DS. $site_id;
		}

	   // Create the "to" configuration file based on the "from" configuration file
	   $template = null;
	   if ( !empty( $dbInfo['fromTemplateID'])) {
	      if ( $dbInfo['fromTemplateID'] == '[unselected]'
	        || $dbInfo['fromTemplateID'] == ':master_db:') {
	         $fromSiteID = ':master_db:';
	      }
	      else {
   	      $template = new Jms2WinTemplate();
   	      $template->load( $dbInfo['fromTemplateID']);
   	      $fromSiteID       = $template->fromSiteID;
   	      
   	      $dbsharing = new Jms2WinDBSharing();
   	      if ( $dbsharing->load()) {
      	      $sharedTables  = $dbsharing->getSharedTables( $template->dbsharing);
   	      }
	      }
	      $fromConfig =& Jms2WinFactory::getMultiSitesConfig( $fromSiteID);
	   }
	   else if ( isset( $dbInfo['fromSiteID'])) {
	      $fromSiteID = $dbInfo['fromSiteID'];
	      $fromConfig =& Jms2WinFactory::getMultiSitesConfig( $fromSiteID);
	   }
	   else {
	      $fromSiteID = null;
	      $fromConfig =& Jms2WinFactory::getMasterConfig();
	   }
	   
	   if ( empty( $fromConfig)) {
			return array( JText::sprintf( 'SITE_DEPLOY_CONFIG_NOT_FOUND', $fromSiteID));
	   }
	   
	   // If the "to" configuration files does not exists
      $toConfig =& Jms2WinFactory::getMultiSitesConfig( $site_id);
	   if ( empty( $toConfig)) {
   	   // Duplicate the "from" config file
   	   $toConfig = clone( $fromConfig);
   	}

	   // Update the "to" config file with site name, db prefix, and path for logs and tmp
	   if ( isset( $dbInfo['toSiteName'])) {
   	   $sitename			= htmlspecialchars( $dbInfo['toSiteName']);
   	   $toConfig->setValue( 'config.sitename', $sitename);
   	   $toConfig->setValue( 'config.fromname', $sitename);
	   }
	   // Reset the live_site in case where it has something to avoid a bad root URL on the slave
	   $toConfig->setValue( 'config.live_site', '');
	   
		// If there is a deployment directory
	   if ( !empty( $deploy_dir))
	   {
	      // In case where deploy dir contain '../' in the path when FTP layer is used
	      $log_path = $deploy_dir .DS. 'logs';
	      $tmp_path = $deploy_dir .DS. 'tmp';
	      
	      $real_log_path = $this->_real_path( $log_path);
	      if ( $real_log_path === false) {
   	      $real_log_path = $log_path;
	      }
	      $real_tmp_path = $this->_real_path( $tmp_path);
	      if ( $real_tmp_path === false) {
   	      $real_tmp_path = $tmp_path;
	      }
	   }
	   else {
	      $log_path = $site_dir .DS. 'logs';
	      $tmp_path = $site_dir .DS. 'tmp';
	      
	      $real_log_path = $log_path;
	      $real_tmp_path = $tmp_path;
	   }
	   $toConfig->setValue( 'config.log_path', $real_log_path);
	   $this->_createEmptyFolder( $log_path);

	   $toConfig->setValue( 'config.tmp_path', $real_tmp_path);
	   $this->_createEmptyFolder( $tmp_path);

	   // ----- Shared DB ---
	   // If Share the DB Connection (using the same DB name and table prefix)
	   if ( !empty( $dbInfo['shareDB']) && $dbInfo['shareDB']) {
	      // Compute the "configuration.php" path
   	   if ( empty( $deploy_dir)) {
   	      $fname = $site_dir .DS. 'configuration.php';
   	   }
   	   else {
   	      $fname = $deploy_dir .DS. 'configuration.php';
   	   }
   	   
/*
   	   // If the configuration files does not exists
   	   if ( !JFile::exists( $fname)) {
      	   // Create a new "secret" value to have "specific" cache files in case where the "cache" directory is shared.
      	   // Also used by some application like "JReviews" that make their configuration file unique based on the secret password.
            jimport( 'joomla.user.helper');
      	   $toConfig->setValue( 'config.secret', JUserHelper::genRandomPassword(16));
   	   }
*/
	      // Just create the configuration.php files
   		// Write the "to" configuration file
         if ( version_compare( JVERSION, '1.6') >= 0) {
      		$configStr = $toConfig->toString('PHP', array('class' => 'JConfig'));
      	}
      	else {
      		$configStr = $toConfig->toString('PHP', 'config', array('class' => 'JConfig'));
      	}

   		if ( !Jms2WinFile::write($fname, $configStr)) {
   			return array( JText::sprintf( 'Error writing configuration file [%s]', $fname));
   		}
   		
   		// Success
   		return null;
	   }

	   // ----- Not Shared DB ---
	   // If there is a 'toDBHost' defined in the site
	   if ( !empty( $dbInfo['toDBHost'])) {
	      $toDBHost = MultisitesDatabase::evalStr( $dbInfo['toDBHost'], $site_id, $site_dir, $deploy_dir, $dbInfo);
	   }
	   else if ( isset( $template) && !empty( $template->toDBHost)) {
	      $toDBHost = MultisitesDatabase::evalStr( $template->toDBHost, $site_id, $site_dir, $deploy_dir, $dbInfo);
	   }
	   if ( !empty( $toDBHost)) {
   	   $toConfig->setValue( 'config.host', $toDBHost);
	   }

	   // If there is a 'toDBName' defined in the site
	   if ( !empty( $dbInfo['toDBName'])) {
	      $toDBName = MultisitesDatabase::evalStr( $dbInfo['toDBName'], $site_id, $site_dir, $deploy_dir, $dbInfo);
	   }
	   else if ( isset( $template) && !empty( $template->toDBName)) {
	      $toDBName = MultisitesDatabase::evalStr( $template->toDBName, $site_id, $site_dir, $deploy_dir, $dbInfo);
	   }
	   if ( !empty( $toDBName)) {
   	   $toConfig->setValue( 'config.db', $toDBName);
   	}

	   // If there is a 'toDBUser' defined in the site
	   if ( !empty( $dbInfo['toDBUser'])) {
	      $toDBUser = MultisitesDatabase::evalStr( $dbInfo['toDBUser'], $site_id, $site_dir, $deploy_dir, $dbInfo);
	   }
	   else if ( isset( $template) && !empty( $template->toDBUser)) {
	      $toDBUser = MultisitesDatabase::evalStr( $template->toDBUser, $site_id, $site_dir, $deploy_dir, $dbInfo);
	   }
	   if ( !empty( $toDBUser)) {
   	   $toConfig->setValue( 'config.user', $toDBUser);
   	}

	   // If there is a 'toDBPsw' defined in the site
	   if ( !empty( $dbInfo['toDBPsw'])) {
	      $toDBPsw = MultisitesDatabase::evalStr( $dbInfo['toDBPsw'], $site_id, $site_dir, $deploy_dir, $dbInfo);
	   }
	   else if ( isset( $template) && !empty( $template->toDBPsw)) {
	      $toDBPsw = MultisitesDatabase::evalStr( $template->toDBPsw, $site_id, $site_dir, $deploy_dir, $dbInfo);
	   }
	   if ( !empty( $toDBPsw)) {
   	   $toConfig->setValue( 'config.password', $toDBPsw);
   	}

	   // If there is a 'toPrefix' defined in the site
	   if ( isset( $dbInfo['toPrefix'])) {
	      $toPrefix = MultisitesDatabase::evalStr( $dbInfo['toPrefix'], $site_id, $site_dir, $deploy_dir, $dbInfo);
	   }
	   else if ( isset( $template) && !empty( $template->toPrefix)) {
	      $toPrefix = MultisitesDatabase::evalStr( $template->toPrefix, $site_id, $site_dir, $deploy_dir, $dbInfo);
	   }
	   $toConfig->setValue( 'config.dbprefix', $toPrefix);

	   // If New FTP parameter is Yes or No
	   if ( isset( $dbInfo['toFTP_enable']) && ($dbInfo['toFTP_enable']=='0' || $dbInfo['toFTP_enable']=='1')) {
   	   // If there is a new 'to FTP enable' defined in the site
   	   if ( isset( $dbInfo['toFTP_enable'])) {
   	      $toFTP_enable = MultisitesDatabase::evalStr( $dbInfo['toFTP_enable'], $site_id, $site_dir, $deploy_dir, $dbInfo);
   	   }
   	   else if ( isset( $template) && !empty( $template->toFTP_enable)) {
   	      $toFTP_enable = MultisitesDatabase::evalStr( $template->toFTP_enable, $site_id, $site_dir, $deploy_dir, $dbInfo);
   	   }
   	   $toConfig->setValue( 'config.ftp_enable', $toFTP_enable);

   	   // If there is a new 'to FTP Host' defined in the site
   	   if ( isset( $dbInfo['toFTP_host'])) {
   	      $toFTP_host = MultisitesDatabase::evalStr( $dbInfo['toFTP_host'], $site_id, $site_dir, $deploy_dir, $dbInfo);
   	   }
   	   else if ( isset( $template) && !empty( $template->toFTP_host)) {
   	      $toFTP_host = MultisitesDatabase::evalStr( $template->toFTP_host, $site_id, $site_dir, $deploy_dir, $dbInfo);
   	   }
   	   if ( isset( $toFTP_host)) {
   	      $toConfig->setValue( 'config.ftp_host', $toFTP_host);
   	   }
   	   

   	   // If there is a new 'to FTP Port' defined in the site
   	   if ( isset( $dbInfo['toFTP_port'])) {
   	      $toFTP_port = MultisitesDatabase::evalStr( $dbInfo['toFTP_port'], $site_id, $site_dir, $deploy_dir, $dbInfo);
   	   }
   	   else if ( isset( $template) && !empty( $template->toFTP_port)) {
   	      $toFTP_port = MultisitesDatabase::evalStr( $template->toFTP_port, $site_id, $site_dir, $deploy_dir, $dbInfo);
   	   }
   	   if ( isset( $toFTP_port)) {
      	   $toConfig->setValue( 'config.ftp_port', $toFTP_port);
   	   }

   	   // If there is a new 'to FTP User' defined in the site
   	   if ( isset( $dbInfo['toFTP_user'])) {
   	      $toFTP_user = MultisitesDatabase::evalStr( $dbInfo['toFTP_user'], $site_id, $site_dir, $deploy_dir, $dbInfo);
   	   }
   	   else if ( isset( $template) && !empty( $template->toFTP_user)) {
   	      $toFTP_user = MultisitesDatabase::evalStr( $template->toFTP_user, $site_id, $site_dir, $deploy_dir, $dbInfo);
   	   }
   	   if ( isset( $toFTP_user)) {
      	   $toConfig->setValue( 'config.ftp_user', $toFTP_user);
   	   }

   	   // If there is a new 'to FTP User' defined in the site
   	   if ( isset( $dbInfo['toFTP_psw'])) {
   	      $toFTP_psw = MultisitesDatabase::evalStr( $dbInfo['toFTP_psw'], $site_id, $site_dir, $deploy_dir, $dbInfo);
   	   }
   	   else if ( isset( $template) && !empty( $template->toFTP_psw)) {
   	      $toFTP_psw = MultisitesDatabase::evalStr( $template->toFTP_psw, $site_id, $site_dir, $deploy_dir, $dbInfo);
   	   }
   	   if ( isset( $toFTP_psw)) {
      	   $toConfig->setValue( 'config.ftp_pass', $toFTP_psw);
   	   }

   	   // If there is a new 'to FTP Root' defined in the site
   	   if ( isset( $dbInfo['toFTP_rootpath'])) {
   	      $toFTP_rootpath = MultisitesDatabase::evalStr( $dbInfo['toFTP_rootpath'], $site_id, $site_dir, $deploy_dir, $dbInfo);
   	   }
   	   else if ( isset( $template) && !empty( $template->toFTP_rootpath)) {
   	      $toFTP_rootpath = MultisitesDatabase::evalStr( $template->toFTP_rootpath, $site_id, $site_dir, $deploy_dir, $dbInfo);
   	   }
   	   if ( isset( $toFTP_rootpath)) {
      	   $toConfig->setValue( 'config.ftp_root', $toFTP_rootpath);
   	   }
	   }


		// Write the "to" configuration file
	   if ( empty( $deploy_dir)) {
	      $fname = $site_dir .DS. 'configuration.php';
	   }
	   else {
	      $fname = $deploy_dir .DS. 'configuration.php';
	   }
/*
	   // If the configuration files does not exists
	   if ( !JFile::exists( $fname)) {
   	   // Create a new "secret" value to have "specific" cache files in case where the "cache" directory is shared.
   	   // Also used by some application like "JReviews" that make their configuration file unique based on the secret password.
         jimport( 'joomla.user.helper');
   	   $toConfig->setValue( 'config.secret', JUserHelper::genRandomPassword(16));
	   }
*/	   
      if ( version_compare( JVERSION, '1.6') >= 0) {
   		$configStr = $toConfig->toString('PHP', array('class' => 'JConfig'));
   	}
   	else {
   		$configStr = $toConfig->toString('PHP', 'config', array('class' => 'JConfig'));
   	}
		if ( !Jms2WinFile::write($fname, $configStr)) {
			return array( JText::sprintf( 'Error writing configuration file [%s]', $fname));
		}

//Debug2Win::debug( '================ Before Get DB connection =====================');
//Debug2Win::debug( 'From Site ID =' . $fromSiteID );


	   // Duplicate the DB
	   if ( !empty( $fromSiteID)) {
	      // If duplicate in the same DB ?
	      if ( ($toConfig->getValue( 'config.dbtype') == $fromConfig->getValue( 'config.dbtype'))
	        && ($toConfig->getValue( 'config.host')   == $fromConfig->getValue( 'config.host'))
	        && ($toConfig->getValue( 'config.db')     == $fromConfig->getValue( 'config.db'))
	         )
	      {} // Do nothing
	      // Else try Create the "to DB" when it does not exists
	      else {
	         $errors = MultisitesDatabase::makeDB( $fromConfig, $toConfig);
      	   if ( !empty( $errors )) {
         	   return $errors;
         	}
	      }
	      
	      // Connect on the "From" DB
	      if ( $fromSiteID == ':master_db:') {
   	      $fromDB =& Jms2WinFactory::getMasterDBO();
	      }
	      else {
   	      $fromDB     =& Jms2WinFactory::getSlaveDBO( $fromSiteID);
	      }
	   }
	   else {
	      $fromDB =& Jms2WinFactory::getMasterDBO();
	   }

      $toDB =& Jms2WinFactory::getSlaveDBO( $site_id);
      if ( empty( $toDB)) {
			return array( JText::_( 'Unable to connect on the "to" DB'));
		}

//Debug2Win::debug( '================ Before COPY DB =====================');
//Debug2Win::debug( 'Before copy "From DB" =' . var_export( $fromDB, true) );
//Debug2Win::debug( 'Before copy "to DB" =' . var_export( $toDB, true) );


	   $errors = MultisitesDatabase::copyDBSharing( $fromDB, $toDB, $sharedTables, $toConfig);
	   if ( empty( $errors )) {
//Debug2Win::debug( '================= Before COnfigure DB ====================');
//Debug2Win::debug( 'Before Config "From DB" =' . var_export( $fromDB, true) );
//Debug2Win::debug( 'Before Config "to DB" =' . var_export( $toDB, true) );
   	   $errors = MultisitesDatabase::configureDB( $fromSiteID, $fromDB, $toDB, $enteredvalues, $site_id, $site_dir, $deploy_dir, $dbInfo, $template);
	   }

//Debug2Win::debug_stop( '<< duplicateDBandConfig() - STOP');
	   return $errors;
	}

   //------------ _deployDirLinks ---------------
   /**
    * @brief Create the template dir if not alreay exists and fill its content with the one of the "from site" or from the master sites when not specified.
    *
    * @par Implementation:
    * - If target template directory already exists, do nothing;
    * - If not exists, retreive the "from" template directory path based on "template website" and the "from" site ID.
    * - If the "from" template directory can not be found, use the master template directory for the replication.
    * - Copy the "from" template directory into "to" template directory.
    */
   function _deployDirLinks( $source_dir, $target_dir, $wrapper_files)
   {
      $from_dir = JPath::clean( $source_dir);
      $to_dir   = JPath::clean( $target_dir);

      // Duplicate the full content of the directory
      if ( isset( $from_dir) && $to_dir != $from_dir) {
   	   // If windows, the symbolic links does not exist
         if ( !MultisitesHelper::isSymbolicLinks()) {
            // Copy (duplicate) all templates
            if ( !Jms2WinFolder::copy( $from_dir, $to_dir)) {
               $this->setError( JText::sprintf( 'Unable to replicate the directory "%s" into "%s', $from_dir, $to_dir));
               return false;
            }
         }
         // Symbolic Links are available
         else {
            $excluding_patterns = array('.svn', 'CVS', 'vssver.scc');
            if ( !empty( $wrapper_files)) {
               $excluding_patterns = array_merge( $excluding_patterns, $wrapper_files);
            }
            $folders = Jms2WinFolder::folders( $from_dir, '.', false, false, $excluding_patterns);
            $savDir = getcwd();
            chdir( $to_dir);
      	   // If error
      	   if ( !is_array( $folders)) {
      	      // Do nothing
      	   }
      	   // Otherwise process the array
      	   else foreach( $folders as $link)
      	   {
      	      // If target folder does not already exists
      	      if ( !JFolder::exists( $to_dir .DS. $link)) {
         	      $target_path = $from_dir .DS. $link;
         	      // Try creaet a Symbolic Link on this folder,
         	      if ( !$this->symlink( $target_path, $link)) {
         	         // If it fails, retry with a copy of the folder
         	         $to_path = $to_dir .DS. $link;
                     // Copy (duplicate) the templates
                     if ( !JFolder::copy( $target_path, $to_path)) {
                        $this->setError( JText::sprintf( 'Unable to replicate the directory "%s" into "%s', $from_dir, $to_dir));
                        chdir( $savDir);
                        return false;
                     }
         	      }
      	      }
      	   }
            chdir( $savDir);

            $files = Jms2WinFolder::files( $from_dir, '.', false, false, $excluding_patterns);
            $savDir = getcwd();
            chdir( $to_dir);
      	   // If error
      	   if ( !is_array( $files)) {
      	      // Do nothing
      	   }
      	   // Otherwise process the array
      	   foreach( $files as $link)
      	   {
      	      // If target files does not already exists
      	      if ( !Jms2WinFile::exists( $to_dir .DS. $link)) {
         	      $target_path = $from_dir .DS. $link;
         	      // If Symbolic Link creation has failed,
         	      if ( !$this->symlink( $target_path, $link)) {
         	         // Try copy the folder
         	         $to_path = $to_dir .DS. $link;
                     // Copy (duplicate) the templates
                     if ( !Jms2WinFile::copy( $target_path, $to_path)) {
                        $this->setError( JText::sprintf( 'Unable to replicate the directory "%s" into "%s', $from_dir, $to_dir));
                        chdir( $savDir);
                        return false;
                     }
         	      }
      	      }
      	   }
            chdir( $savDir);

            
            // Foreach wrappers files to write
      	   foreach( $wrapper_files as $filename) {
      	      $from_filename = $from_dir .DS. $filename;
      	      $to_filename   = $to_dir   .DS. $filename;
      	      // Check that the "from" file exists and that the wrapper is not already created (to file does not exists)
      	      if ( Jms2WinFile::exists( $from_filename) && !JFile::exists( $to_filename)) {
      	         // Create the special wrapper to include the files
         	      $content = "<?php\n"
         	               . "// Don't use a Symbolic Link because the links maybe wrong.\n"
         	               . "// Just include the original file to redirect the processing.\n"
         	               . "//include( '$from_filename');\n"
         	               . "// Evaluate the original include file to redirect to keep the __FILE__ value.\n"
                           . "\$filename = '$from_filename';\n"
                           . '$handle = fopen ($filename, "r");' . "\n"
                           . '$contents = fread ($handle, filesize ($filename));' . "\n"
                           . 'fclose ($handle);' . "\n"
                           . 'unset($handle);' . "\n"
                           . 'eval("?>" . $contents);' . "\n"
         	               ;
         	      Jms2WinFile::write( $to_filename, $content);
      	      }
      	   }
      	   
      	   // Finally, when the directory is created, check if there is an index.html that must be created.
      	   // Only create an "index.html" when the "index.php" is not present.
      	   $to_index_html = $to_dir .DS. 'index.html';
      	   $to_index_php  = $to_dir .DS. 'index.html';
      	   if ( !Jms2WinFile::exists( $to_index_html) && !JFile::exists( $to_index_php)) {
      	      $content = '<html><body bgcolor="#FFFFFF"></body></html>';
      	      JFile::write( $to_index_html, $content);
      	   }
         }
      }

      return true;
   }


   //------------ _deployTemplates_special ---------------
   /**
    * @brief Create the template dir if not alreay exists and fill its content with the one of the "from site" or from the master sites when not specified.
    *
    * @par Implementation:
    * - If target template directory already exists, do nothing;
    * - If not exists, retreive the "from" template directory path based on "template website" and the "from" site ID.
    * - If the "from" template directory can not be found, use the master template directory for the replication.
    * - Copy the "from" template directory into "to" template directory.
    */
   function _deployTemplates_special( $templates_dir, $template, $site_id, $site_dir, $deploy_dir, $dbInfo, $force_copy = false)
   {
      // If the template directory already exists,
      if ( Jms2WinFolder::exists( $templates_dir)) {
         // do nothing
         return true;
      }
      $to_dir = Jms2WinPath::clean( $templates_dir);

      // Load the 'From' site configuration file
      $fromSiteID = $template->fromSiteID;
      $filename = JPATH_MULTISITES.DS.$fromSiteID.DS.'config_multisites.php';
      if ( !file_exists( $filename))
      {
         if ( class_exists( 'MultisitesLetterTree')) {
            // Try to compute a path using the letter tree
            $lettertree_dir = MultisitesLetterTree::getLetterTreeDir( $fromSiteID);
            if( !empty( $lettertree_dir)) {
               $filename = $site_dir.DIRECTORY_SEPARATOR.'config_multisites.php';
            }
         }
      }
      @include($filename);
      // If the 'FROM' site has a specific templates directory
      if ( isset( $config_dirs) && !empty( $config_dirs) && !empty( $config_dirs['templates_dir'])) {
         // Use the 'FROM' slave site 'front-end' template directory
         $from_dir = Jms2WinPath::clean( $config_dirs['templates_dir']);
      }
      else {
         // Use the master 'front-end' template directory
         $from_dir = Jms2WinPath::clean( JPATH_ROOT .DS. 'templates');
      }
      // Try get the action defined in the template
      if ( !empty( $template)
        && !empty( $template->symboliclinks)
        && !empty( $template->symboliclinks['templates'])
        && !empty( $template->symboliclinks['templates']['file']))
      {
	      $srcfile = $template->symboliclinks['templates']['file'];
         $from_dir = $this->_getSourcePath( 'templates', $srcfile, $site_id, $site_dir, $deploy_dir, $dbInfo);
      }

      // Duplicate the full content of the directory
      if ( isset( $from_dir) && $to_dir != $from_dir) {
   	   // If windows, the symbolic links does not exist
         if ( !MultisitesHelper::isSymbolicLinks() || $force_copy) {
            // Copy (duplicate) all templates (as JFolder::copy does not copy the symbolic links, then go the copy manually
            $this->_createEmptyFolder( $to_dir);
            $folders = Jms2WinFolder::folders( $from_dir, '.', false, false, array('.svn', 'CVS', 'vssver.scc'));
            $savDir = getcwd();
            chdir( $to_dir);
      	   foreach( $folders as $link)
      	   {
   	         // Try copy the folder
      	      $from_path = $from_dir .DS. $link;
   	         $to_path   = $to_dir   .DS. $link;
               // Copy (duplicate) the templates
               if ( !Jms2WinFolder::copy( $from_path, $to_path)) {
                  $this->setError( JText::sprintf( 'Unable to copy the template directory "%s" into "%s', $from_path, $to_path));
                  chdir( $savDir);
                  return false;
               }
      	   }
            chdir( $savDir);
         }
         // Symbolic Links are available
         else {
            $this->_createEmptyFolder( $to_dir);
            $folders = Jms2WinFolder::folders( $from_dir, '.', false, false, array('.svn', 'CVS', 'vssver.scc'));
            $savDir = getcwd();
            chdir( $to_dir);
      	   foreach( $folders as $link)
      	   {
      	      $target_path = $from_dir .DS. $link;
      	      // If Symbolic Link creation has failed,
      	      if ( !$this->symlink( $target_path, $link)) {
      	         // Try copy the folder
      	         $to_path = $to_dir .DS. $link;
                  // Copy (duplicate) the templates
                  if ( !Jms2WinFolder::copy( $target_path, $to_path)) {
                     $this->setError( JText::sprintf( 'Unable to replicate the template directory "%s" into "%s', $from_dir, $to_dir));
                     chdir( $savDir);
                     return false;
                  }
      	      }
      	   }
            chdir( $savDir);
         }
      }

      return true;
   }


   
   //------------ _deployTemplates ---------------
   /**
    * @brief Create the template dir if not alreay exists and fill its content with the one of the "from site" or from the master sites when not specified.
    *
    * @par Implementation:
    * - If target template directory already exists, do nothing;
    * - If not exists, retreive the "from" template directory path based on "template website" and the "from" site ID.
    * - If the "from" template directory can not be found, use the master template directory for the replication.
    * - Copy the "from" template directory into "to" template directory.
    */
   function _deployTemplates( $templates_dir, $template, $site_id, $site_dir, $deploy_dir, $dbInfo)
   {
      if ( empty( $template)) {
         return $this->_deployTemplates_special( $templates_dir, $template, $site_id, $site_dir, $deploy_dir, $dbInfo);
      }
      
      // Try get the action defined in the template
      if ( !empty( $template->symboliclinks)
        && !empty( $template->symboliclinks['templates'])
        && !empty( $template->symboliclinks['templates']['action'])) {
         $action = $template->symboliclinks['templates']['action'];
         if ( $action == 'copy') {
            return $this->_deployTemplates_special( $templates_dir, $template, $site_id, $site_dir, $deploy_dir, $dbInfo, true);
         }
         else if ( $action == 'unzip') {
            return true;
         }
      }
      // Default is a special copy
      return $this->_deployTemplates_special( $templates_dir, $template, $site_id, $site_dir, $deploy_dir, $dbInfo);
   }

   //------------ _calcConfigDirs ---------------
   /**
    * @brief Compute different directory values by using the entered values or the template value or none.
    *
    * Directories computed are:
    * - deploy_dir;
    * - template_dir; This also copy the template into the new template_dir.
    * - cache_dir;
    * - symboliclinks;
    */
   function _calcConfigDirs( $enteredvalues, $site_id, $site_dir, $dbInfo, $template)
   {
      $config_dirs = array();

	   // ----- deploy_dir ----
	   // For Unix platform (having possibility to use symnolic links)
	   // deploy the wrapper files that will improve security as 'installation' directory in slave site
	   // can be removed after the setup;
      $deploy_dir = null;
		// For unix platform, also create symbolic links
 	   if ( MultisitesHelper::isSymbolicLinks()) {
	      // If a specific deploy directory is specified,
	      if ( isset( $enteredvalues['deploy_dir']) && !empty( $enteredvalues['deploy_dir'])) {
	         // Use the deployed directory (ie. Plesk directory or CPanel, ...)
	         $deploy_dir = $enteredvalues['deploy_dir'];
	      }
	      else if ( isset( $template) && !empty( $template->deploy_dir)) {
	         // Use the deployed directory (ie. Plesk directory or CPanel, ...)
	         $deploy_dir = $template->deploy_dir;
	      }
	   }
	   if ( isset( $deploy_dir) && !empty( $deploy_dir)) {
         $config_dirs['deploy_dir'] = JPath::clean( MultisitesDatabase::evalStr( $deploy_dir, $site_id, $site_dir, null, $dbInfo));
         // If there is a transation of the deploy_dir statement,
         if ( !empty( $config_dirs['deploy_dir'])) {
            // Set here the resolved value;
            $deploy_dir = $config_dirs['deploy_dir'];
         }
	   }

	   // ----- alias_link ----
      if ( !empty( $enteredvalues['alias_link'])) {
         // Use the alias link specified in Site Management
         $alias_link = $enteredvalues['alias_link'];
      }
      else if ( isset( $template) && !empty( $template->alias_link)) {
         // Use the alias link specified in the Template management
         $alias_link = $template->alias_link;
      }
	   // Evaluate keywords
	   if ( !empty( $alias_link)) {
         $path = trim( JPath::clean( MultisitesDatabase::evalStr( $alias_link, $site_id, $site_dir, $deploy_dir, $dbInfo)));
         // If the evaluation has produced something,
   	   if ( !empty( $path)) {
            // Save the alias link path
            $config_dirs['alias_link'] = strtolower( $path);
         }
         else {
   	      return false;
         }
	   }

	   // ----- template_dir ----
      if ( isset( $enteredvalues['templates_dir']) && !empty( $enteredvalues['templates_dir'])) {
         // Use the directory specified in Site Management
         $templates_dir = JPath::clean( $enteredvalues['templates_dir']);
      }
      else if ( isset( $template) && !empty( $template->templates_dir)) {
         // Use the directory specified in the Template management
         $templates_dir = JPath::clean( $template->templates_dir);
      }
	   if ( isset( $templates_dir)) {
         $path = JPath::clean( MultisitesDatabase::evalStr( $templates_dir, $site_id, $site_dir, $deploy_dir, $dbInfo));
         // If the template directory has been correctly deployed,
         if ( $this->_deployTemplates( $path, $template, $site_id, $site_dir, $deploy_dir, $dbInfo)) {
            // Save the new path (otherwise, ignore the new template directory info)
            $config_dirs['templates_dir'] = $path;
         }
         else {
   	      return false;
         }
	   }


	   // ----- cache_dir ----
      if ( isset( $enteredvalues['cache_dir']) && !empty( $enteredvalues['cache_dir'])) {
         // Use the directory specified in Site Management
         $cache_dir = Jms2WinPath::clean( $enteredvalues['cache_dir']);
      }
      else if ( isset( $template) && !empty( $template->cache_dir)) {
         // Use the directory specified in the Template management
         $cache_dir = Jms2WinPath::clean( $template->cache_dir);
      }
	   if ( isset( $cache_dir)) {
         $config_dirs['cache_dir'] = Jms2WinPath::clean( MultisitesDatabase::evalStr( $cache_dir, $site_id, $site_dir, $deploy_dir, $dbInfo));
	   }
	   else {
	      if ( !empty( $deploy_dir)) {
	         if ( Jms2WinFolder::exists( $deploy_dir)) {
      	      $path = Jms2WinPath::clean( $deploy_dir .DS. 'cache');
      	      $this->_createEmptyFolder( $path);
      	      $config_dirs['cache_dir'] = $path;
	         }
	      }
	      else {
   	      $path = Jms2WinPath::clean( $site_dir .DS. 'cache');
   	      $this->_createEmptyFolder( $path);
   	      $config_dirs['cache_dir'] = $path;
	      }
	   }


		if ( !empty( $template)) {
		   $config_dirs['symboliclinks'] = $template->symboliclinks;
		   $config_dirs['dbsharing']     = $template->dbsharing;
		}

	   return $config_dirs;
   }

   //------------ _getTemplate ---------------
   /**
    * @return Return a template object based on its "fromTemplateID" value.
    */
   function &_getTemplate( $enteredvalues)
   {
      static $instance;

      if ( empty( $instance)) {
   	   $template = null;
   	   if ( !empty( $enteredvalues['fromTemplateID'])) {
   	      $template = new Jms2WinTemplate();
   	      $template->load( $enteredvalues['fromTemplateID']);
   	   }
         $instance = $template;
      }
      return $instance;
   }

   //------------ _getSiteInfo ---------------
   /**
    * @brief Return an array with site information.
    * The site information are:
    * - Site prefix
    * - Site Alias
    */
   function &_getSiteInfo( $enteredvalues)
   {
      static $instance;

      if ( empty( $instance)) {
         $siteInfo = array();
         $siteInfo['site_prefix']   = !empty( $enteredvalues['site_prefix'])
                                    ? $enteredvalues['site_prefix']
                                    : '';
         $siteInfo['site_alias']    = !empty( $enteredvalues['site_alias'])
                                    ? $enteredvalues['site_alias']
                                    : '';
         $instance = $siteInfo;
      }
      return $instance;
   }

   //------------ getSiteID ---------------
   /**
    * @brief Get a Site ID number based on input values (template, site_prefix, ...)
    *
    * This routine translate the special keyword with their real values.
    *
    * @par Implementation:
    * - If there is an enteredvalues['id'], use it;
    * - If there is no ID and a template ID is specified, translate the template "toSiteID" using
    *   the keyword in aim to produce a new site id based on generic rule.
    */
   function getSiteID( $enteredvalues)
   {
      $template = MultisitesModelManage::_getTemplate( $enteredvalues);
      $siteInfo = MultisitesModelManage::_getSiteInfo( $enteredvalues);

	   // --- If site_id is missing, try to compute one from the template ---
	   // If the Site ID is empty and there is a template
	   $id   = !empty( $enteredvalues['id'])
	         ? $enteredvalues['id']
	         : '';
	   if ( empty( $id) && !empty( $template) && !empty( $template->fromSiteID)) {
	      // Try to compute the ID based on the template
	      $str = $template->toSiteID;
	      $id = MultisitesDatabase::evalStr( $str, null, null, null, $siteInfo);
	   }
	   if ( empty($id)) {
	      $this->setError( JText::_( 'SITE_PROVIDE_ID'));
	      return false;
	   }
	   return $id;

   }


   //------------ _getWebsiteID ---------------
   /**
    * @return
    * - false = error
    * - < 0 = Website with max Quota reached
    * - = 0 = Free website
    * - > 0 = Valid front-end Website ID
    */
   function _getWebsiteID( $enteredvalues)
   {
      // If new slave site, check if there is a eShop script present (If YES, a fee is required)
		$pc_b64  = JRequest::getString('payment_code');
		$Err_b64 = JRequest::getString('onDeploy_Err_code');
		$OK_b64  = JRequest::getString('onDeploy_OK_code');
      if ( empty( $pc_b64) && empty( $Err_b64) && empty( $OK_b64) && empty( $enteredvalues['payment_ref']) ) {
         return 0;  // Free website
      }

      // If this is an update, the website ID is already present.
      if ( !empty( $enteredvalues['website_id']) && (int)$enteredvalues['website_id']!=0) {
         return (int)$enteredvalues['website_id'];
      }

      // Here we have a new slave where fees must be charged

      // Ensure, we have a product ID
      require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'models' .DS. 'registration.php');
      $regInfo    = Edwin2WinModelRegistration::getRegistrationInfo();
      if ( empty( $regInfo) || empty( $regInfo['product_id']) || empty( $regInfo['product_key'])) {
	      $this->setError( JText::_( 'You must register the product to create a website from the front-end'));
         return false;
      }
      $product_id = $regInfo['product_id'];

      // Request a Website ID
      $site_id       = $enteredvalues['id'];
      $site_prefix   = $enteredvalues['site_prefix'];
      $owner_id      = $enteredvalues['owner_id'];
      $domains       = $enteredvalues['domains'][0];
      $vars = array( 'option'          => 'com_pay2win',
                     'task'            => 'jms.getWebSite',
                     'product_id'      => $product_id,
                     'site_id'         => $site_id,
                     'site_prefix'     => $site_prefix,
                     'owner_id'        => $owner_id,
                     'domains'         => $domains,
                     'enteredvalues'   => serialize( $enteredvalues)
                   );

      $data = '';
      $url =& Edwin2WinModelRegistration::getURL();
      if ( empty( $url)) {
	      $this->setError( JText::_( 'Unable to get a Website ID'));
         return false;
      }
      $result = HTTP2Win::request( $url, $vars);
      if ( $result === false) {
	      $this->setError( JText::_( 'The Website ID cannot be computed'));
      }
      else {
         $status = HTTP2Win::getLastHttpCode();
         // If HTTP OK
         if ( $status == '200') {
            $data =& HTTP2Win::getLastData();
   	      if ( strncmp( $data, '[OK]', 4) == 0) {
   	         // Retreive the website_id
   	         $arr        = explode( '|', $data);
   	         $website_id = $arr[1];
               return $website_id;
            }
   	      else if ( strncmp( $data, '[ERR]', 5) == 0) {
   	         // Extract error info
   	         $arr = explode( '|', $data);
   	         $err_level  = $arr[1];
   	         $website_id = $arr[2];
   	         $err_code   = $arr[3];
   	         // Translate the Return Code into a Front-End message
   	         $err_code_key = 'JMS2WIN_ERR_FE_'.$err_code;
   	         $user_msg = JText::_( $err_code_key);
   	         if ( $user_msg == $err_code_key) {
      	         $msg        = $arr[4];
   	         }
   	         else {
      	         $msg        = $user_msg;
   	         }
   	         if ( !empty( $msg)) {
            		$this->setError( $msg);
   	         }

               // Post an email ALERT to the adminstrator
               $subject = 'PRODUCTION ALERT: An error occurs when creating a billable website';
               $body = "An error occurs when creating the site ID = [$site_id] for the user [$owner_id]\n"
                     . "The error reported is : $err_code\n"
                     . "The message is: " .$msg
                     . "Error code starting with the letter [W] is a warning and the website is created for a limited period of time with ads displayed.\n"
                     . "If this is the case, you probably must increase your website quota in the www.jms2win.com website.\n"
                     ;
               $mail = JFactory::getMailer();
               $mail->addRecipient( $mail->From);
               $mail->setSubject( $subject);
               $mail->setBody( $body);
               $mail->Send();

               // If warning
               if ( $err_level == 'W') {
                  return - (int)$website_id;   // Negative number
               }
               // Fatal error
               return false;
            }
            else {
         		$this->setError( "Unable to register the new slave site into Joomla Multi Sites. Returned data=[".$data."]");
            }
         }
      }

      // Return ERROR
      return false;
   }


   //------------ _deleteWebsiteID ---------------
   /**
    * @brief Logical delete of the website
    */
   function _deleteWebsiteID()
   {
	   $site_id    = JRequest::getCmd('id');
	   $site = new Site();
	   $site->load( $site_id);
	   // If this is a free website
	   if ( empty( $site->website_id)) {
	      return true;
	   }

      // Ensure, we have a product ID
      require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'models' .DS. 'registration.php');
      $regInfo    = Edwin2WinModelRegistration::getRegistrationInfo();
      if ( empty( $regInfo) || empty( $regInfo['product_id']) || empty( $regInfo['product_key'])) {
	      $this->setError( JText::_( 'You must register the product to delete a website created from the front-end'));
         return false;
      }
      $product_id = $regInfo['product_id'];

      // Forward the delete of the Website ID
	   $website_id    = $site->website_id;
      $vars = array( 'option'          => 'com_pay2win',
                     'task'            => 'jms.deleteWebSite',
                     'product_id'      => $product_id,
                     'site_id'         => $site_id,
                     'website_id'      => $website_id
                   );

      $data = '';
      $url =& Edwin2WinModelRegistration::getURL();
      if ( empty( $url)) {
	      $this->setError( JText::_( 'Unable to know where to post the website to delete'));
         return false;
      }
      $result = HTTP2Win::request( $url, $vars);
      if ( $result === false) {
	      // $this->setError( JText::_( 'The Website cannot be marked as deleted'));
      }
      else {
         $status = HTTP2Win::getLastHttpCode();
         // If HTTP OK
         if ( $status == '200') {
            $data =& HTTP2Win::getLastData();
   	      if ( strncmp( $data, '[OK]', 4) == 0) {
   	         return true;
            }
   	      else if ( strncmp( $data, '[ERR]', 5) == 0) {
   	         // Extract error info
   	         $arr = explode( '|', $data);
   	         $err_level  = $arr[1];
   	         $website_id = $arr[2];
   	         $err_code   = $arr[3];
   	         // Translate the Return Code into a Front-End message
   	         $err_code_key = 'JMS2WIN_ERR_FE_'.$err_code;
   	         $user_msg = JText::_( $err_code_key);
   	         if ( $user_msg == $err_code_key) {
      	         $msg        = $arr[4];
   	         }
   	         else {
      	         $msg        = $user_msg;
   	         }
   	         if ( !empty( $msg)) {
            		$this->setError( $msg);
   	         }

               // If warning
               if ( $err_level == 'W') {
                  return true;
               }
               // Fatal error
               return false;
            }
            else {
         		$this->setError( "Unable to mark the website as deleted into Joomla Multi Sites. Returned data=[".$data."]");
            }
         }
      }

      // Return ERROR
      return false;
   }

   //------------ canCreateSlave ---------------
   function canCreateSlave( $enteredvalues, $front_end = false)
   {
      // If this is the back-end, always accept
      if ( !$front_end) {
         return true;
      }

      // In case of front-end, accept update or unknown operation
      if ( empty( $enteredvalues['isnew']) || $enteredvalues['isnew']==false) {
         // OK, it is autorized to create and deploy a slave site
         return true;
      }

      // Check if the product is registered
      require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'models' .DS. 'registration.php');
   	// If not registered
		if ( !Edwin2WinModelRegistration::isRegistered()) {
	      $this->setError( JText::_( 'You must register the product to create a website from the front-end'));
		   return false;
		}

		return true;
   }


   //------------ writeSite ---------------
   /**
    * @brief Write the configuration file
    * @param site_dir      This is the multisites/site_id path
    * @param domains       Array with the list of domain entered by the user. The keyword are not resolved.
    * @param indexDomains  Array with the list of domain entered by the user where keyword are resolved.
    *                      It is used to compute the Master Index
    */
   function writeSite( $site_dir, $domains, $indexDomains, $newDBInfo, $config_dirs)
	{
	   // Save the list of domains into a special configuration files
		$config = "<?php\n";
		// *****************************
		// DON'T TEST ANY DEFINED TO MAKE THE FILE DIE() BECAUSE IT CAN BE CALLED IN DIRECT WHEN CONFIGURATION FILES CALLED
		// Case of PayPal notify.php file of VirtueMart
		// $config .= "if( !defined( '_JEXEC' )) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); \n\n";
		// *****************************
		$config .= "\$domains = array( '" . implode( "' , '", $domains) . "');\n";
		$config .= "\$indexDomains = array( '" . implode( "' , '", $indexDomains) . "');\n";
		if ( !empty( $newDBInfo)) {
   		$config .= '$newDBInfo = array( ';
   		$sep = '';
   		foreach( $newDBInfo as $key => $value) {
   		   $config .= $sep . "'$key' => '" . addslashes($value) ."'";
   		   $sep = ', ';
   		}
   		$config .= ");\n";
		}
		if ( !empty( $config_dirs)) {
   		$config .= '$config_dirs = array( ';
   		$sep = '';
   		foreach( $config_dirs as $key => $value) {
   		   if ( is_array( $value)) {
   		      $config .= $sep . "'$key' => " . 'array( ' . MultisitesUtils::CnvArray2Str( '          ', $value) . ')';
   		   }
   		   else {
   		      $config .= $sep . "'$key' => '" . addslashes($value) ."'";
   		   }
   		   $sep = ', ';
   		}
   		$config .= ");\n";
		}
		$config .= "?>";

	   $filename = $site_dir .DS. 'config_multisites.php';
      return JFile::write( $filename, $config);
   }


   
   //------------ _countWebSites ---------------
   /**
    * @brief Count the current number of websites having the owner_id = the value specified.
    * It is used to check the maximum number of websites that a front-end user can create.
    */
   function _countWebSites( $owner_id = null)
	{
	   // If there is no owner, don't count
	   if ( empty( $owner_id) || $owner_id <= 0) {
	      return 0;
	   }
	   $count = 0;
	   /* Collect all Site name and domain names */
	   $sites = $this->getSites();
	   foreach( $sites as $site) {
	      // Only count the websites having a status confirmed
	      // Or status confirmed and not expired
	      // Otherwise, skip the site
	      if ( empty( $site->status) || $site->status == 'Confirmed') {
	         if ( !empty( $site->owner_id) && $site->owner_id == $owner_id) {
	            $count++;
	         }
	      }
	   }
	   
	   return $count;
	}

   //------------ _isDeployedFTPEnabled ---------------
   /**
    * @brief Check if the website must be deployed using the FTP enabled.
    */
   function _isDeployedFTPEnabled( $newDBInfo)
	{
	   if ( !defined( 'MULTISITES_REDIRECT_FTP') || !(MULTISITES_REDIRECT_FTP)) {
	      return false;
	   }

	   // If FTP Enable = No
	   if ( isset( $newDBInfo['toFTP_enable']) && $newDBInfo['toFTP_enable'] == 0) {
   	   return false;
	   }
	   // If FTP Enable = Yes
	   else if ( isset( $newDBInfo['toFTP_enable']) && $newDBInfo['toFTP_enable'] == 1) {
   	   return true;
	   }
	   // If FTP Enable = default or a keyword

		// Initialize variables
		jimport('joomla.client.helper');
		$ftpOptions = JClientHelper::getCredentials('ftp');
		if ($ftpOptions['enabled'] == 1) {
   	   return true;
		}

	   return false;
	}

   //------------ _saveFTPInfos ---------------
   /**
    * @brief Save the FTP informations into the array provided in parameters
    */
   function _saveFTPInfos( &$sav_FTPInfos)
	{
		$config =& JFactory::getConfig();

	   $sav_FTPInfos['toFTP_enable']  = $config->getValue('config.ftp_enable');
	   $sav_FTPInfos['toFTP_host']    = $config->getValue('config.ftp_host');
	   $sav_FTPInfos['toFTP_port']    = $config->getValue('config.ftp_port');
	   $sav_FTPInfos['toFTP_user']    = $config->getValue('config.ftp_user');
	   $sav_FTPInfos['toFTP_psw']     = $config->getValue('config.ftp_pass');
	   $sav_FTPInfos['toFTP_rootpath']= $config->getValue('config.ftp_root');
	}

   //------------ _setNewFTPInfos ---------------
   /**
    * @brief Change the current "config" with new FTP parameters.
    */
   function _setNewFTPInfos( $newFTPInfos)
	{
	   if ( empty( $newFTPInfos)) {
	      return;
	   }
		$config =& JFactory::getConfig();

   	$orig_ftp_enable = $config->getValue('config.ftp_enable');
		$orig_ftp_root   = $config->getValue('config.ftp_root');
		
		
		$config->setValue('config.ftp_enable', $newFTPInfos['toFTP_enable']);
		$config->setValue('config.ftp_host',   $newFTPInfos['toFTP_host']);
		$config->setValue('config.ftp_port',   $newFTPInfos['toFTP_port']);
		$config->setValue('config.ftp_user',   $newFTPInfos['toFTP_user']);
		$config->setValue('config.ftp_pass',   $newFTPInfos['toFTP_psw']);
		
		// $config->setValue('config.ftp_root',   $newFTPInfos['toFTP_rootpath']);

		// Force to recompute the FTP parameters to replace the current cache info
		jimport('joomla.client.helper');
		$ftpOptions = JClientHelper::getCredentials('ftp', true);
		
		$ftp = &Jms2WinFTP::getInstance(
			$ftpOptions['host'], $ftpOptions['port'], null,
			$ftpOptions['user'], $ftpOptions['pass'],
			$newFTPInfos['toFTP_dir'],
			$newFTPInfos['toFTP_rootpath'],
			$orig_ftp_enable, 
			$orig_ftp_root
		);
	}
	
   function _restoreFTPInfos( $newFTPInfos)
	{
	   if ( empty( $newFTPInfos)) {
	      return;
	   }
		// Force to recompute the FTP parameters to replace the current cache info
		jimport('joomla.client.helper');
		$ftpOptions = JClientHelper::getCredentials('ftp');
		$ftp = &JFTP::getInstance(
			$ftpOptions['host'], $ftpOptions['port'], null,
			$ftpOptions['user'], $ftpOptions['pass']
		);
	   if ( is_a( $instance, 'Jms2WinFTP')) {
	      $ftp->restoreOriginalInstance();
	   }

		// Force to recompute the FTP parameters to replace the current cache info
		$config =& JFactory::getConfig();
		$config->setValue('config.ftp_enable', $newFTPInfos['toFTP_enable']);
		$config->setValue('config.ftp_host',   $newFTPInfos['toFTP_host']);
		$config->setValue('config.ftp_port',   $newFTPInfos['toFTP_port']);
		$config->setValue('config.ftp_user',   $newFTPInfos['toFTP_user']);
		$config->setValue('config.ftp_pass',   $newFTPInfos['toFTP_psw']);
		$ftpOptions = JClientHelper::getCredentials('ftp', true);
	}

 
   //------------ deploySite ---------------
   /**
    * @brief This create a directory 'id' into 'multisites' directory and deploy the redirection files.
    */
   function deploySite( &$enteredvalues, $front_end = false)
	{
	   // If Can Not create a front-end website
	   if ( !$this->canCreateSlave($enteredvalues, $front_end)) {
	      $err = $this->getError();
	      if ( empty( $err)){
   	      $this->setError( JText::_( 'SITE_DEPLOY_CANNOT_CREATE'));
	      }
	      return false;
	   }

	   // Check that Multisites directory exists
	   if ( !JFolder::exists( JPATH_MULTISITES)) {
	      // Try to create the directory and check it really exists. Otherwise, report an error
   	   if ( ! JFolder::create( JPATH_MULTISITES, MULTISITES_DIR_RIGHTS)
   	     || ! JFolder::exists( JPATH_MULTISITES))
   	   {
   	      $this->setError( JText::sprintf( 'SITE_MSDIR_NOTFOUND', JPATH_MULTISITES));
   	      return false;
   	   }
   	   // Ensure that directory has the rights request (for unknow reason, mkdir may alway give 0755)
   	   @chmod( JPATH_MULTISITES, MULTISITES_DIR_RIGHTS);
	   }


      $template = MultisitesModelManage::_getTemplate( $enteredvalues);
      $siteInfo = MultisitesModelManage::_getSiteInfo( $enteredvalues);
      $id       = MultisitesModelManage::getSiteID( $enteredvalues);
      if ( $id === false) {
	      $this->setError( JText::_( 'SITE_DEPLOY_SITE_ID_ERR'));
         return false;
      }
	   $enteredvalues['id'] = $id;
	   if ( !empty( $enteredvalues['force_flat_dir']) && $enteredvalues['force_flat_dir']) { $force_flat_dir = true; }
	   else                                                                                { $force_flat_dir = false; }
	   
	   if ( $force_flat_dir) {}
	   // If letter tree,
	   else {
	      // In case of an update on a flat structure, cross-check that there is no slave site is not present using the flat structure
	      if ( Site::is_Site( $id)) {
	         // If this is an update on a flat structure then force the flat format
	         $force_flat_dir = true;
	      }
	   }
	   
	   $site_dir = &$this->getSiteDir( $id, $force_flat_dir);
	   
	   // If there is a owner to create the website and there is a maximum of websites that is specified in a template
	   if ( !empty( $enteredvalues['owner_id']) 
	     && !empty( $template) && !empty( $template->maxsite)) {
	      $count = $this->_countWebSites( $enteredvalues['owner_id']);
	      if ( $count >= $template->maxsite) {
   	      $this->setError( JText::sprintf( 'SITE_MAXSITE_REACHED', $template->maxsite, $count));
   	      return false;
	      }
	   }

	   // ---- if domain is missing, try to compute one based on template ----
	   // If the domain is empty and there is a template with a domain defined,
	   if ( empty( $enteredvalues['domains']) && !empty( $template) && !empty( $template->toDomains)) {
	      $enteredvalues['domains'] = array();
	      // Try to compute the domain based on the template
	      foreach( $template->toDomains as $domain) {
	         // If a site_prefix is used in the template and there is no site_prefix value, skip the domain
	         if ( empty( $siteInfo['site_prefix'])
	           && strstr( $domain, '{site_prefix}') !== false) {
	            continue;
	         }
	         // If a site_alias is used in the template and there is no site_alias value, skip the domain
	         if ( empty( $siteInfo['site_alias'])
	           && strstr( $domain, '{site_alias}') !== false) {
	            continue;
	         }
   	      $str = MultisitesDatabase::evalStr( $domain, $id, $site_dir, null, $siteInfo);
   	      $enteredvalues['domains'][] = $str;
	      }
	   }
	   if ( empty($enteredvalues['domains'])) {
	      $this->setError( JText::_( 'SITE_DOMAIN_MISSING'));
	      return false;
	   }
	   
	   // Evaluate the domains to use the keyword and give a list of domain that will be indexed
	   $enteredvalues['indexDomains'] = array();
	   foreach( $enteredvalues['domains'] as $domain) {
	      $str = MultisitesDatabase::evalStr( $domain, $id, $site_dir, null, $siteInfo);
	      $enteredvalues['indexDomains'][] = str_replace( "\\", '/', $str);
	   }

	   // ---- if the expiration date is not present, try to compute one based validity present in the template ----
      $month_str = array( 0, 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
	   if ( $front_end
	     && empty( $enteredvalues['expiration']) && !empty($template->validity) ) {
	      $validity = $template->validity;
	      // Years
	      if ( $template->validity_unit == 'years') {
	         $expiration = strtotime("+$validity years");
	      }
	      // Months
	      else if ( $template->validity_unit == 'months') {
	         $expiration = strtotime("+$validity months");
	      }
	      // Days
	      else {
	         $expiration = strtotime("+$validity days");
	      }
         if ( !empty( $expiration)) {
            // Force writing date in American format to avoid problem when used on french environment or other.
            // strftime routine only accept american format
            $expiration_str = strftime( '%d-%m-%Y', $expiration);
            $expiration_arr = explode( '-', $expiration_str);
            $expiration_arr[1] = $month_str[ (int)$expiration_arr[1]];
            $expiration_str = implode( '-', $expiration_arr);
            $enteredvalues['expiration'] = $expiration_str;
         }
	   }
	   // Check the expiration date format
	   if ( !empty( $enteredvalues['expiration'])) {
         $expiration_arr = explode( '-', $enteredvalues['expiration']);
         // If format YYYY-MM-DD
         if ( strlen( $expiration_arr[0]) == 4) {
            // Convert in DD-MMM-YYYY
            $expiration_str = $expiration_arr[2]
                            . '-'
                            . $month_str[(int)$expiration_arr[1]]
                            . '-'
                            . $expiration_arr[0];
            $enteredvalues['expiration'] = $expiration_str;
         }
	   }

	   // If an expiration date is present and there is an expiration url in the template
	   if ( !empty( $enteredvalues['expiration']) && !empty($template->expireurl)) {
         $enteredvalues['expireurl'] = MultisitesDatabase::evalStr( $template->expireurl, $id, $site_dir, null, $siteInfo);
	   }

	   if ( empty( $enteredvalues['shareDB']) && !empty($template->shareDB) ) {
	      $enteredvalues['shareDB'] = $template->shareDB;
	   }

	   // ---- if the "new" DB parameters are not present, try to compute them based on template parameters ----
	   if ( empty( $enteredvalues['toDBHost']) && !empty($template->toDBHost) ) {
	      $enteredvalues['toDBHost'] = MultisitesDatabase::evalStr( $template->toDBHost, $id, $site_dir, null, $siteInfo);
	   }
	   if ( empty( $enteredvalues['toDBName']) && !empty($template->toDBName) ) {
	      $enteredvalues['toDBName'] = MultisitesDatabase::evalStr( $template->toDBName, $id, $site_dir, null, $siteInfo);
	   }
	   if ( empty( $enteredvalues['toDBUser']) && !empty($template->toDBUser) ) {
	      $enteredvalues['toDBUser'] = MultisitesDatabase::evalStr( $template->toDBUser, $id, $site_dir, null, $siteInfo);
	   }
	   if ( empty( $enteredvalues['toDBPsw']) && !empty($template->toDBPsw) ) {
	      $enteredvalues['toDBPsw'] = MultisitesDatabase::evalStr( $template->toDBPsw, $id, $site_dir, null, $siteInfo);
	   }

	   // ---- if the DB toPrefix is not present, try to compute one based on template toPrefix ----
	   if ( empty( $enteredvalues['toPrefix']) && !empty($template->toPrefix) ) {
	      // Try to compute the ID based on the template
	      $enteredvalues['toPrefix'] = MultisitesDatabase::evalStr( $template->toPrefix, $id, $site_dir, null, $siteInfo);
	   }
      // In case of the front-end, the DB toPrefix is mandatory
      if ( $front_end && empty( $enteredvalues['toPrefix'])) {
	      $this->setError( JText::_( 'SITE_TABLE_PREFIX_MANDATORY'));
	      return false;
      }


	   // ---- if the "new" FTP parameters are not present (not 0 or 1) then use the template default informations
	   $ftpInfos = array();
	   if ( isset( $enteredvalues['toFTP_enable']) 
	     && ($enteredvalues['toFTP_enable']=='0' || $enteredvalues['toFTP_enable']=='1'))
	   {
	      $ftpInfos['toFTP_enable'] = $enteredvalues['toFTP_enable'];

   	   if ( !empty( $enteredvalues['toFTP_host'])) {
   	      // resolve expression when present
   	      $ftpInfos['toFTP_host'] = MultisitesDatabase::evalStr( $enteredvalues['toFTP_host'], $id, $site_dir, null, $siteInfo);
   	   }
   	   else if ( !empty($template->toFTP_host) ) {
   	      // Try to compute the FTP host based on the template
   	      $ftpInfos['toFTP_host'] = MultisitesDatabase::evalStr( $template->toFTP_host, $id, $site_dir, null, $siteInfo);
   	   }
   	   if ( !empty( $enteredvalues['toFTP_port'])) {
   	      // resolve expression when present
   	      $ftpInfos['toFTP_port'] = MultisitesDatabase::evalStr( $enteredvalues['toFTP_port'], $id, $site_dir, null, $siteInfo);
   	   }
   	   else if ( !empty($template->toFTP_port) ) {
   	      // Try to compute the FTP Port based on the template
   	      $ftpInfos['toFTP_port'] = MultisitesDatabase::evalStr( $template->toFTP_port, $id, $site_dir, null, $siteInfo);
   	   }
   	   if ( !empty( $enteredvalues['toFTP_user'])) {
   	      // resolve expression when present
   	      $ftpInfos['toFTP_user'] = MultisitesDatabase::evalStr( $enteredvalues['toFTP_user'], $id, $site_dir, null, $siteInfo);
   	   }
   	   else if ( !empty($template->toFTP_user) ) {
   	      // Try to compute the FTP user based on the template
   	      $ftpInfos['toFTP_user'] = MultisitesDatabase::evalStr( $template->toFTP_user, $id, $site_dir, null, $siteInfo);
   	   }
   	   if ( !empty( $enteredvalues['toFTP_psw'])) {
   	      // resolve expression when present
   	      $ftpInfos['toFTP_psw'] = MultisitesDatabase::evalStr( $enteredvalues['toFTP_psw'], $id, $site_dir, null, $siteInfo);
   	   }
   	   else if ( !empty($template->toFTP_psw) ) {
   	      // Try to compute the FTP password based on the template
   	      $ftpInfos['toFTP_psw'] = MultisitesDatabase::evalStr( $template->toFTP_psw, $id, $site_dir, null, $siteInfo);
   	   }
   	   if ( !empty( $enteredvalues['toFTP_rootpath'])) {
   	      // resolve expression when present
   	      $ftpInfos['toFTP_rootpath'] = MultisitesDatabase::evalStr( $enteredvalues['toFTP_rootpath'], $id, $site_dir, null, $siteInfo);
   	   }
   	   else if ( !empty($template->toFTP_rootpath) ) {
   	      // Try to compute the FTP root path based on the template
   	      $ftpInfos['toFTP_rootpath'] = MultisitesDatabase::evalStr( $template->toFTP_rootpath, $id, $site_dir, null, $siteInfo);
   	   }
	   }
	   // ---- If FTP Default the use the Template FTP parameters when present (Template FTP Enabled Yes/No) 
	   else if ( isset($template->toFTP_enable) && ($template->toFTP_enable=='0' || $template->toFTP_enable=='1') ) {
	      $ftpInfos['toFTP_enable'] = MultisitesDatabase::evalStr( $template->toFTP_enable, $id, $site_dir, null, $siteInfo);

   	   if ( !empty($template->toFTP_host) ) {
   	      // Try to compute the FTP host based on the template
   	      $ftpInfos['toFTP_host'] = MultisitesDatabase::evalStr( $template->toFTP_host, $id, $site_dir, null, $siteInfo);
   	   }
   	   if ( !empty($template->toFTP_port) ) {
   	      // Try to compute the FTP Port based on the template
   	      $ftpInfos['toFTP_port'] = MultisitesDatabase::evalStr( $template->toFTP_port, $id, $site_dir, null, $siteInfo);
   	   }
   	   if ( !empty($template->toFTP_user) ) {
   	      // Try to compute the FTP user based on the template
   	      $ftpInfos['toFTP_user'] = MultisitesDatabase::evalStr( $template->toFTP_user, $id, $site_dir, null, $siteInfo);
   	   }
   	   if ( !empty($template->toFTP_psw) ) {
   	      // Try to compute the FTP password based on the template
   	      $ftpInfos['toFTP_psw'] = MultisitesDatabase::evalStr( $template->toFTP_psw, $id, $site_dir, null, $siteInfo);
   	   }
   	   if ( !empty($template->toFTP_rootpath) ) {
   	      // Try to compute the FTP root path based on the template
   	      $ftpInfos['toFTP_rootpath'] = MultisitesDatabase::evalStr( $template->toFTP_rootpath, $id, $site_dir, null, $siteInfo);
   	   }
	   }
   	// ---- Else FTP enable = default and Template FTP enable = default
	   else {}
	   


	   // ---- If the status is not filled, put Pending value ----
	   if ( empty( $enteredvalues['status'])) {
	      $enteredvalues['status'] = 'Pending';
	   }

	   // When creating a new site, Check if the Site ID is already created
	   if ( !empty( $enteredvalues['isnew']) && $enteredvalues['isnew'] && Site::is_Site( '', $site_dir)) {
	      $this->setError( JText::sprintf( 'SITE_ID_EXISTS', $site_dir));
	      return false;
	   }

	   // When the site id does not exist, Create its directory
	   if ( !JFolder::exists( $site_dir) && !JFolder::create( $site_dir, MULTISITES_DIR_RIGHTS)) {
	      $this->setError( JText::sprintf( 'SITE_CREATE_ID_ERR', $site_dir));
	      return false;
	   }
	   // Ensure that directory has the rights request (for unknow reason, mkdir may always give 0755)
	   @chmod( $site_dir, MULTISITES_DIR_RIGHTS);

	   // Compute the billable front-end slave site id
	   $website_id = '';
	   if ( $front_end) {
	      $website_id = $this->_getWebsiteID( $enteredvalues);
	      if ( $website_id === false) {
   	      $this->setError( JText::_( 'SITE_DEPLOY_GETWEBSITEID_ERR'));
	         return false;
	      }
	      $enteredvalues['website_id']  = $website_id;
	   }


	   $newDBInfo = array();
	   if ( !empty( $enteredvalues['status']))         { $newDBInfo['status']        = $enteredvalues['status']; }
	   if ( !empty( $enteredvalues['payment_ref']))    { $newDBInfo['payment_ref']   = $enteredvalues['payment_ref']; }
	   if ( !empty( $enteredvalues['expiration']))     { $newDBInfo['expiration']    = $enteredvalues['expiration']; }
	   if ( !empty( $enteredvalues['expireurl']))      { $newDBInfo['expireurl']     = $enteredvalues['expireurl']; }
	   if ( !empty( $enteredvalues['owner_id']))       { $newDBInfo['owner_id']      = $enteredvalues['owner_id']; }
	   if ( !empty( $enteredvalues['fromTemplateID'])) { $newDBInfo['fromTemplateID']= $enteredvalues['fromTemplateID']; }
	   if ( !empty( $enteredvalues['site_prefix']))    { $newDBInfo['site_prefix']   = $enteredvalues['site_prefix']; }
	   if ( !empty( $enteredvalues['site_alias']))     { $newDBInfo['site_alias']    = $enteredvalues['site_alias']; }
	   if ( !empty( $enteredvalues['siteComment']))    { $newDBInfo['siteComment']   = $enteredvalues['siteComment']; }
	   if ( !empty( $enteredvalues['toSiteName']))     { $newDBInfo['toSiteName']    = $enteredvalues['toSiteName']; }
	   if ( !empty( $enteredvalues['shareDB']))        { $newDBInfo['shareDB']       = $enteredvalues['shareDB']; }
	   if ( !empty( $enteredvalues['toDBHost']))       { $newDBInfo['toDBHost']      = $enteredvalues['toDBHost']; }
	   if ( !empty( $enteredvalues['toDBName']))       { $newDBInfo['toDBName']      = $enteredvalues['toDBName']; }
	   if ( !empty( $enteredvalues['toDBUser']))       { $newDBInfo['toDBUser']      = $enteredvalues['toDBUser']; }
	   if ( !empty( $enteredvalues['toDBPsw']))        { $newDBInfo['toDBPsw']       = $enteredvalues['toDBPsw']; }
	   if ( !empty( $enteredvalues['toPrefix']))       { $newDBInfo['toPrefix']      = $enteredvalues['toPrefix']; }
	   if ( !empty( $enteredvalues['website_id']))     { $newDBInfo['website_id']    = $enteredvalues['website_id']; }
// Don't write the password in clear	   if ( !empty( $enteredvalues['newAdminPsw']))    { $newDBInfo['newAdminPsw']   = $enteredvalues['newAdminPsw']; }

	   if ( !empty( $enteredvalues['media_dir']))      { $newDBInfo['media_dir']     = $enteredvalues['media_dir']; }
	   if ( !empty( $enteredvalues['images_dir']))     { $newDBInfo['images_dir']    = $enteredvalues['images_dir']; }
	   if ( !empty( $enteredvalues['templates_dir']))  { $newDBInfo['templates_dir'] = $enteredvalues['templates_dir']; }
	   if ( !empty( $enteredvalues['cache_dir']))      { $newDBInfo['cache_dir']     = $enteredvalues['cache_dir']; }
	   if ( !empty( $enteredvalues['tmp_dir']))        { $newDBInfo['tmp_dir']       = $enteredvalues['tmp_dir']; }

	   // --- FTP ---
	   if ( isset( $ftpInfos['toFTP_enable']) && $ftpInfos['toFTP_enable']!='')
	                                                   { $newDBInfo['toFTP_enable']  = $ftpInfos['toFTP_enable']; }
	   if ( !empty( $ftpInfos['toFTP_host']))          { $newDBInfo['toFTP_host']    = $ftpInfos['toFTP_host']; }
	   if ( !empty( $ftpInfos['toFTP_port']))          { $newDBInfo['toFTP_port']    = $ftpInfos['toFTP_port']; }
	   if ( !empty( $ftpInfos['toFTP_user']))          { $newDBInfo['toFTP_user']    = $ftpInfos['toFTP_user']; }
	   if ( !empty( $ftpInfos['toFTP_psw']))           { $newDBInfo['toFTP_psw']     = $ftpInfos['toFTP_psw']; }
	   if ( !empty( $ftpInfos['toFTP_rootpath']))      { $newDBInfo['toFTP_rootpath']= $ftpInfos['toFTP_rootpath']; }

	   $config_dirs = $this->_calcConfigDirs( $enteredvalues, $id, $site_dir, $newDBInfo, $template);
	   if ( $config_dirs === false) {
	      $this->setError( JText::_( 'SITE_DEPLOY_CONFIG_DIR_ERR'));
	      return false;
	   }

      // If an alias link must be created on the deploy_dir.
      if ( !empty( $config_dirs['deploy_dir'])
        && !empty( $config_dirs['alias_link'])
        && MultisitesHelper::isSymbolicLinks()
         )
      {
         // try to get the current alias_link value when the site already exists
         $cur_alias_link = $this->getAliasLink( $id);

	      // If a link already exists
   	   if ( is_link( $config_dirs['alias_link'])) {
   	      // read its current value
   	      $cur_path = readlink( $config_dirs['alias_link']);
   	      if ( $cur_path === false) {
   	      }
   	      // If it does not match, report the error
   	      else if ( $cur_path != $config_dirs['deploy_dir']) {
               if ( !empty( $ftproot_perms)) { Jms2WinPath::chmod( $ftpInfos['toFTP_rootpath'], $ftproot_perms); }
      	      $this->_setNewFTPInfos( $sav_FTPInfos);
      	      $this->setError( JText::_( 'SITE_DEPLOY_ALIAS_CREATION_ERROR'));
      	      return false;
   	      }
   	   }
   	}
         
	   // Save the list of domains into a special configuration files
      $this->writeSite( $site_dir, 
                        $enteredvalues['domains'], $enteredvalues['indexDomains'], 
                        $newDBInfo, $config_dirs);

      // If the slave site needs to be deployed using FTP
      $sav_FTPInfos = array();
      if ( $this->_isDeployedFTPEnabled( $ftpInfos)) {
         // Save the current FTP infos present in the configuration
         $this->_saveFTPInfos( $sav_FTPInfos);
         // Replace the current FTP infos with "slave site" FTP infos
         if ( !empty( $config_dirs['deploy_dir'])) {
            $ftpInfos['toFTP_dir'] = $config_dirs['deploy_dir'];
         }
         else {
            $ftpInfos['toFTP_dir'] = $site_dir;
         }
         
         $this->_setNewFTPInfos( $ftpInfos);
         
         // Now that jms conf is written, we can save the "resolved" value in the "enteredvalues".
         foreach( $ftpInfos as $key => $value) {
            $enteredvalues[$key] = $value;
         }
         
			// Get current FTP directory permissions
			$ftp = &Jms2WinFTP::getInstance(
				$ftpInfos['toFTP_host'], $ftpInfos['toFTP_port'], null,
				$ftpInfos['toFTP_user'], $ftpInfos['toFTP_psw']
			);

         $ftproot_perms = $ftp->fileperms( $ftpInfos['toFTP_rootpath']);
         
         // Try using FTP SITE CHMOD 777 <directory>
         if ( !empty( $ftproot_perms)) { $ftp->chmod( $ftpInfos['toFTP_rootpath'], '0777', false); }
      }
      
      // If a deploy_dir is present and was resolved.
      if ( !empty( $config_dirs['deploy_dir'])) {
         $deploy_dir = $config_dirs['deploy_dir'];
         // If the $deploy_dir does NOT exists but we can create it
         if ( !Jms2WinFolder::exists( $deploy_dir)
           && ( !empty( $enteredvalues['deploy_create']) || !empty($template->deploy_create))
            ) {
               Jms2WinFolder::create( $deploy_dir);
         }
         // Check that $deploy_dir exists
         if ( !Jms2WinFolder::exists( $deploy_dir) ) {
            if ( !empty( $ftproot_perms)) { Jms2WinPath::chmod( $ftpInfos['toFTP_rootpath'], $ftproot_perms); }
   	      $this->_setNewFTPInfos( $sav_FTPInfos);
   	      $this->setError( JText::sprintf( 'SITE_DEPLOY_DEPLOY_DIR_NOTFOUND', $deploy_dir));
   	      return false;
         }
         if ( strtolower( rtrim( JPath::clean( $deploy_dir), '/')) == strtolower( rtrim( JPath::clean( JPATH_ROOT), '/'))) {
            if ( !empty( $ftproot_perms)) { Jms2WinPath::chmod( $ftpInfos['toFTP_rootpath'], $ftproot_perms); }
   	      $this->_setNewFTPInfos( $sav_FTPInfos);
   	      $this->setError( JText::_( 'SITE_DEPLOY_DEPLOY_DIR_ROOT'));
   	      return false;
         }
      }

      // If an alias link must be created on the deploy_dir.
      if ( !empty( $config_dirs['alias_link'])) {
         // Check that deploy directory exists
         if ( !empty( $deploy_dir) && Jms2WinFolder::exists( $deploy_dir)) {
            if ( !$this->symlink( $deploy_dir, $config_dirs['alias_link'])) {
               if ( !empty( $ftproot_perms)) { Jms2WinPath::chmod( $ftpInfos['toFTP_rootpath'], $ftproot_perms); }
      	      $this->_setNewFTPInfos( $sav_FTPInfos);
      	      $this->setError( JText::_( 'SITE_DEPLOY_ALIAS_CREATION_ERROR2'));
      	      return false;
            }
         }
         
         // If the new alias link is different of the previous one (current one)
         if ( !empty( $cur_alias_link) && $config_dirs['alias_link'] != $cur_alias_link) {
   		   // try delete the previous link that will be replaced by the new one
   			if (@unlink( $cur_alias_link)) {}
         }
      }

      // If there is a definition to create a new DB based on a template
      if ( !empty( $newDBInfo)
        && (!empty( $newDBInfo['fromTemplateID']) || !empty( $newDBInfo['fromSiteID']))
        && !empty( $newDBInfo['toPrefix'])
         ) {
         if ( !empty( $deploy_dir)) {
            $error = $this->duplicateDBandConfig( $enteredvalues, $newDBInfo, $id, $site_dir, $deploy_dir);
         }
         else {
            $error = $this->duplicateDBandConfig( $enteredvalues, $newDBInfo, $id, $site_dir);
         }
         if ( !empty( $error)) {
            if ( !empty( $ftproot_perms)) { Jms2WinPath::chmod( $ftpInfos['toFTP_rootpath'], $ftproot_perms); }
   	      $this->_setNewFTPInfos( $sav_FTPInfos);
	         $this->setError( implode( '<br/>', $error));
   			return false;
         }
      }

	   // For Unix platform (having possibility to use symnolic links)
	   // deploy the wrapper files that will improve security as 'installation' directory in slave site
	   // can be removed after the setup;
      if ( !MultisitesHelper::isSymbolicLinks()) {
         $deploy_dir = null;
      }
		// For unix platform, also create symbolic links
	   else
	   {
	      // If a specific deploy dir is NOT specified
	      if ( !isset( $deploy_dir)) {
	         // Use the multi site directory to deploy the symbolic links
	         $deploy_dir        = JPath::clean( $site_dir);
	         $this->_deploy_dir = $deploy_dir; // Save the result into the cache and that will be displayed to the user.
	      }

   	   // If the deploy_dir does not exists,
   	   if ( !Jms2WinFolder::exists( $deploy_dir)) {
   	      // Create the deployment directory
   	      if ( !Jms2WinFolder::create( $deploy_dir)) {
   	         // Otherwise, return an error
            if ( !empty( $ftproot_perms)) { Jms2WinPath::chmod( $ftpInfos['toFTP_rootpath'], $ftproot_perms); }
      	      $this->_setNewFTPInfos( $sav_FTPInfos);
   	         $this->setError( JText::sprintf( 'SITE_DEPLOY_ERR', $deploy_dir));
      			return false;
   	      }
   	   }
	   }
	   // For all platform, try to deploy the directory structure that can contain Symbolic Links, copy, create, unzip, ...
		$errors = $this->_deployLinks( $config_dirs, $id, $site_dir, $deploy_dir, $newDBInfo, $enteredvalues['domains'], $enteredvalues['indexDomains']);

      // Ensure that all directories have a 'index.html'
      $this->_checkEmptyFolders( $site_dir);

      if ( !empty( $ftproot_perms)) { Jms2WinPath::chmod( $ftpInfos['toFTP_rootpath'], $ftproot_perms); }
      // If we had errors during the deployment
      if ( !empty( $errors)) {
	      $this->_setNewFTPInfos( $sav_FTPInfos);
         $this->setError( implode( '</li><li>', $errors));
         return false;
      }
      
      $this->_setNewFTPInfos( $sav_FTPInfos);
      $this->setError( JText::_( 'Success' ));
      return true;
	}

} // End class
