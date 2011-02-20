<?php

/**
 * Cookies class library
 *
 * @class    Cookies_library
 * @parent   Uplc_library
 */
class Cookies_library extends Uplc_library {
	
	/**
	 * Constructor
	 */
	public function construct() {
		import('input');
	}
	
	/**
	 * Set a cookie
	 *
	 * @access  public
	 * @param   string    the cookie name
	 * @param   string    the cookie value
	 * @param   int       the expiration length
	 * @param   string    the cookie domain
	 * @param   string    the cookie path
	 * @return  void
	 */
	public function set($name, $value, $expire = 0, $domain = '', $path = '/') {
		// Get the expiration timestamp
		if ($expire < 0) {
			$expire = time() - 86500;
		} elseif ($expire > 0) {
			$expire = time() + $expire;
		}
		
		// Set the cookie
		return setcookie($name, $value, $expire, $path, $domain, 0);
	}
	
	/**
	 * Get a cookie
	 *
	 * @access  public
	 * @param   string    the cookie to read
	 * @return  string
	 */
	public function get($name) {
		return Input()->cookie($name);
	}
	
	/**
	 * Delete a cookie
	 *
	 * @access  public
	 * @param   string    the cookie name
	 * @param   string    the domain
	 * @param   string    the path
	 * @return  void
	 */
	public function delete($name, $domain = '', $path = '/') {
		return $this->set($name, '', -1, $domain, $path);
	}
	
}

/* End of file cookies.php */
/* Location: ./lib/cookies.php */
