<?php

class Pcre_library {
	
	public function __construct() {
	
	}
	
	/**
	 * Get the version and release date of the currently
	 * running PCRE extension.
	 *
	 * @access  public
	 * @return  string
	 */
	public function version() {
		return PREG_VERSION;
	}
	
	/**
	 * Create a Pcre_library_regex object
	 *
	 * @access  public
	 * @param   string    the pattern string
	 * @param   string    the modifiers
	 * @return  Pcre_library_regex
	 */
	public function compile(string $pattern, string $modifiers = '') {
		if (! strlen($pattern)) {
			throw new Exception('No pattern string given');
		}
	}
	
	/**
	 * Quote a regex pattern segment
	 *
	 * @access  public
	 * @param   string    the segment
	 * @param   string    the pattern delim
	 * @return  string
	 */
	public function quote(string $segment, $delim = null) {
		return preg_quote($segment, $delim);
	}
	
	/**
	 * Perform a regular expression match
	 *
	 * @access  public
	 * @param   string    the pattern string
	 * @param   string    the subject string
	 * @param   bool      capture offset info?
	 * @param   int       the offset position
	 * @return  array -or- NULL
	 */
	public function match(string $pattern, string $subject, $capture_offset = false, $offset = 0) {
		if ($capture_offset) {
			preg_match($pattern, $subject, $result, PREG_OFFSET_CAPTURE, $offset);
		} else {
			preg_match($pattern, $subject, $result);
		}
		return $result;
	}
	
	/**
	 * Get an array of all matches in a string
	 *
	 * @access  public
	 * @param   string    the pattern string
	 * @param   string    the subject string
	 * @param   int       options flags
	 * @param   int       the offset position
	 * @return  array -or- NULL
	 */
	public function match_all(string $pattern, string $subject, $flags = PREG_PATTERN_ORDER, $offset = 0) {
		preg_match_all($pattern, $subject, $result, $flags, $offset);
		return $result;
	}
	
	/**
	 * Replace matches in a string/array
	 *
	 * @access  public
	 * @param   string    the pattern string
	 * @param   string    the replacement
	 * @param   mixed     the subject string/array
	 * @param   int       the maximum number of replacements
	 * @param   &int      the number of replacements
	 * @return  mixed
	 */
	public function replace(string $pattern, string $replace, $subject, $limit = -1, &$count = 0) {
		return preg_replace($pattern, $replace, $subject, $limit, $count);
	}
	
	/**
	 * Replace matches in a string/array using a callback
	 *
	 * @access  public
	 * @param   string    the pattern string
	 * @param   function  the replacement callback
	 * @param   mixed     the subject string/array
	 * @param   int       the maximum number of replacements
	 * @param   &int      the number of replacements
	 * @return  mixed
	 */
	public function replace_callback(string $pattern, $callback, $subject, $limit = -1, &$count = 0) {
		return preg_replace_callback($pattern, $callback, $subject, $limit, $count);
	}
	
	/**
	 * Check for matches in an array of strings
	 *
	 * @access  public
	 * @param   string    the pattern string
	 * @param   array     the subject strings
	 * @param   bool      invert the match?
	 * @return  array
	 */
	public function grep(string $pattern, $input, $invert = false) {
		if ($invert) {
			$result = preg_grep($pattern, $input, PREG_GREP_INVERT);
		} else {
			$result = preg_grep($pattern, $input);
		}
		return $result;
	}
	
	/**
	 * Get the last occuring PCRE error
	 *
	 * @access  public
	 * @return  string -or- FALSE
	 */
	public function last_error() {
		$err = preg_last_error();
		switch ($err) {
			case PREG_INTERNAL_ERROR:
				return 'PCRE internal error';
			break;
			case PREG_BACKTRACK_LIMIT_ERROR:
				return 'Backtrack limit exhausted';
			break;
			case PREG_RECURSION_LIMIT_ERROR:
				return 'Recursion limit exhausted';
			break;
			case PREG_BAD_UTF8_ERROR:
				return 'Malformed UTF-8 data';
			break;
			case PREG_BAD_UTF8_OFFSET_ERROR:
				return 'Offset did not correspond to the begining of a valid UTF-8 code point';
			break;
			case PREG_NO_ERROR:
			default:
				return false;
			break;
		}
	}
	
}

/**
 * The regular expression class
 */
class Pcre_library_regex {
	
	/**
	 * The pattern string
	 *
	 * @access  protected
	 * @type    string
	 */
	protected function $pattern = null;
	
	/**
	 * Pattern modifiers
	 *
	 * @access  protected
	 * @type    array
	 */
	protected function $modifiers = array(
		
		// PCRE_CASELESS
		'i' => false,
		
		// PCRE_MULTILINE
		'm' => false,
		
		// PCRE_DOTALL
		's' => false,
		
		// PCRE_EXTENDED
		'x' => false,
		
		// PREG_REPLACE_EVAL
		'e' => false,
		
		// PCRE_ANCHORED
		'A' => false,
		
		// PCRE_DOLLAR_ENDONLY
		'D' => false,
		
		// Study Pattern
		'S' => false,
		
		// PCRE_UNGREEDY
		'U' => false,
		
		// PCRE_EXTRA
		'X' => false,
		
		// PCRE_INFO_JCHANGED
		'J' => false,
		
		// PCRE8
		'u' => false
		
	);
	
	/**
	 * Constructor
	 *
	 * @access  public
	 * @param   string    the pattern string
	 * @param   string    pattern modifiers
	 */
	public function __construct(string $pattern, string $modifiers = '') {
		if (! strlen($pattern)) {
			throw new Exception('No pattern string given');
		}
		$this->pattern = preg_quote($pattern, '/');
		$this->process_modifiers($modifiers);
	}
	
	/**
	 * Make string casts result in a vaiable pattern string
	 *
	 * @access  public
	 * @return  string
	 */
	public function __toString() {
		return $this->pattern_string();
	}
	
	/**
	 * Build a PCRE pattern string
	 *
	 * @access  public
	 * @return  string
	 */
	public function pattern_string() {
		return '/'.$this->pattern.'/'.$this->modifier_string();
	}
	
	/**
	 * Process a modifier string
	 *
	 * @access  protected
	 * @param   string    the modifiers
	 * @return  void
	 */
	protected function process_modifiers($mods) {
		for ($i = 0, $c = strlen($mods); $i < $c; $i++) {
			if (isset($this->modifiers[$mods[$i]])) {
				$this->modifiers[$mods[$i]] = true;
			}
		}
	}
	
	/**
	 * Get a string of active modifiers
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function modifier_string() {
		$str = '';
		foreach ($this->modifiers as $mod => $active) {
			if ($active) $str .= $mod;
		}
		return $str;
	}
	
	/**
	 * Run a match
	 *
	 * @access  public
	 * @param   string    the subject string
	 * @param   bool      get offset info?
	 * @param   int       the matching offset
	 * @return  array -or- NULL
	 */
	public function match(string $subject, $capture_offset = false, $offset = 0) {
		return Pcre()->match($this->pattern_string(), $subject, $capture_offset, $offset);
	}
	
	/**
	 * Test a string for a match
	 * 
	 * @access  public
	 * @param   string    the subject string
	 * @param   bool      get offset info?
	 * @param   int       the matching offset
	 * @return  bool
	 */
	public function test(string $subject, $capture_offset = false, $offset = 0) {
		return (!! $this->match($subject, $capture_offset, $offset));
	}
	
	/**
	 * Tests for pattern matches in an array
	 *
	 * @access  public
	 * @param   array     the array of values
	 * @param   bool      invert?
	 * @return  array
	 */
	public function grep($input, $invert = false) {
		return Pcre()->grep($this->pattern_string(), $input, $invert);
	}
	
	/**
	 * Sets/gets the caseless modifier (i)
	 *
	 * @access  public
	 * @param   bool      switch the value
	 * @return  bool
	 */
	public function caseless($switch = null) {
		if (is_bool($switch)) {
			
		}
	}
	
}

/* End of file pcre.php */
