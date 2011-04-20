<?php
/**
 * @file       check_saveli.php
 * @brief      Check if the 'helpers/update.php' files contains the Multi Sites patches 
 *             to allow a specific acymailing license for each websites.
 *
 * @version    1.2.27
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
 * - V1.2.22  26-DEC-2009: Initial version
 * - V1.2.27  10-FEB-2010: Only apply the patch when using an AcyMailing with a license.
 *                         When this is a free AcyMailing, there is no license info and therefore
 *                         no patch to perform.
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkAcyMailingSaveLi ---------------
/**
 * check if following lines are present:
 * - if ( defined( 'MULTISITES_HOST'))
 *   is present
 */
function jms2win_checkAcyMailingSaveLi( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[IGNORE]|File Not Found';
	}
	
   $str = file_get_contents( $filename);

   // Check that this is an AcyMailing version with a license.
   // Otherwise, ignore the patch
   $pos = strpos( $str, "'li.txt'");
   if ($pos === false) {
	   return '[IGNORE]|File Not Found';
   }
   
   // if 'MULTISITES_ID' is present
   $pos = strpos( $str, 'MULTISITES_ID');
   if ($pos === false) $wrapperIsPresent = false;
   else                $wrapperIsPresent = true;
   
   $result = "";
   $rc = '[OK]';
   if ( !$wrapperIsPresent) {
	   $rc = '[NOK]';
      $result .= JText::_( 'The specific AcyMailing license for each websites is not present.');
      $result .= '|[ACTION]';
      $result .= '|Add twice 3 lines to have specific license file for each slave site.';
   }
   
   return $rc .'|'. $result;
}


//------------ jms2win_actionAcyMailingSaveLi ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionAcyMailingSaveLi( $model, $file)
{
   $parts = explode( DS, dirname(__FILE__));
   array_pop( $parts );
   $patch_dir = implode( DS, $parts );
   
   include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( '..' .DS. 'acymailing' .DS. 'patch_saveli.php');
   if ( $patchStr === false) {
      return false;
   }


//	$filename = JPATH_ROOT.DS.$file;
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
   $content = file_get_contents( $filename);
   if ( $content === false) {
      return false;
   }
   
   // Search/Replace the statement
   /*
      ===========
      Search for:
      ===========
		........
		........
		$path = ACYMAILING_BACK.'li.txt';
		........
		$path = ACYMAILING_BACK.'li.txt';
		........
      
      ===========
      and Replace by:
      ===========
		........
		$path = ACYMAILING_BACK.'li.txt';
//_jms2win_begin v1.2.22
		if ( defined( 'MULTISITES_ID')) {
   		$path = ACYMAILING_BACK.'li.' . MULTISITES_ID . '.txt';
		}
//_jms2win_end
		........
		........
		$path = ACYMAILING_BACK.'li.txt';
//_jms2win_begin v1.2.22
		if ( defined( 'MULTISITES_ID')) {
   		$path = ACYMAILING_BACK.'li.' . MULTISITES_ID . '.txt';
		}
//_jms2win_end
		........

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n .... $path = ACYMAILING_BACK.'li.txt'; .....\n
      p0                              p1             p2 
      
      \n .... $path = ACYMAILING_BACK.'li.txt'; .....\n
      p4                              p5             p6 
      
      Produce
      begin -> p2 + INSERT PATCH + (p2+1) -> p6 + INSERT PATCH + (p6+1)-> end
      
    */
   
   // p1: Search for 'li.txt'
   $p1 = strpos( $content, "'li.txt'");
   if ( $p1 === false) {
      return false;
   }

   // p2: Search for "\n"
   $p2 = strpos( $content, "\n", $p1);
   if ( $p2 === false) {
      return false;
   }
   $p2++;
 
   // p5: Search for 'li.txt'
   $p5 = strpos( $content, "'li.txt'", $p2);
   if ( $p5 === false) {
      return false;
   }

   // p6: Search for "\n"
   $p6 = strpos( $content, "\n", $p5);
   if ( $p6 === false) {
      return false;
   }
   $p6++;

 
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p2)
           . $patchStr
           . substr( $content, $p2, $p6-$p2)
           . $patchStr
           . substr( $content, $p6)
           ;

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

   return true;
}

