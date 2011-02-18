<?php

/**
 * The database scheme interface
 */
interface Database_scheme_iface {
	
	public function __construct   ($conf);
	public function __destruct    ();
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
	public function table_exists  ($database = null);
	
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
	public function create_table  ($table, $definition, $other = array(), $if_not_exists = false);
	public function create_db     ($db, $if_not_exists = false);
	
	public function num_rows      (&$resource);
	public function fetch_row     (&$resource);
	public function fetch_assoc   (&$resource);
	public function build_table   (&$resource);
	public function free_result   (&$resource);
	public function insert_id     ($bigint = false);
	
	public function escape_string ($str, $bytea = false);
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
	 * The last recieved result object
	 *
	 * @access  protected
	 * @type    resource
	 */
	protected $last_result;
	
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
	 * Destructor
	 *
	 * @access  public
	 * @return  void
	 */
	public final function __destruct() {
		$this->close();
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
		$this->last_result = $this->run_query($query);
		return $this->last_result;
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
		$query = UPLC()->load_class('database-select', $this);
		return $query->fields($fields);
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
		$query = 'DROP TABLE '.$this->quote_ident($table);
		return $this->query($query);
	}
	
	/**
	 * Runs a `drop database` query
	 *
	 * @access  public
	 * @param   string    the database
	 * @return  void
	 */
	public function drop_db($database = null) {
		if (! is_string($database)) {
			$database = $this->db_name;
		}
		$query = 'DROP DATABASE '.$this->quote_ident($database);
		return $this->query($query);
	}
	
	/**
	 * Runs a `create table` query
	 *
	 * @access  public
	 * @param   string    the table name
	 * @param   array     the definition
	 * @param   bool      add an IF NOT EXISTS clause
	 * @param   array     foriegn/primary keys + db engine
	 * @return  void
	 */
	public function create_table($table, $definition, $other = array(), $if_not_exists = false) {
		$query = 'CREATE TABLE ';
		if ($if_not_exists) {
			$query .= 'IF NOT EXISTS ';
		}
		$query .= '( ';
		
		if (! is_array($definition)) {
			return false;
		}
		
		$items = array();
		
		// Add the basic column definitions
		foreach ($definition as $name => $desc) {
			// Add defualt values
			$desc = array_merge(array(
				'type' => null,
				'null' => true,
				'default' => null,
				'unsigned' => false,
				'auto_increment' => false
			), $desc);
			// Add the basic definition
			$item = $this->quote_ident($name);
			if (! is_string($desc['type'])) {
				return false;
			}
			$item .= ' '.$desc['type'];
			// Add NULL/NOT NULL
			if (! $desc['null']) {
				$item .= ' NOT';
			}
			$item .= ' NULL';
			// Add the unsigned flag
			if ($desc['unsigned']) {
				$item .= ' UNSIGNED';
			}
			// Add the auto_increment flag
			if ($desc['auto_increment']) {
				$item .= ' AUTO_INCREMENT';
			}
			// Add the default value
			$item .= ' DEFAULT '.(($desc['default'] === null) ? 'NULL' : $this->quote_string($desc['default']));
			
			$items[] = $item;
		}
		
		// Add keys
		if (isset($other['key'])) {
			if (is_array($other['key'])) {
				$item = 'KEY '.$this->quote_ident(implode('_', $other['key'])).' (';
				foreach ($other['key'] as $i => $key) {
					$other['key'][$i] = $this->quote_ident($key);
				}
				$item .= implode(', ', $other['key']).')';
			} else {
				$item = 'KEY '.$other['key'].' ('.$this->quote_ident($other['key']).')';
			}
			$items[] = $item;
		}
		
		// Add a primary key
		if (isset($other['primary'])) {
			$items[] = 'PRIMARY KEY ('.$this->quote_ident($other['primary']).')';
		}
		
		// Finish building the query
		$query .= implode(', ', $items).' )';
		if (isset($other['engine'])) {
			$query .= ' ENGINE='.$engine;
		}
		
		return $this->query($query);
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
	 * Check if a table exists
	 *
	 * @access  public
	 * @param   string    the table name
	 * @param   string    the database to test in
	 * @return  bool
	 */
	public function table_exists($table, $database = null) {
		// Test for the database.table syntax
		if (strpos($table, '.') !== false && $database === null) {
			$table = explode('.', $table);
			$database = $table[0];
			$table = $table[1];
		}
		// Check if the table exists
		$tables = list_tables($table, $database);
		return in_array($table, $tables);
	}
	
	/**
	 * Quotes a value string
	 *
	 * @access  public
	 * @param   string    the string
	 * @return  string
	 */
	public function quote_string($str) {
		if (is_string($str)) {
			$str = sprintf("'%s'", $this->escape_string($str));
		}
		return $str;
	}
	
	/**
	 * Quotes an identifier string
	 *
	 * @access  public
	 * @param   string    the identifier
	 * @return  string
	 */
	public function quote_ident($str, $ignore_dots = false) {
		if (! $ignore_dots && strpos('.', $str) !== false) {
			$str = str_replace('.', '`.`', $str);
		}
		return sprintf('`%s`', $str);
	}
	
}

/* End of file core.php */
/* Location: ./lib/db-schemes/core.php */
