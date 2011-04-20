//_jms2win_begin v1.2.54      _jms2win_fix_j1_5_22_
// remove the code introduced in Joomla 1.5.16 and higher to accept Single Sign-In
//_jms2win_end
/*_jms2win_undo
			$session = &JFactory::getSession();

			// we fork the session to prevent session fixation issues
			$session->fork();
			$this->_createSession($session->getId());
  _jms2win_undo */
