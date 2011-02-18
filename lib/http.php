<?php

// A CR+LF constant
if (! defined('CRLF')) {
	define('CRLF', "\r\n");
}

// Transfer methods
define('UPLC_HTTP_TCP', 0);
define('UPLC_HTTP_SSL', 1);
define('UPLC_HTTP_TLS', 2);

/**
 * The HTTP class
 */
class Http_library extends Uplc_library {
	
	/**
	 * The request headers for the current script with some
	 * modifications. Used as the default headers for a new
	 * request.
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $request_headers;
	
	/**
	 * Constructor
	 *
	 * @access  public
	 * @return  void
	 */
	public function construct() {
		// Use the input library to get a list of headers
		import('input');
		$this->request_headers = Input()->headers();
		
		// Default to a closed connection
		$this->request_headers['Connection'] = 'close';
		
		// Remove any unwanted headers
		if (isset($this->request_headers['Cookie'])) {
			unset($this->request_headers['Cookie']);
		}
		if (isset($this->request_headers['Host'])) {
			unset($this->request_headers['Host']);
		}
	}
	
	/**
	 * Gets the default headers
	 *
	 * @access  public
	 * @return  array
	 */
	public function default_headers() {
		return $this->request_headers;
	}
	
	/**
	 * Opens a generic HTTP request
	 *
	 * @access  public
	 * @param   string    request method
	 * @return  Http_request
	 */
	public function open($method = 'GET') {
		return UPLC()->load_class('http-request', $method);
	}
	
	/**
	 * Perform an HTTP request
	 *
	 * @access  public
	 * @param   string    the host/url
	 * @param   string    request method
	 * @param   mixed     request body
	 * @return  Http_response
	 */
	public function request($url, $method = 'GET', $body = null) {
		return $this
			->open($method)
			->url($url)
			->send($body);
	}
	
	/**
	 * Perform an HTTP GET request
	 *
	 * @access  public
	 * @param   string    the host/url
	 * @param   mixed     request body
	 * @return  Http_response
	 */
	public function get($url, $body = null) {
		return $this->request($url, 'GET', $body);
	}
	
	/**
	 * Perform an HTTP POST request
	 *
	 * @access  public
	 * @param   string    the host/url
	 * @param   mixed     request body
	 * @return  Http_response
	 */
	public function post($url, $body = '') {
		return $this->request($url, 'POST', $body);
	}
	
}

/* End of file http.php */
/* Location: ./lib/http.php */
