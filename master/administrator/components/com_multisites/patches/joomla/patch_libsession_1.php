//_jms2win_begin v1.2.10
      if ( defined( 'MULTISITES_COOKIE_DOMAINS')) {
         $cookie_domains = explode( '|', MULTISITES_COOKIE_DOMAINS);
         if ( !empty( $cookie_domains[0])) {
            $cookie_domain = $cookie_domains[0];
            ini_set('session.cookie_domain', $cookie_domain);
         }
      }
//_jms2win_end
