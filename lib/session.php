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
			if ($this->read_session()) {
				$this->regenerate_sessid();
			} else {
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
		$this->session_data[$item] = $value;
		$this->write_db();
	}
	
	/**
	 * Reads a value from the session
	 *
	 * @access  public
	 * @param   string    the item
	 * @return  mixed
	 */
	public function get_item($item) {
		return ((isset($this->session_data[$item])) ? $this->session_data[$item] : null);
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
		unset($this->session_data[$item]);
		$this->write_db();
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
			'primary' => 'sess_id',
			'engine' => 'InnoDB'
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
	 * Create a new session ID
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function generate_sessid() {
		$sessid = mt_rand(0, mt_getrandmax()).Input()->ip_address('0.0.0.0');
		$sessid = Hashing()->shash_hmac(uniqid($sessid, true),
			$this->conf->get('encryption_key'), false, mt_rand(8000, 12000)
		);
		return $sessid;
	}
	
	/**
	 * Should the session ID be regenerated?
	 *
	 * @access  protected
	 * @return  bool
	 */
	protected function should_regenerate() {
		$last_active = $this->session_data['last_active'];
		return (($last_active + $this->conf->get('regeneration')) < $this->now);
	}
	
	/**
	 * Create a new session ID for the session
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function regenerate_sessid() {
		if ($this->is_active() && $this->should_regenerate()) {
			$old_sessid = $this->sess_id;
			$new_sessid = $this->generate_sessid();
			// Replace session IDs in this object
			$this->sess_id = $this->session_data['sess_id'] = $new_sessid;
			$this->last_active = $this->now;
			// Update the database
			$this->db->update($this->db_table, array(
				'sess_id' => $new_sessid,
				'last_active' => $this->session_data['last_active'],
				'user_agent' => $this->session_data['user_agent'],
				'ip_address' => $this->session_data['ip_address']
			), array('sess_id' => $old_sessid));
			// Build and set the new cookie
			$this->write_cookie();
		}
	}
	
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
		
		// Seperate out the hash (last 32 characters)
		$len = strlen($cookie);
		$hash = substr($cookie, $len - 32);
		$cookie = substr($cookie, 0, $len - 32);
		
		// Check the hash for validity
		if ($this->hash_string($cookie) != $hash) {
			return false;
		}
		
		// Unserialize the cookie data back into an array
		$cookie = $this->unserialize($cookie);
		
		// Check for a valid cookie structure
		if (! (is_array($cookie) && isset($cookie['sess_id']) && isset($cookie['user_agent'])
		&& isset($cookie['ip_address']) && isset($cookie['last_active']))) {
			return false;
		}
		
		// Check for a useragent match
		$user_agent = substr(Input()->user_agent(), 0, 50);
		if (trim($cookie['user_agent']) != trim($user_agent)) {
			return false;
		}
		
		// Check for expiration
		if (($cookie['last_active'] + $this->conf->get('expiration')) < $this->now) {
			return false;
		}
		
		// Update some info
		$session = $cookie;
		$session['ip_address'] = Input()->ip_address('0.0.0.0');
		$session['user_agent'] = substr(Input()->user_agent(), 0, 50);
		
		// Fetch session data from the database
		$db_data = $this->db
			->select('*')
			->from($this->db_table)
			->where(array(
				'sess_id' => $session['sess_id']
			))
			->get();
		
		// Make sure there was a database row
		if (count($db_data) == 0) {
			return false;
		}
		
		// Add user data to the session array
		$user_data = $this->unserialize($db_data[0]['user_data']);
		foreach ($user_data as $key => $value) {
			$session[$key] = $value;
		}
		
		// Store data in the local object
		$this->sess_id = $session['sess_id'];
		$this->session_data = $session;
		
		return true;
	}
	
	/**
	 * Creates a new session
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function create_session() {
		// Generate the session ID
		$sessid = $this->generate_sessid();
		
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
		$this->write_db(true);
		
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
			$session_data = array();
			foreach (array('sess_id', 'user_agent', 'ip_address', 'last_active') as $key) {
				$session_data[$key] = $this->session_data[$key];
			}
			// Serialize the array and add a hash
			$session_data = $this->serialize($session_data);
			$cookie = $session_data.$this->hash_string($session_data);
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
			$expire = ($this->conf->get('expire_on_close')) ? 0 : $this->conf->get('expiration');
			Cookies()->set(self::SESSION_COOKIE, $cookie, $expire);
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
	 * Writes the session data to the database
	 *
	 * @access  protected
	 * @param   bool      is it a new session?
	 * @return  void
	 */
	protected function write_db($is_new = false) {
		if ($this->is_active()) {
			if ($is_new) {
				$session_data = $this->session_data;
				$session_data['user_data'] = $this->serialize(array());
				$this->db->insert($this->db_table, $session_data);
			} else {
				$user_data = array();
				$dont_copy = array('sess_id', 'user_agent', 'ip_address', 'last_active');
				foreach ($this->session_data as $key => $value) {
					if (! in_array($key, $dont_copy)) {
						$user_data[$key] = $value;
					}
				}
				$this->db->update($this->db_table, array(
					'user_data' => $this->serialize($user_data)
				), array('sess_id' => $this->sess_id));
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
			return $this->db->delete($this->db_table, array('sess_id' => $this->sess_id));
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
		$data = unserialize(stripslashes($data));

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
