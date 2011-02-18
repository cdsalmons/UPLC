<?php

// Initialize UPLC
require_once '../init.php';

// Select the right library to test
if (@$_GET['lib']) {
	header('Content-Type: text/plain');
	require_once 'tests/'.$_GET['lib'].'.php';
}

// If no library was specified, display a list
else {
	header('Content-Type: text/html');
	require_once 'show-tests.php';
}

/* End of file index.php */
/* Location: ./test/index.php */
