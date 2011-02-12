<?php

/**
 * Template Database Driver
 *
 * @class       TEMPLATE_scheme
 * @parent      Database_scheme
 * @implements  Database_scheme_iface
 */
class TEMPLATE_scheme extends Database_scheme {
	
	/**
	 * Opens a TEMPLATE connection
	 *
	 * @access  public
	 * @return  void
	 */
	public function open() {
		//
		// Should do the following:
		//
		//  1. Close any open connection
		//  2. Open a new connection and store it at $this->link
		//  3. If a database is stored at $this->db_name, select it
		//
	}
	
	/**
	 * Closes the connection if one is open
	 *
	 * @access  public
	 * @return  void
	 */
	public function close() {
		//
		// Should close the connection at $this->link if one is open.
		//
	}
	
	/**
	 * Runs a SQL query on the database
	 *
	 * @access  public
	 * @param   string    the query string
	 * @return  mixed
	 */
	public function run_query($query) {
		//
		// Should run the query given and return its result directly.
		//
	}
	
	/**
	 * Escapes a string for query insertion
	 *
	 * @access  public
	 * @param   string    the string to escape
	 * @return  string
	 */
	public function escape_string($str) {
		//
		// Should return a query-safe escaped string based on
		// the parameter passed in.
		//
	}
	
	/**
	 * Gets the number of rows in a query result
	 *
	 * @access  public
	 * @param   resource  the query result
	 * @return  int
	 */
	public function num_rows(&$resource) {
		//
		// Should return the number of rows in the result set.
		//
	}
	
	/**
	 * Fetches the last error (number and message)
	 *
	 * @access  public
	 * @return  array
	 */
	public function last_error() {
		//
		// Should return the last error that occured in the
		// follow format:
		//
		//  array(
		//    0 => error number,
		//    1 => error message
		//  )
		//
	}
	
	/**
	 * Selects a database for querying
	 *
	 * @access  public
	 * @param   string    the database name
	 * @return  void
	 */
	public function select_db($db_name) {
		//
		// Should do the following:
		//
		//  1. Set $this->db_name to the given database name
		//  2. Select the database (a SQL `USE` command)
		//
	}
	
	/**
	 * Set the default charset
	 *
	 * @access  public
	 * @param   string    the new charset
	 * @return  void
	 */
	public function set_charset($charset) {
		//
		// Set the connection charset to the one given.
		//
	}
	
	/**
	 * Gets the insert ID of the last query
	 *
	 * @access  public
	 * @return  int
	 */
	public function insert_id($bigint = false) {
		//
		// Return the last insert ID. Note, BIGINT fields can cause
		// issues with PHP's integar max, so there should be a fallback
		// for BIGINT fields (identified by the $bigint parameter).
		//
	}
	
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
	
}

/* End of file template.php */
/* Location: ./lib/db-schemes/template.php */
