<?php

class Files_library extends Uplc_library {
	
	/**
	 * Constructor
	 */
	public function construct($config) {
		$this->mimes = UPLC()->import_resource('mimes');
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
	 * Reads a "stored" value from a file
	 *
	 * @access  public
	 * @param   string    the file
	 * @return  mixed
	 */
	public function read_object($file) {
		return unserialize($this->read($file));
	}
	
	/**
	 * Stores an object in a file
	 *
	 * @access  public
	 * @param   string    the file
	 * @param   mixed     the object to store
	 * @return  void
	 */
	public function write_object($file, $obj) {
		return $this->write($file, serialize($obj));
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
		if (! is_dir($path) && ! @mkdir($path, 0777)) {
			trigger_error('Could not create directory '.basename($path).'.', E_USER_ERROR);
		}
	}
	
	/**
	 * Reads a listing of the files in a directory
	 *
	 * @access  public
	 * @param   string    the directory path
	 * @param   bool      include dot files?
	 * @param   bool      include "." and ".."?
	 * @return  array
	 */
	public function read_directory($path, $dots = true, $rels = false) {
		if ($dir = opendir($path)) {
			// Read the files
			$files = array();
			while (false !== ($file = readdir($dir))) {
				// Exclude dot files if requested
				if (! $dots && $file[0] == '.') {
					continue;
				}
				// Exclude "."/".." if requested
				if (! $rels && ($file == '.' || $file == '..')) {
					continue;
				}
				// Add the file to the list
				$files[] = $file;
			}
			
			return $files;
		}
		
		return false;
	}
	
}
















/* End of file files.php */
