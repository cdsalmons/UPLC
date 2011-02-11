<?php

define('DB_SCHEME_PATH', BASEPATH.'db-schemes/');

class Database_library {
	
	/**
	 * The listing of connections
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $connections = array();
	
	/**
	 * Database schemes to use
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $schemes = array();
	
	/**
	 * Load a database scheme
	 *
	 * @access  protected
	 * @param   string    the scheme
	 * @return  object
	 */
	protected function &load_scheme($scheme) {
		if (! isset($this->schemes[$scheme])) {
			if (! is_file(DB_SCHEME_PATH.$scheme.EXT)) {
				show_error(5, "Could not load database scheme '{$scheme}'. File does not exist.", true);
			}
			require(DB_SCHEME_PATH.$scheme.EXT);
			$class = ucwords($scheme).'_scheme';
			if (! class_exists($class)) {
				show_error(6, "Could not load database scheme '{$scheme}'. Scheme file invalid.", true);
			}
			$this->schemes[$scheme] = new $class();
		}
		return $this->schemes[$scheme];
	}
	
	/**
	 * Constructor
	 */
	public function __construct() { }
	
	/**
	 * Checks a config array/object for validity
	 *
	 * @access  public
	 * @param   mixed     the config
	 * @return  bool
	 */
	protected function config_is_valid($config) {
		$config = (array) $config;
		$needed = array('host', 'user', 'pass', 'db', 'scheme');
		foreach ($needed as $item) {
			if (! isset($config)) return false;
		}
		return true;
	}
	
	/**
	 * Open a database connection
	 *
	 * @access  public
	 * @param   string    the connection name
	 * @param   array     the config data
	 * @return  object
	 */
	public function &open($arg1, $arg2 = null) {
		if (is_string($arg1)) {
			if (is_array($arg2) || is_object($arg2)) {
				if (! $this->config_is_valid($arg2)) {
					show_error(7, 'Database configuration is invalid.', true);
				}
				$this->connections[$arg1] = array(
					
				);
			}
		}
	}
	
}

/**
 * Handles an individual database connection
 */
class Database_connection {
	
	/**
	 * The connection config
	 *
	 * @access  protected
	 * @type    object
	 */
	protected $config = null;
	
	/**
	 * The open connection
	 *
	 * @access  protected
	 * @type    resource
	 */
	protected $connection = null;
	
	/**
	 * Constructor
	 */
	public function __construct($config) {
		
		$this->config = (object) $config;
	}
	
	/**
	 * Destructor
	 */
	public function __destruct() {
		$this->close();
	}
	
	/**
	 * Opens a connection to the database
	 *
	 * @access  public
	 * @return  void
	 */
	public function open() {
		
	}
	
	/**
	 * Closes an open connection
	 *
	 * @access  public
	 * @return  void
	 */
	public function close() {
		
	}

}

/**
 * The database scheme interface
 */
interface Database_scheme {
	public function open($host, $user, $pass);
	public function close(&$link);
	public function query($query, &$link);
}

/**
 * Define the access function
 */
function &Database($connection = null) {
	static $inst;
	if (! $inst) {
		$inst = new Database_library();
	}
	if ($connection) {
		return $inst->open($connection);
	} else {
		return $inst;
	}
}

/* End of file database.php */
/* Location ./lib/database.php */
