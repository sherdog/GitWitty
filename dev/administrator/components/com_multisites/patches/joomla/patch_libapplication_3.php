//_jms2win_begin v1.2.11      _jms2win_restore_session_objects_
		$sess_id = $session->getId();
		if ($storage->load( $sess_id)) {
			$storage->update();

			// If the Registry Object is NOT present in the PHP session
			$registry = $session->get('registry');
			if ( empty( $registry)) {
			   // Create a new empty registry
      		$session->set('registry',	new JRegistry('session'));
			}
			
			// If the User Object is NOT present in the PHP session and there is a userid stored in the session table,
			$user = $session->get('user');
			if ( empty( $user) && !empty( $storage->userid)) {
			   // Rebuild the User object that the session has not restored.
			   $user = & JFactory::getUser( $storage->userid);
			   $user->set( 'guest',    $storage->guest);
			   $user->set( 'usertype', $storage->usertype);
			   $user->set( 'gid',      $storage->gid);
      		if ( $storage->guest == 0) {
         		$user->set( 'aid', 1);
         		$acl =& JFactory::getACL();
         		if ( $acl->is_group_child_of( $storage->usertype, 'Registered')
         		  || $acl->is_group_child_of( $storage->usertype, 'Public Backend'))
         		{
         			$user->set( 'aid', 2);
         		}
      		}
			   
      		// Save the user in the session.
      		$session->set('user', $user);
			}
			return $session;
		}
//_jms2win_end
/*_jms2win_undo
		if ($storage->load($session->getId())) {
			$storage->update();
			return $session;
		}
  _jms2win_undo */
