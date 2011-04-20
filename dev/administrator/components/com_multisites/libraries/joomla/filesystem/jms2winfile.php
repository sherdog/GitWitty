<?php
/**
 * @file       jms2winfile.php
 * @brief      Jommla Multi Sites Files API using the FTP layer when present
 *
 * @version    1.2.33
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2009-2010 Edwin2Win sprlu - all right reserved.
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
 * - V1.2.14 25-NOV-2009: Initial version.
 * - V1.2.27 26-APR-2010: Add PHP 5.3 compatibility (remove split function)
 * - V1.2.29 08-MAY-2010: Fix split warning message
 * - V1.2.33 07-JUL-2010: Add Joomla 1.6 beta 4 compatibility
 */
 
defined('_JEXEC') or die( 'Restricted access' );

require_once( dirname( __FILE__).DS. 'jms2winpath.php');

define( 'PUBLIC2WIN', 'public static');

// ===========================================================
//             Jms2WinFile_php4 class
// ===========================================================
class Jms2WinFile_php4 extends JFile
{
   // ------------- _exists ----------------
   /**
    * @brief Check if a file exists.
    * It uses the FTP layer when enabled and when the folder can not be found
    */
	function _exists( $filename)
	{
		if ( parent::exists($filename)) {
		   return true;
		}
		
      if ( !defined( 'MULTISITES_REDIRECT_FTP') || !(MULTISITES_REDIRECT_FTP)) {
         return false;
      }
   		
		// Initialize variables
		jimport('joomla.client.helper');
		$ftpOptions = JClientHelper::getCredentials('ftp');

		if ($ftpOptions['enabled'] == 1)
		{
			// Connect the FTP client
			jimport('joomla.client.ftp');
			$ftp = &JFTP::getInstance(
				$ftpOptions['host'], $ftpOptions['port'], null,
				$ftpOptions['user'], $ftpOptions['pass']
			);

      	// Try determine if this is a directory
	      $parts  = preg_split('/\/|\\\\/', $filename);
         $name   = array_pop($parts);
         $parent = implode( DS, $parts);
			$results = $ftp->listDetails( $parent);
			// If no results
			if ( !empty( $results)) {
   			foreach( $results as $row) {
   			   if ( $row['name'] == $name) {
   			      $flag = substr( $row['rights'], 0, 1); // -rwxr-xr-x or lrwxrwxrwx
   			      
   			      // If files (not 'd')
   			      if ( $flag == '-') {
   			         return true;
   			      }
   			      //  If link
   			      else if (  $flag == 'l') {
   			         // Perhaps should also try check the target link to verify if this is a directory
   			         return true;
   			      }
   			      return false;
   			   }
   			}
			}
		}
		
		return false;
	}

   //------------ isFilePresentIn_open_basedir ---------------
   /**
    * @brief Check if a file is located in open_basedir directory
    */
   function isFilePresentIn_open_basedir( $filename)
	{
      // Cross check with "open_basedir"
      $env_open_basedir = ini_get('open_basedir');
      if ( empty( $env_open_basedir)) {
         return true;
      }
      // Check if the files is located in one of the directory of the open_basedir
      $obd = explode( ':', $env_open_basedir);
      foreach( $obd as $dir) {
         if ( substr( $filename, 0, strlen( $dir)) == $dir) {
            return true;
         }
      }
      
      return false;
	}



   //------------ isFTP_writable ---------------
   /**
    * @brief Check if a file is writable with FTP
    */
   function isFTP_writable( $filename)
	{
      // If the server infos was not available to cross-check the permission
      // Retry with FTP when enabled, 
		jimport('joomla.client.helper');
		$ftpOptions = JClientHelper::getCredentials('ftp');
		if ($ftpOptions['enabled'] == 1)
		{
		   $infos = Jms2WinPath::ftpFileDetails( $filename);
		   if ( !empty( $infos)) {
		      $rights = $infos['rights'];   // drwxr-xr-x
		      $user   = $infos['user'];     // Owner Can be empty on some platform
		      // If check owner
		      if ( !empty( $user) && $user == $ftpOptions['user']) {
		         if ( substr( $rights, 2, 1) == 'w') {
		            return true;
		         }
		      }
		      // Else if group is present, assume that this is the same group as the FTP user.
		      else if ( !empty( $group)) {
		         if ( substr( $rights, 5, 1) == 'w') {
		            return true;
		         }
		      }
		      // World.
		      else {
		         if ( substr( $rights, 8, 1) == 'w') {
		            return true;
		         }
		      }
		   }
		}
	   return false;
	}


   //------------ is_writable ---------------
   /**
    * @brief Check if a file is writable
    */
   function is_writable( $filename)
	{
	   // If is writable
	   if ( is_writable( $filename)) {
         $result = true;
	      // Cross-check the permission to verify if this is with appropriate owner
	      $serverInfos = Jms2WinPath::getServerInfo();
	      if ( !empty( $serverInfos)) {
	         // If same owner,
	         if ( fileowner( $filename) == $serverInfos['owner_id']) {
	            if ( Jms2WinFile::isFilePresentIn_open_basedir( $filename)) {
	               return true;
	            }
	         }
	         // If same group
	         else if ( filegroup( $filename) == $serverInfos['group_id']) {
         	   $mode = @fileperms( $path);
         		// Convert the mode to a string
         		if (is_int($mode)) {
         			$mode = decoct($mode);
         		}
         		$group = (int)substr( $mode, 2, 1);
      		   // If group write permission is ON
      		   if ( ($group & 0x02) == 0x02) {
   	            if ( Jms2WinFile::isFilePresentIn_open_basedir( $filename)) {
   	               return true;
   	            }
      		   }
	         }
	         // World
	         else {
         	   $mode = @fileperms( $path);
         		// Convert the mode to a string
         		if (is_int($mode)) {
         			$mode = decoct($mode);
         		}
         		$world = (int)substr( $mode, 3, 1);
      		   // If group write permission is ON
      		   if ( ($world & 0x02) == 0x02) {
   	            if ( Jms2WinFile::isFilePresentIn_open_basedir( $filename)) {
   	               return true;
   	            }
      		   }
	         }
	         
	         // If the cross-check failed, let retry with FTP when enabled.
	         // Otherwise return false.
	         $result = false;
	      }
	      
	      // If the server infos was not available to cross-check the permission
	      // Retry with FTP when enabled, 
   		jimport('joomla.client.helper');
   		$ftpOptions = JClientHelper::getCredentials('ftp');
   		if ($ftpOptions['enabled'] == 1) {
   		   return Jms2WinFile::isFTP_writable( $filename);
   		}
   		
   		return $result;
	   }
	   // File is not writable. So check with FTP
	   else {
   		jimport('joomla.client.helper');
   		$ftpOptions = JClientHelper::getCredentials('ftp');
   		if ($ftpOptions['enabled'] == 1) {
   		   return Jms2WinFile::isFTP_writable( $filename);
   		}
   		
   		return false;
	   }
	}

} // End class


// ===========================================================
//             Jms2WinFile class
// ===========================================================

/**
 * @remarks: As Joomla 1.6! use "static" keyword only valid in PHP 5,
 * we have created a middle class that provide the PHP 4 & PHP 5 compatibility
 */

if ( version_compare( JVERSION, '1.6') >= 0) { $jms2win_php4_static = 'public static '; }
else                                         { $jms2win_php4_static = ''; }

eval( 'class Jms2WinFile extends Jms2WinFile_php4 { '
    . $jms2win_php4_static . 'function exists($path) { return Jms2WinFile::_exists($path); }'
    . '}'
    ) ;
unset( $jms2win_php4_static);
