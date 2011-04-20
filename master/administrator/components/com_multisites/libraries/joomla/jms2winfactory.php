<?php
/**
 * @file       jms2winfactory.php
 * @brief      Jommla Multi Sites factory to give access using the Master configuration file
 *
 * @version    1.2.36
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
 * - V1.1.0 01-OCT-2008: Add interface on slave DB
 * - V1.1.3 02-DEC-2008: Add a cleanup of the Site_ID when it is used to load the configuration files.
 *                       This avoid to create a class like JConfig_mydomain.com
 *                       Some users have used special characters in site_id that could create a syntax error.
 * - V1.1.15 03-FEB-2009: When a slave site is deployed in a specific directory, the JPATH_ROOT give
 *                      the slave site directory and not the master website directory.
 *                      Therefore, to get the master configuration file, if the MASTER_ROOT_PATH is defined,
 *                      used it instead of the current ROOT_PATH.
 *                      The problem was identified with the Articles Sharing that was unable to return the article
 *                      from a master website when the slave site was deployed in a specific directory.
 * - V1.2.0 18-MAY-2009: Add possibility to open temporary DB connection to avoid reach the maximum connection
 *                       when checking if the websites are synchronized.
 *                       Also change the way the master configuration path file is computed. 
 *                       In previous version it may be wrong when administrating JMS from a slave site.
 * - V1.2.0 RC3 05-JUL-2009: Fix several warning relative to deprecated syntax in PHP 5.x
 * - V1.2.6 18-SEP-2009: Use ':master_db:' to check if views is possible when the site_id is empty.
 * - V1.2.17 05-JAN-2010: Add checks when reading the configuration.php file to report error message
 *                        when it is not possible to process it correctly.
 *                        Also add a "?>" to configuration.php that does not have it at the end.
 * - V1.2.20 03-FEB-2010: Add the master configuration path in case of backup/restore PHP code into another directory
 * - V1.2.26 21-MAR-2010: Add declaration of JFiles in case where the factory is called from a "foreign" plugin.
 * - V1.2.29 21-MAR-2010: Add letter tree directory structure processing.
 * - V1.2.34 17-JUL-2010: Call MultisitesDatabase instances to allow modifying the protected table_prefix field.
 *                        Fix a temporary connection DB connection that could return an error when re-using
 *                        the database connection from another table prefix that could have an error.
 *                        Now when re-using an existing DB connection, reset the error code.
 * - V1.2.35 27-JUL-2010: Use a new setErrorInfo() function to set the DB information as the _errorNum and _errorMsg
 *                        are not protected in Joomla 1.6.
 * - V1.2.36 03-SEP-2010: Add possibility to retreive the domain name based on a site ID.
 *
 * ================== Joomla original source ================
 * This Jms2WinFactory is inspired from JFactory code.
 *
 * @version		$Id: factory.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
 
defined('_JEXEC') or die( 'Restricted access' );

if ( !defined( 'JPATH_MULTISITES')) {
   define( 'JPATH_MULTISITES', JPATH_ROOT.DS.'multisites');
}

jimport( 'joomla.filesystem.file');

@include_once( JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_multisites' .DIRECTORY_SEPARATOR. 'classes' .DIRECTORY_SEPARATOR. 'lettertree.php');

// ===========================================================
//             Jms2WinFactory class
// ===========================================================
class Jms2WinFactory
{

   // ------------- _createMasterConfig ----------------
   /**
    * @brief This routine create a JConfig_xxxx class in memory where xxxx is a site ID.
    *
    * In fact, this routine, read the configuration.php file and rename the JConfig class to add
    * the site id suffix.
    * When the new class name is defined, it evaluate the configuration.php file with the new class name
    * and it register it into 'config' namespace.
    */
	function &_createMasterConfig( $site_id, $file, $type = 'PHP')
	{
		jimport('joomla.registry.registry');
		jimport('joomla.filter.filterinput');
		
		// If get config for the master website,
		if ( empty( $site_id)) {
			$MULTISITES_FORCEMASTER = true;
		}
		
		// As JConfig class already exist, rename this class to load the configuration
		$new_config_class = 'JConfig_' . JFilterInput::clean( $site_id, 'alnum');
		
		if ( class_exists( $new_config_class)) {
//		   debug_print_backtrace();
		}
		else {
   		// Read the configuration as data script
   		jimport('joomla.filesystem.file');
   		$data = JFile::read($file);
   		if ( $data === false) {
   		   jexit( "jms2winfactory.php: Unable to read configuration file [$file]");
   		}
   		if ( empty( $data)) {
   		   jexit( "jms2winfactory.php: Empty configuration file content [$file]");
   		}
   		// Rename the class name and evaluate the file
   		$script = str_replace( 'JConfig', $new_config_class, $data);
   		if ( empty( $script)) {
   		   jexit( "jms2winfactory.php: Empty configuration SCRIPT for file [$file]");
   		}
   		else {
   		   // If the configuration.php is not terminated by a "? >" (case of fantastico)
            $p1 = strpos( $script, '?'.'>');
            if ( $p1 === false) {
               $script .= "\n"
                       .  '?' . '>' 
                       .  "\n";
            }
   		}
   		eval('?>' . $script . '<?php ');
   		
   		// Check if the class is present.
   		// Otherwise abort here and display the path of the configuration.php file that may have the error.
   		if ( !class_exists( $new_config_class)) {
            // If this is called from back-end (JPATH_ROOT != JPATH_BASE)
            if ( defined( 'JPATH_ROOT') && defined( 'JPATH_BASE')
              && JPATH_ROOT != JPATH_BASE
               )
            {
               // If back-end, also write the data and script info
      		   jexit( "jms2winfactory.php: Class [$new_config_class] not found in configuration file [$file]<br />Data = [" . htmlspecialchars($data) . "]<br />Script = [" . htmlspecialchars( $script) . "]");
            }
            else {
      		   jexit( "jms2winfactory.php: Class [$new_config_class] not found in configuration file [$file]");
            }
   		}
		}

		
		// Create the JConfig_<site_id> object
		$config = new $new_config_class();

		// Create the registry with a default namespace of config
		$registry = new JRegistry('config');

		// Load the configuration values into the registry
		$registry->loadObject($config);

		return $registry;
	}
   
   // ------------- getMasterConfig ----------------
   /**
    * @brief This is a duplication of JFactory::getConfig() where a force loading MASTER 'configuration.php' is requested
    */
	function &getMasterConfig($file = null, $type = 'PHP')
	{
		static $instance;

		if (!is_object($instance))
		{
			if ($file === null) {
               // try to compute the path based on this file
               $parts = explode( DS, dirname( __FILE__));
               array_pop( $parts );
               array_pop( $parts );
               array_pop( $parts );
               array_pop( $parts );
               array_pop( $parts );
               $file = implode( DS, $parts )
                     . DS. 'configuration.php';
               
			   // Path to the Master configuration file
            if ( defined( 'MULTISITES_MASTER_ROOT_PATH')) {
   				$file = MULTISITES_MASTER_ROOT_PATH .DS. 'configuration.php';
   				// In case where the path is changed, it may happen that the file is present in the JPATH_ROOT (the default).
      		   if ( !JFile::exists( $file)) {
      				$file = JPATH_ROOT .DS. 'configuration.php';
      		   }
            }
            else {
               
   				$file = JPATH_ROOT .DS. 'configuration.php';
            }
			}
			
			// Disable the possible MultiSites routing service present in the configuration.php file
			$instance =& Jms2WinFactory::_createMasterConfig( '', $file, $type);
		}

		return $instance;
	}


   // ------------- getSlaveConfig ----------------
   /**
    * @brief This is a duplication of JFactory::getConfig() where the SLAVE 'configuration.php' is used.
    * If a deploy directory is defined for the slave site, try to use it in aim to retreive the configuration file.
    * Otherwise, use the multisites/site_id directory to retreive the configuration file
    */
	function &getSlaveConfig( $site_id, $type = 'PHP')
	{
		static $instances;

		if (!isset( $instances )) {
			$instances = array();
		}

		if ( empty( $instances[$site_id]))
		{
         jimport('joomla.filesystem.file');
         // If the slave site has no multisites config file
         $site_dir = JPATH_MULTISITES .DS. $site_id ;
		   $filename = $site_dir .DS. 'config_multisites.php';
		   if ( !JFile::exists( $filename)) {
		      // Retry with 'letter tree' directory structure
            if ( class_exists( 'MultisitesLetterTree')) {
               // Try to compute a path using the letter tree
               $lettertree_dir = MultisitesLetterTree::getLetterTreeDir( $site_id);
               if( !empty( $lettertree_dir)) {
                  $site_dir = JPATH_MULTISITES.DIRECTORY_SEPARATOR.$lettertree_dir;
                  $filename = $site_dir.DIRECTORY_SEPARATOR.'config_multisites.php';
                  if ( !file_exists( $filename)) {
         		      $instances[$site_id] = null;
         		      return $instances[$site_id];
                  }
               }
            }
		   }
		   // Try to find a deploy dir in the multisites configuration file
		   include( $filename);
         // If a deploy_dir is present and was resolved.
         if ( empty( $deploy_dir) && !empty( $config_dirs['deploy_dir'])) {
            $deploy_dir = $config_dirs['deploy_dir'];
         }
		   if ( !empty( $deploy_dir)) {
		      // Try to find a "configuration.php" file into the deployed directory
   		   $filename = $deploy_dir .DS. 'configuration.php';
   		   // If no configuration in deploy directory
   		   if ( !JFile::exists( $filename)) {
   		      // Use the multisites/site_id directory
      		   $filename = $site_dir .DS. 'configuration.php';
   		   }
		   }
		   else {
   		   $filename = $site_dir .DS. 'configuration.php';
		   }
		   // Check that we have found a configuration file
		   if ( !JFile::exists( $filename)) {
		      $instances[$site_id] = null;
		      return $instances[$site_id];
		   }
			
			// Load the configuration.php file
			$instances[$site_id] =& Jms2WinFactory::_createMasterConfig( $site_id, $filename, $type);
		}

		return $instances[$site_id];
	}

   // ------------- getMultiSitesConfig ----------------
   /**
    * @brief Return the master DB configuration or the slave site configuration depending on the site_id.
    *
    * @param site_id When NULL or ":master_db:", return the master website configuration;
    *                Otherwise, return the salve site configuration.
    */
	function &getMultiSitesConfig( $site_id = null)
	{
      if ( empty( $site_id) || $site_id == ':master_db:') {
	      return Jms2WinFactory::getMasterConfig();
      }
      
      return Jms2WinFactory::getSlaveConfig( $site_id);
	}


   // ------------- _getSingleDBInstance ----------------
   /**
    * @brief This routine is the equivalent of JDatabase::getInstance
    *        except that it create only once instance per driver in aim to reduce
    *        the number of open connection.
    */
	function &_getSingleDBInstance( $options	= array() )
	{
	   // Just keep a single instance to avoid having to many connection.
		static $instances;
	   
		if (!isset( $instances )) {
			$instances = array();
		}

		$driver     = array_key_exists( 'driver', $options) 	? $options['driver']	   : 'mysql';
		$driver     = preg_replace('/[^A-Z0-9_\.-]/i', '', $driver);

		$host		   = array_key_exists('host', $options)	   ? $options['host']		: 'localhost';
		$database	= array_key_exists('database', $options)	? $options['database']	: null;
		$user		   = array_key_exists('user', $options)	   ? $options['user']		: '';
		$password	= array_key_exists('password',$options)	? $options['password']	: '';
		$prefix		= array_key_exists('prefix', $options)	   ? $options['prefix']	   : 'jos_';
		$select		= array_key_exists('select', $options)	   ? $options['select']	   : true;

		// If the drive does not exists
		if (empty($instances[$driver]))
		{
   		$path	= dirname( __FILE__)
   		      .DS.'database'
   		      .DS.'database'.DS.$driver.'.php';
   
   		if (file_exists($path)) {
   			require_once($path);
   		} else {
   			$instance->setErrorInfo( 2, JTEXT::_('Unable to load Database Driver:') .$driver);
   			return null;
   		}
   
   		$adapter    = 'MultisitesDatabase'.$driver;
   		$instance	= new $adapter($options);
   
   		if ( $error = $instance->getErrorMsg() )
   		{
   			$instance->setErrorInfo( 2, $error . " DB [$database]");
   			return $instance;
   		}
   		
   		$instance->_user = $user;
			$instances[$driver] = & $instance;
		}
		// Re-use the previous DB instance
		else {
		   $instance = & $instances[ $driver];
		   
		   // If same MySQL connection
		   if ( $instance->_dbserver == $host && $instance->_user == $user) {
		      // If same DB
		      if ( $instance->_dbname == $database) {
      			// Clear the possible error that could be present in the previous database instance
      			$instance->setErrorInfo( 0, '');
		      }
		      // If different DB
		      else {
		         // If connection is closed
		         if ( !$instance->connected()) {
            		if (!($instance->_resource = @mysql_connect( $host, $user, $password, true ))) {
            			$instance->setErrorInfo( 2, "Could not connect to MySQL host=[$host] with user=[$user]");
            		}
            		else {
               		if ( $select ) {
               			$instance->select($database);
               		}
            		}
		         }
		         else {
            		if ( $select ) {
            			$instance->select($database);
            		}
		         }
		      }
		   }
		   // Connection with the same driver on another server or other user name
		   else {
		      // Get a new DB connection
      		if (!($con = @mysql_connect( $host, $user, $password, true ))) {
      			$instance->setErrorInfo( 2, "Could not connect to MySQL host=[$host] with user=[$user]");
      		}
      		else {
   		      // If the connection is open
   	         if ( $instance->connected()) {
         		    // Close previous connection
         		   $instance->__destruct();
   	         }
   	         // Use the new connection
      		   $instance->_resource = $con;
         		// select the database
         		if ( $select ) {
         			$instance->select($database);
         		}
      		}
		   }
   		$instance->setPrefix( $prefix);
		}

		return $instance;
	}

	
   // ------------- _createDBO_BasedOnConfig ----------------
   /**
    * @brief This is a duplication of JFactory::__createDBO() but use a $config
    *        parameter to allow using any configuration file
    */
	function &_createDBO_BasedOnConfig( $conf, $tempConnection=false)
	{
		jimport( 'joomla.database.database');
		jimport( 'joomla.database.table' );

		$host 		= $conf->getValue('config.host');
		$user 		= $conf->getValue('config.user');
		$password 	= $conf->getValue('config.password');
		$database	= $conf->getValue('config.db');
		$prefix 	   = $conf->getValue('config.dbprefix');
		$driver 	   = $conf->getValue('config.dbtype');
		$debug 		= $conf->getValue('config.debug');

		$options	= array ( 'driver'   => $driver, 
		                   'host'     => $host, 
		                   'user'     => $user, 
		                   'password' => $password, 
		                   'database' => $database, 
		                   'prefix'   => $prefix 
		                 );

		if ( $tempConnection) {
		   $db =& Jms2WinFactory::_getSingleDBInstance( $options );
		}
		else {
   		$db =& JDatabase::getInstance( $options );
   		if ( JError::isError($db) ) {
   			jexit('Database Error: ' . $db->toString() );
   		}
   
   		if ($db->getErrorNum() > 0) {
   			JError::raiseError(500 , 'JDatabase::getInstance: Could not connect to database <br/>' . 'joomla.library:'.$db->getErrorNum().' - '.$db->getErrorMsg() );
   		}
		}


		$db->debug( $debug );
		$db->_dbserver = $host;
		$db->_dbname   = $database;
		return $db;
	}

   // ------------- getMasterDBO ----------------
   /**
    * @brief This is a duplication of JFactory::getDBO() where is modified the getConfig
    */
	function &getMasterDBO( $tempConnection=false)
	{
		static $instance;

		if (!is_object($instance))
		{
			//get the debug configuration setting
			$conf =& Jms2WinFactory::getMasterConfig();
			$debug = $conf->getValue('config.debug');

			if ( $tempConnection) {
   			$db = & Jms2WinFactory::_createDBO_BasedOnConfig( $conf, $tempConnection);
      		if ($db->getErrorNum() > 0) {
      		   $none = null;
      		   return $none;
      		}
   			$db->debug($debug);
   			return $db;
			}
			
			$instance = & Jms2WinFactory::_createDBO_BasedOnConfig( $conf);
			$instance->debug($debug);
		}

		return $instance;
	}

   // ------------- getMultiSitesDBO ----------------
   /**
    * @brief Return a DBO connection of a site ID or the master website.
    * @param $site_id   When = ':master_db:', it returns the getMasterDBO()
    *                   When NULL, it try to find a parameter "site_id" in the URL (GET) or in the POST
    *                   Otherwise, it returns getSlaveDBO( $site_id)();
    */
	function &getMultiSitesDBO( $site_id = null, $tempConnection=false)
	{
	   // If the site id is not present,
	   if ( empty( $site_id)) {
	      // Try to see if there is a site id in parameter
	      $site_id = JRequest::getString('site_id', null);
	      // In Joomla 1.6, it may happen that the site_id is stored in an array
	      if ( $site_id=='Array') {
	         $arr = JRequest::getVar('site_id', null, 'get', 'array');
	         if ( !empty( $arr) && is_array($arr) && count( $arr)>0) {
	            $site_id = $arr[0];
	         }
	      }
	   }
	   
	   if ( empty( $site_id) || $site_id == ':master_db:') {
	      return Jms2WinFactory::getMasterDBO( $tempConnection);
	   }
	   return Jms2WinFactory::getSlaveDBO( $site_id, $tempConnection);
	}


   // ------------- getSlaveDBO ----------------
   /**
    * @brief This is a duplication of JFactory::getDBO() where the function getConfig is modified
    * to use the slave configuration file
    */
	function &getSlaveDBO( $site_id, $tempConnection=false)
	{
		static $instances;
		static $none = null;

		if (!isset( $instances )) {
			$instances = array();
		}

		if ( empty( $instances[$site_id]))
		{
			//get the debug configuration setting
			$conf =& Jms2WinFactory::getSlaveConfig( $site_id);
			if ( empty( $conf)) {
			   $instances[$site_id] = null;
			   return $instances[$site_id];
			}
			$debug = $conf->getValue('config.debug');

			if ( $tempConnection) {
   			$db = & Jms2WinFactory::_createDBO_BasedOnConfig( $conf, $tempConnection);
      		if ($db->getErrorNum() > 0) {
               // echo "( site_id=[$site_id]) temp connection error <br/>" .var_export( $db, true);
      		   $instances[$site_id] = null;
      		   return $instances[$site_id];
      		}
   			$db->debug($debug);
   			return $db;
			}
			
			$instances[$site_id] = & Jms2WinFactory::_createDBO_BasedOnConfig( $conf);
			$instances[$site_id]->debug($debug);
		}

		return $instances[$site_id];
	}

   // ------------- getSlaveRootPath ----------------
   /**
    * @brief This return the equivalent of JPATH_ROOT corresponding to the site identifier
    */
	function &getSlaveRootPath( $site_id)
	{
	   $filename = JPATH_MULTISITES .DS. $site_id .DS. 'config_multisites.php';
	   @include( $filename);
	   if ( isset( $config_dirs)) {
	      if ( !empty( $config_dirs['deploy_dir'])) {
	         return $config_dirs['deploy_dir'];
	      }
	   }
	   // Else retry with letter tree directory structure
	   else {
         if ( class_exists( 'MultisitesLetterTree')) {
            // Try to compute a path using the letter tree
            $lettertree_dir = MultisitesLetterTree::getLetterTreeDir( $site_id);
            if( !empty( $lettertree_dir)) {
               $site_dir = JPATH_MULTISITES.DIRECTORY_SEPARATOR.$lettertree_dir;
               $filename = $site_dir.DIRECTORY_SEPARATOR.'config_multisites.php';

         	   @include( $filename);
         	   if ( isset( $config_dirs)) {
         	      if ( !empty( $config_dirs['deploy_dir'])) {
         	         return $config_dirs['deploy_dir'];
         	      }
         	   }
            }
         }
	   }
	   // If there is not specific deploy directory, use the master root directory
	   return JPATH_ROOT;
	}


   // ------------- getMasterUser ----------------
	/**
	 * Get an user object
	 *
	 * Returns a reference to the global {@link JUser} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param 	int 	$id 	The user to load - Can be an integer or string - If string, it is converted to ID automatically.
	 *
	 * @access public
	 * @return object JUser
	 */
	function &getMasterUser($id = null)
	{
		jimport('joomla.user.user');

		if(is_null($id))
		{
			$session  =& JFactory::getSession();
			$instance =& $session->get('user');
			if (!is_a($instance, 'JUser')) {
      		// Replace the current instance of the site with the Master DB connection (possibly using the master table prefix)
      		// This is done to let all function using the Master DB (like Create JUser)
      		$db         =& JFactory::getDBO();
      		$saveDB     = $db;
      		$dbMaster   =& Jms2WinFactory::getMasterDBO();
      		$db         = $dbMaster;  // Replace the current DB instance with the Master Instance

				$instance =& JUser::getInstance();
				
   		   // Restore the current DB
   		   $db = $saveDB;
			}
		}
		else
		{
   		// Replace the current instance of the site with the Master DB connection (possibly using the master table prefix)
   		// This is done to let all function using the Master DB (like Create JUser)
   		$db         =& JFactory::getDBO();
   		$saveDB     = $db;
   		$dbMaster   =& Jms2WinFactory::getMasterDBO();
   		$db         = $dbMaster;  // Replace the current DB instance with the Master Instance
   		
			$instance =& JUser::getInstance($id);
			
		   // Restore the current DB
		   $db = $saveDB;
		}

		return $instance;
	}
	
	
   // ------------- import ----------------
	/**
	 * @brief Include a file in which a set of Search/Replace are applied before its is interpreted by PHP
	 * @param multisites_path  Target root path where must be writen the Multisites conversion result.
	 * @param original_path    Source path of the file to convert for multisite.
	 * @param filename         File to convert for multisites.
	 * @param searchReplace    Array of a list of Search/Replace couples that must be used to convert the file
	 * @param writeResult      Parameter that indicate if a file must be written with the result
	 *                         This may improve the performance as it is not converted each times.
	 *
	 * @par Implementation:
	 * - Read the file content
	 * - Apply a Search/Replace on the file read
	 * - Evaluate the results
	 */
	function import( $multisites_path,
	                 $original_path,
	                 $filename, 
	                 $searchReplace = array(),
	                 $writeResult = true)
	{
      jimport('joomla.filesystem.file');
      // If there is something to change in the original file
      if ( !empty( $searchReplace)) {
         $force_Recompute = false;
         // Compute the Multisite file corresponding to the one that must be converted
         $jmsFilename = $multisites_path .DS. 'multisites.' . $filename;

         // Read the "cache" configuration file
         // If a file is already converted for the same Joomla version number, keep it.
         // Otherwise, force to re-convert the file.

   		$version             = new JVersion();
   		$cur_joomlaversion   = $version->getShortVersion();

         $config_filename = $multisites_path .DS. 'multisites.cfg.php';
         @include( $config_filename);
         // If there is no version in the configuration
         if ( empty( $joomla_vers)) {
            // Assume this is a new configuration to create
            $joomla_vers     = $cur_joomlaversion;
            $converted_files = array();
            $force_Recompute = true;
         }
         // If a Joomla version is present in the configuration
         else  {
            // Check if this is the same version than the current joomla
            if ( $joomla_vers != $cur_joomlaversion) {
               // If joomla version has changed, reset the configuration
               $joomla_vers     = $cur_joomlaversion;
               $converted_files = array();
               $force_Recompute = true;
            }
            else {
               // If the file is not in the collection of files already converted
               // Or it does not match the current joomla version
               if ( empty( $converted_files[$jmsFilename]) 
                 || $converted_files[$jmsFilename] != $cur_joomlaversion
                  )
               {
                  // Force recomputation of the conversion
                  $force_Recompute = true;
               }
            }
         }
         
         // If a converted files is present, use it
         if ( !$force_Recompute && JFile::exists( $jmsFilename)) {
            require_once( $jmsFilename);
         }
         // Else generate the Multisite file
         else {
            $search  = array_keys( $searchReplace);
            $replace = array_values( $searchReplace);
            
            $fullname = $original_path .DS. $filename;
            $content = JFile::read( $fullname);
            $content = str_replace( $search, $replace, $content);
            
            if ( !empty( $content)) {
               if ( $writeResult) {
                  $success = JFile::write( $jmsFilename, $content);
               }
               // Ensure the file is present
               if ( $writeResult && $success) {
                  // Update the "cache"
                  require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'utils.php');
                  $converted_files[$jmsFilename] = $cur_joomlaversion;
            		$config = "<?php\n";
            		$config .= "if( !defined( '_JEXEC' )) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); \n\n";
            		$config .= "\$joomla_vers     = '$joomla_vers';\n";
      		      $config .= "\$converted_files = array( " . MultisitesUtils::CnvArray2Str( '     ', $converted_files) . ");\n";
            		$config .= "?>";
                  JFile::write( $config_filename, $config);
                  
                  // Now, execute the converted file;
                  require_once( $jmsFilename);
               }
               // If it was impossible to write the converted file,
               else {
                  // Only evaluate its content
                  $str = trim( $content);
                  if ( substr( $str, 0, 5) == '<?php') {
                     if ( substr( $str, -2) == '?>') {
                        eval('?>' . $content . '<?php ');
                     }
                     else {
                        eval('?>' . $content);
                     }
                  }
                  else {
                     eval( $content);
                  }
               }
            }
         }
      }
      // If there is no Search/Replace, just include the file
      else {
         $fullname = $original_path .DS. $filename;
         require_once( $fullname);
      }
	}

   //------------ getDBOVersion ---------------
	/**
	* Return the MySQL version number of the site ID database
	*/
	function getDBOVersion( $site_id = ':master_db:')
	{
	   $rows = array();

      if ( $site_id == ':master_db:') {
         $db =& Jms2WinFactory::getMasterDBO();
      }
      else {
         $db =& Jms2WinFactory::getSlaveDBO( $site_id);
      }
      if ( empty( $db)) {
         return '';
      }
      $query = "SELECT Version() AS version";
      $db->setQuery( $query );
		$db->setQuery( $query );
		$version = $db->loadResult();
		return $version;
	}

   //------------ isCreateView ---------------
   /**
    * @return TRUE when the DB engine support the CREATE VIEW statement (MySQL 5.1.2 or higher)
    *         FALSE when CREATE VIEW is NOT supported (ie. MySQL 4.x < 5.1.2)
    */
	function isCreateView( $site_id = ':master_db:')
	{
	   $result = false;
	   if ( empty( $site_id)) {
	      $site_id = ':master_db:';
	   }
	   
      require_once( JPATH_COMPONENT .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
      $versStr = Jms2WinFactory::getDBOVersion( $site_id);
      if ( !empty( $versStr)) {
         // <version>-xxx-log
         $vers = explode( '-', $versStr);
         if ( !empty( $vers)) {
            // Version.Release.Build
            $version = $vers[0];
            $vers = explode( '.', $version);
            if ( !empty( $vers)) {
               $v = intval( $vers[0]);
               // If Version >= 5.0.0
               if ( $v >= 5) {
                  $result = true;
               }
/*
               // If Version >= 5.1.2
               if ( intval( $vers[0]) > 5 
                 || (intval( $vers[0]) == 5 && ( (intval( $vers[1]) == 1 && intval( $vers[2]) >= 2)
                                          || (intval( $vers[1]) > 1)
                                           )
                    )
                  ) {
                  $result = true;
               }
*/
            }
         }
      }
      
      return $result;
	}



   //------------ getSiteDomainName ---------------
   /**
    * @brief Get the domain name based on the site ID and return the domain name and facultative _host_ when working on localhost.
    */
   function getSiteDomainName( $site_id, $master_domain='', &$_host_)
   {
      // Retreive the Website domain name
      if ( $site_id == ':master_db:') {
         // If localhost
         if ( method_exists('MultiSites','isLocalHost') && MultiSites::isLocalHost()) {
         	$domain = '';
         }
         else {
            if ( empty( $master_domain)) {
               if ( defined( 'MULTISITES_MASTER_DOMAIN')) {
                  $master_domain = MULTISITES_MASTER_DOMAIN;
               }
            }
      	   $domain = $master_domain;
      	}
      }
      else {
         require_once( dirname( dirname( dirname( __FILE__))) .DS. 'classes' .DS. 'site.php');
   	   $site = & Site::getInstance( $site_id);
         if ( !empty( $site->indexDomains)) {
            $domain = $site->indexDomains[0];
         }
         else if ( !empty( $site->domains)) {
            $domain = $site->domains[0];
         }
      }
      
      // If the domain is found, check that it starts with http(s)://
      // If not present then add "http://" in front of the domain
      if ( !empty( $domain)) {
         $pos = strpos( $domain, 'http://');
         if ( $pos === false) {
            $pos = strpos( $domain, 'https://');
            if ( $pos === false) {
               $domain = 'http://' . $domain;
            }
         }
      }
      // Normalize the domain name to be sure it is terminated by a slash
      if ( !empty( $domain)) {
         $domain = rtrim( $domain, '/') . '/';
      }
      
      if ( method_exists('MultiSites','isLocalHost') && MultiSites::isLocalHost()) {
         $pos = strpos( $domain, '://');
         if ( $pos === false) {
            $_host_ = '&_host_='.rtrim( $domain, '/');
         }
         else {
            $_host_ = '&_host_='.rtrim( substr( $domain, $pos+3), '/');
         }
         $domain = '';
      }
      
      return $domain;
   }

} // End class
