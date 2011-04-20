<?php
/**
 * @file       template.php
 * @version    1.2.47
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2011 Edwin2Win sprlu - all right reserved.
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
 * - V1.1.5 16-DEC-2008: Add template wrapper to change the "templates" root path when a specific folder
 *                       is specified in JMS.
 * - V1.2.14 05-DEC-2009: Add Joomla 1.6 alpha 2 compatibility.
 * - V1.2.47 03-FEB-2011: Add "setParent" for compatibility with Joomla 1.6.0.
 *
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

require_once(JPATH_LIBRARIES
             .DS.'joomla'
             .DS.'installer'
             .DS.'adapters'
             .DS.'template.php');

// ===========================================================
//             JInstallerTemplateMultisites class
// ===========================================================
/**
 * @brief Multi Sites Template Uninstaller
 */
class JInstallerTemplateMultisites extends JInstallerTemplate
{
	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param	object	$parent	Parent object [JInstaller instance]
	 * @return	void
	 * @since	1.5
	 */
	function __construct(&$parent)
	{
		$this->parent =& $parent;
	}

	public function setParent( &$parent)   { $this->parent =& $parent; }

	/**
	 * Custom install method
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function install()
	{
		jimport('joomla.application.helper');
      jimport( 'joomla.filesystem.path');

		$manifest =& $this->parent->getManifest();
		$root =& $manifest->document;

		// Get the client application target
		if ($cname = $root->attributes('client')) {
		}
		else {
		   $root->addAttribute('client', 'site');
		}
      
		$client =& JApplicationHelper::getClientInfo( 0);
		$sav_path = $client->path;
		
		// If there is a specific template folder specified, give this one to the "template" installer.
		if ( defined( 'MULTISITES_ID')) {
         if ( defined( 'MULTISITES_ID_PATH'))   { $filename = MULTISITES_ID_PATH.DIRECTORY_SEPARATOR.'config_multisites.php'; }
         else                                   { $filename = JPATH_MULTISITES.DS.MULTISITES_ID.DS.'config_multisites.php'; }
         
         @include($filename);
         if ( isset( $config_dirs) && !empty( $config_dirs) && !empty( $config_dirs['templates_dir'])) {
            $templates_dir = JPath::clean( $config_dirs['templates_dir']);
            $parts = explode( DS, $templates_dir );
            array_pop( $parts );
            $client->path = implode( DS, $parts );
         }
		}
		
   	$result = parent::install();
		
		$client->path = $sav_path;
		return $result;
	}

	/**
	 * Custom uninstall method
	 *
	 * @access	public
	 * @param	int		$path		The template name
	 * @param	int		$clientId	The id of the client
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function uninstall( $name, $clientId )
	{
		$mainframe	= &JFactory::getApplication();
		
		jimport('joomla.application.helper');
      jimport( 'joomla.filesystem.path');
      jimport( 'joomla.filesystem.folder');
      jimport( 'joomla.filesystem.file');
      $result = false;

		$client =& JApplicationHelper::getClientInfo( $clientId);
		$sav_path = $client->path;
		
		// If there is a specific template folder specified, give this one to the "template" installer.
		if ( defined( 'MULTISITES_ID')) {
         if ( defined( 'MULTISITES_ID_PATH'))   { $filename = MULTISITES_ID_PATH.DIRECTORY_SEPARATOR.'config_multisites.php'; }
         else                                   { $filename = JPATH_MULTISITES.DS.MULTISITES_ID.DS.'config_multisites.php'; }
         
         @include($filename);
         if ( isset( $config_dirs) && !empty( $config_dirs) && !empty( $config_dirs['templates_dir'])) {
            $templates_dir = JPath::clean( $config_dirs['templates_dir']);
            $folder = $templates_dir.DS.$name;
   		   if ( is_link( $folder)) {
               $result = JFile::delete( $folder);
            }
            else {
      			$result = JFolder::delete( $folder);
            }
         }
         else {
      		$mainframe->enqueueMessage( JText::_('MSJINSTALL_TMPL_CANNOT_REMOVE'));
            $result = false;
         }
		}
      else {
   		$mainframe->enqueueMessage( JText::_('MSJINSTALL_TMPL_CANNOT_REMOVE'));
         $result = false;
      }
		
		
		$client->path = $sav_path;
		return $result;
	}
}