<?php
/**
 * @file       jms2winpath.php
 * @brief      Jommla Multi Sites Path API using the FTP layer when present
 *
 * @version    1.2.27
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
 */
 
defined('_JEXEC') or die( 'Restricted access' );


// ===========================================================
//             Jms2WinFolder class
// ===========================================================
class Jms2WinPath extends JPath
{

   //------------ ftpFileDetails ---------------
   /**
    * @brief return the file details using the FTP layer when enabled.
    */
   function ftpFileDetails( $path)
	{
   	$results = array();
   	
	   // Check if the FTP layer is enabled
		jimport('joomla.client.helper');
		$ftpOptions = JClientHelper::getCredentials('ftp');
		if ($ftpOptions['enabled'] == 1) {
			// Connect the FTP client
			jimport('joomla.client.ftp');
			$ftp = &JFTP::getInstance(
				$ftpOptions['host'], $ftpOptions['port'], null,
				$ftpOptions['user'], $ftpOptions['pass']
			);

      	// Try determine if this is a directory
	      $parts  = preg_split('/\/|\\\\/', $path);
         $name   = array_pop($parts);
         $parent = implode( DS, $parts);
			
			$list = $ftp->listDetails( $parent);
			foreach( $list as $row) {
			   if ( $row['name'] == $name) {
      	      return $row;
			   }
			}
		}
   	
	   return $results;
	}

   //------------ fileperms ---------------
   /**
    * @brief Read the current permission of a file or directory
    */
   function fileperms( $path, $forceFTP=false)
	{
	   // If it is not possible to retreive the permission on the disk, retry using FTP.
	   $perms = @fileperms( $path);
	   if ( ($perms === false && !empty( $path)) 
	     || ($forceFTP && !empty( $path))
	      )
	   {
	      $row = Jms2WinPath::ftpFileDetails( $path);
		   if ( !empty( $row)) {
		      $rights = $row['rights']; // drwxr-xr-x
		      
		      $owner = 0;
		      if ( substr( $rights, 1, 1) == 'r') { $owner |= 0x04; }
		      if ( substr( $rights, 2, 1) == 'w') { $owner |= 0x02; }
		      if ( substr( $rights, 3, 1) == 'x') { $owner |= 0x01; }
		      $group = 0;
		      if ( substr( $rights, 4, 1) == 'r') { $group |= 0x04; }
		      if ( substr( $rights, 5, 1) == 'w') { $group |= 0x02; }
		      if ( substr( $rights, 6, 1) == 'x') { $group |= 0x01; }
		      $world = 0;
		      if ( substr( $rights, 7, 1) == 'r') { $world |= 0x04; }
		      if ( substr( $rights, 8, 1) == 'w') { $world |= 0x02; }
		      if ( substr( $rights, 9, 1) == 'x') { $world |= 0x01; }
		      
		      $perms = '0'.$owner.$group.$world;
		   }
	   }
	   
	   return $perms;
	}

	
   //------------ chmod ---------------
   /**
    * @brief Try to modify the permission of a file or a directory
	 * @param string/int	$mode	Octal value to change mode to, e.g. '0777', 0777 or 511
    */
   function chmod( $path, $mode)
	{
		// If no filename is given, we assume the current directory is the target
		if ($path == '') {
			$path = '.';
		}

		// Convert the mode to a string
		if (is_int($mode)) {
			$mode = decoct($mode);
		}

	   // If it is not possible to modify the permission directly
		if (!@ chmod($path, octdec($mode))) {
		   // Check if the FTP layer is enabled
   		jimport('joomla.client.helper');
   		$ftpOptions = JClientHelper::getCredentials('ftp');
   		if ($ftpOptions['enabled'] == 1) {
   			// Connect the FTP client
   			jimport('joomla.client.ftp');
   			$ftp = &JFTP::getInstance(
   				$ftpOptions['host'], $ftpOptions['port'], null,
   				$ftpOptions['user'], $ftpOptions['pass']
   			);

         	if ( $ftp->chmod($path, $mode)) {
         	   return true;
         	}
         	return false;
   		}
   		// If FTP is not enable
   		else {
      	   return false;
   		}
		}
		   
   	return true;
	}



   //------------ _write_file_content ---------------
   /**
    * @brief Write a content into the temporary file
    */
   function _write_file_content( $filename, $content)
   {
      $fp = fopen( $filename, "w");
      if ( !empty( $fp)) {
         fputs( $fp, $content);
         fclose( $fp);
   
         return true;
      }
      return false;
   }

   //------------ getServerInfo ---------------
   /**
    * @brief Try to collect server information concerning the owner id, group id.
    */
   function getServerInfo()
	{
		static $serverInfos;

		if (isset( $serverInfos )) {
			return $serverInfos;
		}

		$serverInfos = array();
		
      // Try to find a directory where we have write permissions
   	$tmp = uniqid('multisites_') . '.txt';;
   	$ssp = ini_get('session.save_path');
   	$jtp = getcwd() .DIRECTORY_SEPARATOR.'tmp';
   	$cur = getcwd();
   	$content = 'this file can be deleted';

   	// Try to find a writable directory where a "tmp" file can be written
   	$dir = Jms2WinPath::_write_file_content( "/tmp/$tmp", $content) ? '/tmp' : false;
   	if ( $dir === false) { if ( Jms2WinPath::_write_file_content( $ssp.DIRECTORY_SEPARATOR.$tmp, $content)) { $dir = $ssp; }}
   	if ( $dir === false) { if ( Jms2WinPath::_write_file_content( $jtp.DIRECTORY_SEPARATOR.$tmp, $content)) { $dir = $jtp; }}
   	if ( $dir === false) { if ( Jms2WinPath::_write_file_content( $cur.DIRECTORY_SEPARATOR.$tmp, $content)) { $dir = $cur; }}

   	// If it was possible to write a tmp file in a directory
   	if (($dir !== false))
   	{
         $return = -1;
   		$test = $dir.DIRECTORY_SEPARATOR.$tmp;
   
   		// Get owner's info (ID + Name)
   		$serverInfos['owner_id'] = fileowner($test);
         if ( function_exists( 'posix_getpwuid')) {
            $userinfo = posix_getpwuid( $serverInfos['owner_id']);
            if ( !empty( $userinfo['name'])) {
               $serverInfos['owner_name'] = $userinfo['name'];
            }
         }

   		// Get group infos (ID + name)
   		$serverInfos['group_id'] = filegroup($test);
         if ( function_exists( 'posix_getgrgid')) {
            $groupid   = getmygid();
            $groupinfo = posix_getgrgid($groupid);
            if ( !empty( $groupinfo['name'])) {
               $serverInfos['group_name'] = $groupinfo['name'];
            }
         }
   		
   		// Delete the test file
         if ( file_exists( $test)) {
            unlink( $test);
         }
   	}
   	
   	return $serverInfos;
	}

} // End class
