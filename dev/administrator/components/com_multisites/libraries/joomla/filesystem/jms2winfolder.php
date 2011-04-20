<?php
/**
 * @file       jms2winfolder.php
 * @brief      Jommla Multi Sites Folder API using the FTP layer when present
 *
 * @version    1.2.29
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
 * - V1.2.15 19-DEC-2009: Fix PHP 4.x compatibilty.
 * - V1.2.27 26-APR-2010: Add PHP 5.3 compatibility (remove split function)
 * - V1.2.29 08-MAY-2010: Fix split warning message
 */
 
defined('_JEXEC') or die( 'Restricted access' );


// ===========================================================
//             Jms2WinFolder_php4 class
// ===========================================================
/**
 * Implementation of a PHP4 & PHP5 & Joomla 1.5 & Joomla 1.6 compatibility
 */
class Jms2WinFolder_php4 extends JFolder
{

   // ------------- exists ----------------
   /**
    * @brief Check if a folder exists.
    * It uses the FTP layer when enabled and when the folder can not be found
    */
	function _exists($file)
	{
		if ( parent::exists($file)) {
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
	      $parts  = preg_split('/\/|\\\\/', $file);
         $name   = array_pop($parts);
         $parent = implode( '/', $parts);
			
			$results = $ftp->listDetails( $parent);
			// If there is a results
			if ( !empty( $results)) {
			   // Check if it has the "folder" attribute or "link"
   			foreach( $results as $row) {
   			   if ( $row['name'] == $name) {
   			      $flag = substr( $row['rights'], 0, 1); // drwxr-xr-x or lrwxrwxrwx
   			      
   			      // If directory
   			      if ( $flag == 'd') {
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
} // End class

// ===========================================================
//             Jms2WinFolder class
// ===========================================================

/**
 * @remarks: As Joomla 1.6! use "static" keyword only valid in PHP 5,
 * we have created a middle class that provide the PHP 4 & PHP 5 compatibility
 */

if ( version_compare( JVERSION, '1.6') >= 0) { $jms2win_php4_static = 'public static '; }
else                                         { $jms2win_php4_static = ''; }

eval( 'class Jms2WinFolder extends Jms2WinFolder_php4 { '
    . $jms2win_php4_static . 'function exists($path) { return Jms2WinFolder::_exists($path); }'
    . '}'
    ) ;
unset( $jms2win_php4_static);
