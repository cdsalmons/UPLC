<?php

// Set plain text output
header('Content-Type: text/plain');

// Initialize UPLC
require_once 'init.php';

// Load the database library
import_library('database');

// Open a MySQL connection
Database()->open('test', 'mysql://testuser:testpass@localhost/testdb');

// Output a list of databases
print_r(
	Database('test')->list_dbs()
);

// Describe a table
print_r(
	Database('test')->describe('testtable')
);

// Empty out the table
Database('test')->empty_table('testtable');

// Insert a value
$value = md5(rand());
Database('test')->insert('testtable', array('value' => $value));

// View the table contents
print_r(
	Database('test')->select('testtable')
);

// Output the actual queries that were run
print_r(
	Database('test')->query_log()
);

/* End of file test.php */
