<?php

/**
 * Session library class
 */
class Session_library extends Uplc_library {
	
	/**
	 * The cookie in which to store session data
	 *
	 * @const   UPLCSESS
	 * @access  public
	 * @type    string
	 */
	const SESSION_COOKIE = 'UPLCSESS';
	
	/**
	 * The value that seperates cookie segments
	 *
	 * @const   COOKIE_SEP
	 * @access  public
	 * @type    string
	 */
	const COOKIE_SEP = '|';
	
	/**
	 * Config data
	 *
	 * @access  protected
	 * @type    Config_class
	 */
	protected $conf;
	
	/**
	 * The session ID
	 *
	 * @access  public
	 * @type    string
	 */
	protected $sess_id;
	
	/**
	 * The data in the current session
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $session_data = array();
	
	/**
	 * The database handle
	 *
	 * @access  protected
	 * @type    Database_scheme
	 */
	protected $db;
	
	/**
	 * The database table to store session data in
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $db_table;
	
	/**
	 * Class init time
	 *
	 * @access  protected
	 * @type    int
	 */
	protected $now;

// ----------------------------------------------------------------------------
	
	/**
	 * Constructor
	 *
	 * @access  public
	 * @return  void
	 */
	public function construct($config) {
		// Import the needed libraries
		import('database', 'input', 'cookies', 'datetime', 'hashing', 'crypto');
		
		// Store the current time
		$this->now = Datetime()->now();
		
		// Store the config values
		$this->conf =& $config;
		
		// Prep the database handle
		$this->db = Database()->open($this->conf->get('database'));
		$this->db_table = $this->conf->get('db_table');
		
		// Start a session if the table exists
		if ($this->db->table_exists($this->db_table)) {
			if (! $this->read_session()) {
				$this->destroy_session();
				$this->create_session();
			}
		}
	}
	
	/**
	 * Checks if there is an active session
	 *
	 * @access  public
	 * @return  bool
	 */
	public function is_active() {
		return (is_string($this->sess_id));
	}
	
	/**
	 * Destroys the current session
	 *
	 * @access  public
	 * @return  void
	 */
	public function destroy() {
		return $this->destroy_session();
	}

// ----------------------------------------------------------------------------
//   Session Item I/O

	/**
	 * Writes a value to the session
	 *
	 * @access  public
	 * @param   string    the item
	 * @param   mixed     the value
	 * @return  void
	 */
	public function set_item($item, $value) {
	
	}
	
	/**
	 * Reads a value from the session
	 *
	 * @access  public
	 * @param   string    the item
	 * @return  mixed
	 */
	public function get_item($item) {
	
	}
	
	/**
	 * Gets all values from the session
	 *
	 * @access  public
	 * @return  array
	 */
	public function get_all() {
		return $this->session_data;
	}
	
	/**
	 * Removes a value from the session
	 *
	 * @access  public
	 * @param   string    the item
	 * @return  void
	 */
	public function unset_item($item) {
		
	}
	
// ----------------------------------------------------------------------------
//   Database Administrative Functions
	
	/**
	 * Installs the session table as defined in the config file
	 *
	 * @access  public
	 * @return  bool
	 */
	public function install_session_table() {
		$table_def = array(
			'sess_id' => array(
				'type'     => 'varchar(40)',
				'default'  => '0',
				'null'     => false
			),
			'ip_address' => array(
				'type'     => 'varchar(16)',
				'default'  => '0',
				'null'     => false
			),
			'user_agent' => array(
				'type'     => 'varchar(50)',
				'default'  => '0',
				'null'     => false
			),
			'last_active' => array(
				'type'     => 'int(10)',
				'default'  => 0,
				'null'     => false,
				'unsigned' => true
			),
			'user_data' => array(
				'type'    => 'text',
				'default' => '',
				'null'    => false
			)
		);
		
		$other = array(
			'primary' => 'sess_id'
		);
		
		return $this->db->create_table($this->db_table, $table_def, $other, true);
	}
	
	/**
	 * Completely empty the session table
	 *
	 * @access  public
	 * @return  bool
	 */
	public function empty_session_table() {
		return $this->db->empty_table($this->db_table);
	}
	
	/**
	 * Delete the session table
	 *
	 * @access  public
	 * @return  bool
	 */
	public function drop_session_table() {
		return $this->db->drop_table($this->db_table);
	}
	
// ----------------------------------------------------------------------------
//   Low-level Internal Session Control
	
	/**
	 * Reads and loads a session
	 *
	 * @access  protected
	 * @return  bool
	 */
	protected function read_session() {
		// Read the session cookie
		if (! ($cookie = $this->read_cookie())) {
			return false;
		}
		
		// Parse the cookie
		$cookie = explode(self::COOKIE_SEP, $cookie);
		if (count($cookie) != 2) {
			return false;
		}
		
		// Decrypt the cookie
		if ($this->hash_string($cookie[0]) != $cookie[1]) {
			return false;
		}
		
		// Fetch session data from the database
		if (! $this->read_db($cookie[0])) {
			return false;
		}
		
		// Check for a useragent match
		$user_agent = substr(Input()->user_agent(), 0, 50);
		if (trim($this->session_data['user_agent']) != trim($user_agent)) {
			return false;
		}
	}
	
	/**
	 * Creates a new session
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function create_session() {
		// Generate the session ID
		$sessid = mt_rand(0, mt_getrandmax()).Input()->ip_address('0.0.0.0');
		$sessid = Hashing()->shash_hmac(uniqid($sessid, true),
			$this->conf->get('encryption_key'), false, mt_rand(8000, 12000)
		);
		
		// Store data
		$this->sess_id = $sessid;
		$this->session_data = array(
			'sess_id' => $sessid,
			'ip_address' => Input()->ip_address('0.0.0.0'),
			'user_agent' => substr(Input()->user_agent(), 0, 50),
			'last_active' => $this->now,
			'user_data' => array()
		);
	}
	
	/**
	 * Destroys the active session
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function destroy_session() {
		
	}
	
	/**
	 * Reads a stored session cookie
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function read_cookie() {
		return Cookies()->get(self::SESSION_COOKIE);;
	}
	
	/**
	 * Generates a session cookie based on the session ID
	 *
	 * @access  public
	 * @return  string
	 */
	protected function generate_cookie() {
		if ($this->is_active()) {
			return Crypto()->encrypt($this->serialize($this->session_data), $this->conf->get('encryption_key'));
		}
		
		return false;
	}
	
	/**
	 * Serializes a variable into a storeable string.
	 *
	 * @access  protected
	 * @param   mixed     the value to serialize
	 * @return  string
	 */
	protected function serialize($data) {
		if (is_array($data)) {
			foreach ($data as $key => $val) {
				if (is_string($val)) {
					$data[$key] = str_replace('\\', '{{slash}}', $val);
				}
			}
		} else {
			if (is_string($data)) {
				$data = str_replace('\\', '{{slash}}', $data);
			}
		}

		return serialize($data);
	}

	/**
	 * Unserializes a string back into a usable value
	 *
	 * @access  protected
	 * @param   string    the serialized data
	 * @return  mixed
	 */
	protected function unserialize($data) {
		$data = @unserialize(strip_slashes($data));

		if (is_array($data)) {
			foreach ($data as $key => $val) {
				if (is_string($val)) {
					$data[$key] = str_replace('{{slash}}', '\\', $val);
				}
			}

			return $data;
		}

		return (is_string($data)) ? str_replace('{{slash}}', '\\', $data) : $data;
	}
	
	/**
	 * Generates the hash version of a string
	 *
	 * @access  public
	 * @param   string    the string to hash
	 * @return  string
	 */
	protected function hash_string($str) {
		$key = $this->conf->get('encryption_key');
		$seed = $this->conf->get('hashing_seed');
		$key_len = strlen($key);
		if ($key_len > 5) {
			$half = (int) ($key_len / 2);
			$subkey1 = substr($key, 0, $half);
			$subkey2 = substr($key, $half);
		} else {
			$subkey1 = $subkey2 = $key;
		}
		$str = $subkey1.'K'.$str.'K'.$subkey2;
		return Hashing()->shash_hmac($str, $key, $seed);
	}
	
	/**
	 * Writes a session cookie
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function write_cookie() {
		Cookies()->set(self::SESSION_COOKIE, $this->generate_cookie(), $this->conf->get('expiration'));
	}
	
	/**
	 * Reads the session data from the database
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function read_db() {
	
	}
	
	/**
	 * Writes the session data to the database
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function write_db() {
		
	}
	
}

/* End of file session.php */
/* Location ./lib/session.php */
