<?php

// Set plain text output
header('Content-Type: text/plain');

// Initialize UPLC
require_once 'init.php';

// Load the database library
import_library('database');

// Open a MySQL connection
Database()->open('test', 'mysql://testuser:testpass@localhost');

// Output a list of databases
print_r(
	Database('test')->list_dbs()
);







/* End of file test.php */
