<?php

class Input_library {
	
	/**
	 * Holds on to request headers
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $headers = null;
	
	/**
	 * Reads request headers
	 *
	 * @access  public
	 * @param   string    the header to read
	 * @return  string
	 */
	public function request_headers($header = null) {
		// Read the headers from the $_SERVER array
		if (! $this->headers) {
			$this->headers = array();
			foreach ($_SERVER as $key => $value) {
				$sections = explode('_', $key);
				if ($sections[0] == 'HTTP') {
					$sections = array_slice($sections, 1);
					$key = implode(' ', $sections);
					$key = str_replace(' ', '-', ucwords(strtolower($key)));
					$this->headers[$key] = $value;
				}
			}
		}
		// Return the requested header, or, an array of all headers
		if ($header) {
			return ((isset($this->headers[$header])) ? $this->headers[$header] : false);
		} else {
			return $this->headers;
		}
	}
	
	/**
	 * The optimal encoding method
	 *
	 * @access  protected
	 * @type    int
	 */
	protected $encoding = null;
	
	/**
	 * Determine the optimal encoding method
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function determine_encoding() {
		import_library('compression');
		if ($this->encoding === null) {
			if (function_exists('gzencode')) {
				$encoding = explode(',', $_SERVER['HTTP_ACCEPT_ENCODING']);
				foreach($encoding as $i => $j) {
					$encoding[$i] = trim($j);
				}
				if (in_array('gzip', $encoding)) {
					$this->encoding = Compression()::GZIP;
				} elseif (in_array('deflate', $encoding)) {
					$this->encoding = Compression()::DEFLATE;
				} else {
					$this->encoding = false;
				}
			}
		}
		return $this->encoding;
	}

}

/* End of file input.php */
