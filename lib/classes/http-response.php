<?php

class Http_response {
	
	/**
	 * The raw response content
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $response_raw;
	
	/**
	 * The response headers
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $response_headers;
	
	/**
	 * The repsonse body
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $response_body;
	
	/**
	 * Constructor
	 */
	public function __construct($response) {
		$this->response_raw     = $response['raw'];
		$this->response_headers = $response['header'];
		$this->response_body    = $response['body'];
	}
	
	/**
	 * Retrieves the status code
	 *
	 * @access  public
	 * @return  int
	 */
	public function status() {
		return $this->response_headers['status'];
	}
	
	/**
	 * Retrieves an array of headers
	 *
	 * @access  public
	 * @return  array
	 */
	public function headers($which = null) {
		if ($which === null) {
			return $this->response_headers;
		} else {
			return (isset($this->response_headers[$which]) ? $this->response_headers[$which] : false);
		}
	}
	
	/**
	 * Retrieves the response body
	 *
	 * @access  public
	 * @return  string
	 */
	public function body() {
		return $this->response_body;
	}
	
	/**
	 * Get the raw response
	 *
	 * @access  public
	 * @return  string
	 */
	public function raw_response() {
		return $this->response_raw;
	}
	
}

/* End of file http-response.php */
/* Location: ./lib/classes/http-response.php */
