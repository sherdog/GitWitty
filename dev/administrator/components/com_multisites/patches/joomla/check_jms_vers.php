<?php
/**
 * @file       check_jms_vers.php
 * @brief      Check the Joomla Multi Sites version when some patches also require to update the kernel.
 *
 * @version    1.2.38
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
 * - V1.2.11 26-SEP-2009: Cleanup the manifest files that could be present in the backup directory
 *                        to avoid it is restored by an "uninstall" patches.
 * - V1.2.36 01-JUN-2010: Suggest the user to update to JMS 1.2.30 that include a new letter tree directory structure.
 * - V1.2.38 10-JUN-2010: Add Joomla 1.6.0 Beta 2 compatibility.
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ checkJMSVers ---------------
/**
 * Compare the patch definition with JMS version in aim to be sure they are compatible
 */
function jms2win_checkJMSVers( $model, $file)
{
   jimport('joomla.filesystem.path');
   
   $result = "";
   $rc = '[OK]';

   $jms_vers         = MultisitesController::_getVersion();
	$patchesVersion   = $model->getPatchesVersion();
	
   // If Patch version is > 1.2.3 and JMS version < 1.2.0
   if ( (version_compare( $patchesVersion, '1.2.3') > 0)
     && (version_compare( $jms_vers, '1.2.0') < 0))
   {
      // Then require update JMS kernel to 1.2.0 or higher
	   $rc = '[NOK]';
      $result .= JText::_( 'The patch definition over 1.2.3 also require to update the JMS kernel to a version 1.2.0 or higher');
      $result .= '|[ACTION]';
      $result .= '|Go to JMS website to <a href="http://www.jms2win.com/get-latest-version">Get latest version</a>';
      $result .= '|In case of problem, also have a look in the <a href="http://www.jms2win.com/faq#sec-122">FAQ procedure</a> to get the latest version.';
      $result .= '|If you do not update the JMS kernel, you may have icons that will not be correctly displayed in JMS tool and JMS template sharing.';
      $result .= '|We recommand to also update the JMS kernel to also benefit of some fixes.';
   }
   // If Patch version is >= 1.2.10 and JMS version < 1.2.6
   else if ( (version_compare( $patchesVersion, '1.2.10') >= 0)
          && (version_compare( $jms_vers, '1.2.6') < 0))
   {
      // Then require update JMS kernel to 1.2.6 or higher
	   $rc = '[NOK]';
      $result .= JText::_( 'The patch definition 1.2.10 (or higher) also require to update the JMS kernel to a version 1.2.6 or higher');
      $result .= '|[ACTION]';
      $result .= '|Go to JMS website to <a href="http://www.jms2win.com/get-latest-version">Get latest version</a>';
      $result .= '|In case of problem, also have a look in the <a href="http://www.jms2win.com/faq#sec-122">FAQ procedure</a> to get the latest version.';
      $result .= '|If you do not update the JMS kernel, you will not be able to install some patches relative to Single Sign-In for sub-domains.';
      $result .= '|We also recommand to update the JMS kernel to benefit of other fixes and enhancement. See FAQ change history for more details.';
   }
   // If Patch version is >= 1.2.35 and JMS version < 1.2.30
   else if ( (version_compare( $patchesVersion, '1.2.35') >= 0)
          && (version_compare( $jms_vers, '1.2.30') < 0))
   {
      // Then require update JMS kernel to 1.2.30 or higher
	   $rc = '[NOK]';
      $result .= JText::_( 'The patch definition 1.2.35 (or higher) also require to update the JMS kernel to a version 1.2.30 or higher');
      $result .= '|[ACTION]';
      $result .= '|Go to JMS website to <a href="http://www.jms2win.com/get-latest-version">Get latest version</a>';
      $result .= '|In case of problem, also have a look in the <a href="http://www.jms2win.com/faq#sec-122">FAQ procedure</a> to get the latest version.';
      $result .= '|If you do not update the JMS kernel, you will not benefit of the new JMS internal structure to allow creating several thousand (and perhaps one million or more) of slave site from the front-end.';
      $result .= '|We also recommand to update the JMS kernel to benefit of other fixes and enhancement. See FAQ change history for more details.';
   }
   
	// Ensure that there is no backup of the Joomla 1.5 manifest file "install.xml"
	$manifest = 'administrator/components/com_multisites/install.xml';
	$filename = JPath::clean( JPATH_MUTLISITES_COMPONENT .DS. 'backup' .DS. $manifest);
	if ( JFile::exists( $filename)) {
	   JFile::delete( $filename);
	}
	$filename = JPath::clean( JPATH_MUTLISITES_COMPONENT .DS. 'backup_on_install' .DS. $manifest);
	if ( JFile::exists( $filename)) {
	   JFile::delete( $filename);
	}
   
	// Ensure that there is no backup of the Joomla 1.6 manifest file "extension.xml"
	$manifest = 'administrator/components/com_multisites/extension.xml';
	$filename = JPath::clean( JPATH_MUTLISITES_COMPONENT .DS. 'backup' .DS. $manifest);
	if ( JFile::exists( $filename)) {
	   JFile::delete( $filename);
	}
	$filename = JPath::clean( JPATH_MUTLISITES_COMPONENT .DS. 'backup_on_install' .DS. $manifest);
	if ( JFile::exists( $filename)) {
	   JFile::delete( $filename);
	}
	
   return $rc .'|'. $result;
}

//------------ _actionJMSVers ---------------
function jms2win_actionJMSVers( $model, $file)
{
   return true;
}
