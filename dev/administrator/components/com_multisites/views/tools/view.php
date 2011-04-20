<?php
/**
 * @file       view.php
 * @version    1.2.42
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
 * - 23-MAY-2009 V1.2.0 : Initial version
 *               V1.2.0 : Add icon for the "-", "+" and "x" table types.
 * - 13-JUN-2010 V1.2.32 : Add Joomla 1.6 beta 2 compatibility.
 * - 05-NOV-2010 V1.2.42 : Add compatibility with Joomla 1.6 beta 13
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.view');


// ===========================================================
//            MultisitesViewTools class
// ===========================================================
/**
 * @brief Content the different Views available for the Tools Manager.
 *
 * Views available are:
 * - display() This is the default view that display a tree of the slave sites dependencies
 */
class MultisitesViewTools extends JView
{
   // Private members
   var $_formName   = 'Tools';
   var $_lcFormName = 'tools';


   //------------ display ---------------
   /**
    * @brief Display the Website Tree dependencies
    */
	function display($tpl=null)
	{
		$mainframe	= &JFactory::getApplication();
   	$option     = JRequest::getCmd('option');

		$this->setLayout( 'default');

		/*
		 * Set toolbar items for the page
		 */
		$formName   = $this->_formName;
		$lcFormName = $this->_lcFormName;

		JToolBarHelper::title( JText::_( 'TOOLS_VIEW_TITLE' ), 'config.png' );
//		JToolBarHelper::custom( "save$formName", 'save.png', 'save_f2.png', 'Save', false );
		JToolBarHelper::apply( "apply$formName", JText::_( 'Execute'));
		JToolBarHelper::cancel( 'manage');
		JToolBarHelper::help( 'screen.' .$lcFormName. 'manager', true );

		$document = & JFactory::getDocument();
		$document->setTitle(JText::_('TOOLS_VIEW_TITLE'));

		JHTML::_('behavior.mootools');
		$document->addScript('components/com_multisites/assets/treesites.js');
		$document->addStyleSheet('components/com_multisites/assets/treesites.css');
		
		$document->addScript('components/com_multisites/assets/inputtree.js');
		JHTML::stylesheet('mootree.css');

		$document->addScript( JURI::root(true). '/media/system/js/tabs.js' );

		$this->assignAds();
//		$this->assignRef('lists', $lists);
		
		$treeSites = &$this->get('SiteDependencies');
//		MultisitesModelTools::dumpTree( $treeSites);
		
		$this->assignRef('treeSites', $treeSites);
		$this->assign('node_id'	, 0);
		$this->assignRef('option',       $option);

		JHTML::_('behavior.tooltip');

		parent::display($tpl);
	}

   //------------ assignAds ---------------
	function assignAds()
	{
      if ( !defined('_EDWIN2WIN_'))    { define('_EDWIN2WIN_', true); }
      require_once( JPATH_COMPONENT.DS.'classes'.DS.'http.php' );
      require_once( JPATH_COMPONENT.DS.'models'.DS.'registration.php' );
      
   	// Compute Ads
   	$isRegistered =& Edwin2WinModelRegistration::isRegistered();
   	if ( !$isRegistered)    { $ads =& Edwin2WinModelRegistration::getAds(); }
   	else                    { $ads = ''; }
		$this->assignRef('ads', $ads);
	}

   //------------ getChildrenTree ---------------
	function getChildrenTree( $sites, $tree_id = '')
	{
	   if ( empty( $sites)) {
	      return '';
	   }
	   
	   $txt = "<ul $tree_id>";
	   foreach( $sites as $site) {
	      $this->assignRef( 'site', $site);
		   $this->assign('tree_id', $tree_id);
			$str = $this->loadTemplate('site');
			$child_txt = '';
			if ( !empty( $site->_treeChildren)) {
			   $child_txt = $this->getChildrenTree( $site->_treeChildren);
			}
			
			$txt .= str_replace( "{__children__}", $child_txt, $str);
	   }
	   $txt .= '</ul>';
		return $txt;
	}
	

   //------------ applyTools ---------------
	function applyTools()
	{
		JRequest::setVar( 'hidemainmenu', 1 );

		$mainframe	= &JFactory::getApplication();
   	$option     = JRequest::getCmd('option');

		$this->setLayout( 'apply');
		/*
		 * Set toolbar items for the page
		 */
		$formName   = $this->_formName;
		$lcFormName = $this->_lcFormName;

		JToolBarHelper::title( JText::_( 'TOOLS_VIEW_TITLE_APPLY' ), 'config.png' );
		JToolBarHelper::cancel( 'tools');

		$document = & JFactory::getDocument();
		$document->setTitle(JText::_('TOOLS_VIEW_TITLE_APPLY'));
		JHTML::_('behavior.mootools');
		$document->addScript('components/com_multisites/assets/toolapply.js');
		$document->addStyleSheet('components/com_multisites/assets/toolapply.css');

		$this->assignAds();
		
		$enteredvalues = array();
		$enteredvalues['site_id']       = JRequest::getString('site_id', null);;
		$enteredvalues['comActions']    = JRequest::getVar( 'acom', null,    'post', 'array' );
		$enteredvalues['comPropagates'] = JRequest::getVar( 'ccom', array(), 'post', 'array' );
		$enteredvalues['comOverwrites'] = JRequest::getVar( 'cow',  array(), 'post', 'array' );

		$enteredvalues['modActions']    = JRequest::getVar( 'amod', null,    'post', 'array' );
		$enteredvalues['modPropagates'] = JRequest::getVar( 'cmod', array(), 'post', 'array' );
		$enteredvalues['modOverwrites'] = JRequest::getVar( 'cmow', array(), 'post', 'array' );

		$enteredvalues['plgActions']    = JRequest::getVar( 'aplg', null,    'post', 'array' );
		$enteredvalues['plgPropagates'] = JRequest::getVar( 'cplg', array(), 'post', 'array' );
		$enteredvalues['plgOverwrites'] = JRequest::getVar( 'cpow', array(), 'post', 'array' );
		
		$model   = & $this->getModel();
		$sites = $model->getActionsToDo( $enteredvalues);

		$this->assignRef('sites', $sites);
		$this->assignRef('option',       $option);

		JHTML::_('behavior.tooltip');
		parent::display();
	}



	// =====================================
	//             AJAX services
	// =====================================

   //------------ getSiteExtensions ---------------
	function getSiteExtensions()
	{
		$this->setLayout( 'extensions');
		
		$site_id = JRequest::getString( 'site_id', '');

		$model = & $this->getModel();
		$site_info   = & $model->getSiteInfo( $site_id);
		$tablesInfo  = & $model->getListOfTables( $site_id);
//	   $dbtables    = & Jms2WinDBTables::getInstance();
//	   $dbtables->load();
		$extensions  = & $model->getExtensions( $site_id);
		
		$this->assignRef('site_info',    $site_info);
		$this->assignRef('extensions',   $extensions);
		$this->assignRef('tablesInfo',   $tablesInfo);
		$result = $this->loadTemplate();
		
		return $result;
	}
	
   //------------ _isExtensionSite ---------------
	function _isExtensionSite( $colNumber)
	{
	   foreach( $this->extensions as $categories) {
	      foreach( $categories as $extension) {
   	      if ( isset( $extension[$colNumber])) {
   	         return true;
   	      }
	      }
	   }
	   
	   return false;
	}

   //------------ _isMaster ---------------
	function _isMaster()
	{
	   if ( $this->site_info->id == ':master_db:') {
	      return true;
	   }
	   return false;
	}

   //------------ _hasChildren ---------------
	function _hasChildren()
	{
		$model = & $this->getModel();
		return $model->hasChildren( $this->site_info->id);
	}


   //------------ _getToolTips ---------------
   /**
    * @brief Return a tooltip string
    */
	function _getToolTips( $columns, $rowNbr)
	{
      $extension = & $columns[0];
      // If Component
      if ( !empty( $extension->option)) {
         $option = $extension->option;
      }
      // If Module
      else if ( !empty( $extension->module)) {
         $option = $extension->module;
      }
      // If Plugin
      else if ( !empty( $extension->folder) && !empty( $extension->element)) {
         $option = $extension->folder . '/' . $extension->element;
      }
      // Error
      else {
         return '';
      }
	   $result = "<b>Option:</b> $option";
	   
      // If there are table patterns defined for this extension
      if ( !empty( $columns[5])) {
         $tablepatterns = array();
         foreach( $columns[5] as $xmltable) {
            $tablepattern = $xmltable->attributes( 'name');
            if ( $tablepattern == '[none]') {}
            else {
               $tablepatterns[] = $tablepattern;
            }
         }
         if ( !empty( $tablepatterns)) {
            $result .= '<br /><b>' . JText::_( 'TOOLS_VIEW_TABLE_PATTERNS') . ":</b>\n"
                    .  "<ul>\n"
                    .  '<li>' . implode( "</li>\n<li>", $tablepatterns) . "</li>\n"
                    .  "</ul>\n"
                    ;
            
         }
      }
      // If there are SHARED table patterns defined for this extension
      if ( !empty( $columns[4])) {
         $tablepatterns = array();

         $tables = Jms2WinDBSharing::getTables( $columns[4]);
         foreach( $tables as $xmltable) {
            $tablepattern = $xmltable->attributes( 'name');
            if ( $tablepattern == '[none]') {}
            else {
               $tablepatterns[] = $tablepattern;
            }
         }
         if ( !empty( $tablepatterns)) {
            $result .= '<br /><b>' . JText::_( 'TOOLS_VIEW_SHARED_PATTERNS') . ":</b>\n"
                    .  "<ul>\n"
                    .  '<li>' . implode( "</li>\n<li>", $tablepatterns) . "</li>\n"
                    .  "</ul>\n"
                    ;
            
         }
      }
      
      return $result;
	}


   //------------ _getTableType ---------------
	function _getTableType( $columns, $rowNbr)
	{
      $result = '-';
      $result = '<img src="components/com_multisites/images/minus.png" title="' . JText::_( 'TOOLS_VIEW_INSTALL_TABLES') . '" />';
	   // If No installed in the site (2)
	   if ( empty( $columns[2])) {
	      // If there is no table patterns or share pattern defined for this extension
	      if ( empty( $columns[5]) && empty( $columns[4])) {
            $result = '<span class="editlinktip hasDynTip"'
                    .      ' title="' . JText::_( 'TOOLS_VIEW_TABLES_UNDEFINED_IN_JMS') .'"'
                    . '>X</span>';
            $result = '<img src="components/com_multisites/images/missing.png" title="' . JText::_( 'TOOLS_VIEW_TABLES_UNDEFINED_IN_JMS') . '" />';
            $result = '<span class="editlinktip hasDynTip"'
                    .      ' title="' . JText::_( 'TOOLS_VIEW_TABLES_UNDEFINED_IN_JMS') .'"'
                    . '><img src="components/com_multisites/images/missing.png" title="Undefined extension" /></span>';
	      }
	      // If sharing allowed
	      else if ( !empty( $columns[4])) {
            $result = '+';
            $result = '<img src="components/com_multisites/images/plus.png" title="' . JText::_( 'TOOLS_VIEW_INSTALL_SHARE_TABLES') . '" />';
	      }
	   }
	   // If installed in the site (2)
	   else {
   		$model = & $this->getModel();
         $viewCount  = 0;
         $tableCount = 0;
         $noneCount  = 0; // Extension that does not use any DB tables or views
         $result = '';
	      // If there are table patterns defined for this extension
	      if ( !empty( $columns[5])) {
	         // Check if there is at least one table corresponding to the pattern
            // $result .= ' - TABLES -';
            foreach( $columns[5] as $xmltable) {
               $tablepattern = $xmltable->attributes( 'name');
               if ( $tablepattern == '[none]') {
                  $noneCount++;
               }
               else {
                  $tables = $model->getTableUsingPattern( $tablepattern);
                  if ( !empty( $tables)) {
                     foreach( $tables as $table) {
                        if ( $table->_isView) {
                           $viewCount++;
                           $viewFrom = $table->_viewFrom;
                           // Just optimze to avoid browsing all tables info when we know that we have both tables and views
                           if ( $tableCount > 0) {
                              break;
                           }
                        }
                        else {
                           $tableCount++;
                           // Just optimze to avoid browsing all tables info when we know that we have both tables and views
                           if ( $viewCount > 0) {
                              break;
                           }
                        }
                     } // Next table
                  }
               }
            } // Next tablePattern
	      }
	      // If there are sharing pattern defined for this extension
	      if ( !empty( $columns[4])) {
	         // Check if there is at least one view that match the pattern
            // $result .= ' - SHARING -';
/*
            foreach( $columns[4] as $xmltable) {
               $tablepattern = $xmltable->attributes( 'name');
               $table = $model->getTableUsingPattern( $tablepattern);
               if ( !empty( $table)) {
                  if ( $table->_isView) {
                     $viewCount++;
                     // Just optimze to avoid browsing all tables info when we know that we have both tables and views
                     if ( $tableCount > 0) {
                        break;
                     }
                  }
                  else {
                     $tableCount++;
                     // Just optimze to avoid browsing all tables info when we know that we have both tables and views
                     if ( $viewCount > 0) {
                        break;
                     }
                  }
               }
            }
*/            
	      }
	      
	      if ( $tableCount > 0) {
	         $result .= '<img src="components/com_multisites/images/table.png" title="' . JText::_( 'TOOLS_VIEW_SPECIFIC_TABLES') . '" />';
	      }
	      if ( $viewCount > 0) {
	         $str = '';
	         if ( !empty( $viewFrom)) {
	            $str = " from $viewFrom";
	         }
	         $result .= '<img src="components/com_multisites/images/view.png" title="' . JText::_( 'TOOLS_VIEW_SHARED_TABLES') . $str . '" />';
	      }
	      if ( empty( $result)) {
   	      if ( $noneCount > 0) {
   	         $result .= '<img src="components/com_multisites/images/tocgreen.png" title="' . JText::_( 'TOOLS_VIEW_NO_TABLES') . '" />';
   	      }
   	      else {
   	         $result .= '<img src="components/com_multisites/images/tocred.png" title="' . JText::_( 'TOOLS_VIEW_NO_TABLES') . '" />';
   	      }
	      }
	   } // End If site is installed
      return $result;
	}


   //------------ _getComponentAction ---------------
   /**
    * @brief Compute the ComboBox values associated to the current table name
    */
	function _getComponentAction( $isTemplate, $option, $columns, $fieldname, $rowNbr)
	{
	   $o = array();

	   // If Install
	   if ( empty( $columns[2])) {
         $o[] = '<OPTION value="[unselected]">&nbsp;</OPTION>';
         $className = '';
         if ( $isTemplate) {
   	      if ( !empty( $columns[5])) {
               $label = JText::_( "install from template");
               $labelMaster = JText::_( "install from master");
               $className = 'class="install"';
      		}
      		else {
      		   $label = JText::_( "define from template");
               $labelMaster = JText::_( "define from master");
               $className = 'class="define"';
      		}
      		// If the option is present in the template
   	      if ( !empty( $columns[1])) {
               $o[] = '<OPTION value="table.template|'.$option.'"'.$className.'>'.$label.'</OPTION>';
   	      }
   	      // If sharing is possible
   	      if ( !empty( $columns[4])) {
      	      // And if the option is present in the template
      	      if ( !empty( $columns[1])) {
                  $o[] = '<OPTION value="share.template|'.$option.'" class="share">'.JText::_( "Share from template").'</OPTION>';
               }
   	      }
            $o[] = '<OPTION value="table.master|'.$option.'"'.$className.'>'.$labelMaster.'</OPTION>';
   	      // If sharing is possible
   	      if ( !empty( $columns[4])) {
               $o[] = '<OPTION value="share.master|'.$option.'" class="share">'.JText::_( "Share from master").'</OPTION>';
   	      }
         }
         else {
   	      if ( !empty( $columns[5])) {
               $label = JText::_( "install extension");
               $className = ' class="install"';
      		}
      		else {
      		   $label = JText::_( "define extension");
               $className = ' class="define"';
      		}
            $o[] = '<OPTION value="table.master|'.$option.'"'.$className.'>'.$label.'</OPTION>';
   	      // If sharing is possible
   	      if ( !empty( $columns[4])) {
               $o[] = '<OPTION value="share.master|'.$option.'" class="share">'.JText::_( "Share installation").'</OPTION>';
   	      }
         }
	   }
	   // If un-install
	   else {
         $o[] = '<OPTION value="[unselected]">&nbsp;</OPTION>';
         $o[] = '<OPTION value="uninstall|'.$option.'">'.JText::_( "un-install").'</OPTION>';
	   }
	   
	   $onChange = '';
/*
	   if ( $this->_hasChildren()) {
	      $onChange = ' onchange="updateCB( this, \'com' .$rowNbr. '\' );"';
	   }
*/	   
		                  
		$list = '<select name="'. $fieldname.'[]"'
		      . ' id="'. $fieldname.'_'.$rowNbr .'"'
		      . ' class="actionlist"'
		      . ' size="1"'
		      . $onChange .'>'
		      . implode( "\n", $o)
            . '</select>';
		return $list;
	}

} // End class
