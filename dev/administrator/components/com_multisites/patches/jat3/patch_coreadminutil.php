//_jms2win_begin v1.2.54
      if ( defined( 'MULTISITES_ID')) {
   		$path = JPATH_SITE.DS.'templates'.DS.$this->template.DS.'params_' . MULTISITES_ID . '.ini';
         if ( !file_exists( $path)) {
      		$path = JPATH_SITE.DS.'templates'.DS.$this->template.DS.'params.ini';
         }
		}
		else {
   		$path = JPATH_SITE.DS.'templates'.DS.$this->template.DS.'params.ini';
      }
//_jms2win_end
/*_jms2win_undo
		$path = JPATH_SITE.DS.'templates'.DS.$this->template.DS.'params.ini';
  _jms2win_undo */
