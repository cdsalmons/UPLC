<?php

/**
 * The database scheme interface
 */
interface Database_scheme_iface {
	
	public function __construct   ($conf);
	public function pre_init      (&$conf);
	public function init          ();
	
	public function open          ();
	public function close         ();
	public function is_open       ();
	public function set_charset   ($charset);
	public function last_error    ();
	public function query_log     ();
	
	public function select_db     ($db_name);
	public function list_dbs      ();
	public function list_tables   ($database = null);
	
	public function query         ($query);
	public function run_query     ($query);
	public function select        ($table, $fields = '*', $conditions = null, $limit = 0);
	public function insert        ($table, $data);
	public function update        ($table, $data, $conditions = null);
	public function delete        ($table, $conditions = null);
	public function empty_table   ($table);
	public function describe      ($table);
	
	public function drop_table    ($table);
	public function drop_db       ($database = null);
	public function create_table  ($table, $definition, $if_not_exists = false);
	public function create_db     ($db, $if_not_exists = false);
	
	public function num_rows      (&$resource);
	public function fetch_row     (&$resource);
	public function fetch_assoc   (&$resource);
	public function build_table   (&$resource);
	public function free_result   (&$resource);
	public function insert_id     ($bigint = false);
	
	public function escape_string ($str);
	public function quote_string  ($str);
	public function quote_ident   ($str);
	
}

/**
 * The database scheme class
 */
abstract class Database_scheme implements Database_scheme_iface {
	
	/**
	 * The connection resource
	 *
	 * @access  protected
	 * @type    resource
	 */
	protected $link;
	
	/**
	 * The currently active database
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $db_name;
	
	/**
	 * Configuration data
	 *
	 * @access  protected
	 * @type    object
	 */
	protected $conf;
	
	/**
	 * The last occuring error
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $last_error;
	
	/**
	 * A list of all queries run
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $queries = array();
	
	/**
	 * Constructor
	 *
	 * @access  public
	 * @param   object    the config
	 * @return  void
	 */
	public final function __construct($conf) {
		$this->pre_init($conf);
		$this->conf = $conf;
		$this->db_name = $this->conf->db;
		$this->open();
		$this->init();
	}
	
	/**
	 * Pre-init
	 */
	public function pre_init(&$conf) { }
	
	/**
	 * Init
	 */
	public function init() { }
	
	/**
	 * Test if there is an open connection
	 *
	 * @access  public
	 * @return  bool
	 */
	public function is_open() {
		return is_resource($this->link);
	}
	
	/**
	 * Runs a SQL query
	 *
	 * @access  public
	 * @param   string    the query
	 * @return  mixed
	 */
	public final function query($query) {
		if (! $this->is_open()) {
			$this->open();
		}
		$this->queries[] = $query;
		return $this->run_query($query);
	}
	
	/**
	 * Gets a log of all queries run
	 *
	 * @access  public
	 * @return  array
	 */
	public final function query_log() {
		return $this->queries;
	}
	
	/**
	 * Builds the WHERE clause portion of a query
	 *
	 * @access  protected
	 * @param   mixed     WHERE conditions
	 * @return  string
	 */
	protected function build_where_clause($conditions = null) {
		$clause = '';
		
		if (! empty($conditions)) {
			$clause .= ' WHERE ';
			if (is_array($conditions)) {
				$conds = array();
				foreach ($conditions as $field => $value) {
					$conds[] = sprintf('%s=%s', $this->quote_ident($field), $this->quote_string($value));
				}
				$conditions = implode(' AND ', $conds);
			}
			if (is_string($conditions)) {
				$clause .= $conditions;
			} else {
				return false;
			}
		}
		
		return $clause;
	}
	
	/**
	 * Runs a select query
	 *
	 * @access  public
	 * @param   string    the table name
	 * @param   mixed     the fields to select
	 * @param   mixed     conditions on the selection
	 * @param   mixed     the limit clause
	 * @return  void
	 */
	public function select($table, $fields = '*', $conditions = null, $limit = 0) {
		$query = 'SELECT ';
		
		// Add the fields to the query
		if (is_string($fields) && strpos('|', $fields) !== false) {
			$fields = explode('|', $fields);
		}
		if (is_array($fields)) {
			foreach ($fields as $i => $field) {
				$fields[$i] = $this->quote_ident($field);
			}
			$fields = implode(', ', $fields);
		}
		if (is_string($fields)) {
			$query .= $fields;
		} else {
			trigger_error('Bad fields value given to select method', E_USER_ERROR);
		}
		
		// Add the table to the query
		$query .= sprintf(' FROM %s ', $this->quote_ident($table));
		
		// Add conditionals to the query
		if (($where = $this->build_where_clause($conditions)) === false) {
			return false;
		}
		$query .= $where;
		
		// Add the limit clause
		if ($limit) {
			$query .= ' LIMIT ';
			if (is_int($limit)) {
				$query .= '0, '.$limit;
			} else {
				$query .= $limit;
			}
		}
		
		// Run the query and process the result into an array
		$result = $this->query($query);
		$table = $this->build_table($result);
		$this->free_result($result);
		return $table;
	}
	
	/**
	 * Runs an insert query
	 *
	 * @access  public
	 * @param   string    the table
	 * @param   array     the new data
	 * @return  void
	 */
	public function insert($table, $data) {
		$query = sprintf('INSERT INTO %s (', $this->quote_ident($table));
		
		// Add values to the query
		$keys = $values = array();
		foreach ($data as $key => $value) {
			$keys[] = $this->quote_ident($key);
			$values[] = $this->quote_string($value);
		}
		$query .= implode(', ', $keys).") VALUES (".implode(', ', $values).")";
		
		// Run the query
		return $this->query($query);
	}
	
	/**
	 * Runs an update query
	 *
	 * @access  public
	 * @param   string    the table
	 * @param   array     the new data
	 * @param   mixed     conditions
	 * @return  void
	 */
	public function update($table, $data, $conditions = null) {
		$query = sprintf('UPDATE %s SET ', $this->quote_ident($table));
		
		// Add the SET clause
		$data_items = array();
		foreach ($data as $key => $value) {
			$value = ($value == DB_DEFAULT) ? 'DEFAULT' : $this->quote_string($value);
			$data_items[] = sprintf('%s=%s', $this->quote_ident($key), $value);
		}
		$query .= implode(', ', $data_items);
		
		// Add the WHERE clause
		if (($where = $this->build_where_clause($conditions)) === false) {
			return false;
		}
		$query .= $where;
		
		return $this->query($query);
	}
	
	/**
	 * Runs a delete query
	 *
	 * @access  public
	 * @param   string    the table
	 * @param   mixed     conditions
	 * @return  void
	 */
	public function delete($table, $conditions = null) {
		$query = 'DELETE FROM '.$this->quote_ident($table);
		
		// Add the WHERE clause
		if (($where = $this->build_where_clause($conditions)) === false) {
			return false;
		}
		$query .= $where;
		
		return $this->query($query);
	}
	
	/**
	 * Empties out a table using `DELETE FROM table`
	 *
	 * @access  public
	 * @param   string    the table
	 * @return  void
	 */
	public function empty_table($table) {
		return $this->delete($table);
	}
	
	/**
	 * Runs a describe query
	 *
	 * @access  public
	 * @param   string    the table
	 * @return  void
	 */
	public function describe($table) {
		$query = 'DESCRIBE '.$this->quote_ident($table);
		if (($result = $this->query($query)) === false) {
			return false;
		}
		$table = $this->build_table($result);
		$this->free_result($result);
		return $table;
	}
	
	/**
	 * Runs a `drop table` query
	 *
	 * @access  public
	 * @param   string    the table
	 * @return  void
	 */
	public function drop_table($table) {
		
	}
	
	/**
	 * Runs a `drop database` query
	 *
	 * @access  public
	 * @param   string    the database
	 * @return  void
	 */
	public function drop_db($database = null) {
		
	}
	
	/**
	 * Runs a `create table` query
	 *
	 * @access  public
	 * @param   string    the table name
	 * @param   array     the definition
	 * @param   bool      add an IF NOT EXISTS clause
	 * @return  void
	 */
	public function create_table($table, $definition, $if_not_exists = false) {
		
	}
	
	/**
	 * Runs a `create database` query
	 *
	 * @access  public
	 * @param   string    the database name
	 * @return  void
	 */
	public function create_db($database, $if_not_exists = false) {
		$query = 'CREATE DATABASE ';
		if ($if_not_exists) {
			$query .= 'IN NOT EXISTS ';
		}
		$query .= $this->quote_ident($database);
		return $this->query($query);
	}
	
	/**
	 * Builds an array structure from a resource
	 *
	 * @access  public
	 * @param   resource  the query result
	 * @param   bool      use object instead of arrays
	 * @return  array
	 */
	public function build_table(&$resource, $use_objects = false) {
		$result = array();
		if ($this->num_rows($resource)) {
			while ($row = $this->fetch_assoc($resource)) {
				if ($use_objects) {
					$row = (object) $row;
				}
				$result[] = $row;
			}
		}
		return $result;
	}
	
	/**
	 * Gets a list of all databases
	 *
	 * @access  public
	 * @return  array
	 */
	public function list_dbs() {
		$result = array();
		$query = $this->query('SHOW DATABASES');
		if ($this->num_rows($query)) {
			while ($row = $this->fetch_row($query)) {
				$result[] = $row[0];
			}
		}
		return $result;
	}
	
	/**
	 * Gets a list of all tables in a database
	 *
	 * @access  public
	 * @param   string    the database
	 * @return  array
	 */
	public function list_tables($database = null) {
		if (! is_string($database)) {
			$database = $this->db_name;
		}
		$result = array();
		$query = $this->query("SHOW TABLES IN ".$this->quote_ident($database));
		if ($this->num_rows($query)) {
			while ($row = $this->fetch_row($query)) {
				$result[] = $row[0];
			}
		}
		return $result;
	}
	
	/**
	 * Quotes a value string
	 *
	 * @access  public
	 * @param   string    the string
	 * @return  string
	 */
	public function quote_string($str) {
		return sprintf("'%s'", $this->escape_string($str));
	}
	
	/**
	 * Quotes an identifier string
	 *
	 * @access  public
	 * @param   string    the identifier
	 * @return  string
	 */
	public function quote_ident($str) {
		return sprintf('`%s`', $this->escape_string($str));
	}
	
}

/* End of file core.php */
/* Location: ./lib/db-schemes/core.php */
