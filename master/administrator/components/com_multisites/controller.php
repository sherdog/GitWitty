<?php
/**
 * @file       controller.php
 * @brief      Manage the different 'slave' site instances and allow Install/Uninstall the patches of the core joomla.
 *
 * @version    1.2.36
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
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
 * - V1.0.7 23-AUG-2008: Replace file_put_contents by JFile::write in aim to use the FTP Layer
 * - V1.1.0 24-OCT-2008: Add "Website templates" management
 *                       Add Ajax interface to allow the Site Management get template information 
 *                           and get available admin users (template case)
 *                       Add "Settings" to allow order Billable Website Quota
 * - V1.1.5 13-DEC-2008: Rebuid the master index when a slave site is deleted
 * - V1.1.17 18-FEB-2009: Add 2009 in the about copyright
 * - V1.2.00 27-APR-2009: Add extension sharing in the "website template"
 *                        Add tools menu that allow managing the website install/uninstall synchronisation
 * - V1.2.00 02-AUG-2009: Add display of Latest Version number in the about and in "check patches"
 * - V1.2.14 21-NOV-2009: Add Ajax 'FTP parameters' for the "manage site".
 *                        Add Joomla 1.6 alpha 2 compatibility.
 * - V1.2.20 01-FEB-2010: Add Ajax to get layout parameters.
 * - V1.2.23 07-MAR-2010: Add the possibility to create a new template and new slave site based on an existing one (new like).
 * - V1.2.32 12-JUN-2010: Add Joomla 1.6 Compatibility.
 *                        Change all call to getModel as Joomla 1.6 does not return the reference to the model anymore.
 * - V1.2.33 14-JUL-2010: Add Joomla 1.6 beta 5 Compatibility.
 *                        Build sub menus
 * - V1.2.36 21-AUG-2010: Add Joomla 1.6 beta 7 Compatibility.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.controller');



// ===========================================================
//             MultisitesController class
// ===========================================================
/**
 * @brief Multi Sites controler.
 * Process the operations during the administration of the multi sites.
 *
 * The management of a 'slave' site consists in creating a directory inside the 'multisites' directory
 * present in the 'master' (root) joomla directory.\n
 * Each directory present in 'multisites' directory corresponds to a 'slave' identifier.\n
 * In each 'slave' instance, a special 'config_multisites.php' file contain the list of domains 
 * (ie. www.slave_x.com) attached to the site.\n
 * A master 'config_multisites.php' is also created into the 'multisites' directory.
 * It contains the description of ALL domains and the associated 'slave' site directory where
 * the joomla configuration file can be retreived.
 *
 * The Install/Uninstall patches consists in updating some core joomla file.
 * To allow 'slave' sites defines their own configuration, the 'installation' joomla directory
 * must be restored.
 *
 */
class MultisitesController extends JController
{
	// =====================================
	//             MANAGE
	// =====================================
	
   //------------  manage ---------------
   /**
    * @brief List all 'slave' site instances.
    */
	function manage()
	{
		$model	=& $this->getModel( 'Manage' );
		$view    =& $this->getView( 'Manage');
		$view->setModel( $model, true );

		// Add a second model that will be used to test if the patches are installed
		$modelPatches	=& $this->getModel( 'Patches' );
		$view->setModel( $modelPatches);

		$view->display();

		MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
	}

   //------------ editSite ---------------
   /**
    * @brief Edit a specific site instances.
    * This allow to update the list of domain attached to the site.
    */
	function editSite()
	{
		$model	=& $this->getModel( 'Manage' );
		$view    =& $this->getView( 'Manage');
		$view->setModel( $model, true );

		// Add a second model that is used to compute the lists
		$modelTemplates	=& $this->getModel( 'Templates' );
		$view->setModel( $modelTemplates);

		$view->editForm('edit',null);
		MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
	}

   //------------ addSite ---------------
   /**
    * @brief Add a new site instances.
    * This operation will create a sub-directory in 'multisites' directory.
    * The name of the sub-directory is the site ID.
    */
	function addSite()
	{
		$model	=& $this->getModel( 'Manage' );
		$view    =& $this->getView( 'Manage');
		$view->setModel( $model, true );

		// Add a second model that is used to compute the lists
		$modelTemplates	=& $this->getModel( 'Templates' );
		$view->setModel( $modelTemplates);

		$view->editForm('new',null);
		MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
	}

   //------------ addLikeSite ---------------
   /**
    * @brief Add a new site like an existing one.
    * This operation will create a sub-directory in 'multisites' directory.
    * The name of the sub-directory is the site ID.
    */
	function addLikeSite()
	{
		$model	=& $this->getModel( 'Manage' );
		$view    =& $this->getView( 'Manage');
		$view->setModel( $model, true );

		// Add a second model that is used to compute the lists
		$modelTemplates	=& $this->getModel( 'Templates' );
		$view->setModel( $modelTemplates);

		$view->editForm('newLike',null);
		MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
	}

   //------------ saveSite ---------------
   /**
    * @brief Action that is called by edit/add site and in charge to save the site information.
    * This mainly consists in creation of the site directory (if not exists) and storing the list
    * of domain attached to the site.
    */
	function saveSite()
	{
   	$option = JRequest::getCmd('option');

		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$model	=& $this->getModel( 'Manage' );
		$view    =& $this->getView( 'Manage');
		$view->setModel( $model, true );
		$msg = $view->saveSite();
		MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));

		$this->setRedirect( 'index.php?option=' . $option, $msg);
	}

   //------------ deleteSite ---------------
	/**
	 * Request confirmation before deletion of the site.
	 * When this is confirmed, this call doDeleteSite.
	 */
	function deleteSite()
	{
		$model	=& $this->getModel( 'Manage' );
		$view    =& $this->getView( 'Manage');
		$view->setModel( $model, true );
		$view->deleteForm();
		MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
	}


   //------------ doDeleteSite ---------------
	/**
	 * Perform the deletion of the site.
	 */
	function doDeleteSite()
	{
   	$option = JRequest::getCmd('option');
	   
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$id = JRequest::getVar( 'id', false, '', 'string' );
		if ($id === false) {
			JError::raiseWarning( 500, JText::_( 'Invalid ID provided' ) );
			$this->setRedirect( 'index.php?option=' . $option );
			return false;
		}

		$model =& $this->getModel( 'Manage' );
		if (!$model->canDelete()) {
			JError::raiseWarning( 500, $model->getError() );
			$this->setRedirect( 'index.php?option=' . $option );
			return false;
		}
		$err = null;
		if (!$model->delete()) {
			 $err = $model->getError();
		}
		
		// Re-create the master index containing all the host name and associated directories
		$model->createMasterIndex();
		
		$this->setRedirect( 'index.php?option=' . $option, $err );
	}
	
	// =====================================
	//             TEMPLATES
	// =====================================

   //------------ templates ---------------
   /**
    * @brief List all available "Website templates".
    */
	function templates()
	{
		$model	=& $this->getModel( 'Templates' );
		$view    =& $this->getView( 'Templates');
		$view->setModel( $model, true );

		// Add also the Manage model to allow creating the filters
		$modelManage    =& $this->getModel( 'Manage');
		$view->setModel( $modelManage);
		
		$view->display();
		MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
	}

   //------------ editTemplate ---------------
   /**
    * @brief Edit a specific "Website Template" instances.
    */
	function editTemplate()
	{
		$model	=& $this->getModel( 'Templates' );
		$view    =& $this->getView( 'Templates');
		$view->setModel( $model, true );

		// Add also the Manage model to allow creating the list of sites
		$modelManage   =& $this->getModel( 'Manage');
		$view->setModel( $modelManage);

   	// Add the XML DB Sharing interface used by the "Sharing" panel.
   	$modelSharing     =& $this->getModel( 'dbsharing');
		$view->setModel( $modelSharing);

		$view->editForm('edit',null);
		MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));

	}

   //------------ addTemplate ---------------
   /**
    * @brief Add a new "Website Template" instances.
    */
	function addTemplate()
	{
		$model	=& $this->getModel( 'Templates' );
		$view    =& $this->getView( 'Templates');
		$view->setModel( $model, true );

		// Add also the Manage model to allow creating the list of sites
		$modelManage    =& $this->getModel( 'Manage');
		$view->setModel( $modelManage);
		
   	// Add the XML DB Sharing interface used by the "Sharing" panel.
   	$modelSharing     =& $this->getModel( 'dbsharing');
		$view->setModel( $modelSharing);

		$view->editForm('new',null);
		MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
	}


   //------------ addLikeTemplate ---------------
   /**
    * @brief Add a new "Website Template" like an existing one.
    */
	function addLikeTemplate()
	{
		$model	=& $this->getModel( 'Templates' );
		$view    =& $this->getView( 'Templates');
		$view->setModel( $model, true );

		// Add also the Manage model to allow creating the list of sites
		$modelManage    =& $this->getModel( 'Manage');
		$view->setModel( $modelManage);
		
   	// Add the XML DB Sharing interface used by the "Sharing" panel.
   	$modelSharing     =& $this->getModel( 'dbsharing');
		$view->setModel( $modelSharing);

		$view->editForm('newLike',null);
		MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
	}

   //------------ saveTemplate ---------------
   /**
    * @brief Action that is called by edit/add Template and in charge to save the Template information.
    * This mainly consists in saving the template information into the file multisites/config_templates.php
    * that contain the collection of all templates available.
    */
	function saveTemplate()
	{
   	$option = JRequest::getCmd('option');

		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$model	=& $this->getModel( 'Templates' );
		$view    =& $this->getView( 'Templates');
		$view->setModel( $model, true );
		$msg = $view->saveTemplate();
		MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));

		$this->setRedirect( 'index.php?task=templates&option=' . $option, $msg);
	}

   //------------ deleteTemplate ---------------
	/**
	 * Request confirmation before deletion of the Template.
	 * When this is confirmed, this call doDeleteTemplate.
	 */
	function deleteTemplate()
	{
		$model	=& $this->getModel( 'Templates' );
		$view    =& $this->getView( 'Templates');
		$view->setModel( $model, true );
		$view->deleteForm();
		MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
	}


   //------------ doDeleteTemplate ---------------
	/**
	 * Perform the deletion of the Template.
	 */
	function doDeleteTemplate()
	{
   	$option = JRequest::getCmd('option');
	   
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$id = JRequest::getVar( 'id', false, '', 'string' );
		if ($id === false) {
			JError::raiseWarning( 500, JText::_( 'Invalid ID provided' ) );
			$this->setRedirect( 'index.php?task=templates&option=' . $option );
			return false;
		}

		$model =& $this->getModel( 'Templates' );
		if (!$model->canDelete()) {
			JError::raiseWarning( 500, $model->getError() );
			$this->setRedirect( 'index.php?task=templates&option=' . $option );
			return false;
		}
		$err = null;
		if (!$model->delete()) {
			 $err = $model->getError();
		}
		$this->setRedirect( 'index.php?task=templates&option=' . $option, $err );
	}

	// =====================================
	//             TOOLS
	// =====================================

   //------------ tools ---------------
   /**
    * @brief Provide a Tree View on website dependencies.
    *        It is used to provide "synchornisation" and extension update services
    */
	function tools()
	{
		$model	=& $this->getModel( 'Tools' );
		$view    =& $this->getView( 'Tools');
		$view->setModel( $model, true );

		$view->display();
		MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
	}

   //------------ applyTools ---------------
   /**
    * @brief Apply the Install, Sharing / Uninstall extension
    */
	function applyTools()
	{
		$model	=& $this->getModel( 'Tools' );
		$view    =& $this->getView( 'Tools');
		$view->setModel( $model, true );
		
		$view->applyTools();
		MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
	}


	// =====================================
	//             CHECK PATCHES
	// =====================================

   //------------ checkPatches ---------------
   /**
    * @brief Check if the patches are installed.
    * When some patches are not installed, it propose to install them.
    * Otherwise, it is proposed to uninstall the patches.
    */
	function checkPatches()
	{
		$model	=& $this->getModel( 'patches' );
		$view    =& $this->getView( 'patches');
		$view->setModel( $model, true );
      $jmsVersion =  $this->_getVersion();
		$view->assign('jmsVersion',  $jmsVersion);

		$modelReg       =& $this->getModel( 'registration', 'Edwin2WinModel' );
	   $latestVersion  = $modelReg->getLatestVersion();
		$view->assign('latestVersion',  $latestVersion);

	   $view->check();
		MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
	}
	

   //------------ doInstallPatches ---------------
   /**
    * @brief Perform the deployment of the patches
    */
	function doInstallPatches()
	{
   	$option = JRequest::getCmd('option');
	   
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$model =& $this->getModel( 'patches' );
		if (!$model->canInstall()) {
			JError::raiseWarning( 500, $model->getError() );
			$this->setRedirect( 'index.php?option=' . $option );
			return false;
		}
		
		$renamed_install_dir = JRequest::getString( 'ren_inst_dir');
		$err = null;
		if (!$model->install( $renamed_install_dir)) {
			 $err = $model->getError();
		}
		// Recheck the installation
		$this->setRedirect( 'index.php?option=' . $option . '&task=checkpatches', $err );

	}


   //------------  doUninstallPatches ---------------
   /**
    * @brief Perform the restore of the original files from the backup
    */
	function doUninstallPatches()
	{
   	$option = JRequest::getCmd('option');
	   
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$model =& $this->getModel( 'patches' );
		
		$err = null;
		if (!$model->uninstall()) {
			 // $err = $model->getError();
		}
		// Recheck the installation
		$this->setRedirect( 'index.php?option=' . $option . '&task=checkpatches', $err );

	}
	
   //------------ _getVersion ---------------
   /**
    * @brief Retreive the version number of this component.
    */
	function _getVersion()
	{
	   jimport( 'joomla.application.helper');
	   $version = "unknown";
	   $filename = dirname(__FILE__) .DS. MULTISITES_MANIFEST_FILENAME;
		if ($data = JApplicationHelper::parseXMLInstallFile($filename)) {
		   // If the version is present
		   if (isset($data['version']) && !empty($data['version'])) {
		      $version = $data['version'];
		   }
		}
		return $version;
	}

   //------------ checkUpdates ---------------
   /**
    * @brief Check for Updates.
    */
	function checkUpdates( $ignoreVersion=false)
	{
   	$option = JRequest::getCmd('option');
      $err = null;
      
   	$model =& $this->getModel( 'registration', 'Edwin2WinModel' );
   	// If not registered
		if ( !$model->isRegistered()) {
		   $err = JText::_( 'Your must be registered to check for updates');
		}
		else {
		   // Retreive the Product ID
         $data = Edwin2WinModelRegistration::getRegistrationInfo();
         if ( !empty($data)) {
      		$modelPatches =& $this->getModel( 'patches' );
      		$url = Edwin2WinModelRegistration::getURL();
      		$err = $modelPatches->checkUpdates( $url, $data, $ignoreVersion);
         }
         else {
   		   $err = JText::_( 'Product key is missing, your must be correctly registered to check for updates');
         }
		}
		
		// If raised error, don't redirect to avoid loosing the error message
		if ( $err === false) {
		   return;
		}
		
		// Go to Check Patches
		$this->setRedirect( 'index.php?option=' . $option . '&task=checkpatches', $err);
	}

	
   //------------ forceCheckUpdates ---------------
   /**
    * @brief Check for Updates without Patch Version number.
    *        Force check update with latest version.
    *        This is a rescue task in case where the patches files contain PHP syntax error
    */
	function forceCheckUpdates()
	{
	   $this->checkUpdates( true);
	}

	// =====================================
	//             USER MANUAL
	// =====================================
 
   //------------ usersManual ---------------
   /**
    * @brief Redirect to the online User Manual.
    */
	function usersManual()
	{
   	$option = JRequest::getCmd('option');
		$mainframe	= &JFactory::getApplication();
      $version = $this->_getVersion();
      $url = 'http://www.jms2win.com/index.php?option=com_docman&task=findkey&keyref='.$option.'.usersmanual&version='.$version;
      $mainframe->redirect( $url);
	}

	// =====================================
	//             SETTINGS
	// =====================================

   //------------ showSettings ---------------
   /**
    * @brief Show current setting and allow to buy Website quote
    */
	function showSettings()
	{
		$model	=& $this->getModel( 'settings' );
		$view    =& $this->getView( 'settings');
		$view->setModel( $model, true );
		$view->showSettings();
		MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
	}

	// =====================================
	//             LAYOUTS
	// =====================================
 
   //------------ layouts ---------------
   /**
    * @brief Redirect to the layouts installer.
    */
	function layouts()
	{
		$mainframe	= &JFactory::getApplication();
   	$option     = JRequest::getCmd('option');
      if ( version_compare( JVERSION, '1.6') >= 0) { $url        = "index.php?option=$option&task=manage&controller=installer&type=layouts&view=layouts"; }
      else                                         { $url        = "index.php?option=$option&task=manage&controller=installer&type=layouts"; }
      
      $mainframe->redirect( $url);
	}

	// =====================================
	//             ABOUT
	// =====================================


   //------------ about ---------------
	function about()
	{
		$modelPatches	 =& $this->getModel( 'patches' );
	   $patchesVersion = $modelPatches->getPatchesVersion();
   	$model          =& $this->getModel( 'registration', 'Edwin2WinModel' );
	   $latestVersion  = $model->getLatestVersion();
?>
<h3>Multisites for Joomla! 1.5.x</h3>
<p>Version <?php
$getLatestURL = '';
$version = $this->_getVersion();
if ( !empty( $latestVersion['version'])) {
   if ( version_compare( $version, $latestVersion['version']) < 0) {
      echo '<font color="red">' . $version .'</font>';
      $getLatestURL = ' <a href="http://www.jms2win.com/get-latest-version">Get Latest Version</a>';
   }
   else {
      echo '<font color="green">' . $version .'</font>';
   }
   echo ' <em>(' . JText::_( 'Latest available') . ': ' . $latestVersion['version'] . ')</em>';
}
else {
   echo $version;
}
?><br/>
Patches definition Version <?php
if ( !empty( $latestVersion['patch_version'])) {
   if ( version_compare( $patchesVersion, $latestVersion['patch_version']) < 0) {
      echo '<font color="red">' . $patchesVersion .'</font>';
      // If only the patch definition is different
      if ( empty( $getLatestURL)) {
         $getLatestURL = ' <a href="index.php?option=com_multisites&task=checkpatches">Check for update</a>';
      }
   }
   else {
      echo '<font color="green">' . $patchesVersion .'</font>';
   }
   echo ' <em>(' . JText::_( 'Latest available') . ': ' . $latestVersion['patch_version'] . ')</em>';
}
else {
   echo $this->patchesVersion;
}
// If is registered and there is a new version
if ( $model->isRegistered() && !empty( $getLatestURL)) {
   echo '<br/>' . $getLatestURL;
}

?></p>
<img src="components/com_multisites/images/multisites_logo.jpg" alt="Joomla Multi Sites" />
<h3>Copyright</h3>
<p>Copyright 2008-2010 Edwin2Win sprlu<br/>
Rue des robiniers, 107<br/>
B-7024 Ciply<br/>
Belgium
</p>
<p>All rights reserved.</p>
<a href="http://www.jms2win.com">www.jms2win.com</a>
<?php
   	// If not registered
		if ( !$model->isRegistered()) {
      	$view    =& $this->getView(  'registration', '', 'Edwin2WinView');
      	$view->setModel( $model, true );
      	$view->registrationButton();
		}
		MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
	} // End about

   //------------ registered ---------------
   /**
    * @brief When the status is OK, this save the registered informations.
    * This task is called by the redirect url parameters for the registration button.
    */
	function registered()
	{
   	$option = JRequest::getCmd('option');
   	$model   =& $this->getModel( 'registration',     'Edwin2WinModel' );
   	$view    =& $this->getView(  'registration', '', 'Edwin2WinView');
   	$view->setModel( $model, true );
   	$msg = $view->registered( false);
		$this->setRedirect( 'index.php?option=' . $option . '&task=manage', $msg);
	}


	// =====================================
	//             Hidden Configuration editor
	// =====================================

   // -------------- getConfigFileName ------------------------------
   function getConfigFileName()
   {
   	$config_file = dirname(__FILE__) . DS . 'multisites.cfg.php';
      return $config_file;
   }

   // -------------- editconfig ------------------------------
   function editconfig() {
   	$option = JRequest::getCmd('option');
      $myFilename = $this->getConfigFileName();
      $content = file_get_contents($myFilename);
   ?>
      <form action="index.php?option=<?php echo $option; ?>&task=saveconfig" method="POST">
      <table class="adminform">
       <tr class="row0">
         <td class="labelcell"><strong>Configuration file:</strong><?php echo $myFilename; ?></td>
       </tr>
       <tr class="row1">
         <td>
            <textarea class="inputbox" name="content" cols="120" rows="20"><?php echo htmlspecialchars( $content ); ?></textarea>
         </td>
       </tr>
       <tr>
         <td><input type="submit" value="Save"/></td>
       </tr>
     </table>
     </form>
   <?php
   }
   
   // -------------- unhtmlspecialchars ------------------------------
   function unhtmlspecialchars($string) {
      $trans_tbl =get_html_translation_table (HTML_SPECIALCHARS );
      $trans_tbl =array_flip ($trans_tbl );
      return strtr ($string ,$trans_tbl );
   }
   
   // -------------- saveconfig ------------------------------
   function saveconfig() {
      jimport('joomla.filesystem.file');
      $myFilename = $this->getConfigFileName();
      
      $content = stripslashes( $this->unhtmlspecialchars( $_POST['content']));
   
      JFile::write( $myFilename, $content);
      echo "File saved";
   }


	// =====================================
	//             Hidden Master Index editor
	// =====================================

   // -------------- getMasterIndexFileName ------------------------------
   function getMasterIndexFileName()
   {
   	if ( defined( 'JPATH_MULTISITES')) {
      	$config_file = JPATH_MULTISITES .DS. 'config_multisites.php';
   	}
   	else {
      	$config_file = JPATH_ROOT.DS.'multisites' .DS. 'config_multisites.php';
   	}
      return $config_file;
   }

   // -------------- editindex ------------------------------
   function editindex() {
   	$option = JRequest::getCmd('option');
      $myFilename = $this->getMasterIndexFileName();
      $content = file_get_contents($myFilename);
   ?>
      <form action="index.php?option=<?php echo $option; ?>&task=saveindex" method="POST">
      <table class="adminform">
       <tr class="row0">
         <td class="labelcell"><strong>Master index file:</strong><?php echo $myFilename; ?></td>
       </tr>
       <tr class="row1">
         <td>
            <textarea class="inputbox" name="content" cols="120" rows="20"><?php echo htmlspecialchars( $content ); ?></textarea>
         </td>
       </tr>
       <tr>
         <td><input type="submit" value="Save"/></td>
       </tr>
     </table>
     </form>
   <?php
   }
   
   // -------------- saveindex ------------------------------
   function saveindex() {
      jimport('joomla.filesystem.file');
      $myFilename = $this->getMasterIndexFileName();
      
      $content = $this->unhtmlspecialchars( $_POST['content']);
   
      JFile::write( $myFilename, $content);
      echo "File saved";
   }

	// =====================================
	//             AJAX services
	// =====================================
   
   // -------------- getUsersList ------------------------------
   /**
    * @brief Return the combo box with the list of all the users present in the site ID.
    */
   function ajaxGetUsersList()
   {
		// Check for request forgeries
		JRequest::checkToken( 'get') or jexit( 'Invalid Token' );
		
		$site_id = JRequest::getString( 'site_id', null);
		$replyStr = MultisitesHelper::getUsersList( $site_id);
		
		jexit( $replyStr);
   }
   
   
   // -------------- ajaxGetTemplate ------------------------------
   /**
    * @brief Return the detailled information concerning a template ID.
    * @note
    * request : id = template identifier
    */
   function ajaxGetTemplate()
   {
		// Check for request forgeries
		JRequest::checkToken( 'get') or jexit( 'Invalid Token' );

		// Load the template based on its id
		$model =& $this->getModel( 'Templates' );
		$template = $model->getCurrentRecord();
		if (!$template) {
   		jexit( '<error>' . JText::_( 'TEMPLATE_NOT_FOUND') . '</error>');
		}
		$result = 'template'
		        . '|' . $template->id
		        . '|' . $template->toPrefix
		        . '|' . $template->deploy_dir
		        . '|' . $template->media_dir
		        . '|' . $template->images_dir
		        . '|' . $template->templates_dir
		        . '|' . (!empty($template->toDBHost)       ? $template->toDBHost : '')
		        . '|' . (!empty($template->toDBName)       ? $template->toDBName : '')
		        . '|' . (!empty($template->toDBUser)       ? $template->toDBUser : '')
		        . '|' . (!empty($template->toDBPsw)        ? $template->toDBPsw : '')
		        . '|' . (!empty($template->deploy_create)  ? $template->deploy_create : '')
		        . '|' . (!empty($template->alias_link)     ? $template->alias_link : '')
		        . '|' . (isset($template->toFTP_enable)    ? $template->toFTP_enable : '')
		        . '|' . (!empty($template->toFTP_host)     ? $template->toFTP_host : '')
		        . '|' . (!empty($template->toFTP_port)     ? $template->toFTP_port : '')
		        . '|' . (!empty($template->toFTP_user)     ? $template->toFTP_user : '')
		        . '|' . (!empty($template->toFTP_psw)      ? $template->toFTP_psw : '')
		        . '|' . (!empty($template->toFTP_rootpath) ? $template->toFTP_rootpath : '')
		        ;
		jexit( $result);
   }

   // -------------- ajaxToolsGetSite ------------------------------
   /**
    * @brief Return a <table> with the comparaison between Master / Template webiste / Current Website extension installation
    * request : site_id = The site identifier
    */
   function ajaxToolsGetSite()
   {
		// Check for request forgeries
		// JRequest::checkToken( 'get') or jexit( 'Invalid Token' );

		// Load the template based on its id
		$model =& $this->getModel( 'Tools' );
		$view    =& $this->getView( 'Tools');
		$view->setModel( $model, true );
		
		$result = $view->getSiteExtensions();
		
		jexit( $result);
   }

   // -------------- ajaxToolsApply ------------------------------
   /**
    * @brief Return a <table> with the comparaison between Master / Template webiste / Current Website extension installation
    * request : site_id = The site identifier
    */
   function ajaxToolsApply()
   {
		// Check for request forgeries
		// JRequest::checkToken( 'get') or jexit( 'Invalid Token' );

		$enteredvalues = array();
		$enteredvalues['site_id']     = JRequest::getString('site_id', null);;
		$enteredvalues['nbActions']   = JRequest::getInt('nbActions', 0);;
		$enteredvalues['actions']     = JRequest::getVar( 'action',     array(), 'request', 'array' );
		$enteredvalues['fromSiteIDs'] = JRequest::getVar( 'fromSiteID', array(), 'request', 'array' );
		$enteredvalues['options']     = JRequest::getVar( 'opt',        array(), 'request', 'array' );
		$enteredvalues['overwrites']  = JRequest::getVar( 'overwrite',  array(), 'request', 'array' );

		// Load the template based on its id
		$model =& $this->getModel( 'Tools' );
		$errors = $model->doActions( $enteredvalues);
		if ( empty($errors)) {
   		jexit( '[OK]');
		}
		
		jexit( '[NOK]|' . implode( '|', $errors));
   }


   // -------------- ajaxGetLayoutParams ------------------------------
   /**
    * @brief Return a <table> with the comparaison between Master / Template webiste / Current Website extension installation
    * request : site_id = The site identifier
    */
   function ajaxGetLayoutParams()
   {
		// Check for request forgeries
		// JRequest::checkToken( 'get') or jexit( 'Invalid Token' );

		$enteredvalues = array();
		$enteredvalues['client']        = 'site';
		$enteredvalues['layout']        = JRequest::getCmd('layout', null);
		$enteredvalues['control_name']  = JRequest::getCmd( 'control_name', 'params');
		$enteredvalues['name']          = JRequest::getCmd( 'name', 'jmslayout_params');


		// Load the template based on its id
		$model =& $this->getModel( 'Layouts' );
		$params = $model->getLayoutParams( $enteredvalues);
		if ( is_string( $params) || !is_a( $params, 'JParameter')) {
   		jexit( '[NOK]|' . $params);
		}
		
		$result = '<!-- multisites_reply_ajaxGetLayoutParams -->'
		        . $params->render( $enteredvalues['control_name']);
		
		jexit( $result);
   }

} // End class
