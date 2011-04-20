//_jms2win_begin v1.2.10
               if ( defined( 'MULTISITES_COOKIE_DOMAINS')) {
                  $cookie_domains = explode( '|', MULTISITES_COOKIE_DOMAINS);
                  $hash = JUtility::getHash('JLOGIN_REMEMBER');
                  foreach ( $cookie_domains as $cookie_domain) {
                     if ( !empty( $cookie_domain)) {
         					setcookie( $hash, $rcookie, $lifetime, '/', $cookie_domain);
                     }
                     else {
         					setcookie( $hash, $rcookie, $lifetime, '/' );
                     }
                  }
               }
               else {
   					setcookie( JUtility::getHash('JLOGIN_REMEMBER'), $rcookie, $lifetime, '/' );
   				}
//_jms2win_end
/*_jms2win_undo
					setcookie( JUtility::getHash('JLOGIN_REMEMBER'), $rcookie, $lifetime, '/' );
  _jms2win_undo */
