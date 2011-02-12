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
define('UPLC_RESPATH', UPLC_BASEPATH.'resources/');

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

/**
 * Imports a UPLC library
 *
 * @access  global
 * @param   string    the library to load
 * @return  void
 */
function import_library($lib) {
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

/**
 * Imports multiple UPLC libraries in one call
 *
 * @access  global
 * @param   string..  the libraries to load
 * @return  void
 */
function import_libs() {
	$libs = func_get_args();
	foreach ($libs as $lib) {
		import_library($lib);
	}
}

/**
 * Imports a UPLC resource file
 *
 * @access  global
 * @param   string    the file name
 * @return  mixed
 */
function import_resource($file) {
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

/* End of file init.php */
/* Location: ./init.php */
