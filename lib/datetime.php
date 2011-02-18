<?php

/**
 * The date-time library class
 */
class Datetime_library extends Uplc_library {
	
	/**
	 * Constructor
	 *
	 * @access  public
	 * @return  void
	 */
	public function construct() {
		@date_default_timezone_set(
			@date_default_timezone_get()
		);
	}
	
	/**
	 * Retrieve the current time
	 *
	 * @access  public
	 * @return  int
	 */
	public function now() {
		return time();
	}
	
	/**
	 * Retrieve the current time in microseconds
	 *
	 * @access  public
	 * @param   bool      return as a float in seconds?
	 * @return  int
	 */
	public function micro_now($float = false) {
		return microtime($float);
	}
	
	/**
	 * Creates a timer object
	 *
	 * @access  public
	 * @return  Timer
	 */
	public function get_timer() {
		return UPLC()->load_class('timer');
	}
	
}

/* End of file datetime.php */
/* Location: ./lib/datetime.php */
