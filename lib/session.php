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
	 * Placeholder for serialized slashes
	 *
	 * @const   SLASH_HOLDER
	 * @access  public
	 * @type    string
	 */
	const SLASH_HOLDER = '{{slash}}';
	
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
		
		// Decrypt the cookie
		$cookie = Crypto()->decrypt($cookie, $this->conf->get('encryption_key'));
		$cookie = $this->unserialize($cookie);
		
		// Seperate out the hash (last 32 characters)
		$len = strlen($cookie);
		$hash = substr($cookie, $len - 32);
		$cookie = substr($cookie, 0, $len - 32);
		
		// Check the hash for validity
		if ($this->hash($cookie) != $hash) {
			return false;
		}
		
		// Check for a valid cookie structure
		if (! (is_array($cookie) && isset($cookie['sess_id']) && isset($cookie['user_agent'])
		&& isset($cookie['ip_address']) && isset($cookie['last_active']))) {
			return false;
		}
		
		// Store the session data
		$this->sess_id = $cookie['sess_id'];
		$this->session_data = $cookie;
		
		// Check for a useragent match
		$user_agent = substr(Input()->user_agent(), 0, 50);
		if (trim($cookie['user_agent']) != trim($user_agent)) {
			return false;
		}
		
		// Check for expiration
		if (($cookie['last_active'] + $this->conf->get('expiration')) < $this->now) {
			return false;
		}
		
		// Fetch session data from the database
		if (! $this->read_db()) {
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
		
		// Store data in $this
		$this->sess_id = $sessid;
		$this->session_data = array(
			'sess_id' => $sessid,
			'ip_address' => Input()->ip_address('0.0.0.0'),
			'user_agent' => substr(Input()->user_agent(), 0, 50),
			'last_active' => $this->now,
			'user_data' => array()
		);
		
		// Write to the database
		$this->write_db();
		
		// Write the cookie
		$this->write_cookie();
	}
	
	/**
	 * Destroys the active session
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function destroy_session() {
		$this->delete_cookie();
		$this->delete_db();
		$this->session_data = null;
		$this->sess_id = null;
	}
	
// ----------------------------------------------------------------------------
//   Cookie I/O Methods
	
	/**
	 * Reads a stored session cookie
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function read_cookie() {
		return Cookies()->get(self::SESSION_COOKIE);
	}
	
	/**
	 * Generates a session cookie based on the session ID
	 *
	 * @access  public
	 * @return  string
	 */
	protected function generate_cookie() {
		if ($this->is_active()) {
			// Get a session data array
			$session_data = $this->session_data;
			unset($session_data['user_data']);
			// Serialize the array and add a hash
			$session_data = $this->serialize($session_data);
			$cookie = $session_data.$this->hash($session_data);
			// Encrypt the cookie
			return Crypto()->encrypt($cookie, $this->conf->get('encryption_key'));
		}
		
		return false;
	}
	
	/**
	 * Writes a session cookie
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function write_cookie() {
		if ($cookie = $this->generate_cookie()) {
			Cookies()->set(self::SESSION_COOKIE, $cookie, $this->conf->get('expiration'));
		}
	}
	
	/**
	 * Delete the session cookie
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function delete_cookie() {
		Cookies()->delete(self::SESSION_COOKIE);
	}
	
// ----------------------------------------------------------------------------
//   Database I/O Methods
	
	/**
	 * Reads the session data from the database
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function read_db() {
		if ($this->is_active()) {
			
		}
	}
	
	/**
	 * Writes the session data to the database
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function write_db() {
		if ($this->is_active()) {
			// Get the data to write
			$session_data = $this->session_data;
			$session_data['user_data'] = $this->serialize($session_data['user_data']);
			// Update if it already exists, otherwise insert
			if ($this->read_db()) {
				$this->db->update($this->db_table, $session_data, array('sess_id' => $this->sess_id));
			} else {
				$this->db->insert($this->db_table, $session_data);
			}
		}
	}
	
	/**
	 * Deletes session data from the database
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function delete_db() {
		if ($this->is_active()) {
			$sessid = $this->session_data['sess_id'];
			$this->db->delete($this->db_table, array('sess_id' => $sessid));
		}
	}
	
// ----------------------------------------------------------------------------
//   String Manipulation Functions
	
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
					$data[$key] = str_replace('\\', self::SLASH_HOLDER, $val);
				}
			}
		} else {
			if (is_string($data)) {
				$data = str_replace('\\', self::SLASH_HOLDER, $data);
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
					$data[$key] = str_replace(self::SLASH_HOLDER, '\\', $val);
				}
			}

			return $data;
		}

		return (is_string($data)) ? str_replace(self::SLASH_HOLDER, '\\', $data) : $data;
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
		return Hashing()->hash_hmac($str, 'md5', $key);
	}
	
}

/* End of file session.php */
/* Location ./lib/session.php */
