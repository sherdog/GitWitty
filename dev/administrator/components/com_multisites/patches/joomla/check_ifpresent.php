<?php
/**
 * @file       check_ifpresent.php
 * @brief      Checks if a file is present.
 *             Is used to test if the 'define_multisites.php' file is present.
 *
 * @version    1.2.10
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
 * - V1.0.0  14-JUL-2008: Initial version
 * - V1.1.0  07-NOV-2008: Deploy the patch.
 *                        With V1.1.0, the file multisites.php is added and must be deployed
 *                        even when define_multisites.php is already present.
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ _checkIfPresent ---------------
/**
 * check if a file is present
 */
function jms2win_checkIfPresent( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[NOK]|File Not Found'
	         .'|[ACTION]|Add the file';
	}

   $str = file_get_contents( $filename);
   
   // if 'MultisitesLetterTree::getLetterTreeDir' is present
   $pos = strpos( $str, 'MultisitesLetterTree::getLetterTreeDir');
   if ($pos === false)  { $wrapperIsPresent = false; }
   else                 { $wrapperIsPresent = true; }
   
   $result = "";
   $rc = '[OK]|File is present';
   if ( !$wrapperIsPresent) {
	   $rc = '[NOK]';
      $result .= JText::_( 'The new Multisites directory structure to allow creating several thousand of slave sites from the front-end is not present');

      $jms_vers = MultisitesController::_getVersion();
      if ( version_compare( $jms_vers, '1.2.30') < 0) {
         $result .= '|[ACTION]';
         $result .= '|Download the <a href="http://www.jms2win.com/get-latest-version">latest jms version</a>.';
         $result .= '|JMS version 1.2.30 or higher is required to install this patch.';
      }
      else {
         $result .= '|[ACTION]';
         $result .= '|Install the new multisite detection';
      }
   }
   
   return $rc .'|'. $result;
}

//------------ _actionIfPresent ---------------
function jms2win_actionIfPresent( $model, $file)
{
   return $model->_deployPatches();
}
