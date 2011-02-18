<?php

// Load the database library
import('database');

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
	Database('test')->select('*')->from('testtable')->get()
);

// Output the actual queries that were run
print_r(
	Database('test')->query_log()
);

/* End of file db.php */
/* Location: ./test/tests/db.php */
