<?php

define('COMPRESS_GZIP', 1); // == FORCE_GZIP
define('COMPRESS_ZLIB', 2); // == FORCE_DEFLATE

class Compression_library {
	
	const GZIP = COMPRESS_GZIP;
	const ZLIB = COMPRESS_ZLIB;
	
	/**
	 * Constructor
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct() {
		
	}
	
	/**
	 * File extensions for compressed files
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $file_extensions = array(
		self::GZIP => '.gz',
		self::ZLIB => '.zlib'
	);
	
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
	 * @return  string
	 */
	public function compress($content, $compression_level = 9, $method = self::GZIP) {
		if (function_exists('gzencode')) {
			return gzencode($content, 9, $method);
		}
	}
	
	/**
	 * Compress a file
	 *
	 * @access  public
	 * @param   string    the input file
	 * @param   string    the output file
	 * @param   int       the compression level
	 * @param   int       the compression method
	 * @return  string or FALSE
	 */
	public function compress_file($input_file, $output_file = null, $compression_level = 9, $method = self::GZIP) {
		import_library('files');
		$should_return = ($output_file === true);
		if (! $output_file) {
			$output_file = $input_file.$this->file_extensions[$method];
		}
		if ($content = Files()->read($input_file)) {
			$content = $this->compress($content, $compression_level, $method);
			if ($should_return) return $content;
			return ((Files()->write($output_file, $content)) ? $output_file : false);
		}
		return false;
	}
	
}

/* End of file compression.php */
