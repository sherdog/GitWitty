<?php
/**
 * @file       tld2win.php
 * @brief      TLD (Top Level Domain) file converter.
 *
 * @version    1.2.29
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
 * - V1.0.0 12-DEC-2009: Initial version
 * - V1.0.1 20-APR-2010: Avoid using the "rec" reference variable when not required to avoid PHP fatal
 *                       on some system. It seems that it may take a lot of memory.
 * - V1.2.29 17-MAY-2010: Give the possibility to disable the TLD processing to speed-up the website creation.
 */

require_once( dirname( __FILE__) .DS. 'utils.php');


// ===========================================================
//             TLD2Win class
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
class TLD2Win
{
   var $_tld_rows = null;
   
   //------------------- Constructor ---------------
   function &getInstance()
   {
		static $instance;

		if (!is_object($instance))
		{
		   $instance = new TLD2Win();
		}
		
		return $instance;
   }

   //------------ _processRow ---------------
   /**
    * @brief Process a tabulated row to normalize the info
    */
	function _processRow( $row)
	{
	   $result = array();
	   
	   $ncol = count( $row);
	   if ( $ncol > 0) {
   	   $tld1 = strtolower( $row[0]);
   	   if ( $ncol > 1) {
   	      if ( !empty( $row[1])) {
      	      $type = strtoupper( substr( $row[1], 0, 1));
   	      }
   	      if ( !empty( $type)) {
   	         // A	Only second level domains	*.com, *.nl
   	         if ( $type == 'A') {
   	            if ( $ncol > 2) {
   	               // Process it as C (mixed)
   	               $dl = '# ' . $row[2];
   	            }
   	            else {
      	            $dl = '#';
   	            }
   	         }
   	         // B	Only third level domains	*.co.uk
   	         else if ( $type == 'B') {
   	            if ( $ncol > 2) {
   	               $dl = $row[2];
   	            }
   	            else {
      	            $dl = null; // Error
   	            }
   	         }
   	         // C	Mixed second and third level domains	*.be, *.ac.be
   	         else if ( $type == 'C') {
   	            if ( $ncol > 2) {
   	               $dl = '# ' . $row[2];
   	            }
   	            else {
      	            $dl = '#';
   	            }
   	         }
   	         // D	Other
   	         else if ( $type == 'D') {
   	               // Process it as C (mixed)
   	            if ( $ncol > 2) {
   	               $dl = $tld1 . ' ' . $row[2];
   	            }
   	            else {
      	            $dl = $tld1;
   	            }
   	         }
   	         // Default
   	         else {
   	               // Process it as C (mixed)
   	            if ( $ncol > 2) {
   	               $dl = $tld1 . ' ' . $row[2];
   	            }
   	            else {
      	            $dl = $tld1;
   	            }
   	         }
   	      }
   	   }
	   }
	   
	   if ( !empty( $dl)) {
	      $tldsuffix = array();
	      $domlist = explode( ' ', $dl);
	      foreach( $domlist as $dom) {
	         if ( !empty( $dom )) {
	            $d = ltrim( $dom, '.');
	            $tld3rd = explode( '.', $d);
	            $n = count( $tld3rd);
	            if ( $n >= 2) {
	               if ( strtolower( $tld3rd[$n-1] == $tld1)) {
	                  array_pop( $tld3rd);
	               }
	               else {
	                  echo "Error on " . var_export( $row, true) . '<br />';
	                  echo "- TDL1=[$tld1] TLD3 = ". var_export( $tld3rd, true) . '<br />';
	               }
	            }
	            else if ( $n == 1) {
	               if ( strtolower( $tld3rd[$n-1]) == $tld1) {
	                  array_pop( $tld3rd);
	               }
	               // If mixed, keep the #
	               else if ( $tld3rd[$n-1]=='#') {}
	               else {
	                  echo "Error 2 on " . var_export( $row, true) . '<br />';
	                  echo "-no2 TDL1=[$tld1] TLD3 = ". var_export( $tld3rd, true) . '<br />';
	               }
	            }
	            else { 
                  echo "Error no3 - Unexpected empty tld3rd on " . var_export( $row, true) . '<br />';
	            }
	            
	            if ( count( $tld3rd) == 1) {
	               $str = $tld3rd[0];
   	            $tldsuffix[$str] = '#';
	            }
	            else if ( count( $tld3rd) > 1) {
	               if ( $type == 'D') {
	                  if ( count( $tld3rd) == 2) {
	                     if ( !empty( $tldsuffix[$tld3rd[1]])) {
   	                     if ( is_array( $tldsuffix[$tld3rd[1]])) {
   	                        $tldsuffix[$tld3rd[1]][$tld3rd[0]] = '#';
   	                     }
   	                     else {
   	                        $tldsuffix[$tld3rd[1]] = array( $tldsuffix[$tld3rd[1]]=>$tldsuffix[$tld3rd[1]], $tld3rd[0] => '#');
   	                     }
	                        
	                     }
	                     else {
   	                     $tldsuffix[$tld3rd[1]] = array($tld3rd[0]=>'#');
	                     }
	                  }
	                  else {
                        echo "Error no5 - Unexpected tld3rd size on " . var_export( $row, true) . '<br />';
                        echo var_export( $tld3rd, true) . '<br />';
	                  }
	               }
	               else {
                     echo "Error no4 - Unexpected tld3rd size on " . var_export( $row, true) . '<br />';
                     echo var_export( $tld3rd, true) . '<br />';
	               }
	            }
	         }
	      }
	   }
	   
	   if ( !empty( $tld1)) {
	      if ( !empty( $tldsuffix)) {
      	   $result[$tld1] = $tldsuffix;
	      }
	      else {
      	   $result[$tld1] = '#';
	      }
	   }
	   
	   return $result;
	}

   //------------  loadtab ---------------
   /**
    * @brief Load a TLD files in format
    * TLD | type | List of domains
    * Types:
    * - A	Only second level domains	*.com, *.nl
    * - B	Only third level domains	*.co.uk
    * - C	Mixed second and third level domains	*.be, *.ac.be
    * - D	Other	
    *
    * @notes
    * Source URL: https://wiki.mozilla.org/TLD_List
    */
	function loadtab( $filename)
	{
      $handle = fopen( $filename, "r");
      if ($handle ) {
         $this->_tld_rows = array();
         for ( $i=0; !feof($handle); $i++) {
            $str = fgets($handle, 4096);
            $line = trim( $str);
            // If empty line or comment (;)
            if ( empty( $line) || substr( $line, 0, 1) == ';' ) {
               // Skip the line
               continue;
            }
   	      // Read the column values
   	      $row = array();
            $fields = explode( "\t", $line);
            $j = 0;
            foreach( $fields as $field) {
               $row[$j] = trim( str_replace( '"', '', $field));
               $j++;
            }
            $tld_rec = $this->_processRow( $row);
            $row['_tlds'] = $tld_rec;
            $this->_tld_rows[strtolower($row[0])] = $row;
         }
         fclose($handle);
      }
	}

   //------------ exportTLDs ---------------
   /**
    * @brief Export the Top Level Domain name into an array to speed up it loading and processing
    */
	function exportTLDs( $filename)
	{
		$content = "<?php\n"
		         . '$tlds = array( ' . "\n"
		         . MultisitesUtils::CnvArray2Str( '', $this->_tld_rows) . "\n"
		         . ");\n"
		         . "?>"
		         ;

      $fp = fopen( $filename, "w");
      fputs( $fp, $content);
      fclose( $fp);
	}

   //------------ importTLDs ---------------
   /**
    * @brief Import the TLDs array
    */
	function importTLDs( $filename=null, $forceReload=false)
	{
	   // If already imported and not forced to reload
	   if ( !empty( $this->_tld_rows) && !$forceReload) {
	      return;
	   }
	   
	   // Check if there is a filename or apply a default one
	   if ( empty( $filename)) {
	      $filename = dirname( __FILE__) .DIRECTORY_SEPARATOR. 'tld2win.data.php';
	   }
	   // try import the TLDs data
	   @include $filename;
	   if ( isset( $tlds)) {
	      $this->_tld_rows = &$tlds;
	   }
	}

   //------------ splitHost ---------------
   /**
    * @brief Parse a host name and split it into TDLs elements.
    * For example www.domain.co.uk is splitted into 3 elements
    * - [0] = www
    * - [1] = domain
    * - [2] = co.uk
    */
	function splitHost( $host)
	{
	   // Start to split the host with dot '.' separator
      $parts = explode( '.', $host);
      
      if ( empty( $parts)) {
         return $parts;
      }
      
      // If disabled the Multisites Top Level Domain parsing
      if ( defined( 'MULTISITES_TLD_PARSING') && MULTISITES_TLD_PARSING == false) {
         return $parts;
      }
      
      if ( empty( $this->_tld_rows)) {
         // Try to import the default data file.
         $this->importTLDs();
         // If still empty
         if ( empty( $this->_tld_rows)) {
            return $parts;
         }
      }
      
      $tlds = &$this->_tld_rows;
      $result = -1;  // Not found
      $n = count( $parts);
      for ( $i=$n-1; $i>=0; $i--) {
         $name = strtolower( $parts[$i]);
         // If there is no entry in the list of TLDs corresponding to the current tld part
         if ( empty( $tlds[ $name])) {
            // Stop the analysis
            break;
         }
         else {
            if ( is_array( $tlds[ $name])) {
               $rec = &$tlds[ $name];
               // If this is the top level definition, it contains a '_tlds' field.
               if ( !empty( $rec['_tlds'][$name])) {
                  $tlds = &$rec['_tlds'][$name];
               }
               else {
                  $tlds = &$rec;
               }
               // If this tld part also exists as terminal elements
               if ( !empty( $tlds['#'])) {
                  // Save the valid solution
                  $result = $i;
               }
            }
            // This is a string
            else {
               // Check this is a valid terminal node
               if ( $tlds[ $name] == '#') {
                  $result = $i;
               }
               break;
            }
         }
      }
      
      // If there is no solution
      if ( $result < 0) {
         // return the default dot (.) split processing
         return $parts;
      }
      // Otherwise, merge the parts of the tlds starting at the result position
      $suffix = '';
      // remove the additional parts that must be merged and remember their suffix
      while( count( $parts) > ($result+1)) {
         $suffix = '.' . array_pop( $parts) . $suffix;
      }
      // Finally merge (concatanate the suffix) in a single TLD element
      $parts[$result] .= $suffix;
      return $parts;
	}

} // End class
