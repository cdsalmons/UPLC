<?php

// Image libraries that can be used
define('IMGLIB_NONE', 0);
define('IMGLIB_IMAGEMAGICK', 1);
define('IMGLIB_GD', 2);

/**
 * Image Library
 *
 * @class   Image_library
 * @parent  void
 */
class Image_library extends {

	/**
	 * Constructor
	 *
	 * @access  public
	 * @return  void
	 */
	public function construct() {
		import('shell', 'files');
		$this->img_lib = $this->determine_library();
		$this->pngcrush = Shell()->command_exists('pngcrush');
	}
	
	/**
	 * PNGCRUSH support
	 */
	protected $pngcrush = null;
	
	/**
	 * Throw a compatibility error
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function no_engine_found() {
		trigger_error('No compatible image library was found.', E_USER_ERROR);
	}
	
	/**
	 * The image library to use
	 */
	protected $img_lib = null;
	
	/**
	 * Determines which image library to use
	 *
	 * @access  public
	 * @return  int
	 */
	public function determine_library() {
		if (Shell()->command_exists('convert')) {
			return IMGLIB_IMAGEMAGICK;
		} else if (function_exists('imagecreatetruecolor')) {
			return IMGLIB_GD;
		} else {
			return IMGLIB_NONE;
		}
	}
	
	/**
	 * Resizes an image and stores the output
	 *
	 * @access  public
	 * @param   string    the input file
	 * @param   string    the output file
	 * @param   string    the resize rule
	 * @return  void
	 */
	public function resize_image($input_file, $output_file, $resize_rule) {
		switch ($this->img_lib) {
			// ImageMagick
			case IMGLIB_IMAGEMAGICK:
				$cmd = 'convert '.$input_path.' -resize "'.$resize_rule.'" '.$output_file;
				Shell()->run_command($cmd);
			break;
			// GD
			case IMGLIB_GD:
				// Initialize variables
				$aspect_ratio = true;
				$width = null; $height = null;
				$resize_rule = trim($resize_rule);
				// Parse for an !
				if ($resize_rule[strlen($resize_rule) - 1] == '!') {
					$aspect_ratio = false;
					$resize_rule = substr($resize_rule, 0, -1);
				}
				// Parse out width and height
				$resize_rule = explode('x', $resize_rule);
				$width = $resize_rule[0];
				if (count($resize_rule == 2)) {
					$height = $resize_rule[1];
				}
				// Resize...
				$this->resize_gd($input_file, $output_file, $width, $height, $aspect_ratio);
			break;
			// Non-support
			case IMGLIB_NONE:
				$this->no_engine_found();
			break;
		}
	}
	
	/**
	 * Get GD read/write functions
	 *
	 * @access  protected
	 * @param   string    the input file
	 * @return  object
	 */
	protected function gd_functions($input_file_info) {
		$type = substr(strrchr($input_file_info['mime'], '/'), 1);
		switch ($type) {
			case 'png':
				$image_create_func = 'imagecreatefrompng';
				$image_save_func = 'imagepng';
			break;
			case 'bmp':
				$image_create_func = 'imagecreatefrombmp';
				$image_save_func = 'imagebmp';
			break;
			case 'gif':
				$image_create_func = 'imagecreatefromgif';
				$image_save_func = 'imagegif';
			break;
			case 'vnd.wap.wbmp':
				$image_create_func = 'imagecreatefromwbmp';
				$image_save_func = 'imagewbmp';
			break;
			case 'xbm':
				$image_create_func = 'imagecreatefromxbm';
				$image_save_func = 'imagexbm';
			break;
			case 'jpeg':
			default:
				$image_create_func = 'imagecreatefromjpeg';
				$image_save_func = 'imagejpeg';
			break;
		}
		return ((object) array(
			'create' => $image_create_func,
			'save' => $image_save_func
		));
	}
	
	/**
	 * Resize an image using GD
	 *
	 * @access  protected
	 * @param   string    the input file
	 * @param   string    the output file
	 * @param   int       the new width
	 * @param   int       the new height
	 * @param   bool      keep aspect ratio?
	 * @return  void
	 */
	protected function resize_gd($input_file, $output_file, $new_width, $new_height, $aspect_ratio = true) {
		$info = getimagesize($input_file);
		$funcs = $this->gd_functions($info);
		// Fill in any missing dimensions
		if (empty($new_width)) $new_width = $info[0];
		if (empty($new_height)) $new_height = $info[1];
		// Create the new image
		$image_c = imagecreatetruecolor($new_width, $new_height);
		$new_image = $funcs->create($input_file);
		imagecopyresampled($image_c, $new_image, 0, 0, 0, 0, $new_width, $new_height, $info[0], $info[1]);
		// Store the new image
		$funcs->save($image_c, $output_file);
		// Free up memory
		imagedestroy($image_c);
	}
	
	/**
	 * Run PNGCRUSH on an image
	 *
	 * @access  protected
	 * @param   string    the image path
	 * @return  void
	 */
	 protected function run_pngcrush($file) {
	 	if ($this->pngcrush) {
	 		$tmp_file = $file.'.tmp.png';
	 		$cmd = 'pngcrush -reduce -brute '.$file.' '.$tmp_file;
	 		Shell()->run_command($cmd);
	 		Files()->write($file, Files()->read($tmp_file));
	 		Files()->delete($tmp_file);
	 	}
	 }
	 
	 /**
	  * Compress an image if possible
	  *
	  * @access  public
	  * @param   string    the file path
	  * @return  void
	  */
	 public function compress_image($file) {
	 	switch (Files()->get_mime_type($file)) {
	 		case 'image/png':
	 			$this->run_pngcrush($file);
	 		break;
	 	}
	 }

}

/* End of file images.php */
