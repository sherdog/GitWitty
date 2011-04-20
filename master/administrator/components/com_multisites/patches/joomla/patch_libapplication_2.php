//_jms2win_begin v1.2.10
         $hash = JUtility::getHash('JLOGIN_REMEMBER');
         if ( defined( 'MULTISITES_COOKIE_DOMAINS')) {
            $cookie_domains = explode( '|', MULTISITES_COOKIE_DOMAINS);
            foreach ( $cookie_domains as $cookie_domain) {
               if ( !empty( $cookie_domain)) {
         			setcookie( $hash, false, time() - 86400, '/', $cookie_domain);
         		}
            }
         }
			setcookie( $hash, false, time() - 86400, '/' );
//_jms2win_end
/*_jms2win_undo
			setcookie( JUtility::getHash('JLOGIN_REMEMBER'), false, time() - 86400, '/' );
  _jms2win_undo */
