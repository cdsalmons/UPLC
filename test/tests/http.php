<?php

// Load the HTTP library
import('http');

// Try a basic GET request
echo Http()
	->open('GET')
	->url('home.kbjr.local')
	->send(null)
	->raw_response();

/* End of file http.php */
/* Location: ./test/tests/http.php */
