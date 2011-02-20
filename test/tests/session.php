<?php

// Import the session library
import('input', 'session');

// Load the correct mode
switch (Input()->get('mode')) {

	// Installs the session database table
	// ?lib=session&mode=install
	case 'install':
		if (Session()->install_session_table()) {
			echo 'Install successful!';
		} else {
			echo 'Install failed!';
			$error = Database(0)->last_error();
			echo "\n${error[1]}";
		}
	break;
	
	// Runs the basic test
	// ?lib=session&mode=run_test
	case 'run_test':
		if ($val = Session()->get_item('hai')) {
			echo $val;
		} else {
			echo 'Storing session value...  Refresh to see it be read!';
			Session()->set_item('hai', "Hai there :D\nI came out of your session!");
		}
	break;
	
	// Destroys the session (reset)
	// ?lib=session&mode=destroy
	case 'destroy':
		Session()->destroy();
		echo 'Session destroyed.';
	break;
	
	// Emtpies the session table
	// ?lib=session&mode=empty
	case 'empty':
		Session()->empty_session_table();
		echo 'Session table emptied.';
	break;
	
}

/* End of file session.php */
/* Location: ./test/tests/session.php */
