<?php

class Output_library {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		
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
	 * Outputs a variable to the client as HTML safe
	 *
	 * @access  public
	 * @param   mixed     the variable to output
	 * @return  void
	 */
	public function htmlspecialchars($str) {
		$str = (string) $str;
		$str = htmlspecialchars($str);
	}
	
}

/* End of file output.php */
