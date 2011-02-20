<?php

define('DB_SCHEME_PATH', UPLC_LIBPATH.'db-schemes/');
define('DB_DEFAULT', "\0");

class Database_library extends Uplc_library {
	
	/**
	 * Constructor
	 */
	public function construct() { }
	
	/**
	 * Open a database connection
	 *
	 * @access  public
	 * @param   string    the name
	 * @param   mixed     the config
	 * @return  Database_scheme
	 */
	public function &open($name, $conf = null) {
		// Handle a no-name call
		if ($conf === null) {
			$conf = $name;
			$name = null;
		}
		
		// Figure out how to handle the data given
		if (is_string($conf)) {
			$conf = array('dsn' => $conf);
		} elseif (is_object($conf)) {
			$conf = (array) $conf;
		} elseif (! is_array($conf)) {
			trigger_error('Cannot open new database connection, no config data given', E_USER_ERROR);
		}
		
		// Check for a DSN string
		if (isset($conf['dsn'])) {
			$this->parse_dsn($conf);
		}
		
		// Check that the config data is valid
		if (! $this->config_is_valid($conf)) {
			trigger_error('Database configuration is invalid', E_USER_ERROR);
		}
		
		// Fill in any missing optional config data
		$this->complete_config($conf);
		$conf = (object) $conf;
		
		// Open the new connection
		$class = $this->load_scheme($conf->scheme);
		$instance = new $class($conf);
		if ($name === null) {
			$this->connections[] =& $instance;
		} else {
			$this->connections[$name] =& $instance;
		}
		
		return $instance;
	}
	
	/**
	 * Get a connection by name
	 *
	 * @access  public
	 * @param   string    the connection name
	 * @return  Database_scheme
	 */
	public function &get_connection($name) {
		if (! isset($this->connections[$name])) {
			trigger_error("Connection with name '${name}' does not exist", E_USER_ERROR);
		}
		return $this->connections[$name];
	}

// ----------------------------------------------------------------------------
	
	/**
	 * The active connections
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $connections = array();
	
	/**
	 * Load a database scheme
	 *
	 * @access  protected
	 * @param   string    the scheme
	 * @return  string
	 */
	protected function load_scheme($scheme) {
		$class = ucwords($scheme).'_scheme';
		if (! class_exists($class)) {
			if (! is_file(DB_SCHEME_PATH.$scheme.'.php')) {
				trigger_error("Could not load database scheme '{$scheme}'. File does not exist.", E_USER_ERROR);
			}
			require_once(DB_SCHEME_PATH.'core.php');
			require_once(DB_SCHEME_PATH.$scheme.'.php');
			if (! class_exists($class)) {
				trigger_error("Could not load database scheme '{$scheme}'. Scheme file invalid.", E_USER_ERROR);
			}
		}
		return $class;
	}
	
	/**
	 * Checks a config array/object for validity
	 *
	 * @access  protected
	 * @param   mixed     the config
	 * @return  bool
	 */
	protected function config_is_valid($config) {
		$config = (array) $config;
		$needed = array('host', 'user', 'pass', 'scheme');
		foreach ($needed as $item) {
			if (! isset($config[$item])) return false;
		}
		return true;
	}
	
	/**
	 * Parses a DSN string into seperate usable data
	 *
	 * @access  protected
	 * @param   array     the config array
	 * @return  void
	 */
	protected function parse_dsn(&$conf) {
		if (($dsn = @parse_url($conf['dsn'])) === false) {
			trigger_error('Invalid database DSN string', E_USER_ERROR);
		}
		
		$conf['scheme'] = $dsn['scheme'];
		$conf['host']   = (isset($dsn['host'])) ? rawurldecode($dsn['host']) : '';
		$conf['user']   = (isset($dsn['user'])) ? rawurldecode($dsn['user']) : '';
		$conf['pass']   = (isset($dsn['pass'])) ? rawurldecode($dsn['pass']) : '';
		$conf['db']     = (isset($dsn['path'])) ? rawurldecode(substr($dsn['path'], 1)) : null;
		
		if (isset($dsn['port'])) {
			$conf['port'] = $dsn['port'];
		}
	}
	
	/**
	 * Fill in the blanks of a config array
	 *
	 * @access  protected
	 * @param   array     the config
	 * @return  void
	 */
	protected function complete_config(&$conf) {
		$conf = array_merge(array(
			'charset' => 'utf8',
			'collation' => 'utf8_general_ci',
			'port' => null,
			'db' => null
		), $conf);
	}

}

/**
 * The shortcut function
 *
 * @access  global
 * @param   string    connection name/DSN
 * @return  mixed
 */
function &Database($connection = null) {
	static $inst;
	if (! $inst) {
		$inst = new Database_library();
	}
	if (is_string($connection) || is_int($connection)) {
		if (preg_match('/^[a-zA-Z0-9_-]+$/', $connection)) {
			return $inst->get_connection($connection);
		} else {
			return $inst->open($connection);
		}
	} else {
		return $inst;
	}
}

/* End of file database.php */
/* Location: ./lib/database.php */
