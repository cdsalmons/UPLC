<?php

class Input_library extends Uplc_library {
	
	/**
	 * The POST data
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $post_data = null;
	
	/**
	 * The GET data
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $get_data = null;
	
	/**
	 * The SERVER data
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $server_data = null;
	
	/**
	 * The FILES data
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $files_data = null;
	
	/**
	 * The COOKIE data
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $cookie_data = null;
	
	/**
	 * The request headers
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $headers_data = null;
	
	/**
	 * The optimal encoding method
	 *
	 * @access  protected
	 * @type    int
	 */
	protected $encoding = null;
	
	/**
	 * The user agent string
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $user_agent = null;
	
	/**
	 * The IP address
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $ip_address = null;
	
// ----------------------------------------------------------------------------
	
	/**
	 * Constructor
	 */
	public function construct() {
		// Import super-globals
		$this->post_data   = $_POST;
		$this->get_data    = $_GET;
		$this->server_data = $_SERVER;
		$this->files_data  = $_FILES;
		$this->cookie_data = $_COOKIE;
		// Read the headers
		if (function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
		} else {
			$headers = array(
				'Content-Type' => ((isset($_SERVER['CONTENT_TYPE'])) ? $_SERVER['CONTENT_TYPE'] : @env('CONTENT_TYPE'))
			);
			foreach ($this->server_data as $key => $value) {
				if (substr($key, 0, 5) === 'HTTP_') {
					$key = substr($key, 5);
					$headers[$key] = $value;
				}
			}
		}
		// Parse A_HEADER to A-Header
		$headers = array();
		foreach ($headers as $key => $value) {
			$key = str_replace('_', ' ', strtolower($key));
			$key = str_replace(' ', '-', ucwords($key));
			$headers[$key] = $value;
		}
		$this->headers_data = $headers;
	}
	
	/**
	 * Read the user agent string
	 *
	 * @access  public
	 * @return  string
	 */
	public function user_agent() {
		if ($this->user_agent === null) {
			$this->user_agent = $this->headers('User-Agent');
		}
		
		return $this->user_agent;
	}
	
	/**
	 * Read the IP address
	 *
	 * @access  public
	 * @return  string
	 */
	public function ip_address($default = false) {
		if ($this->ip_address === null) {
			// Try to find the client IP address
			if ($this->server('REMOTE_ADDR') && $this->server('HTTP_CLIENT_IP')) {
				$this->ip_address = $this->server('HTTP_CLIENT_IP');
			} elseif ($this->server('REMOTE_ADDR')) {
				$this->ip_address = $this->server('REMOTE_ADDR');
			} elseif ($this->server('HTTP_CLIENT_IP')) {
				$this->ip_address = $this->server('HTTP_CLIENT_IP');
			} elseif ($this->server('HTTP_X_FORWARDED_FOR')) {
				$this->ip_address = $this->server('HTTP_X_FORWARDED_FOR');
			}
			
			// If no IP could be found, default to FALSE
			if (! $this->ip_address) {
				$this->ip_address = false;
			}
			
			// If a list was given, select the last one
			elseif (strpos($this->ip_address, ',') !== false) {
				$ip = explode(',', $this->ip_address);
				$this->ip_address = trim(end($ip));
			}
			
			// Check the IP's validity
			if (! $this->is_ip_address($this->ip_address)) {
				$this->ip_address = false;
			}
		}
		
		return (($this->ip_address === false) ? $default : $this->ip_address);
	}
	
	/**
	 * Checks if an IP address is valid
	 *
	 * @access  public
	 * @param   string    the ip to test
	 * @return  bool
	 */
	public function is_ip_address($ip) {
		if (! is_string($ip)) {
			return false;
		}
	
		$ip = explode('.', $ip);

		// Check the basic form
		if (count($ip) != 4 || $ip[0][0] == '0') {
			return false;
		}
		
		// Check individual segment values
		foreach ($ip as $seg) {
			if ($seg == '' || preg_match("/[^0-9]/", $seg) || $seg > 255 || strlen($seg) > 3) {
				return false;
			}
		}

		return true;
	}
	
	/**
	 * Read a POST variable
	 *
	 * @access  public
	 * @param   string    the variable to read
	 * @return  mixed
	 */
	public function post($item = null) {
		return $this->read_data('post', $item);
	}
	
	/**
	 * Read a GET variable
	 *
	 * @access  public
	 * @param   string    the variable to read
	 * @return  mixed
	 */
	public function get($item = null) {
		return $this->read_data('get', $item);
	}
	
	/**
	 * Read a REQUEST variable, giving preference to POST
	 *
	 * @access  public
	 * @param   string    the variable to read
	 * @return  mixed
	 */
	public function request($item = null) {
		if ($item) {
			$result = $this->post($item);
			if (! $result) {
				$result = $this->get($item);
			}
		} else {
			$result = array_merge(
				$this->get(), $this->post()
			);
		}
		return $result;
	}
	
	/**
	 * Read a SERVER variable
	 *
	 * @access  public
	 * @param   string    the variable to read
	 * @return  mixed
	 */
	public function server($item = null) {
		return $this->read_data('server', $item);
	}
	
	/**
	 * Read a FILES variable
	 *
	 * @access  public
	 * @param   string    the variable to read
	 * @return  mixed
	 */
	public function files($item = null) {
		return $this->read_data('files', $item);
	}
	
	/**
	 * Read a COOKIE variable
	 *
	 * @access  public
	 * @param   string    the variable to read
	 * @return  mixed
	 */
	public function cookie($item = null) {
		return $this->read_data('cookie', $item);
	}
	
	/**
	 * Reads request headers
	 *
	 * @access  public
	 * @param   string    the header to read
	 * @return  string
	 */
	public function headers($item = null) {
		return $this->read_data('headers', $item);
	}
	
	/**
	 * Determine the optimal encoding method
	 *
	 * @access  public
	 * @param   bool      allow deflate compression?
	 * @return  mixed
	 */
	public function determine_encoding($allow_deflate = false) {
		import('compression');
		if ($this->encoding === null) {
			if (function_exists('gzencode')) {
				$encoding = explode(',', $this->header('Accept-Encoding'));
				foreach($encoding as $i => $j) {
					$encoding[$i] = trim($j);
				}
				if (in_array('gzip', $encoding)) {
					$this->encoding = COMPRESS_GZIP;
				} elseif (in_array('deflate', $encoding) && $allow_deflate) {
					$this->encoding = COMPRESS_ZLIB;
				} else {
					$this->encoding = false;
				}
			}
		}
		return $this->encoding;
	}

// ----------------------------------------------------------------------------
	
	/**
	 * Reads from a *_data array
	 *
	 * @access  protected
	 * @param   string    the array to read
	 * @param   string    the item to read
	 * @return  mixed
	 */
	protected function read_data($arr, $item = null) {
		$arr = $arr.'_data';
		$arr = $this->$arr;
		if ($item) {
			if (array_key_exists($item, $arr)) {
				return $arr[$item];
			} else {
				return false;
			}
		} else {
			return $arr;
		}
	}

}

/* End of file input.php */
