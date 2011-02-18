<?php

class Config_class {
	
	protected $conf;
	
	public function __construct($conf) {
		$this->conf = $conf;
	}
	
	public function get($item) {
		return (isset($this->conf[$item]) ? $this->conf[$item] : false);
	}
	
	public function get_all() {
		return $this->conf;
	}
	
}

/* End of file config-class.php */
/* Location: ./lib/classes/config-class.php */
