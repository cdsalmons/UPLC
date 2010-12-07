<?php

/**
 * Shell Library
 *
 * @class   Shell_library
 * @parent  void
 */
class Shell_library {

	/**
	 * Runs a shell command
	 *
	 * @access  public
	 * @param   string    the command to run
	 * @return  string
	 */
	public function run_command($command) {
		$descriptorspec = array(
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w'),
		);
		$pipes = array();
		$resource = proc_open($command, $descriptorspec, $pipes, getcwd());

		$stdout = stream_get_contents($pipes[1]);
		$stderr = stream_get_contents($pipes[2]);
		foreach ($pipes as $pipe) {
			fclose($pipe);
		}

		$status = trim(proc_close($resource));
		if ($status) throw new Exception($stderr);

		return $stdout;
	}
	
	/**
	 * Test if a shell command exists
	 *
	 * @access  public
	 * @param   string    the command
	 * @return  bool
	 */
	public function command_exists($command) {
		try {
			$this->run_command($command);
		} catch (Exception $e) {
			return false;
		}
		return true;
	}

}

/**
 * Initialize the class
 */
	function &Shell() {
		return Shell_library::get_instance();
	}

/* End of file shell.php */
