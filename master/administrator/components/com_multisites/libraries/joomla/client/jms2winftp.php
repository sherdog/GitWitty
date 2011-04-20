<?php
/**
 * @file       jms2winftp.php
 * @brief      Jommla Multi Sites FTP API using the FTP layer when present
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
 * - V1.2.27 26-APR-2010: Add PHP 5.3 compatibility (remove split function)
 * - V1.2.29 08-MAY-2010: Fix split warning message
 */
 
defined('_JEXEC') or die( 'Restricted access' );

 

jimport('joomla.client.ftp');

// ===========================================================
//             Jms2WinFTP class
// ===========================================================

// If disabled MULTISITES Redirect FTP
if ( !defined( 'MULTISITES_REDIRECT_FTP') || !(MULTISITES_REDIRECT_FTP)) {
   class Jms2WinFTP extends JFTP {
      function fileperms( $path)    { return false; }
      function fileDetails( $path)  { $none = array(); return $none; }
   }
}
// If overwrite the standard Joomla FTP to allow redirect FTP to another connection
else {
class Jms2WinFTP extends JFTP
{

   //------------ getInstance ---------------
   /**
    * @brief Factory that allow replace the original FTP instance by a new Jms2Win instance.
    * The objective is to allow all the joomla processing benefit of different FTP target
    * in case of JFolder::copy, JFiles::copy, write, .... have a target other than the current website directory (JPATH_ROOT)
    */
	function &getInstance($host = '127.0.0.1', $port = '21', $options = null, $user = null, $pass = null, $path_root=null, $ftp_root=null,
	                      $orig_ftp_enable=null, $orig_ftp_root=null)
	{
	   $instance =& parent::getInstance( $host, $port, $options, $user, $pass);
	   // If this is not an instance of this class
	   if ( is_a( $instance, 'Jms2WinFtp')) {}
	   // This is an original instance that must be replaced by a Jms2Win one
	   else {
	      // Save current values
	      $orig_instance = $instance;
   		if ( is_null( $orig_ftp_root)) {
      		$config =& JFactory::getConfig();
   	      $orig_ftp_root = $config->getValue('config.ftp_root');
   		}
	      
	      // replace the current instance
   		$instance = new Jms2WinFtp($options);
   		// Save original values
	      $instance->_orig_instance =& $orig_instance;
	      $instance->_orig_host     = $host;
	      $instance->_orig_port     = $port;
	      $instance->_orig_options  = $options;
	      $instance->_orig_user     = $user;
	      $instance->_orig_pass     = $pass;
	      $instance->_orig_ftp_enable = $orig_ftp_enable;
	      $instance->_orig_ftp_root   = $orig_ftp_root;
	      // Set new values
//	      $instance->_path_root      = $path_root;
//	      $instance->_real_path_root = $this->_real_path( $path_root);
//	      $instance->_ftp_root       = $ftp_root;
	      $instance->_new_dir        = $path_root;
	      $instance->_new_real_dir   = $this->_real_path( $path_root);
	      $instance->_new_ftp        = $ftp_root;
	   }
	   return $instance;
	}
	
   //------------ restoreOriginalInstance ---------------
   /**
    * @brief Restore the original instance.
    */
	function restoreOriginalInstance()
	{
	   if ( !empty( $this->_orig_instance)) {
   	   $instance =& parent::getInstance( $this->_orig_host, $this->_orig_port, $this->_orig_options, $this->_orig_user, $this->_orig_pass);
   	   if ( is_a( $instance, 'Jms2WinFTP')) {
   	      $instance =& $this->_orig_instance;
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


   //------------ _replace_maxlen ---------------
	function _replace_maxlen( $maxlen_array, $replace_str, $path)
	{
	   $max_indice = 0;
	   for( $i=1; $i<count( $maxlen_array); $i++) {
	      if ( strlen( $maxlen_array[ $i]) > strlen( $maxlen_array[ $max_indice])) {
	         $max_indice = $i;
	      }
	   }
	   
      $result = str_replace( $maxlen_array[ $max_indice], $replace_str, $path);
      return $result;
	}

   //------------ normalizedPath ---------------
   /**
    * Decision matrix
    * The "path" starts with
    *    Orig ftp root | Orig dir root | New ftp root | new dir root |=> action
    * 00     N         |       N       |     N        |      N       |  Don't touch
    * 01     N         |       N       |     N        |      Y       |  replace New Dir by New FTP
    * 02     N         |       N       |     Y        |      N       |  Don't touch
    * 03     N         |       N       |     Y        |      Y       |  Replace max len( New dir, New FTP) by New FTP
    * 04     N         |       Y       |     N        |      N       |  replace Orig Dir by New FTP
    * 05     N         |       Y       |     N        |      Y       |  replace max len( Orig Dir, New Dir) by New FTP
    * 06     N         |       Y       |     Y        |      N       |  replace max len( Orig Dir, New FTP) by New FTP
    * 07     N         |       Y       |     Y        |      Y       |  replace max len( Orig Dir, New Dir, New FTP) by New FTP
    *
    * 08     Y         |       N       |     N        |      N       |  Restore Orig Dir + (when New Dir exists then replace New Dir by New FTP ELSE replace Orig FTP by New FTP)
    * 09     Y         |       N       |     N        |      Y       |  replace New Dir by New FTP
    * 10     Y         |       N       |     Y        |      N       |  Don't touch
    * 11     Y         |       N       |     Y        |      Y       |  Replace max len( Orig FTP, New dir, New FTP) by New FTP
    * 12     Y         |       Y       |     N        |      N       |  Replace max len( Orig FTP, Orig dir) by New FTP
    * 13     Y         |       Y       |     N        |      Y       |  replace max len( Orig FTP, Orig Dir, New Dir) by New FTP
    * 14     Y         |       Y       |     Y        |      N       |  replace max len( Orig FTP, Orig Dir, New FTP) by New FTP
    * 15     Y         |       Y       |     Y        |      Y       |  replace max len( Orig FTP, Orig Dir, New Dir, New FTP) by New FTP
    *
    */
	function normalizedPath( $path, $normalize=true)
	{
	   if ( !$normalize) {
	      return $path;
	   }

	   $result = $path;

	   // Normalize the path notation - always use the DS directory separator
      $clean_path     = JPath::clean( $path);
      $clean_orig_dir = JPath::clean( JPATH_ROOT);
      if ( !empty( $this->orig_ftp_root)) { $clean_orig_ftp = JPath::clean( $this->orig_ftp_root); }
      else                                { $clean_orig_ftp = null; }

/*
      if ( !empty( $this->_ftp_root))     { $clean_new_ftp = JPath::clean( $this->_ftp_root); }
      else                                { $clean_new_ftp = null; }

      if ( !empty( $this->_path_root))    { $clean_new_dir = JPath::clean( $this->_path_root); }
      else                                { $clean_new_dir = null; }
*/

      if ( !empty( $this->_new_ftp))      { $clean_new_ftp = JPath::clean( $this->_new_ftp); }
      else                                { $clean_new_ftp = null; }

      if ( !empty( $this->_new_dir))      { $clean_new_dir = JPath::clean( $this->_new_dir); }
      else                                { $clean_new_dir = null; }

      // Compute the boolean value for the different parameters
      $is_orig_ftp = false;
      if ( !empty( $clean_orig_ftp)) {
         if ( substr( $clean_path, 0, strlen( $clean_orig_ftp)) == $clean_orig_ftp) {
            $is_orig_ftp = true;
         }
      }

      $is_orig_dir = false;
      if ( !empty( $clean_orig_dir)) {
         if ( substr( $clean_path, 0, strlen( $clean_orig_dir)) == $clean_orig_dir) {
            $is_orig_dir = true;
         }
      }

      $is_new_ftp = false;
      if ( !empty( $clean_new_ftp)) {
         if ( substr( $clean_path, 0, strlen( $clean_new_ftp)) == $clean_new_ftp) {
            $is_new_ftp = true;
         }
      }


      $is_new_dir = false;
      if ( !empty( $clean_new_dir)) {
         if ( substr( $clean_path, 0, strlen( $clean_new_dir)) == $clean_new_dir) {
            $is_new_dir = true;
         }
      }
      
      // Process the decision matrix
      // Case 0->7
      if ( !$is_orig_ftp) {
         // Case 0->3
         if ( !$is_orig_dir) {
            // Case 0->1
            if ( !$is_new_ftp) {
               // Case 0
               if ( !$is_new_dir) {
            	   // Don't touch
            	   return( $path);
               }
               // Case 1
               else {
            	   // replace New Dir by New FTP
         	      $result = str_replace( $clean_new_dir, $clean_new_ftp, $clean_path);
               }
            }
            // Case 2->3
            else {
               // Case 2
               if ( !$is_new_dir) {
            	   // Don't touch
            	   return( $path);
               }
               // Case 3
               else {
                  // Replace max len( New dir, New FTP) by New FTP
         	      $result = Jms2WinFTP::_replace_maxlen( array( $clean_new_dir, $clean_new_ftp), $clean_new_ftp, $clean_path);
               }
            }
         }
         // Case 4->7
         else {
            if ( !$is_new_ftp) {
               // Case 4
               if ( !$is_new_dir) {
            	   // replace Orig Dir by New FTP
         	      $result = str_replace( $clean_orig_dir, $clean_new_ftp, $clean_path);
               }
               // Case 5
               else {
                  // replace max len( Orig Dir, New Dir) by New FTP
         	      $result = Jms2WinFTP::_replace_maxlen( array( $clean_orig_dir, $clean_new_dir, $clean_new_ftp), $clean_new_ftp, $clean_path);
               }
            }
            else {
               // Case 6
               if ( !$is_new_dir) {
                  // replace max len( Orig Dir, New FTP) by New FTP
         	      $result = Jms2WinFTP::_replace_maxlen( array( $clean_orig_dir, $clean_new_ftp), $clean_new_ftp, $clean_path);
               }
               // Case 7
               else {
                  // replace max len( Orig Dir, New Dir, New FTP) by New FTP
         	      $result = Jms2WinFTP::_replace_maxlen( array( $clean_orig_dir, $clean_new_dir, $clean_new_ftp), $clean_new_ftp, $clean_path);
               }
            }
         }
      }
      // Case 8->15
      else {
         // Case 8->11
         if ( !$is_orig_dir) {
            // Case 8->9
            if ( !$is_new_ftp) {
               // Case 8
               if ( !$is_new_dir) {
                  // Restore Orig Dir + (when New Dir exists then replace New Dir by New FTP ELSE replace Orig root by New FTP)
         	      $str = str_replace( $clean_orig_ftp, $clean_orig_dir, $clean_path);
                  if ( substr( $str, 0, strlen( $clean_new_dir)) == $clean_new_dir) {
                     // replace New Dir by New FTP
            	      $result = str_replace( $clean_new_dir, $clean_new_ftp, $str);
                  }
                  else {
                     // ELSE replace Orig FTP by New FTP
            	      $result = str_replace( $clean_orig_ftp, $clean_new_ftp, $clean_path);
                  }
         	      
               }
               // Case 9
               else {
            	   // replace New Dir by New FTP
         	      $result = str_replace( $clean_new_dir, $clean_new_ftp, $clean_path);
               }
            }
            // Case 10->11
            else {
               // Case 10
               if ( !$is_new_dir) {
            	   // Don't touch
            	   return( $path);
               }
               // Case 11
               else {
                  // Replace max len( Orig FTP, New dir, New FTP) by New FTP
         	      $result = Jms2WinFTP::_replace_maxlen( array( $clean_orig_ftp, $clean_new_dir, $clean_new_ftp), $clean_new_ftp, $clean_path);
               }
            }
         }
         // Case 12->15
         else {
            if ( !$is_new_ftp) {
               // Case 12
               if ( !$is_new_dir) {
                  // Replace max len( Orig FTP, Orig dir) by New FTP
         	      $result = Jms2WinFTP::_replace_maxlen( array( $clean_orig_ftp, $clean_orig_dir), $clean_new_ftp, $clean_path);
               }
               // Case 13
               else {
                  // replace max len( Orig FTP, Orig Dir, New Dir) by New FTP
         	      $result = Jms2WinFTP::_replace_maxlen( array( $clean_orig_ftp, $clean_orig_dir, $clean_new_dir), $clean_new_ftp, $clean_path);
               }
            }
            else {
               // Case 14
               if ( !$is_new_dir) {
                  // replace max len( Orig FTP, Orig Dir, New FTP) by New FTP
         	      $result = Jms2WinFTP::_replace_maxlen( array( $clean_orig_ftp, $clean_orig_dir, $clean_new_ftp), $clean_new_ftp, $clean_path);
               }
               // Case 15
               else {
                  // replace max len( Orig FTP, Orig Dir, New Dir, New FTP) by New FTP
         	      $result = Jms2WinFTP::_replace_maxlen( array( $clean_orig_ftp, $clean_orig_dir, $clean_new_dir, $clean_new_ftp), $clean_new_ftp, $clean_path);
               }
            }
         }
      }

      // Always use forward slash by FTP
      $parts   = preg_split('/\/|\\\\/', $result);
      $result  = implode( '/', $parts);

      return $result;

/*
      
      // Restore the original path
	   if ( empty( $this->orig_ftp_root)) {
	      if ( substr( $clean_path, 0, strlen( $clean_path_root)) == $clean_path_root) {}
	      else {
	         // If the path already start with the "new FTP root" then don't change the path
	         $clean_ftp_root = JPath::clean( $this->_ftp_root);
	         if ( !empty( $this->_ftp_root) && substr( $clean_path, 0, strlen( $clean_ftp_root)) == $clean_ftp_root) {}
	         // Otherwise
	         else {
	            // restore the current root path in front of the path
      	      $path = JPATH_ROOT . '/' . ltrim( $path, '/');
	         }
	      }
	   }
	   else {
	      // Restore the original path
	      $path = str_replace( $this->orig_ftp_root, JPATH_ROOT, $path);
	   }
	   
	   $real_path = $this->_real_path( $path);
      // If real path can be access using the "original" FTP connection
      if ( substr( $real_path, 0, strlen( JPATH_ROOT)) == JPATH_ROOT) {
      }
      // Suppose that it can be access from the new "path root"
      else {
         // $real_path_root
      }

      // Apply new FTP root
      if ( !empty( $this->_path_root)) {
         $path = JPath::clean(str_replace( $this->_path_root, $this->_ftp_root, $path), '/');
      }
      else {
         // Check if the FTP root path is present only once in the URL
         $pos = strpos( $path, $this->_ftp_root);
         if ( $pos === false) {}
         else {
            // If new FTP root path is present only once in the path
            $p2 = strpos( $path, $this->_ftp_root, $pos+1);
            if ( $p2 === false) {
               // Then use it
               $path = substr( $path, $pos);
            }
            // If the new path is present more than once
            else {
               // Just try replace the current path with new FTP root path
               $path = JPath::clean(str_replace( JPATH_ROOT, $this->_ftp_root, $path), '/');
            }
         }
      }
*/      
	}
	

   //------------ fileDetails ---------------
   /**
    * @brief return the file details of a specific file.
    * It search for all the files present in the "folder" and try retreive the one that match the file name.
    * @return
    * - an empty array when the path is not found;
    * - an array with file detail information.
    */
   function fileDetails( $path)
	{
   	$none = array();
   	
   	if ( empty( $path )) {
   	   return $none;
   	}
   	
   	// Try determine if this is a directory
      $parts   = preg_split('/\/|\\\\/', $path);
      $name    = array_pop($parts);          // the name can be a directory or a file
      $dir     = implode( '/', $parts);      // Always use forward slash by FTP
			
		$list = parent::listDetails( $dir);
		foreach( $list as $row) {
		   if ( $row['name'] == $name) {
   	      return $row;
		   }
		}
   	
	   return $none;
	}


   //------------ fileperms ---------------
   /**
    * @brief Read the current permission of a file or directory.
    * @return
    * - FALSE when the permission can not be retreive (for example file or directory not found);
    * - A string with octal value like '0777'
    */
   function fileperms( $path)
	{
	   $result = false;
	   
      $row = Jms2WinFTP::fileDetails( $path);
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
	      
	      $result = '0'.$owner.$group.$world;
	   }
	   
	   return $result;
	}




	function chmod($path, $mode, $normalize=true)      { return parent::chmod( $this->normalizedPath( $path, $normalize), $mode);	}
	function create($path, $normalize=true)            { return parent::create( $this->normalizedPath( $path, $normalize));	}
	function mkdir($path, $normalize=true) {
	   $arr = debug_backtrace();
	   return parent::mkdir( $this->normalizedPath( $path, $normalize));
	}
	function listDetails($path = null, $type = 'all')  { return parent::listDetails( $this->normalizedPath( $path), 'all');	}



} // End class
}
