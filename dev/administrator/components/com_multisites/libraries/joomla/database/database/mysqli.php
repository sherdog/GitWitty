<?php
/**
 * @file       mysqli.php
 * @brief      MySQLi driver that allow replace the "protected" table prefix
 *
 * @version    1.2.35
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  JMS Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2010 Edwin2Win sprlu - all right reserved.
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
 * - V1.2.34 17-JUL-2010: Initial version
 * - V1.2.35 27-JUL-2010: Set the "protected" error information with new values.
 */
 
// No direct access
defined('_JEXEC') or die( 'Restricted access' );


require_once( JPATH_LIBRARIES .DS. 'joomla' .DS. 'database' .DS. 'database' .DS. 'mysqli.php');

// ===========================================================
//             MultisitesDatabaseMySQLi class
// ===========================================================
class MultisitesDatabaseMySQLi extends JDatabaseMySQLi
{

   // ------------- setPrefix ----------------
	/**
	 * @brief   Replace the current table prefix and return the previous value
	 *
	 * @param	$table_prefix	The new table prefix
	 * @return	Return the previous table prefix value.
	 */
	function setPrefix( $table_prefix)
	{
	   $result = $this->_table_prefix;
	   $this->_table_prefix = $table_prefix;

		return $result;
	}

   // ------------- setErrorInfo ----------------
   /**
    * @brief set the MySQL error information
    */
	function setErrorInfo( $errorNum, $errorMsg)
	{
		$this->_errorNum = $errorNum;
		$this->_errorMsg = $errorMsg;
	}
}

