<?php

class Files_library {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		require UPL_BASEPATH.'mimes.php';
		$this->mimes = $mimes;
	}

	/**
	 * File mime types by extension (limited down to only image files)
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $mimes = null;
	
	/**
	 * Read a file's contents
	 *
	 * @access  public
	 * @param   string    the file name
	 * @return  string
	 */
	public function read($file) {
		return @file_get_contents($file);
	}
	
	/**
	 * Write a string to a file
	 *
	 * @access  public
	 * @param   string    the file name
	 * @param   string    the file contents
	 * @return  void
	 */
	public function write($file, $contents) {
		return @file_put_contents($file, $contents);
	}
	
	/**
	 * Delete a file
	 *
	 * @access  public
	 * @param   string    the file name
	 * @return  void
	 */
	public function delete($file) {
		return @unlink($file);
	}
	
	/**
	 * Get the extension of a file
	 *
	 * @access  public
	 * @param   string    the file name
	 * @return  string
	 */
	public function extension($file) {
		$info = pathinfo($file);
		return $info['extension'];
	}
	
	/**
	 * Get the mime type of a file
	 *
	 * @access  public
	 * @param   string    the file name
	 * @return  string
	 */
	public function get_mime_type($file) {
		$ext = $this->extension($file);
		return ((isset($this->mimes[$ext])) ? $this->mimes[$ext] : 'text/plain');
	}
	
	/**
	 * Compares the modification timestamp of two files
	 *
	 * @access  public
	 * @param   string    file one
	 * @param   string    file two
	 * @return  int
	 */
	public function mtime_compare($file1, $file2) {
		$t1 = @filemtime($file1);
		$t2 = @filemtime($file2);
		if ($t1 > $t2) return 1;
		if ($t1 < $t2) return -1;
		return 0;
	}
	
	/**
	 * Checks if a directory exists, and if not, creates it. If
	 * an error occurs which results in no directory being created,
	 * this function will end the script. This function runs recursively.
	 *
	 * @access  public
	 * @param   string    the directory path
	 * @return  void
	 */
	public function touch_dir($path) {
		// Make sure the parent directory exists
		$parent = dirname($path);
		if (! is_dir($parent)) $this->touch_dir($parent);
		// Check if the directory exists
		if (! is_dir($path))
		{
			if (! @mkdir($path, 0777))
			{
				show_error('Could not create directory '.basename($path).'.', 500);
			}
		}
	}
	
}

/* End of file files.php */
