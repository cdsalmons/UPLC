<?php

// Load the HTTP library
import_library('http');

// Try a basic GET request
echo Http()
	->open('GET')
	->url('home.kbjr.local')
	->send(null)
	->raw_response();

/* End of file http.php */
/* Location: ./test/http.php */
