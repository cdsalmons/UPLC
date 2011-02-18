<?php

// Import the hashing library
import('hashing', 'datetime');

// The string to hash
$str = 'Hello World!';

// Start a timer to benchmark
$timer = Datetime()->get_timer();
$timer->start();

// Try a hash
echo "Hashing '${str}'\n".Hashing()->shash($str, true, 8413)."\n";

// Output the benchmark result
echo 'Runtime took '.$timer->get_time()." seconds\n\n";

// Reset the timer
$timer->start();

// Now, rehash using hmac
echo "Hashing '${str}' using hmac\n".Hashing()->shash_hmac($str, 'hi', true, 8413)."\n";

// Output the benchmark result
echo 'Runtime took '.$timer->get_time().' seconds';

/* End of file hashing.php */
/* Location: ./test/tests/hashing.php */
