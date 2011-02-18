<?php

// Load the scalar typehinting library
import('typehint');

// Create a function with scalar typehinting
function foo(string $arg1, int $arg2) {
	return str_repeat($arg1, $arg2);
}

// Try running the function...
echo "Running: foo('bar', 2)\n";
echo "Should output: 'barbar'\n";
echo "Output: ".foo('bar', 2)."\n\n";

/* End of file typehint.php */
/* Location: ./test/tests/typehint.php */
