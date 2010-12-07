<?php

/**
 * Loads the UPLC (Ultimate Php Libraries Collection)
 */

// The UPLC directories
define('UPLC_BASEPATH', dirname(__FILE__).'/');
define('UPLC_LIBPATH', UPLC_BASEPATH.'lib/');
define('UPLC_RESPATH', UPLC_BASEPATH.'resources/');

// Define a function with a given name
function define_function($func_name, $params, $body = null) {
	if ($body === null) {
		$body = $params;
		$params = '';
	}
	eval('function '.$func_name.'('.$params.') { '.$body.' }');
}

// Imports a UPLC library
function import_library($lib) {
	$uc_lib = ucwords($lib);
	if (! function_exists($uc_lib)) {
		$file = UPLC_LIBPATH.$lib.'.php';
		require $file;
		$class = $uc_lib.'_library';
		$body = implode(' ', array(
			'static $inst;',
			'if (! $inst) $inst = new '.$class.';',
			'return $inst;'
		));
		define_function('&'.$uc_lib, $body);
	}
}

// Imports a UPLC resource file
function import_resource($file) {
	$file = UPLC_RESPATH.$file.'.php';
	if (is_file($file)) {
		require $file;
		if (isset($export)) {
			return $export;
		}
		trigger_error(UPLC_RESPATH.$file.'.php is not a valid resource file');
	}
	trigger_error('Resource file '.UPLC_RESPATH.$file.'.php does not exist');
}

/* End of file init.php */
