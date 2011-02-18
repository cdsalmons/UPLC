<?php

/*
|------------------------------------------------
| Scalar typehinting for PHP 5
|------------------------------------------------
|
| Based heavily on the implementation found at
| @link http://us3.php.net/manual/en/language.oop5.typehinting.php#83442
|
*/

/**
 * Only initialize if using PHP 5
 *
 * Hopefully, PHP 6 should have this functionality natively. If not, we can
 * always remove this test later, but here's to hoping...
 */
if (! is_php('6.0.0')) :

/**
 * The typehint class
 */
class Typehint_library {
	
	/**
	 * The parser regex
	 *
	 * @const   REGEX
	 * @access  public
	 * @type    string
	 */
	const REGEX = '/^Argument (\d)+ passed to (.+) must be an instance of (?<hint>.+), (?<given>.+) given/';
	
	/**
	 * A list of all scalar types
	 *
	 * @access  protected
	 * @type    array
	 */
	protected static $scalar_types = array(
		'string', 'int', 'integer', 'float', 'double', 'bool', 'boolean'
	);
	
	/**
	 * Constructor
	 */
	public function __construct() { }
	
	/**
	 * Initializes the system, adding the error handler
	 *
	 * @access  public
	 * @return  void
	 */
	public static function initialize() {
		set_error_handler('Typehint_library::handle_typehint');
	}
	
	/**
	 * The typehint handler
	 *
	 * @access  public
	 * @param   int       the error level
	 * @param   string    the error message
	 * @return  bool
	 */
	public static function handle_typehint($lvl, $msg) {
		// Make sure we're dealing with a typehint error
		if ($lvl == E_RECOVERABLE_ERROR && preg_match(self::REGEX, $msg, $match)) {
			switch ($match['hint']) {
				// Convert synonomous data types
				case 'int':
					$match['hint'] = 'integer';
				break;
				case 'double':
					$match['hint'] = 'float';
				break;
				case 'bool':
					$match['hint'] = 'boolean'; 
				break;
				// Handle a general object hint
				case 'object':
					return (! in_array($match['given'], self::$scalar_types));
				break;
			}
			return ($match['hint'] == $match['given']);
		}

		return false;
	}
	
}

// Initialize
Typehint_library::initialize();

/**
 * End of PHP < 6 test
 */
else : class Typehint_library { } endif;

/* End of file typehint.php */
