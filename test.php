<?php

// Set plain text output
header('Content-Type: text/plain');

// Initialize UPLC
require_once 'init.php';

// Select the right library to test
if (@$_GET['lib']) {
	$lib = $_GET['lib'];
	require_once 'test/'.$lib.'.php';
}

/* End of file test.php */
/* Location: ./test.php */
