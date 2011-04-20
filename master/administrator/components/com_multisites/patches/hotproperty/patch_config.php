//_jms2win_begin v1.1.11
if ( defined( 'MULTISITES_ID')) {
   jimport( 'joomla.filesystem.file');
   if ( !JFile::exists( HOTPROPERTY_ADMINISTRATOR . DS . 'configuration.' .MULTISITES_ID. '.php')
     &&  JFile::exists( HOTPROPERTY_ADMINISTRATOR . DS . 'configuration.php')
      )
   {
      JFile::copy( HOTPROPERTY_ADMINISTRATOR . DS . 'configuration.php', 
                   HOTPROPERTY_ADMINISTRATOR . DS . 'configuration.' .MULTISITES_ID. '.php');
   }
   define( 'HOTPROPERTY_CONFIGURATION',					HOTPROPERTY_ADMINISTRATOR . DS . 'configuration.' .MULTISITES_ID. '.php' );
}
else {
   define( 'HOTPROPERTY_CONFIGURATION',					HOTPROPERTY_ADMINISTRATOR . DS . 'configuration.php' );
}
//_jms2win_end
/*_jms2win_undo
define( 'HOTPROPERTY_CONFIGURATION',						HOTPROPERTY_ADMINISTRATOR . DS . 'configuration.php' );
  _jms2win_undo */
