<?php
/**
 * @file       debug.php
 * @brief      Independant logging system.
 *             When integrated into Joomla, it write the messages into the JLog system.
 * @version    1.2.27
 * @author     Edwin CHERONT     (cheront@edwin2win.com)
 *             Edwin2Win sprlu   (www.edwin2win.com)
 * @copyright  (C) 2008-2010 Edwin2Win sprlu - all right reserved.
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
 * - V1.1.0 29-SEP-2008: Add Block Comment when a debug message contain a new line.
 * - V1.2.0 28-JAN-2009: Use the Joomla FTP Layer when it is present to write the log file.
 * - V1.2.27 26-APR-2010: Add PHP 5.3 compatibility (remove split function)
 */

// Check to ensure this file is included in Edwin2Win application
if( !defined( '_EDWIN2WIN_' ) && !defined( '_JEXEC' ) ) die( 'Restricted access.' );



// ===========================================================
//             Debug2Win class
// ===========================================================
// Check the class is not already declare by another Edwin2Win product. (ie. Joomal Multi Sites)
if ( !class_exists( 'Debug2Win'))
{
   class Debug2Win
   {
      // ***********************************
      //!@name    Private functions
      // ***********************************
      //!@{
      
      //------------ _getDebugLevel ---------------
      function &_getDebugLevel()
      {
   		static $instance;
   
   		if (!isset( $instance )) {
   			$instance = 0;
   		}
   		
   		return $instance;
      }
   
      //------------ _getDebugPrefix ---------------
      function &_getDebugPrefix()
      {
   		static $instance;
   
   		if (!isset( $instance )) {
   			$instance = "";
   		}
   		
   		return $instance;
      }
      
      //------------ _setDebugPrefix ---------------
      function _setDebugPrefix( $new_prefix='')
      {
         $prefix =& Debug2Win::_getDebugPrefix();
         $prefix = $new_prefix;
      }
   
      //!@}
      // ***********************************
      //!@name    Public functions
      // ***********************************
      //!@{
      //!@}
   
      //------------ getFileName ---------------
      function &getFileName()
      {
   		static $instance;
   
   		if (!isset( $instance )) {
   			$instance = 'debug.log.php';
   		}
   		
   		return $instance;
      }
      //------------ getFileName ---------------
      function setFileName( $filename)
      {
   		$instance =& Debug2Win::getFileName();
   		$instance = $filename;
      }
   
      //------------ isDebug ---------------
      function &isDebug()
      {
   		static $instance;
   
   		if (!isset( $instance )) {
   			$instance = false;
   		}
   		
   		return $instance;
      }
      //------------ enableDebug ---------------
      function enableDebug()
      {
   		$instance =& Debug2Win::isDebug();
   		$instance = true;
      }
      //------------ disableDebug ---------------
      function disableDebug()
      {
   		$instance =& Debug2Win::isDebug();
   		$instance = false;
      }
   
      //------------ isStandalone ---------------
      function &isStandalone()
      {
   		static $instance;
   
   		if (!isset( $instance )) {
   			$instance = false;
   		}
   		
   		return $instance;
      }
      //------------ enableStandalone ---------------
      /**
       * @brief Standalone means not using the Joomla JLog feature.
       */
      function enableStandalone()
      {
   		$instance =& Debug2Win::isStandalone();
   		$instance = true;
      }
      //------------ disableStandalone ---------------
      function disableStandalone()
      {
   		$instance =& Debug2Win::isStandalone();
   		$instance = false;
      }
   
      //------------ debug ---------------
      function debug($message, $blockComment=false)
      {
         $isDebug =& Debug2Win::isDebug();
         if ( !$isDebug) {
            return;
         }
         
         $filename      =  Debug2Win::getFileName();
         $isStandalone  =& Debug2Win::isStandalone();
         $prefix        =& Debug2Win::_getDebugPrefix();
         $level         =& Debug2Win::_getDebugLevel();
         
         // If not standalone Joomla 1.5
         if ( !$isStandalone && function_exists( 'jimport')) {
            // This means that JLog is available
      		jimport('joomla.error.log');
      		$log = &JLog::getInstance( $filename);
      		
            $comment = $prefix . str_repeat(" ", $level * 3) . $message;
      		$log->addEntry(array('comment' => $comment));
      		return;
         }

         // If Joomla, Read the FTP Layer parameters
         $FTPOptions = array();
         if ( defined( 'JPATH_LIBRARIES')) {
            require_once( JPATH_LIBRARIES.DS.'joomla'.DS.'import.php');
      		jimport( 'joomla.filesystem.path' );
      		jimport( 'joomla.filesystem.folder' );
      		jimport( 'joomla.filesystem.file' );
   
   
      		// Read the Joomla 'master' configuration file
      		jimport('joomla.client.helper');
            $parts = explode( DIRECTORY_SEPARATOR, dirname( __FILE__));
            array_pop( $parts );
            array_pop( $parts );
            array_pop( $parts );
            array_pop( $parts );
            $parts[] = 'configuration.php';
            $configname = implode( DIRECTORY_SEPARATOR, $parts );
            $configdata = JFile::read( $configname);
            $p1 = strpos( $configdata, 'class JConfig {');
            $config = array();
            if ( $p1 === false) { }
            else {
               $p1 += 15;
               $statements = substr( $configdata, $p1);
               $str = str_replace( 'var ', '', $statements);
               $lines = explode( "\n", $str);
               foreach ($lines as $line) {
                  $variable = explode( '=', $line);
                  if ( count( $variable) == 2) {
                     $value = trim($variable[1]);
                     $value = ltrim( $value, "'");
                     $value = rtrim( $value, "';");
                     $config[trim($variable[0])] = $value;
                  }
               }
            }
   
            // Compute $FTPOptions to simulate FTP Credential
            if ( isset( $config['$ftp_enable']))   { $FTPOptions['enabled'] = $config['$ftp_enable'];   }
            if ( isset( $config['$ftp_host']))     { $FTPOptions['host'] = $config['$ftp_host'];   }
            if ( isset( $config['$ftp_port']))     { $FTPOptions['port'] = $config['$ftp_port'];   }
            if ( isset( $config['$ftp_user']))     { $FTPOptions['user'] = $config['$ftp_user'];   }
            if ( isset( $config['$ftp_pass']))     { $FTPOptions['pass'] = $config['$ftp_pass'];   }
            if ( isset( $config['$ftp_root']))     { $FTPOptions['root'] = $config['$ftp_root'];   }
         }

         // When Stanalone or Joomla is not present,
         // Check that Logs directory exists
         $dir = dirname( __FILE__);
         $log_path = $dir .DIRECTORY_SEPARATOR. 'logs';
         if ( !is_dir($log_path)) {
            if ( class_exists( 'JFolder')) {
               JFolder::create( $log_path);
            }
            else {
               mkdir( $log_path, 0755);
            }
         }
   		// If the destination directory doesn't exist we need to create it
         $myFilename = $log_path .DIRECTORY_SEPARATOR. $filename;

         // If Joomla FTP Layer Enabled
   		if ( !empty( $FTPOptions) && $FTPOptions['enabled'] == 1) {
   			// Connect the FTP client
   			jimport('joomla.client.ftp');
   			$ftp = & JFTP::getInstance($FTPOptions['host'], $FTPOptions['port'], null, $FTPOptions['user'], $FTPOptions['pass']);
   
            if ( !file_exists( $myFilename)){
               $buffer = "<?php\r\ndie( 'Access forbidden');\r\n";
            }
            else {
               $buffer = JFile::read( $myFilename);
            }
            
            if ( $blockComment) {
               $buffer .= "/*" . $prefix . str_repeat(" ", $level * 3) . $message . "*/\r\n";
            }
            else {
               $buffer .= "//" . $prefix . str_repeat(" ", $level * 3) . $message . "\r\n";
            }
            
   			// Translate path for the FTP account and use FTP write buffer to file
   			$myFilename = JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $myFilename), '/');
   			$ret = $ftp->write($myFilename, $buffer);
   		}
   		// If Not Joomla or Joomla with FTP Layer Disabled
   		else {
            if ( !file_exists( $myFilename)){
               $handle = fopen( $myFilename, "a+");
               // Add a PHP Header with a die() statement to avoid reading from a browser
               // This should protect the file from HACKING and access potential sensitive data
               fwrite($handle, "<?php\r\ndie( 'Access forbidden');\r\n");
            }
            else {
               $handle = fopen( $myFilename, "a+");
            }
         
            if ( $blockComment) {
               fwrite($handle, "/*" . $prefix . str_repeat(" ", $level * 3) . $message . "*/\r\n");
            }
            else {
               fwrite($handle, "//" . $prefix . str_repeat(" ", $level * 3) . $message . "\r\n");
            }
            fclose($handle);
   		}
      }
      
      //------------ debug_Start ---------------
      /**
       * Called at the begin of a function.
       * It write the message into the log file and increment the indent level
       *
       * @param $message the message to write into the log file
       */
      function debug_Start($message, $dbg_prefix="")
      {
         $level = Debug2Win::_getDebugLevel();
         Debug2Win::_setDebugPrefix( $dbg_prefix);
         Debug2Win::debug( $message);
         $level++;
      }
      
      //------------ debug_Stop ---------------
      function debug_Stop($message)
      {
         $level = Debug2Win::_getDebugLevel();
         $level--;
         Debug2Win::debug( $message);
      }
      
      
   } // End Class
}
