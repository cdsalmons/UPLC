<?php

class Compression_library {
	
	const GZIP = FORCE_GZIP;
	const DEFLATE = FORCE_DEFLATE;
	
	/**
	 * Constructor
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct() {
		$this->method = $this->determine_method();
	}
	
	/**
	 * Sets the content encoding header
	 *
	 * @access  public
	 * @return  void
	 */
	public function set_header() {
		if ($this->method) {
			header('Content-Encoding: '.$this->method);
		}
	}
	
	/**
	 * Compresses content
	 *
	 * @access  public
	 * @param   string    the content to compress
	 * @return  void
	 */
	public function compress($content, $compression_level = 9, $method = self::GZIP) {
		return gzencode($content, 9, $method);
	}
	
	/**
	 * Compress a file
	 *
	 * @access  public
	 * @param   string    the input file
	 * @param   string    the output file
	 * @param   int       the compression level
	 * @param   int       the compression method
	 * @return  mixed
	 */
	public function compress_file($input_file, $output_file = null, 
	
}

function &Compression() {
	return Compression_library::get_instance();
}

/* End of file compression.php */
