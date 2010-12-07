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
	}
	
}

/* End of file output.php */
