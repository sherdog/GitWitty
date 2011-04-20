//_jms2win_begin v1.2.33      _jms2win_fix_j1_5_16_
		   $session->fork();
		   $this->_createSession($session->getId());
//_jms2win_end
/*_jms2win_undo
			$session->fork();
  _jms2win_undo */
