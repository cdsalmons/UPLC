<?php

class Input_library {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		// Import super-globals
		$this->post_data   = $_POST;
		$this->get_data    = $_GET;
		$this->server_data = $_SERVER;
		$this->files_data  = $_FILES;
		$this->cookie_data = $_COOKIE;
		// Read the headers from the $_SERVER array
		$this->headers_data = array();
		foreach ($this->server_data as $key => $value) {
			$sections = explode('_', $key);
			if ($sections[0] == 'HTTP') {
				$sections = array_slice($sections, 1);
				$key = implode(' ', $sections);
				$key = str_replace(' ', '-', ucwords(strtolower($key)));
				$this->headers_data[$key] = $value;
			}
		}
	}
	
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
		import_library('compression');
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

}

/* End of file input.php */
