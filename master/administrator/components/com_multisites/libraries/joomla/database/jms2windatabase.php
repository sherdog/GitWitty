<?php
/**
 * @file       jms2winmodel.php
 * @brief      Wrapper to JDatabase to allow switch the reall database connection depending on
 *             on the context.
 *
 * @version    1.2.47
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
 * - V1.2.47 02-FEB-2011: Fix PHP Syntax error
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access or JMS2Win patches are not installed');

jimport('joomla.database.database');




// ===========================================================
//             Jms2WinModel class
// ===========================================================
class Jms2WinDatabase extends JDatabase
{
   function writeTrace()
   {
      require_once( JPATH_MUTLISITES_COMPONENT .DS. 'classes' .DS. 'debug.php');
      $prevStandalone   = Debug2Win::isStandalone();
      $prevFilename     = Debug2Win::getFileName();;
      $prevDebug        = Debug2Win::isDebug();
      Debug2Win::enableStandalone();   // Write the log in administrator/components/com_multisites/classes/logs
      Debug2Win::setFileName( 'database.log.php');
      Debug2Win::enableDebug();        // Remove the comment to enable the debugging

      // Function available from PHP 4.3.0
      $arr = debug_backtrace();
      Debug2Win::debug( var_export( $arr, true));

      Debug2Win::setFileName( $prevFilename);
      if ( !$prevDebug) {
         Debug2Win::disableDebug( $prevDebug);        // Remove the comment to enable the debugging
      }
      if ( !$prevStandalone) {
         Debug2Win::disableStandalone();
      }
   }
} // End Class
?>