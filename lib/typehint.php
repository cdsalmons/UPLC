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



// Used to pull the error messages apart
define('TYPEHINT_PCRE','/^Argument (\d)+ passed to (?:(\w+)::)?(\w+)\(\) must be an instance of (\w+), (\w+) given/');

// The actual handler class
class Typehint_library {
	
	// Tell UPLC not to create a Typehint() function
	public static $_no_init = true;
	
	/**
	 * Typehint handlers
	 */
	private static $Typehints = array(
		'boolean'  => 'is_bool',
		'integer'  => 'is_int',
		'float'    => 'is_float',
		'string'   => 'is_string',
		'resource' => 'is_resource'
	);
	
	// We do not need a constructor
	private function __construct() { }

	/**
	 * Initialize the typehint handler
	 *
	 * @access  public
	 * @return  Bool(true)
	 */
	public static function initializeHandler()
	{
		set_error_handler('Typehint_library::handleTypehint');
		return true;
	}

	/**
	 * Gets arguments that have been typehinted
	 *
	 * @access  private
	 * @param   array     backtrace
	 * @param   string    the function
	 * @param   int       the argument index
	 * @param   &mixed    the value
	 * @return  bool
	 */
	private static function getTypehintedArgument($ThBackTrace, $ThFunction, $ThArgIndex, &$ThArgValue)
	{
		foreach ($ThBackTrace as $ThTrace)
		{
			// Match the function; Note we could do more defensive error checking.
			if (isset($ThTrace['function']) && $ThTrace['function'] == $ThFunction)
			{
				$ThArgValue = $ThTrace['args'][$ThArgIndex - 1];
				return true;
			}
		}

		return false;
	}
	
	/**
	 * Handles a specific typehint instance
	 *
	 * @access  public
	 * @param   int       the error level
	 * @param   string    the error string
	 * @return  bool
	 */
	public static function handleTypehint($ErrLevel, $ErrMessage)
	{
		// Make sure the error can be recovered
		if ($ErrLevel == E_RECOVERABLE_ERROR)
		{
			// Check that it is a typehint error
			if (preg_match(TYPEHINT_PCRE, $ErrMessage, $ErrMatches))
			{
				list($ErrMatch, $ThArgIndex, $ThClass, $ThFunction, $ThHint, $ThType) = $ErrMatches;
				
				// Check that it is a registered scalar
				if (isset(self::$Typehints[$ThHint]))
				{
					$ThBacktrace = debug_backtrace();
					$ThArgValue  = null;
					
					// Run the type check
					if (self::getTypehintedArgument($ThBacktrace, $ThFunction, $ThArgIndex, $ThArgValue))
					{
						if (call_user_func(self::$Typehints[$ThHint], $ThArgValue))
						{
							return true;
						}
					}
				}
			}
		}

		return false;
	}
}

// Initialize...
Typehint_library::initializeHandler();



/**
 * End of PHP < 6 test
 */
endif;



/* End of file typehint.php */
