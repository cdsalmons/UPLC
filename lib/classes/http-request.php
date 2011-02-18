<?php

/**
 * The HTTP request class
 */
class Http_request {
	
	/**
	 * The request host
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $host;
	
	/**
	 * The request URI and query string
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $uri;
	
	/**
	 * Any recieved error message
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $error_msg;
	
	/**
	 * Any recieved error number
	 *
	 * @access  protected
	 * @type    int
	 */
	protected $error_num;
	
	/**
	 * The request port
	 *
	 * @access  protected
	 * @type    int
	 */
	protected $port;
	
	/**
	 * Number of seconds before timeout
	 *
	 * @access  protected
	 * @type    int
	 */
	protected $timeout = 5;
	
	/**
	 * Transport scheme (TCP, SSL, TLS)
	 *
	 * @access  protected
	 * @type    bool
	 */
	protected $transport = UPLC_HTTP_TCP;
	
	/**
	 * Basic authentication data
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $auth;
	
	/**
	 * The request body
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $body;
	
	/**
	 * Should redirects be ignored
	 *
	 * @access  protected
	 * @type    bool
	 */
	protected $ignore_location = false;
	
	/**
	 * Maximum number of allowed redirects
	 *
	 * @access  protected
	 * @type    int
	 */
	protected $maximum_redirects = 5;
	
	/**
	 * Number of redirects made
	 *
	 * @access  protected
	 * @type    int
	 */
	protected $redirects = 0;
	
	/**
	 * Use HTTP 1.1?
	 *
	 * @access  protected
	 * @type    bool
	 */
	protected $http11 = true;

// ----------------------------------------------------------------------------
	
	/**
	 * Constructor
	 *
	 * @access  public
	 * @param   string    the request method
	 * @param   int       number of previous redirects
	 * @return  void
	 */
	public function __construct($method = 'GET', $redirects = 0) {
		$method = strtoupper($method);
		if (! in_array($method, array('GET', 'POST', 'HEAD'))) {
			trigger_error('Invalid HTTP request method "'.$method.'"', E_USER_ERROR);
		}
		$this->method = $method;
		$this->redirects = $redirects;
	}
	
	/**
	 * Set the request URL
	 *
	 * @access  public
	 * @param   string    the url
	 * @return  $this
	 */
	public function url($url) {
		// Check for a simple host name
		if (preg_match('/^[a-zA-Z0-9.\-]+$/', $url)) {
			$this->host = $url;
			$this->uri = '/';
			return $this;
		}
		
		// Parse the URL
		$url = parse_url($url);
		
		// Get the host name and path
		$this->host = @$url['host'];
		$this->uri  = @$url['path'].((isset($url['query'])) ? '?'.$url['query'] : '');
		
		// Check for a transport scheme
		if (isset($url['scheme'])) {
			switch ($url['scheme']) {
				case 'http':
				case 'tcp':
					$trans = UPLC_HTTP_TCP;
				break;
				case 'https':
				case 'ssl':
					$trans = UPLC_HTTP_SSL;
				break;
				case 'tls':
					$trans = UPLC_HTTP_TLS;
				break;
				default:
					trigger_error('Invalid transport scheme '.$url['scheme'], E_USER_ERROR);
				break;
			}
			$this->transport = $trans;
		}
		
		// Check for a port number
		if (isset($url['port'])) {
			$this->port = $url['port'];
		}
		
		// Check for user credentials
		if (isset($url['user'])) {
			$this->auth = array($url['user'], $url['pass']);
		}
		
		return $this;
	}
	
	/**
	 * Sets basic authentication credentials
	 *
	 * @access  public
	 * @param   string    username
	 * @param   string    password
	 * @return  $this
	 */
	public function auth($user = null, $pass = null) {
		$this->auth = ($user === null) ? null : array($user, $pass);
		return $this;
	}
	
	/**
	 * Activates/deactivates SSL
	 *
	 * @access  public
	 * @param   bool      use SSL?
	 * @return  $this
	 */
	public function transport($scheme = 'tcp') {
		switch ($scheme) {
			case 'http':
			case 'tcp':
			case UPLC_HTTP_TCP:
				$trans = UPLC_HTTP_TCP;
			break;
			case 'https':
			case 'ssl':
			case UPLC_HTTP_SSL:
				$trans = UPLC_HTTP_SSL;
			break;
			case 'tls':
			case UPLC_HTTP_TLS:
				$trans = UPLC_HTTP_TLS;
			break;
			default:
				trigger_error('Invalid transport scheme '.$url['scheme'], E_USER_ERROR);
			break;
		}
		$this->transport = $trans;
		return $this;
	}
	
	/**
	 * Sets the port number
	 *
	 * @access  public
	 * @param   int       the port number
	 * @return  $this
	 */
	public function port($port) {
		if (is_int($port)) {
			$this->port = $port;
		}
		return $this;
	}
	
	/**
	 * Sets the request body
	 *
	 * @access  public
	 * @param   mixed     the request body
	 * @return  $this
	 */
	public function request_body($body) {
		if (is_array($body) || is_object($body)) {
			$body = http_build_query($body);
		}
		$this->body = $body;
		return $this;
	}
	
	/**
	 * Set Location header preference
	 *
	 * @access  public
	 * @param   bool      ignore redirects?
	 * @return  $this
	 */
	public function ignore_redirects($ignore = true) {
		$this->ignore_location =!! $ignore;
		return $this;
	}
	
	/**
	 * Send the request
	 *
	 * @access  public
	 * @param   mixed     the request body
	 * @param   bool      just build the request, do no send
	 * @return  Http_response
	 */
	public function send($body = null, $debug = false) {
		if (isset($body)) {
			$this->request_body($body);
		}
		
		// Figure out the transport scheme and port
		switch ($this->transport) {
			case UPLC_HTTP_TCP:
				$host = $this->host;
				$port = 80;
			break;
			case UPLC_HTTP_SSL:
				$host = 'ssl://'.$this->host;
				$port = 443;
			break;
			case UPLC_HTTP_TLS:
				$host = 'tls://'.$this->host;
				$port = 443;
			break;
		}
		if (isset($this->port) && $this->port) {
			$port = $this->port;
		}
		
		// Open the connection
		$fp = @fsockopen($host, $port, $this->error_num, $this->error_msg, $this->timeout);
		if (! $fp) {
			trigger_error('Could not connect to '.$host.' on port '.$port.': '.$this->error_msg, E_USER_WARNING);
		}
		
		// Build the output
		$out   = array();
		$out[] = $this->method.' '.$this->uri.' HTTP/'.(($this->http11) ? '1.1' : '1.0');
		$out[] = 'Host: '.$this->host;
		
		// Add headers
		foreach (Http()->default_headers() as $header => $value) {
			$out[] = "${header}: ${value}";
		}
		
		// Add request body
		$out[] = '';
		$out[] = (is_string($this->body)) ? $this->body : '';
		
		if (! $debug) {
			// Send the output
			fwrite($fp, implode(CRLF, $out));
		
			// Receive the response header
			$header = '';
			do {
				$header .= fgets($fp, 4096);
			} while(strpos($header, CRLF.CRLF) === false);
			$header_arr = $this->decode_header($header);
		
			// Recieve the response body
			$response = '';
			while (! feof($fp)) {
				$response .= fgets($fp, 8192);
			}
			fclose($fp);
		
			// Decode the response body
			$response = $this->decode_body($header_arr, $response);
			
			// Handle Location header redirects
			if (! $this->ignore_location && isset($header_arr['Location'])) {
				$status = $header_arr['status'];
				// Given a redirect status, make the new request
				if ($status >= 300 && $status <= 400) {
					if ($this->redirects >= $this->maximum_redirects) {
						trigger_error('Maximum number of redirects exceeded', E_USER_WARNING);
						return false;
					}
					// Create the new request
					$new_request = new self($this->method, $this->redirects + 1);
					$new_request->url($header_arr['Location']);
				}
			}
		
			// Finish up
			return UPLC()->load_class('http-response', array(
				'header' => $header_arr,
				'body' => $response,
				'raw' => $header.$response
			));
		} else {
			fclose($fp);
			return implode(CRLF, $out);
		}
	}
	
// ----------------------------------------------------------------------------

	/**
	 * Decodes the header of an HTTP response
	 *
	 * @access  protected
	 * @param   string    the header string
	 * @return  array
	 */
	protected function decode_header($str) {
		$part = preg_split("/\r?\n/", $str, -1, PREG_SPLIT_NO_EMPTY);
		$out = array();
		
		for ($h = 0; $h < sizeof($part); $h++) {
			if ($h != 0) {
				$pos = strpos($part[$h], ':');
				$k = str_replace(' ', '', substr($part[$h], 0, $pos));
				$v = trim (substr($part[$h], ($pos + 1)));
			} else {
				$k = 'status';
				$v = explode(' ', $part[$h]);
				$v = (int) $v[1];
			}

			$out[$k] = $v;
		}

		return $out;
	}

	/**
	 * Decodes the body of an HTTP response
	 *
	 * @access  protected
	 * @param   array     parsed header data
	 * @param   string    the body string
	 * @param   string    the EOL marker
	 * @return  string
	 */
	protected function decode_body($info, $str, $eol = CRLF) {
		$tmp = $str;
		$add = strlen($eol);
		$str = '';
		
		if (isset ($info['Transfer-Encoding']) && $info['Transfer-Encoding'] == 'chunked') {
			do {
				$tmp = ltrim($tmp);
				$pos = strpos($tmp, $eol);
				$len = hexdec(substr($tmp, 0, $pos));
				
				if (isset($info['Content-Encoding'])) {
					$str .= gzinflate(substr($tmp, ($pos + $add + 10), $len));
				} else {
					$str .= substr($tmp, ($pos + $add), $len);
				}

				$tmp = substr($tmp, ($len + $pos + $add));
				$check = trim($tmp);
			} while (! empty($check));
		} else if (isset($info['Content-Encoding'])) {
			$str = gzinflate(substr($tmp, 10 ));
		} else {
			$str = $tmp;
		}
		
		return $str;
	}
		
}

/* End of file http-request.php */
/* Location: ./lib/classes/http-request.php */
