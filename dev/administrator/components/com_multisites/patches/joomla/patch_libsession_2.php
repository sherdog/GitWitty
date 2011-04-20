//_jms2win_begin v1.2.10
         if ( defined( 'MULTISITES_COOKIE_DOMAINS')) {
            $cookie_domains = explode( '|', MULTISITES_COOKIE_DOMAINS);
            foreach ( $cookie_domains as $cookie_domain) {
               if ( !empty( $cookie_domain)) {
         			setcookie(session_name(), '', time()-42000, '/', $cookie_domain);
         	   }
            }
         }
			setcookie(session_name(), '', time()-42000, '/');
//_jms2win_end
/*_jms2win_undo
			setcookie(session_name(), '', time()-42000, '/');
  _jms2win_undo */
