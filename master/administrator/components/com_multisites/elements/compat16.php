<?php
/**
 * @file       compat16.php
 * @brief      Interface to provide a Joomla 1.5 and 1.6 compatibility.
 *
 * @version    1.2.34
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  JMS Multi Sites
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
 * - V1.1.5  20-DEC-2008: Save the current value of the site id to allow customized the article links, ...
 * - V1.2.34 17-JUL-2010: Add multisites selection
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();


// If Joomla 1.6
if ( version_compare( JVERSION, '1.6') >= 0) { 
   class MultisitesElement extends JFormField
   {
   	function getAttribute( $element, $name)
   	{
   	   return $element[$name] ? (string) $element[$name] : '';
   	}

   	protected function getInput()
   	{
   		$control_name   = &$this->name;
   		$name           = ''; //&$this->fieldname;
   		$value          = &$this->value;
   		$node           = &$this->element;
   		
   		return $this->fetchElement( $name, $value, $node, $control_name);
   	}
   }
}
// Else: Default Joomla 1.5
else {
   class MultisitesElement extends JElement
   {
   	function getAttribute( $node, $name)
   	{
   	   $value = $node->attributes( $name);
   	   if ( !empty( $value)) {
   	      return $value;
   	   }
   	   return '';
   	}
   }
}