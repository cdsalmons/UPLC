<?php

/*
|----------------------------------------------------------
| Loads the UPLC (Ultimate Php Libraries Collection)
|----------------------------------------------------------
|
| 
|
*/

/**
 * Set the error reporting
 */
error_reporting(E_ALL|E_STRICT);

/**
 * The UPLC directories
 */
define('UPLC_BASEPATH', dirname(__FILE__).'/');
define('UPLC_LIBPATH', UPLC_BASEPATH.'lib/');
define('UPLC_CLASSPATH', UPLC_LIBPATH.'classes/');
define('UPLC_RESPATH', UPLC_BASEPATH.'resources/');

/**
 * The config directory. Only define if not already that way
 * the config location can be overriden before init.
 */
if (! defined('UPLC_CONFIGPATH')) {
	define('UPLC_CONFIGPATH', UPLC_BASEPATH.'config/');
}

// ----------------------------------------------------------------------------

/**
 * Define a function with a given name
 *
 * @access  global
 * @param   string    the function name
 * @param   string    the parameters
 * @param   string    the function body
 * @return  void
 */
function define_function($func_name, $params, $body = null) {
	if ($body === null) {
		$body = $params;
		$params = '';
	}
	eval('function '.$func_name.'('.$params.') { '.$body.' }');
}

// ----------------------------------------------------------------------------

/**
 * Imports a UPLC library
 *
 * @access  global
 * @param   string    the library to load
 * @return  void
 */
function import() {
	$libs = func_get_args();
	foreach ($libs as $lib) {
		$uc_lib = ucwords($lib);
		$class = $uc_lib.'_library';
	
		// Include the file
		$file = UPLC_LIBPATH.$lib.'.php';
		require_once $file;
	
		// Define the shortcut function if needed
		if (! function_exists($uc_lib) && ! isset($class::$_no_init)) {
			$body = implode(' ', array(
				'static $inst;',
				'if (! $inst) $inst = new '.$class.';',
				'return $inst;'
			));
			define_function('&'.$uc_lib, $body);
		}
	}
}

// ----------------------------------------------------------------------------

if (! function_exists('is_php')) :
/**
 * Checks the PHP version
 *
 * @access  global
 * @param   string    the version string to test
 * @return  bool
 */
function is_php($version) {
	return version_compare(PHP_VERSION, $version, '>=');
}
endif;

// ----------------------------------------------------------------------------

/**
 * The UPLC library base class
 */
class Uplc_library {
	
	/**
	 * The library constructor
	 */
	public final function __construct() {
		$classname = get_called_class();
		if (preg_match('/^(.+)_library$/', $classname, $match)) {
			if (method_exists($this, 'construct')) {
				$config_file = UPLC_CONFIGPATH.strtolower($match[1]).'.php';
				$config = UPLC()->read_config($config_file, false);
				$this->construct($config);
			}
		} else {
			trigger_error('Invalid library class name', E_USER_ERROR);
		}
	}
	
}

// ----------------------------------------------------------------------------

/**
 * The core UPLC class
 */
class Uplc_core {
	
	/**
	 * Reads a config file
	 *
	 * @access  public
	 * @param   string    the library
	 * @param   bool      should failure result in an error?
	 * @return  array
	 */
	public function read_config($file, $trigger_error = true) {
		// Check for the config file
		if (file_exists($file)) {
			require $file;
			// Check for a config array
			if (isset($config)) {
				return $this->load_class('config-class', $config);
			} elseif ($trigger_error) {
				trigger_error('The file "'.$file.'" is not a valid UPLC config file', E_USER_WARNING);
			}
		} elseif($trigger_error) {
			trigger_error('The file "'.$file.'" does not exist', E_USER_WARNING);
		}
		
		// If errors are disabled and no config was found, just
		// return an empty config object.
		return $this->load_class('config-class', array());
	}

	/**
	 * Imports a UPLC helper class
	 *
	 * @access  public
	 * @param   string    the library to load
	 * @param   mixed     an argument to pass to the constructor
	 * @return  void
	 */
	public function load_class($class, $arg = null) {
		require_once UPLC_CLASSPATH.$class.'.php';
		$class = str_replace('-', '_', $class);
		return (new $class($arg));
	}
	
	/**
	 * Imports a UPLC resource file
	 *
	 * @access  public
	 * @param   string    the file name
	 * @return  mixed
	 */
	public function import_resource($file) {
		$file = UPLC_RESPATH.$file.'.php';
		if (is_file($file)) {
			require $file;
			if (isset($export)) {
				return $export;
			}
			trigger_error($file.' is not a valid resource file');
		}
		trigger_error('Resource file '.$file.' does not exist');
	}
	
}

/**
 * A shortcut to UPLC functions
 */
function &UPLC() {
	static $inst;
	if (! $inst) {
		$inst = new Uplc_core();
	}
	return $inst;
}

/* End of file init.php */
/* Location: ./init.php */
