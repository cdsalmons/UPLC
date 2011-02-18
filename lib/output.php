<?php

class Output_library extends Uplc_library {
	
	/**
	 * Constructor
	 */
	public function construct() {
		
	}
	
	/**
	 * Holds on to HTTP status codes
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $status_codes = null;
	
	/**
	 * The server protocol
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $server_protocol = null;
	
	/**
	 * Sets the HTTP status code
	 *
	 * @access  public
	 * @param   int       the status code
	 * @return  void
	 */
	public function set_status($code) {
		if (! $this->status_codes) {
			$this->status_codes = import_resource('status-codes');
		}
		if (! $this->server_protocol) {
			$this->server_protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
		}
		$text = $this->status_codes[$code];
		header($this->server_protocol." ${code} ${text}", true, $code);
	}
	
	/**
	 * Sets a response header(s)
	 *
	 * Usage:
	 *  set_header('Header', 'value');
	 *  set_header('Header: value');
	 *  set_header(array(
	 *    'Header' => 'value'
	 *  ));
	 *  set_header(array(
	 *    'Header: value'
	 *  ));
	 *
	 * @access  public
	 * @param   mixed     header data 1
	 * @param   mixed     header data 2
	 * @return  void
	 */
	public function set_header($header1, $header2 = null) {
		if (is_string($header1)) {
			if (is_string($header2)) {
				header($header1.': '.$header2);
			} else {
				header($header1);
			}
		} elseif (is_array($header1)) {
			foreach ($header1 as $key => $value) {
				if (is_string($key)) {
					header($key.': '.$value);
				} else {
					header($value);
				}
			}
		}
	}
	
	/**
	 * The processor functions
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $processors = array();
	
	/**
	 * Processes a string with the requested functions
	 *
	 * @access  public
	 * @param   mixed     the string
	 * @return  void
	 */
	public function process($str) {
		$str = (string) $str;
		foreach ($this->processors as $processor => $active) {
			if ($active) {
				$str = (string) $processor($str);
			}
		}
		return $str;
	}
	
	/**
	 * Adds/removes/checks a processor function in the list
	 *
	 * @access  public
	 * @param   string    the function
	 * @param   bool      what to do
	 * @return  bool
	 */
	public function processor($func, $active = null) {
		if (isset($this->processors[$func])) {
			$this->processors[$func] = false;
		}
		if ($active !== null) {
			$this->processors[$func] = (bool) $active;
		}
		return $this->processors[$func];
	}
	
	/**
	 * Returns the list of processors
	 *
	 * @access  public
	 * @return  array
	 */
	public function get_processors() {
		return $this->processors;
	}
	
	/**
	 * Empty the list of processors
	 *
	 * @access  public
	 * @return  void
	 */
	public function clear_processors() {
		$this->processors = array();
	}
	
}

/* End of file output.php */
