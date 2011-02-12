<?php

/**
 * MySQL Database Driver
 *
 * @class       Mysql_scheme
 * @parent      Database_scheme
 * @implements  Database_scheme_iface
 */
class Mysql_scheme extends Database_scheme {
	
	/**
	 * Opens a MySQL connection
	 *
	 * @access  public
	 * @param   string    the host name
	 * @param   string    the user name
	 * @param   string    the password
	 * @return  void
	 */
	public function open() {
		$this->close();
		$this->link = mysql_connect(
			$this->conf->host.':'.$this->conf->port,
			$this->conf->user,
			$this->conf->pass
		);
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
		if ($this->is_open()) {
			mysql_close($this->link);
		}
	}
	
	/**
	 * Runs a SQL query on the database
	 *
	 * @access  public
	 * @param   string    the query string
	 * @return  mixed
	 */
	public function query($query) {
		if (! $this->is_open()) {
			return false;
		}
		return mysql_query($query, $this->link);
	}
	
	/**
	 * Escapes a string for query insertion
	 *
	 * @access  public
	 * @param   string    the string to escape
	 * @return  string
	 */
	public function escape_string($str) {
		return mysql_real_escape_string($str, $this->link);
	}
	
	/**
	 * Gets the number of rows in a query result
	 *
	 * @access  public
	 * @param   resource  the query result
	 * @return  int
	 */
	public function num_rows(&$resource) {
		return mysql_num_rows($resource);
	}
	
	/**
	 * Fetches the last error (number and message)
	 *
	 * @access  public
	 * @return  array
	 */
	public function last_error() {
		return array(mysql_errno($this->link), mysql_error($this->link));
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
		return mysql_select_db($db_name, $this->link);
	}
	
	/**
	 * Set the default charset
	 *
	 * @access  public
	 * @param   string    the new charset
	 * @return  void
	 */
	public function set_charset($charset) {
		return mysql_set_charset($charset, $this->link);
	}
	
	/**
	 * Gets the insert ID of the last query
	 *
	 * @access  public
	 * @return  int
	 */
	public function insert_id($bigint = false) {
		if ($bigint) {
			return $this->query('LAST_INSERT_ID()');
		} else {
			return mysql_insert_id($this->link);
		}
	}
	
	/**
	 * Fetches a numerical array from a result set
	 *
	 * @access  public
	 * @param   resource  the result
	 * @return  array
	 */
	public function fetch_row(&$resource) {
		return mysql_fetch_row($resource);
	}
	
	/**
	 * Fetches an associative array from a result set
	 *
	 * @access  public
	 * @param   resource  the result
	 * @return  array
	 */
	public function fetch_assoc(&$resource) {
		return mysql_fetch_assoc($resource);
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
		return mysql_free_result($resource);
	}
	
}

/* End of file mysql.php */
/* Location: ./lib/db-schemes/mysql.php */
