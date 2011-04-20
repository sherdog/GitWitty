<?php
/**
 * @file       site.php
 * @brief      Interface used by the Article sharing to select a website.
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


require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites' .DS. 'models' .DS. 'manage.php');
require_once( dirname( __FILE__) .DS. 'compat16.php');

/**
 * Renders a category element
 *
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */

class MultisitesElementSite extends MultisitesElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Site';
	
	function &getLastSiteValue()
	{
   	static $value = '';
   	return $value;
	}
	function setLastSiteValue( $newValue)
	{
	   $value =& MultisitesElementSite::getLastSiteValue();
	   $value =  $newValue;
	}

	function fetchElement($name, $value, &$node, $control_name)
	{
	   MultisitesElementSite::setLastSiteValue( $value);
	   
	   // Check if there is a "class" attribute in the <param ... class="xxx" />
		$class		= $this->getAttribute( $node, 'class');
		if (!$class) {
			$class = "inputbox";
		}

	   // Check if there is a "addScript" attribute in the <param ... addScript="xxx" />
		$addScript = $this->getAttribute( $node, 'addscript');
		if ( !empty( $addScript)) {
   		$document = & JFactory::getDocument();
   		$document->addScript( $addScript);
		}

	   // Check if there is a "onchange" attribute in the <param ... onchange="xxx" />
		$onchange = $this->getAttribute( $node, 'onchange');
		if ( !empty( $onchange)) {
   	   $onchange = ' onchange="' . $onchange .'"';
		}
		else {
   	   $onchange = '';
		}

	   // Check if there is a "multiple" attribute in the <param ... multiple="multiple" />
		$multiple = $this->getAttribute( $node, 'multiple');
		if ( !empty( $multiple)) {
   	   $multiple = ' multiple="' . $multiple .'"';
   	   $control_multiple = '[]';
		}
		else {
   	   $multiple = '';
   	   $control_multiple = '';
		}

	   // Check if there is a "size" attribute in the <param ... size="5" />
		$size = $this->getAttribute( $node, 'size');
		if ( !empty( $size)) {
   	   $size = ' size="' . $size .'"';
		}
		else {
   	   $size = '';
		}
		
		// Now research the list of sites to display in the combo box
		$model =  new MultisitesModelManage();
		$sites =& $model->getSites();

	   $rows = array();
	   if ( isset( $sites)) {
   	   foreach( $sites as $site) {
   	      // If there is DB defined to this site
   	      if ( isset( $site->db)  && isset( $site->dbprefix)
   	        && !empty( $site->db) && !empty( $site->dbprefix)
   	         )
   	      {
      	      $rows[ strtolower( $site->sitename)] = $site;
   	      }
   	   }
   	   ksort( $rows);
	   }
		

	   $opt = array();
		if ( empty( $multiple)) {
		   $opt[] = JHTML::_('select.option', '0', '- '.JText::_('Select Site').' -');
		}
      $opt[] = JHTML::_('select.option', ':master_db:', '< Master Site >');
	   foreach( $rows as $site) {
   		$opt[] = JHTML::_('select.option', $site->id, $site->sitename . ' | '. $site->id);
	   }

		// If Joomla 1.6, control_name is already ok
		if ( substr( $control_name, -2) == '[]') {
		   $select_name = $control_name;
		}
		// If Joomla 1.5, 
		else {
		   $select_name = $control_name.'['.$name.']'.$control_multiple;
		}
		return JHTML::_( 'select.genericlist',  $opt, $select_name,
		                 'class="'.$class.'"' .$multiple .$size . $onchange, 
		                 'value', 'text', 
		                 $value, $control_name.$name );
	}
} // End Class


// ===========================================================
//             Joomla 1.5 / 1.6 compatibility
// ===========================================================

// If Joomla 1.6
if ( version_compare( JVERSION, '1.6') >= 0) { 
   class JFormFieldSite extends MultisitesElementSite
   {
   	protected $type = 'Site';
   }
}
// Else: Default Joomla 1.5
else {
   class JElementSite extends MultisitesElementSite {}
}