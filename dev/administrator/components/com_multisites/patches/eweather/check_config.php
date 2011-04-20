<?php
/**
 * @file       check_config.php
 * @brief      Check if the eWeather configuration file name computation is present based on the site ID.
 * @version    1.2.16
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008 Edwin2Win sprlu - all right reserved.
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
 * - V1.2.16 01-NOV-2009: Initial version
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkeWeatherConfig ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_ID') ...
 *   is present
 */
function jms2win_checkeWeatherConfig( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[IGNORE]|File Not Found';
	}
	
   $str = file_get_contents( $filename);
   
   // if 'MULTISITES_ID' is present
   $pos = strpos( $str, 'MULTISITES_ID');
   if ($pos === false) $wrapperIsPresent = false;
   else                $wrapperIsPresent = true;
   
   $result = "";
   $rc = '[OK]';
   if ( !$wrapperIsPresent) {
	   $rc = '[NOK]';
      $result .= JText::_( 'The computation of the configuration file name based on the site ID is not present.');
      $result .= '|[ACTION]';
      $result .= '|Replace the line that declare the configuration file name by 13 line that compute the name based on the site identifier.';
   }
   
   return $rc .'|'. $result;
}



//------------ jms2win_actioneWeatherConfig ---------------
/**
 * @brief Install the patch
 */
function jms2win_actioneWeatherConfig( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'eweather' .DS. 'patch_config.php');
   if ( $patchStr === false) {
      return false;
   }

//	$filename = JPATH_ROOT.DS.$file;
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
   $content = file_get_contents( $filename);
   if ( $content === false) {
      return false;
   }
   
   // Search/Replace the statement
   /*
      ===========
      Search for:
      ===========
		........
		........
      $this->configFileName_ = JPATH_ADMINISTRATOR.DS."components/com_eweather/eweather.config.data.php";
		........
		........
		........
	   }
		?>
      
      ===========
      and Replace by:
      ===========
		........
		........
      if ( defined( 'MULTISITES_ID')) {
         jimport( 'joomla.filesystem.file');
   		$masterFilename = JPATH_ADMINISTRATOR.DS."components/com_eweather/eweather.config.data.php";
   		$slaveFilename  = JPATH_ADMINISTRATOR.DS.'components/com_eweather/eweather.config.data.' .MULTISITES_ID. '.php';
         if ( !JFile::exists( $slaveFilename) &&  JFile::exists( $masterFilename)) {
            JFile::copy( $masterFilename, $slaveFilename);
         }
   		$this->configFileName_ = $slaveFilename;
      }
      else {
   		$this->configFileName_ = JPATH_ADMINISTRATOR.DS."components/com_eweather/eweather.config.data.php";
      }
		........
		........
		........
	   }
	   }
		?>

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n .... $this->configFileName_ = JPATH_ADMINISTRATOR.DS."components/com_eweather/eweather.config.data.php"; ...\n
      p0                                                                               p1                             p2
      
      Produce
      begin -> p0 + INSERT PATCH + (p2+1) -> end
      
    */
   
   // p1: Search for "eweather.config.data.php"
   $p1 = strpos( $content, "eweather.config.data.php");
   if ( $p1 === false) {
      return false;
   }
   
   // P0: Go to Begin of line
   for ( $p0 = $p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
   $p0++;

   // p2: Search for end of line
   for ( $p2=$p1; $content[$p2] != "\n"; $p2++);
 
 
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p0)
           . $patchStr
           . substr( $content, $p2+1)
           ;

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

   return true;
}

