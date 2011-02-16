<?php

/**
 * PostgreSQL Database Driver
 *
 * @class       Postgres_scheme
 * @parent      Database_scheme
 * @implements  Database_scheme_iface
 */
class Postgres_scheme extends Database_scheme {
	
	/**
	 * Opens a PostgreSQL connection
	 *
	 * @access  public
	 * @return  void
	 */
	public function open() {
		$this->close();
		// Build the connection string
		$conn_str = array(
			'host='.$this->conf->host,
			'user='.$this->conf->user,
			'password='.$this->conf->pass
		);
		if (isset($this->conf->port)) {
			$conn_str[] = 'port='.$this->conf->port;
		}
		// Connect
		$this->link = pg_connect(implode(' ', $conn_str));
		// Select the db
		if ($this->db_name) {
			$this->select_db($this->db_name);
		}
	}
	
	/**
	 * Closes the connection if one is open
	 *
	 * @access  public
	 * @return  void
	 */
	public function close() {
		return pg_close($this->link);
	}
	
	/**
	 * Runs a SQL query on the database
	 *
	 * @access  public
	 * @param   string    the query string
	 * @return  mixed
	 */
	public function run_query($query) {
		return pg_query($this->link, $query);
	}
	
	/**
	 * Escapes a string for query insertion
	 *
	 * @access  public
	 * @param   string    the string to escape
	 * @param   string    use bytea escaping
	 * @return  string
	 */
	public function escape_string($str, $bytea = false) {
		if ($bytea) {
			return pg_escape_bytea($this->link, $str);
		} else {
			return pg_escape_string($this->link, $str);
		}
	}
	
	/**
	 * Gets the number of rows in a query result
	 *
	 * @access  public
	 * @param   resource  the query result
	 * @return  int
	 */
	public function num_rows(&$resource) {
		return pg_num_rows($resource);
	}
	
	/**
	 * Fetches the last error (number and message)
	 *
	 * @access  public
	 * @return  array
	 */
	public function last_error() {
		return array(null, pg_last_error($this->link));
	}
	
	/**
	 * Selects a database for querying
	 *
	 * @access  public
	 * @param   string    the database name
	 * @return  void
	 */
	public function select_db($db_name) {
		$this->db_name = $db_name;
		return $this->query('USE '.$this->quote_ident($db_name));
	}
	
	/**
	 * Set the default charset
	 *
	 * @access  public
	 * @param   string    the new charset
	 * @return  void
	 */
	public function set_charset($charset) {
		pg_set_client_encoding($this->link, $charset);
	}
	
	/**
	 * Gets the insert ID of the last query
	 *
	 * @access  public
	 * @return  int
	 */
#	public function insert_id($bigint = false) {
#		// Get the server version
#		$v = $this->version();
#		$v = $v['server'];
#		
#		// Check parameters
#		$table	= func_num_args() > 0 ? func_get_arg(0) : null;
#		$column	= func_num_args() > 1 ? func_get_arg(1) : null;

#		if ($table == null && $v >= '8.1')
#		{
#			$sql='SELECT LASTVAL() as ins_id';
#		}
#		elseif ($table != null && $column != null && $v >= '8.0')
#		{
#			$sql = sprintf("SELECT pg_get_serial_sequence('%s','%s') as seq", $table, $column);
#			$query = $this->query($sql);
#			$row = $query->row();
#			$sql = sprintf("SELECT CURRVAL('%s') as ins_id", $row->seq);
#		}
#		elseif ($table != null)
#		{
#			// seq_name passed in table parameter
#			$sql = sprintf("SELECT CURRVAL('%s') as ins_id", $table);
#		}
#		else
#		{
#			return pg_last_oid($this->last_result);
#		}
#		$query = $this->query($sql);
#		$row = $query->row();
#		return $row->ins_id;
#	}
	
	/**
	 * Fetches a numerical array from a result set
	 *
	 * @access  public
	 * @param   resource  the result
	 * @return  array
	 */
	public function fetch_row(&$resource) {
		//
		// Fetch a numerical array row from the result set and
		// move the pointer forward one.
		//
	}
	
	/**
	 * Fetches an associative array from a result set
	 *
	 * @access  public
	 * @param   resource  the result
	 * @return  array
	 */
	public function fetch_assoc(&$resource) {
		//
		// Fetch an associative array row from the result set
		// and move the pointer forward one.
		//
	}
	
	/**
	 * Frees up the memory of result resource, useful when dealing with
	 * high memory queries.
	 *
	 * @access  public
	 * @param   resource  the result
	 * @return  void
	 */
	public function free_result(&$resource) {
		//
		// Should free up the memory used by the resource if possible.
		//
	}
	
// ----------------------------------------------------------------------------
//   PostgreSQL Specific Methods
	
	public function version() {
		$result = $this->query('SELECT version() AS ver');
		$result = $this->fetch_assoc($result);
		return $result['ver'];
	}
	
}

/* End of file postgres.php */
/* Location: ./lib/db-schemes/postgres.php */
