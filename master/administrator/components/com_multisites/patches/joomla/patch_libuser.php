//_jms2win_begin v1.1.8
function jms2win_isJACL_DB_OK() {
   $db =& JFactory::getDBO();
   $jacl_pattern = str_replace( '_' , '\_', $db->getPrefix() . 'jaclplus_');
   $db->setQuery( 'SHOW TABLES LIKE \''.$jacl_pattern.'%\'' );
   $tables = $db->loadResultArray();
   if ( empty( $tables)) {
      return false;
   }
   return true;
}

if (!file_exists( JPATH_CONFIGURATION . DS . 'configuration.php' ) || (filesize( JPATH_CONFIGURATION . DS . 'configuration.php' ) < 10) || file_exists( JPATH_INSTALLATION . DS . 'index.php' )) {}
else if ( !jms2win_isJACL_DB_OK()) {}
else 
//_jms2win_end
