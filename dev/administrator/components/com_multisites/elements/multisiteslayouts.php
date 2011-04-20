<?php
/**
 * @file       multisiteslayouts.php
 * @brief      Return the list of "templates" available.
 *
 * @version    1.2.36
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
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
 * - V1.2.20 24-JAN-2010: Initial version
 * - V1.2.36 08-SEP-2010: Add Joomla 1.6 beta 8 compatibility
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

require_once( dirname( dirname( __FILE__)) .DS. 'models' .DS. 'layouts.php');
require_once( dirname( __FILE__) .DS. 'compat16.php');


/**
 * Renders a category element
 *
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */

class MultisitesElementMultisitesLayouts extends MultisitesElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'MultisitesLayouts';
	
	function fetchElement($name, $value, &$node, $control_name)
	{
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


	   $opt = array();
		$opt[] = JHTML::_('select.option', ':select:', '- '.JText::_('Select template').' -');
      $opt[] = JHTML::_('select.option', ':default:', JText::_( '< Default >'));

      // Search for all sub-directories present in the "templates" directory
      $rows = array();
      $templates_dir = JPATH_SITE .DS. 'components' .DS. 'com_multisites' .DS. 'templates';
      if ($dir = @opendir( $templates_dir)) {
         // For each files and directories
         while (($file = readdir($dir)) !== false) {
            if ($file != ".." && $file != ".") {
               // If this is a directory
               if ( is_dir( $templates_dir .DS. $file)) {
                  // That contain a list.php or edit.php or delete.php
                  if ( is_file( $templates_dir .DS. $file .DS. 'list.php')
                    || is_file( $templates_dir .DS. $file .DS. 'edit.php')
                    || is_file( $templates_dir .DS. $file .DS. 'delete.php')
                     )
                  {
                     $rows[] = $file;
                  }
               }
            }
         }
         closedir ($dir);
      }
      asort( $rows);

	   foreach( $rows as $tpl) {
   		$opt[] = JHTML::_('select.option', $tpl, $tpl);
	   }


		$result = JHTML::_( 'select.genericlist',  $opt, ''.$control_name.'['.$name.']',
		                    'class="'.$class.'"' . $onchange, 
		                    'value', 'text', 
		                    $value, $control_name.$name );

		$enteredvalues = array();
		$enteredvalues['client']        = 'site';
		$enteredvalues['layout']        = $value;
		$enteredvalues['control_name']  = $control_name;
		$enteredvalues['name']          = $name;

		$model  = new MultisitesModelLayouts();
		$item   = & $model->getItem();
		$params = & $model->getLayoutParams( $enteredvalues);
		if ( is_string( $params) || !is_a( $params, 'JParameter')) {
   		$layoutFields = '';
   		$style = 'style="display:none"';
		}
		else {
   		$layoutFields = $params->render( $enteredvalues['control_name']);
   		$style = '';
		}
		
		$id = $control_name.$name. '_params';
		$id = str_replace(array('[', ']'), '', $id); // remove [] to be compatible with Joomla 1.6
		$result .= '<div id="' .$id. '" ' . $style. ' cid="' .(empty( $item->id) ? '' : $item->id). '">'
		         . $layoutFields
		         . '</div>'
		         ;
		return $result;
	}
}


// ===========================================================
//             Joomla 1.5 / 1.6 compatibility
// ===========================================================

// If Joomla 1.6
if ( version_compare( JVERSION, '1.6') >= 0) { 
   class JFormFieldMultisitesLayouts extends MultisitesElementMultisitesLayouts
   {
   	protected $type = 'Site';
   }
}
// Else: Default Joomla 1.5
else {
   class JElementMultisitesLayouts extends MultisitesElementMultisitesLayouts {}
}
