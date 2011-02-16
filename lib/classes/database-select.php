<?php

/**
 * The select query class
 */
class Database_select {
	
	/**
	 * A reference to the DB controller
	 *
	 * @access  protected
	 * @type    Database_scheme
	 */
	protected $db = null;
	
	/**
	 * The table name
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $table = null;
	
	/**
	 * Individual clauses for building the statement
	 *
	 * @access  protected
	 * @type    array
	 */
	protected $clauses = array(
		'SELECT'     => null,
		'FROM'       => null,
		'WHERE'      => null,
		'INNER JOIN' => null,
		'LIMIT'      => null,
		'ORDER BY'   => null
	);
	
// ----------------------------------------------------------------------------
	
	/**
	 * Constructor
	 *
	 * @access  public
	 * @param   Database_scheme  the database controller
	 * @return  void
	 */
	public function __construct(&$db) {
		$this->db =& $db;
	}
	
	/**
	 * Set the fields to be selected
	 *
	 * @access  public
	 * @param   mixed     the fields to select
	 * @return  $this
	 */
	public function fields($fields) {
		if ($fields != '*') {
			if (is_string($fields)) {
				$fields = explode(',', $fields);
				foreach ($fields as &$field) {
					$field = trim($field);
				}
			}
			if (is_array($fields)) {
				$fields = '`'.implode('`, `', $fields).'`';
			} else {
				return false;
			}
		}
		$this->clauses['SELECT'] = $fields;
		return $this;
	}
	
	/**
	 * Set the FROM clause
	 *
	 * @access  public
	 * @param   string    the table to select from
	 * @return  $this
	 */
	public function from($table) {
		$table = $this->db->quote_ident($table);
		$this->clauses['FROM'] = $table;
		$this->table = $table;
		return $this;
	}
	
	/**
	 * Creates an INNER JOIN clause
	 *
	 * @access  public
	 * @param   string    the second table name
	 * @param   string    the first table's field name
	 * @param   string    the second table's field name
	 * @return  $this
	 */
	public function inner_join($table, $field1, $field2) {
		$this->clauses['INNER JOIN'] = $table.' ON '.
			$this->quote_ident($this->table.'.'.$field1).'='.
			$this->quote_ident($this->table.'.'.$field2);
		return $this;
	}
	
	/**
	 * Adds a LIMIT clause
	 *
	 * @access  public
	 * @param   int       min limit
	 * @param   int       max limit
	 * @return  $this
	 */
	public function limit($limit1, $limit2 = null) {
		if ($limit2) {
			$this->clauses['LIMIT'] = "${limit1}, ${limit2}";
		} else {
			$this->clauses['LIMIT'] = "0, ${limit1}";
		}
		return $this;
	}
	
	/**
	 * Adds an ORDER BY clause
	 *
	 * @access  public
	 * @param   string    the field names
	 * @param   string    sort order (ASC|DESC)
	 * @return  $this
	 */
	public function order_by($fields, $order ='ASC') {
		$fields = explode(',', $fields);
		foreach ($fields as &$field) {
			$field = $this->quote_ident(trim($field));
		}
		$this->clauses['ORDER BY'] = implode(', ', $fields).' '.$order;
	}

// ----------------------------------------------------------------------------
//   WHERE Clause Functions
	
	/**
	 * Builds a segment of a WHERE clause
	 *
	 * @access  protected
	 * @param   array     the values to add
	 * @param   string    the delimiter (OR, AND)
	 * @return  string
	 */
	protected function build_where_segment($values, $delim) {
		$where = array();
		foreach ($values as $i => $value) {
			$where[] = sprintf("%s=%s", $i, $value);
		}
		return '('.implode(' '.$delim.' ', $where).')';
	}
	
	/**
	 * Set the WHERE clause
	 *
	 * @access  public
	 * @param   array     the values to test
	 * @param   string    the delimiting operator (AND, OR)
	 * @return  $this
	 */
	public function where($conds, $delim = 'AND') {
		$this->clauses['WHERE'] = $this->build_where_segment($conds, $delim);
		return $this;
	}
	
	/**
	 * Add an AND (...) to the WHERE clause
	 *
	 * @access  public
	 * @param   array     the values to add
	 * @param   string    the delimiting operator (AND, OR)
	 * @return  $this
	 */
	public function and_where($conds, $delim = 'AND') {
		$this->clauses['WHERE'] .= ' AND '.$this->build_where_segment($conds, $delim);
		return $this;
	}
	
	/**
	 * Add an OR (...) to the WHERE clause
	 *
	 * @access  public
	 * @param   array      the values to add
	 * @param   string    the delimiting operator (AND, OR)
	 * @return  $this
	 */
	public function or_where($conds, $delim = 'AND') {
		$this->clauses['WHERE'] .= ' OR '.$this->build_where_segment($conds, $delim);
		return $this;
	}
	
	
// ----------------------------------------------------------------------------
	
	/**
	 * Run the constructed query and return the results
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function get() {
		$query = array();
		foreach ($this->clauses as $clause => $value) {
			if (isset($value)) {
				$query[] = $clause.' '.$value;
			}
		}
		$query = implode(' ', $query);
		$result = $this->db->query($query);
		if (is_resource($result)) {
			$table = $this->db->build_table($result);
			$this->db->free_result($result);
			return $table;
		}
	}
	
}

/* End of file database-select.php */
/* Location: ./lib/classes/database-select.php */
