<?php

import('datetime');

class Timer {
	
	/**
	 * Timer data
	 */
	protected $start_time;
	protected $pause_time;
	protected $is_paused = false;
	
	/**
	 * Get a float of the current time
	 *
	 * @access  protected
	 * @return  float
	 */
	protected function now() {
		return Datetime()->micro_now(true);
	}
	
	/**
	 * Constructor
	 */
	public function __construct() { }
	
	/**
	 * Start the timer
	 *
	 * @access  public
	 * @return  void
	 */
	public function start() {
		$this->start_time = $this->now();
	}
	
	/**
	 * Pause the timer
	 *
	 * @access  public
	 * @return  void
	 */
	public function pause() {
		if (! $this->is_paused) {
			$this->is_paused = true;
			$this->pause_time = $this->now();
		}
	}
	
	/**
	 * Unpause the timer
	 *
	 * @access  public
	 * @return  void
	 */
	public function unpause() {
		if (! $this->is_paused) {
			$this->is_paused = false;
			$this->start_time += ($this->now() - $this->pause_time);
			$this->pause_time = null;
		}
	}
	
	/**
	 * Fetches the current runtime
	 *
	 * @access  public
	 * @return  float
	 */
	public function get_time() {
		if ($this->start_time === null) {
			return false;
		}
		$now = ($this->is_paused) ? $this->pause_time : Datetime()->now();
		return ($this->start_time - $now);
	}
	
}

/* End of file timer.php */
/* Location: ./lib/classes/timer.php */
