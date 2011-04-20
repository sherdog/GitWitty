<?php
/**
 * @file       helper.php
 * @version    1.2.45
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
 * - V1.1.0 01-OCT-2008: Add several routines for the template managment
 * - V1.1.1 22-NOV-2008: Add a routine to check if Symbolic Link function is available
 * - V1.1.2 29-NOV-2008: Fix tool tip KeyWord (Duplicate User_ID and missing Site_ID)
 *                       Add function getSiteOwnerList to provide a filter on owner list.
 *                       See also the same function in the front-end helper (getUsersList)
 * - V1.1.4 09-DEC-2008: Replace "[unselected]" by ":master_db:" into the getTemplateList in aim to use
 *                       the correct DB configuration.
 * - V1.1.11 05-JAN-2009: Ignore the warning on SymLink to avoid message for the user when permission is denied
 *                        Add a routine to return the User name of a template admin user.
 * - V1.1.13 12-JAN-2009: Add the username to the template name returned to the front-end in aim to let
 *                        the front-end user know the login name that he has to use to loggin the website he create.
 * - V1.1.16 12-FEB-2009: Give possibility to get the admin name from the master website.
 * - V1.2.00 15-JUN-2009: Add the {rnd_psw_6} to {rnd_psw_10} in the tooltips keywords.
 * - V1.2.06 10-SEP-2009: - Remove the language translation using the "sitename" to avoid fatal error in language file line 171.
 *                        When a "::" is present in a sitename, this crash the language JText:_() function that
 *                        interpret the "::" as a class separator.
 *                        - Add templates action list specific depending on the directory that must be created.
 *                        When creating an installation directory, it is now possible to use "dirlinks", "ignore", "copy", "unzip"
 *                        DirLinks is a special feature that allow create a directory in which all the sub-directories
 *                        are symbolic links and the index*.php are embended files to guarantee the symbolic link value.
 * - V1.2.07 25-SEP-2009: - Improve the Symbolic Link detection in the case where the Global Configuration defines
 *                        tmp or logs directory that does not exists. In this case, try to use the tmp and logs directory 
 *                        that are probably present in the root of the website.
 * - V1.2.13 17-NOV-2009: - Add rewrite actions
 *                        - Add Radio Yes/No/Default HTML generation
 * - V1.2.14 24-NOV-2009: - Make the Radio button "default" value optional.
 * - V1.2.20 24-JAN-2010: Add possibility to ignore the "images" and "templates" copy in case where replicating a slave site
 *                        without deploy folder (same directory than master).
 * - V1.2.27 26-APR-2010: Add PHP 5.3 compatibility (remove split function)
 * - V1.2.32 21-JUN-2010: Add Joomla 1.6 Compatibility.
 *                        Remove the "&nbsp;" present in the HMTL::_ statement that are not more supported
 *                        by 1.6
 * - V1.2.39 10-OCT-2010: Fix a DB warning message that is reported in debug mode in joomla 1.5
 *                        because a Joomla 1.6 peace of code is called from Joomla 1.5.
 * - V1.2.45 15-DEC-2010: Fix submenu display to be compatible with Joomla 1.6 RC1
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

// ===========================================================
//             MultisitesHelper class
// ===========================================================
/**
 * @brief Set of helps used by the MultiSitesView classes.
 *
 * Contain several routine to create 'combo' selection list.
 */
class MultisitesHelper
{
   //------------ isSymbolicLinks ---------------
	/**
	* Check if Symbolic Links are allowed.
	* On Windows, this is not allowed;
	* On Unix, this is theorically available but must be checked because some Hosting provider disable this functionality.
	* When using the FTP Layer, probably the Symbolic Link will not work. This must be checked.
	* The objective of this routine is to create a link in the "tmp" directory that point on the "logs" directory.
	* This suppose that the "tmp" directory exists and that it is allowed write a symbolic link on the "logs" directory
	* that must also be present.
	*/
	function isSymbolicLinks()
	{
		static $instance;

		if (!isset( $instance )) {
		   // On Windows, Symbolic Links are not allowed
   	   if ( JUtility::isWinOS()) {
   	      $instance = false;
   	   }
   	   // On Unix
   	   else {
            jimport( 'joomla.filesystem.path');
            jimport( 'joomla.filesystem.folder');
            jimport( 'joomla.filesystem.file');
            
   	      // By default, we assume that SL is forbidden
   	      $instance = false;
   	      
   	      // Try to create a SL to verify it is available
      		$config =& JFactory::getConfig();
      		
      		// Compute a temporary SL file name
      		$link = uniqid('symlink');

      	   $sav_dir = getcwd();

      		$tmp_dir = $config->getValue('config.tmp_path');
      		// If temporary directory defined in the "configuration.php" files (Global Configuration) does not exists
      		// or is not writable
      		if ( !JFolder::exists( $tmp_dir) || !is_writable( $tmp_dir)) {
      		   // Try using the tmp directory in the root of the website (if it exists).
      		   $tmp_dir = JPATH_ROOT.DS.'tmp';
      		}

      		// If the temporary directory exists and is writable
      		if ( JFolder::exists( $tmp_dir) && is_writable( $tmp_dir))
      		{
      	      chdir( $tmp_dir);
      	      
      		   // Check that the logs directory exists
         		$log_dir = $config->getValue('config.log_path');
         		// If the log directory defined in the "configuration.php" files (Global Configuration) does not exists
         		if ( !JFolder::exists( $log_dir)) {
         		   // Try using the logs directory that is probably present in the root of the website (if it exists).
         		   $log_dir = JPATH_ROOT.DS.'logs';
         		}
         		
         		// If neither the "Global Configuration" logs directory
         		// And the root websites "logs" directory is not present
         		if ( !JFolder::exists( $log_dir)) {
         		   // Create a fake file into the tmp directory that will be used to check if it exists
                  $tmp_fname = JPath::clean( $tmp_dir .DS. uniqid('symlink_file') . '.txt');
                  $fp = fopen( $tmp_fname, "w");
                  if ( !empty( $fp)) {
                     fputs( $fp, 'this file can be deleted');
                     fclose( $fp);
                     
                     $log_dir = $tmp_fname;
                  }
         		}

         		// If the log directory exists or this is a temporary file
         		if ( JFolder::exists( $log_dir) || JFile::exists( $log_dir)) {
                  // If it is possible to create a link on the log path
                  if ( function_exists( 'symlink') && @symlink( $log_dir, $link)) {
                     // Then check that the Symbolic Link exists
                     $fullname = $tmp_dir .DS. $link;
                     // If the link exists
                     if ( JFolder::exists( $fullname) || JFile::exists( $fullname)) {
                        $instance = true;
                        // Remove the link
                        JFile::delete( $fullname);
                     }
                  }
                  
                  // If a temporary file was created to test the link
                  if ( isset( $tmp_fname)) {
                     // remove it
                     JFile::delete( $tmp_fname);
                  }
         		}
      	      
         	   // Restore the current directory
         	   chdir( $sav_dir);
      		}
   	   }
		}

		return $instance;
	}
   
   
   //------------  getSiteNameList ---------------
	/**
	* build the select list for site name
	*/
	function getSiteNameList( &$sites, $filter_sitename)
	{
	   $rows = array();
	   if ( isset( $sites)) {
   	   foreach( $sites as $site) {
   	      $rows[ $site->sitename] = $site;
   	   }
   	   ksort( $rows);
	   }
	   
	   $opt = array();
      $opt[] = JHTML::_('select.option', '[unselected]', '- '.JText::_('Select site').' -');
	   foreach( $rows as $site) {
   		$opt[] = JHTML::_('select.option', $site->id, $site->sitename);
	   }
	   
		$list = JHTML::_( 'select.genericlist', $opt, 'filter_sitename', 
		                  'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 
		                  'value', 'text',
		                  "$filter_sitename");

		return $list;
	}

   //------------  getDBServerList ---------------
	/**
	* build the select list for db server name (host)
	*/
	function getDBServerList( &$sites, $filter_host)
	{
	   $rows = array();
	   if ( isset( $sites)) {
   	   foreach( $sites as $site) {
   	      $host = trim( $site->host);
   	      if ( !empty( $host)) {
      	      $rows[ $host] = $host;
   	      }
   	   }
   	   ksort( $rows);
   	}
	   
	   $opt = array();
      $opt[] = JHTML::_('select.option', '[unselected]', '- '.JText::_('Select server').' -');
	   foreach( $rows as $host) {
   		$opt[] = JHTML::_('select.option', $host, JText::_( $host));
	   }
	   
		$list = JHTML::_( 'select.genericlist', $opt, 'filter_host', 
		                  'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 
		                  'value', 'text',
		                  "$filter_host");

		return $list;
	}

   //------------  getDBNameList ---------------
	/**
	* build the select list for db name
	*/
	function getDBNameList( &$sites, $filter_dbname)
	{
	   $rows = array();
	   if ( isset( $sites)) {
   	   foreach( $sites as $site) {
   	      $dbname = trim( $site->db);
   	      if ( !empty( $dbname)) {
      	      $rows[ $dbname] = $dbname;
   	      }
   	   }
   	   ksort( $rows);
   	}
	   
	   $opt = array();
      $opt[] = JHTML::_('select.option', '[unselected]', '- '.JText::_('Select db').' -');
	   foreach( $rows as $dbname) {
   		$opt[] = JHTML::_('select.option', $dbname, $dbname);
	   }
	   
		$list = JHTML::_( 'select.genericlist', $opt, 'filter_db', 
		                  'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 
		                  'value', 'text',
		                  "$filter_dbname");

		return $list;
	}



   //------------  getSiteIdsList ---------------
	/**
	* build the select list for site ids that a DB defined and display site name, db & prefix
	*/
	function getSiteIdsList( &$sites, $filter_site_ids)
	{
	   $rows = array();
	   if ( isset( $sites)) {
   	   foreach( $sites as $site) {
   	      // If there is DB defined to this site
   	      if ( isset( $site->db)  && isset( $site->dbprefix)
   	        && !empty( $site->db) && !empty( $site->dbprefix)
   	         )
   	      {
      	      $value = $site->id 
      	             . ' ( ' . $site->db
      	             . ', ' . $site->dbprefix
      	             . ' )'
      	             ;
      	      $rows[ $site->id] = $value;
   	      }
   	   }
   	   ksort( $rows);
   	}
	   
	   $opt = array();
      $opt[] = JHTML::_('select.option', '[unselected]', '- '.JText::_('Template Site').' -');

	   // Get Master DB info
	   $db      =& JFactory::getDBO();
		$config	=& JFactory::getConfig();
		$dbname  =  $config->getValue('config.db');
	   $str = ' ( ' . $dbname . ', ' . $db->getPrefix() . ' )';
      $opt[] = JHTML::_('select.option', ':master_db:', '< Master Site >' . $str);

	   foreach( $rows as $key => $value) {
   		$opt[] = JHTML::_('select.option', $key, $value);
	   }
	   
		$list = JHTML::_( 'select.genericlist', $opt, 'filter_site_ids', 
		                  'class="inputbox" size="1" onchange="getUserList(this.options[selectedIndex].value);"', 
		                  'value', 'text',
		                  "$filter_site_ids");

		return $list;
	}

   //------------  getTemplatesList ---------------
	/**
	* build the select list with available templates
	*/
	function getTemplatesList( &$templates, &$selected_value, $front_end=false, $filter_GroupName=null)
	{
	   // When there is no selected value,
	   $firstOpt = false;
	   if ( empty( $selected_value)) {
	      // Return the first value found in the template collection
   	   $firstOpt = true;
	   }
	   $rows = array();
	   if ( isset( $templates)) {
   	   foreach( $templates as $id => $template) {
   	      $groupName  = !empty( $template['groupName'])   ? $template['groupName']   : '';
   	      $title      = !empty( $template['title'])       ? $template['title']       : '';
   	      $fromSiteID = !empty( $template['fromSiteID'])  ? $template['fromSiteID']  : '';
   	      $toSiteID   = !empty( $template['toSiteID'])    ? $template['toSiteID']   	: '';
   	      $fromDB     = !empty( $template['fromDB'])      ? $template['fromDB']   	: '';
   	      $fromPrefix = !empty( $template['fromPrefix'])  ? $template['fromPrefix']  : '';
   	      $toPrefix   = !empty( $template['toPrefix'])    ? $template['toPrefix']    : '';
   	      $toDomains  = !empty( $template['toDomains'])   ? $template['toDomains']    : '';
   	      
   	      // When there is no group name filter, show all template
   	      if ( empty( $filter_GroupName)) {
   	         if ( $front_end) {
   	            // In case of a front-end, if the rule to create a site id is not present and there is no domain defined
   	            if ( empty( $toSiteID) || empty( $toDomains)) {
   	               // Skip the template
   	            }
   	            else {
            	      $rows[ $id] = "$title";
   	            }
   	         }
   	         else {
         	      $rows[ $id] = "$id ( $fromDB : $fromPrefix => $toPrefix )";
   	         }
   	      }
   	      else if ( $groupName == $filter_GroupName) {
   	         if ( $front_end) {
   	            // In case of a front-end, if the rule to create a site id is not present and there is no domain defined
   	            if ( empty( $toSiteID) || empty( $toDomains)) {
   	               // Skip the template
   	            }
   	            else {
            	      $rows[ $id] = "$title";
   	            }
   	         }
   	         else {
         	      $rows[ $id] = "$id ( $fromDB : $fromPrefix => $toPrefix )";
   	         }
   	      }
   	   }
   	   ksort( $rows);
   	}
   	// If there is no record, 
   	if ( count($rows) <= 0) {
   	   // return nothing. This will allow to detect no records
   	   return '';
   	}
   	
   	$db =& JFactory::getDBO();
   	$dbPrefix = $db->getPrefix();
	   
	   $opt = array();
      if ( $front_end) {
         // If there is no selected value, use the first option as seleted value,
         if ( $firstOpt) {
      	   foreach( $rows as $key => $value) {
      	      $selected_value = $key;
      	      break;
      	   }
         }
      }
      else {
         $opt[] = JHTML::_('select.option', '[unselected]', '- '.JText::_('Fresh slave site').' -');
         $opt[] = JHTML::_('select.option', ':master_db:', '- '.JText::_('Master DB'). " ( prefix='$dbPrefix' ) -");
      }


	   foreach( $rows as $key => $value) {
   		$opt[] = JHTML::_('select.option', $key, $value);
	   }
	   
		$list = JHTML::_( 'select.genericlist', $opt, 'fromTemplateID', 
		                  'class="inputbox" size="1" onchange="refreshTemplateDir(this.options[selectedIndex].value);"', 
		                  'value', 'text',
		                  "$selected_value");

		return $list;
	}

   //------------ getSitesUsersList ---------------
	/**
	* build the select list with available users
	*/
	function getSitesUsersList( &$sites, $filter_users)
	{
	   $rows = array();
	   if ( isset( $sites)) {
   	   foreach( $sites as $site) {
   	      // If there is DB defined to this site
   	      if ( isset( $site->db)  && isset( $site->dbprefix)
   	        && !empty( $site->db) && !empty( $site->dbprefix)
   	         )
   	      {
      	      $db =& Jms2WinFactory::getSlaveDBO( $site->id);
               $query = "SELECT id, username FROM #__users ORDER BY username";
               $db->setQuery( $query );
         		$users = $db->loadObjectList();
         		foreach( $users as $user) {
         		   $key = $site->id.'|'.$user->id;
         	      $value = $site->id . ' / ' . $user->username;
         	      $rows[ $key] = $value;
         		}
   	      }
   	   }
   	   ksort( $rows);
   	}
   	// If there is no record, 
   	if ( count($rows) <= 0) {
   	   // return nothing. This will allow to detect no records
   	   return '';
   	}
	   
	   $opt = array();
      $opt[] = JHTML::_('select.option', '[unselected]', '- '.JText::_('Sites & Users').' -');
	   foreach( $rows as $key => $value) {
   		$opt[] = JHTML::_('select.option', $key, $value);
	   }
	   
		$list = JHTML::_( 'select.genericlist', $opt, 'filter_users', 
		                  'class="inputbox" size="1"', 
		                  'value', 'text',
		                  "$filter_users");

		return $list;
	}

   //------------ getUsersList ---------------
	/**
	* build the select list with available users
	*/
	function getUsersList( $site_id, $selected_value = '[unselected]')
	{
	   $rows = array();

      require_once( JPATH_COMPONENT .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
      if ( $site_id == ':master_db:') {
         $db =& Jms2WinFactory::getMasterDBO();
      }
      else {
         $db =& Jms2WinFactory::getSlaveDBO( $site_id);
      }
      if ( empty( $db)) {
         return '';
      }
      $query = "SELECT id, username FROM #__users ORDER BY username";
      $db->setQuery( $query );
		$users = $db->loadObjectList();
		if ( empty( $users)) {
   	   return '';
		}
		foreach( $users as $user) {
		   $key = $user->id;
	      $value = $user->username;
	      $rows[ $key] = $value;
		}
	   asort( $rows);

   	// If there is no record, 
   	if ( count($rows) <= 0) {
   	   // return nothing. This will allow to detect no records
   	   return '';
   	}
	   
	   $opt = array();
      $opt[] = JHTML::_('select.option', '[unselected]', '- '.JText::_('Users').' -');
	   foreach( $rows as $key => $value) {
   		$opt[] = JHTML::_('select.option', $key, $value);
	   }
	   
		$list = JHTML::_( 'select.genericlist', $opt, 'adminUserID', 
		                  'class="inputbox" size="1"', 
		                  'value', 'text',
		                  "$selected_value");

		if ( !empty( $list)) {
		   $list .= JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_ADMIN_USER_TTIPS'));
		}

		return $list;
	}


   //------------ getOwnerList ---------------
	/**
	* build the select list with available users present in the master DB
	*/
	function getOwnerList( $selected_value = '[unselected]', $fieldname = 'owner_id')
	{
	   $rows = array();

      require_once( JPATH_COMPONENT .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
      $db =& JFactory::getDBO();
      if ( empty( $db)) {
         return '';
      }
      $query = "SELECT id, username FROM #__users ORDER BY username";
      $db->setQuery( $query );
		$users = $db->loadObjectList();
		foreach( $users as $user) {
		   $key = $user->id;
	      $value = $user->username;
	      $rows[ $key] = $value;
		}
	   asort( $rows);

   	// If there is no record, 
   	if ( count($rows) <= 0) {
   	   // return nothing. This will allow to detect no records
   	   return '';
   	}
	   
	   $opt = array();
      $opt[] = JHTML::_('select.option', '[unselected]', '- '.JText::_('Owners').' -');
	   foreach( $rows as $key => $value) {
   		$opt[] = JHTML::_('select.option', $key, $value);
	   }
	   
		$list = JHTML::_( 'select.genericlist', $opt, $fieldname, 
		                  'class="inputbox" size="1"', 
		                  'value', 'text',
		                  "$selected_value");

		return $list;
	}

   //------------ getSiteOwnerList ---------------
	/**
	* build a combo box with the list of all the users that have contracts.
	*/
	function getSiteOwnerList( $sites, $selected_value, $title='Select owner')
	{
		$db =& JFactory::getDBO();
		
	   $opt = array();
      $opt[] = JHTML::_('select.option', '[unselected]', '- '.JText::_( $title).' -');
      
	   $owner_ids = array();
	   foreach( $sites as $site) {
	      if ( !empty( $site->owner_id)) {
	         $owner_ids[$site->owner_id] = $site->owner_id;
	      }
	   }
	   foreach( $owner_ids as $owner_id) {
   		$query = 'SELECT name FROM #__users WHERE id=' . $owner_id
   		       . ' LIMIT 1';
   		$db->setQuery( $query );
   		$user_name = $db->loadResult();
   		if ( !empty( $user_name)) {
      		$opt[] = JHTML::_('select.option', $owner_id, $user_name);
   		}
	   }
	   
		$list = JHTML::_( 'select.genericlist', $opt, 'filter_owner_id', 
		                  'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 
		                  'value', 'text',
		                  "$selected_value");

		return $list;
	}

   //------------ getActionsList ---------------
	/**
	* build the select list with possible symbolic links actions
	*/
	function getActionsList( $field_name, $filename, $symbolicLink, $source_fieldname)
	{
      jimport( 'joomla.utilities.utility.php');
      
	   if ( empty( $symbolicLink['action'])) {
   	   $selected_value = '[unselected]';
	   }
	   else {
   	   $selected_value = $symbolicLink['action'];
	   }
	   
	   // If special actions for the installation directory
	   if ( $filename == 'installation') {
         // If Windows,
   	   if ( !MultisitesHelper::isSymbolicLinks()) {
   	      // Symbolic Links does not exists
      	   $rows = array( 'ignore'  => JText::_( 'TEMPLATE_ACTION_IGNORE'),
      	                  'copy'    => JText::_( 'TEMPLATE_ACTION_COPY'),
      	                  'unzip'   => JText::_( 'TEMPLATE_ACTION_UNZIP')
      	                );
   	   }
   	   else {
      	   $rows = array( 'dirlinks'=> JText::_( 'TEMPLATE_ACTION_DIRLINKS'),
      	                  'ignore'  => JText::_( 'TEMPLATE_ACTION_IGNORE'),
      	                  'copy'    => JText::_( 'TEMPLATE_ACTION_COPY'),
      	                  'unzip'   => JText::_( 'TEMPLATE_ACTION_UNZIP')
      	                );
   	   }
	   }
	   else if ( $filename == 'images'
	          || $filename == 'templates') {
   	   $rows = array( 'special' => JText::_( 'TEMPLATE_ACTION_SPECIAL'),
   	                  'copy'    => JText::_( 'TEMPLATE_ACTION_COPY'),
      	               'ignore'  => JText::_( 'TEMPLATE_ACTION_IGNORE'),
   	                  'unzip'   => JText::_( 'TEMPLATE_ACTION_UNZIP')
   	                );
	   }
	   // Default actions
	   else {
         // If Windows,
   	   if ( !MultisitesHelper::isSymbolicLinks()) {
   	      // Symbolic Links does not exists
      	   $rows = array( 'ignore'  => JText::_( 'TEMPLATE_ACTION_IGNORE'),
      	                  'copy'    => JText::_( 'TEMPLATE_ACTION_COPY'),
      	                  'unzip'   => JText::_( 'TEMPLATE_ACTION_UNZIP'),
      	                  'mkdir'   => JText::_( 'TEMPLATE_ACTION_MKDIR')
      	                );
   	   }
   	   else {
      	   $rows = array( 'SL'      => JText::_( 'TEMPLATE_ACTION_SL'),
      	                  'ignore'  => JText::_( 'TEMPLATE_ACTION_IGNORE'),
      	                  'copy'    => JText::_( 'TEMPLATE_ACTION_COPY'),
      	                  'unzip'   => JText::_( 'TEMPLATE_ACTION_UNZIP'),
      	                  'mkdir'   => JText::_( 'TEMPLATE_ACTION_MKDIR')
      	                );
   	   }
	   }
	   

	   // If htaccess, add possibility to change the "RewriteBase" statement
	   if ( $filename == 'htaccess.txt' || $filename == '.htaccess') {
   	   $rows['rewrite'] = JText::_( 'TEMPLATE_ACTION_REWRITEBASE');
	   }


	   $opt = array();
      // $opt[] = JHTML::_('select.option', '[unselected]', '- '.JText::_('Actions').' -');
	   foreach( $rows as $key => $value) {
         // If Joomla 1.6
         if ( version_compare( JVERSION, '1.6') >= 0) {
   	      $value = str_replace( '&nbsp;', ' ', $value);
   	   }
   		$opt[] = JHTML::_('select.option', $key, $value);
	   }

		$list = JHTML::_( 'select.genericlist', $opt, "$field_name", 
		                  'class="inputbox" size="1" onchange="enableSource(this.options[selectedIndex].value,\'' . $source_fieldname . '\');"', 
		                  'value', 'text',
		                  "$selected_value");

		return $list;
	}

   //------------ getDomainList ---------------
   /**
    * @return Return the 'domains' entered request into an array form.
    * This parse the list of domain entered in the form and split the text based on commas or new line
    * to build an array of domain.
    * In fact this convert a string into an array.
    * @return
    * An array with the list of domain entered by the user in the field "domains".
    */
   function getDomainList( $field)
   {
		jimport( 'joomla.environment.uri' );
      $result = array();
      
      $lines = preg_split( "#[ ,\n]#", $_REQUEST[ $field]);
      foreach( $lines as $line)
      {
         $str = trim($line);
         if ( strlen($str)>0)
         {
            // If http(s):// is not present, add it
            $s = strtolower( $str);
            if ( (strncmp( $s, 'http://', 7) == 0)
              || (strncmp( $s, 'https://', 8) == 0)
               ) {}
            else {
               $str = 'http://' . $str;
            }
            
            $uri = new JURI( $str);
            $host = $uri->getHost();
            if (  empty( $host)) {
               $result[] = $str;
            }
            else {
               $url = $uri->toString( array('scheme', 'user', 'pass', 'host', 'port', 'path'));
               // remove trailing '/'
               while ( substr( $url, -1) == '/') {
                  $url = substr( $url, 0, strlen( $url)-1);
               }
               $result[] = $url;
            }
         }
      }
      
      return $result;
   }

   //------------ getFilterActionsCombo ---------------
   /**
    * @brief This is a filter that allow to show or hide the 'ignored' folders.
    * @see views/templates/tmpl/edit_unix.php
    */
	function getFilterActionsCombo( $nbrows)
	{
      $default_value = 'show';
/*
      jimport( 'joomla.utilities.utility.php');
      if ( !MultisitesHelper::isSymbolicLinks()) {
         $default_value = 'hide';
      }
*/
	   $opt = array();
		$opt[] = JHTML::_('select.option', 'show', JText::_( 'Show all'));
		$opt[] = JHTML::_('select.option', 'hide', JText::_( 'Hide ignored'));
		$list = JHTML::_( 'select.genericlist', $opt, "filter_actions", 
		                  'class="inputbox" size="1" onchange="filterActions(this.options[selectedIndex].value, ' . $nbrows . ');"', 
		                  'value', 'text',
		                  "$default_value");
		return $list;
	}


   //------------ tooltipsKeywords ---------------
   /**
    * @return Return the 'domains' entered request into an array form.
    * This parse the list of domain entered in the form and split the text based on commas or new line
    * to build an array of domain.
    * In fact this convert a string into an array.
    * @return
    * An array with the list of domain entered by the user in the field "domains".
    */
   function tooltipsKeywords()
   {
      jimport( 'joomla.utilities.utility.php');
      $deploy_dir = '';
      if ( MultisitesHelper::isSymbolicLinks()) {
         $deploy_dir = '<li>' . JText::_( 'TEMPLATE_KW_DEPLOY_DIR') . '</li>';
      }


		$title = JText::_( 'TEMPLATE_KW')
		       .'::';

		$tooltip = '<ul>'
               . '<li>' . JText::_( 'TEMPLATE_KW_USER_LOGIN') . '</li>'
               . '<li>' . JText::_( 'TEMPLATE_KW_USER_NAME') . '</li>'
               . '<li>' . JText::_( 'TEMPLATE_KW_USER_ID') . '</li>'
               . '<li>' . JText::_( 'TEMPLATE_KW_SITE_ID') . '</li>'
               . '<li>' . JText::_( 'TEMPLATE_KW_SITE_ID_LETTERS') . '</li>'
               . '<li>' . JText::_( 'TEMPLATE_KW_SITE_ALIAS') . '</li>'
               . '<li>' . JText::_( 'TEMPLATE_KW_ROOT') . '</li>'
               . '<li>' . JText::_( 'TEMPLATE_KW_MULTISITES') . '</li>'
               . '<li>' . JText::_( 'TEMPLATE_KW_SITE_DIR') . '</li>'
               . '<li>' . JText::_( 'TEMPLATE_KW_REL_SITE_DIR') . '</li>'
               . '<li>' . JText::_( 'TEMPLATE_KW_SITE_DOMAIN') . '</li>'
               . '<li>' . JText::_( 'TEMPLATE_KW_SITE_URL') . '</li>'
               . $deploy_dir
               . '<li>' . JText::_( 'TEMPLATE_KW_SITE_PREFIX') . '</li>'
               . '<li>' . JText::_( 'TEMPLATE_KW_RND_PSW_6_TO_10') . '</li>'
               . '<li>' . JText::_( 'TEMPLATE_KW_RND_PSW') . '</li>'
               . '<li>' . JText::_( 'TEMPLATE_KW_RESET') . '</li>'
               . '</ul>';
		$style   = 'style="text-decoration: none; color: #333;"';
		$image 	= JURI::root(true).'/administrator/components/com_multisites/images/idea.png';
		$text 	= '<img src="'. $image .'" border="0" alt="'. JText::_( 'Tooltip' ) .'"/>';
		$tip     = '<span class="editlinktip hasTip" title="'.$title.$tooltip.'" '. $style .'>'. $text .'</span>';
		return $tip;
   }

   //------------ getValidityUnits ---------------
	/**
	* build the select list of Validity Units
	*/
	function getValidityUnits( $field_name='validity_unit', $selected_value = '[unselected]')
	{
      // Symbolic Links does not exists
	   $rows = array( 'days'   => JText::_( 'days'),
	                  'months' => JText::_( 'months'),
	                  'years'  => JText::_( 'years')
	                );

	   $opt = array();
	   foreach( $rows as $key => $value) {
   		$opt[] = JHTML::_('select.option', $key, $value);
	   }
	   
		$list = JHTML::_( 'select.genericlist', $opt, "$field_name", 
		                  'class="inputbox" size="1"', 
		                  'value', 'text',
		                  "$selected_value");

		return $list;
	}


   //------------ getAllStatusList ---------------
	/**
	* build the select list of possible site status
	*/
	function getAllStatusList( $field_name='status', $selected_value = '[unselected]', $facultative=false, $filter=false)
	{
      // List of availabe status
	   $rows = array( 'Confirmed' => JText::_( 'Confirmed'),
	                  'Pending'   => JText::_( 'Pending'),
	                  'Cancelled' => JText::_( 'Cancelled'),
	                  'Refunded'  => JText::_( 'Refunded')
	                );

	   $opt = array();
	   if ( $facultative) {
         $opt[] = JHTML::_('select.option', '[unselected]', '- '.JText::_('Status').' -');
      }
	   foreach( $rows as $key => $value) {
   		$opt[] = JHTML::_('select.option', $key, $value);
	   }
	   
	   $onchange = '';
	   if ( $filter) {
   	   $onchange = ' onchange="document.adminForm.submit( );"';
	   }
	   
		$list = JHTML::_( 'select.genericlist', $opt, "$field_name", 
		                  'class="inputbox" size="1"' . $onchange, 
		                  'value', 'text',
		                  "$selected_value");

		return $list;
	}


   //------------ getOwnerName ---------------
	/**
	* @return
	* Returns the user name associated to a user id.
	*/
	function getOwnerName( $owner_id)
	{
	   if  (empty( $owner_id)) {
	      return '';
	   }
	   
		$db =& JFactory::getDBO();
		
		$query = 'SELECT name FROM #__users WHERE id=' . (int)$owner_id;
		$db->setQuery( $query );
		$result = $db->loadResult();

		return $result;
	}

   //------------ getTemplateAdminName ---------------
	/**
	* @return
	* Returns the user name associated to the adminUserID defined in the template
	*/
	function getTemplateAdminName( $template)
	{
	   if ( empty( $template) || empty( $template->adminUserID) || $template->adminUserID<=0) {
	      return '';
	   }
	   
      $db =& Jms2WinFactory::getMultiSitesDBO( $template->fromSiteID);
		
		$query = 'SELECT name, username FROM #__users WHERE id=' . (int)$template->adminUserID;
		$db->setQuery( $query );
		$row = $db->loadObject();
		$result = '';
		if ( !empty( $row)) {
   		$result = $row->name . ' (' . $row->username . ')';
		}

		return $result;
	}


   //------------ getRadioYesNoDefault ---------------
	/**
	* build Radio button list ( No, Yes, [Default])
	* @param defaultOption  True mean that button are ( No, Yes, Default)
	                        False does not diplay the default - Only (No, Yes)
	*/
	function getRadioYesNoDefault( $field_name='yesnodefault', $selected_value = '[unselected]', $onchange_action='', $defaultOption = true)
	{
	   if ( !isset( $selected_value)
	     || (isset( $selected_value) && strlen( $selected_value)<=0)) {
	      $selected_value = '[unselected]';
	   }
      // Symbolic Links does not exists
	   $rows = array( '1'            => JText::_( 'Yes'),
	                  '0'            => JText::_( 'No')
	                );
	   if ( $defaultOption) {
	      $rows['[unselected]'] = JText::_( 'Default');
	   }
	                  

	   $opt = array();
	   foreach( $rows as $key => $value) {
   		$opt[] = JHTML::_('select.option', $key, $value);
	   }

	   $onchange = '';
	   if ( !empty( $onchange_action)) {
   	   $onchange = ' onchange="' . $onchange_action . '"';
	   }
	   
		$list = JHTML::_( 'select.radiolist',  $opt, "$field_name",
		                  'class="inputbox"' . $onchange, 
		                  'value', 'text', 
		                  "$selected_value",
		                  "$field_name");

		return $list;
	}
	
	
	function addSubmenu($vName)
	{
      // If Joomla 1.6, continue
      if ( version_compare( JVERSION, '1.6') >= 0) {}
      // If Joomla 1.5
      else {
         // Don't do anything
         return;
      }
      
      // If Joomla 1.6, build the submenu based on the menu definition present in the DB

	   // Retreive all the submenu
   	$option     = JRequest::getCmd('option');
	   $db =& JFactory::getDBO();
      $query = "SELECT c.title, c.link, c.alias FROM #__menu as p"
             . " LEFT JOIN #__menu as c ON c.parent_id=p.id"
             . " LEFT JOIN #__extensions as x ON x.extension_id=p.component_id"
             . " WHERE p.level = 1 AND x.type='component' AND x.element LIKE '$option'"
             . " ORDER BY c.id"
             ;
      $db->setQuery( $query );
      $rows = $db->loadObjectList();
      if ( empty( $rows)) {
         return;
      }

		// Load the "system" menu
		$lang	= JFactory::getLanguage();
			$lang->load($option.'.sys', JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load($option.'.sys', JPATH_ADMINISTRATOR.'/components/'.$option, null, false, false)
		||	$lang->load($option.'.sys', JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||	$lang->load($option.'.sys', JPATH_ADMINISTRATOR.'/components/'.$option, $lang->getDefault(), false, false);
      
      foreach ($rows as $row) {
         // Extract the task value present in the link
         $pos = strpos( $row->link, '?');
         if ( $pos === false) {
            $param_url = $row->link;
         }
         else {
            $param_url = substr( $row->link, $pos+1);
         }
         $params_array = explode( '&', $param_url);
         foreach( $params_array as $param) {
            $keyvalues = explode( '=', $param);
            if ( $keyvalues[0] == 'task') {
               $menuTask = $keyvalues[1];
            }
         }
         
         // If it was not possible to get the task parameter, use the "alias" as name
         if ( empty( $menuTask)) {
            $menuTask = $row->alias;
         }
         
   		JSubMenuHelper::addEntry(
   			JText::_( strtoupper( $row->title)),
   			$row->link,
   			$vName == $menuTask
   		);
      }
	}

}  // End class
