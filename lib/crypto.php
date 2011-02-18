<?php

/**
 * Encryption library class
 *
 * Strongly influenced by the CodeIgniter encryption library.
 * @link  http://www.codeigniter.com
 *
 * @class   Crypto_library
 * @parent  Uplc_library
 */
class Crypto_library extends Uplc_library {
	
	/**
	 * The config data
	 *
	 * @access  public
	 * @type    Config_class
	 */
	protected $conf;
	
	/**
	 * Is mcrypt enabled?
	 *
	 * @access  protected
	 * @type    bool
	 */
	protected $mcrypt;
	
	/**
	 * Fallback encryption key
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $default_key;
	
	/**
	 * Use SHA1? (as opposed to MD5)
	 *
	 * @access  protected
	 * @type    bool
	 */
	protected $use_sha1;
	
	/**
	 * The mcrypt mode
	 *
	 * @access  protected
	 * @type    int
	 */
	protected $mcrypt_mode = '';
	
	/**
	 * The mcrypt cipher
	 *
	 * @access  protected
	 * @type    int
	 */
	protected $mcrypt_cipher = '';
	
// ----------------------------------------------------------------------------
	
	/**
	 * Constructor
	 *
	 * @access  public
	 * @return  void
	 */
	public function construct($conf) {
		import('hashing');
		$this->conf = $conf;
		$this->set_hash_algorithm($this->conf->get('hash_algorithm'));
		$this->default_key = $this->conf->get('encryption_key');
		$this->mcrypt =!! function_exists('mcrypt_encrypt');
	}
	
	/**
	 * Set the default encryption key
	 *
	 * @access  public
	 * @param   string    the encryption key
	 * @return  void
	 */
	public function set_key($key) {
		$this->default_key = $key;
	}
	
	/**
	 * Set the hashing algorithm to use
	 *
	 * @access  public
	 * @param   string    the hash algorithm
	 * @return  bool
	 */
	public function set_hash_algorithm($alg) {
		if (is_string($alg)) {
			$alg = strtolower($alg);
			if ($alg == 'md5') {
				$this->use_sha1 = false;
			} elseif ($alg == 'sha1') {
				$this->use_sha1 = true;
			} else {
				return false;
			}
			return true;
		}
		
		return false;
	}
	
	/**
	 * Encrypt a string
	 *
	 * @access  public
	 * @param   string    the string to encrypt
	 * @param   string    the encryption key
	 * @return  string
	 */
	public function encrypt($str, $key = '') {
		$key = $this->enc_key($key);
		
		if ($this->mcrypt) {
			$this->mcrypt_encode($str, $key);
		} else {
			
		}
		
		return base64_encode($str);
	}
	
	/**
	 * Decrypt a string
	 *
	 * @access  public
	 * @param   string    the string to decrypt
	 * @param   string    the encryption key
	 * @return  string
	 */
	public function decrypt($str, $key = '') {
		$key = $this->enc_key($key);
		
		if (preg_match('/[^a-zA-Z0-9\/\+=]/', $str)) {
			return false;
		}

		$dec = base64_decode($str);

		if ($this->mcrypt) {
			if (($dec = $this->mcrypt_decode($dec, $key)) === false) {
				return false;
			}
		} else {
			$dec = $this->xor_decode($dec, $key);
		}

		return $dec;
	}
	
	/**
	 * Set the mcrypt cipher
	 *
	 * @access  public
	 * @param   int       the mcrypt cipher
	 * @return  void
	 */
	public function set_cipher($cipher) {
		$this->mcrypt_cipher = $cipher;
	}
	
	/**
	 * Set the mcrypt mode
	 *
	 * @access  public
	 * @param   int       the mcrypt mode
	 * @return  void
	 */
	public function set_mode($mode) {
		$this->mcrypt_mode = $mode;
	}
	
// ----------------------------------------------------------------------------
	
	/**
	 * Make a 128-bit encryption key from the one given
	 *
	 * @access  protected
	 * @param   string    the encryption key
	 * @return  string
	 */
	protected function enc_key($key = '') {
		if ($key == '') {
			$key = $this->default_key;
		}
		
		if (! $key || empty($key)) {
			trigger_error('No encryption key given', E_USER_ERROR);
		}
		
		return Hashing()->hash($key, 'md5');
	}
	
	/**
	 * Get the mcrypt cipher
	 *
	 * @access  protected
	 * @return  int
	 */
	protected function get_cipher() {
		if ($this->mcrypt_cipher == '') {
			$this->mcrypt_cipher = MCRYPT_RIJNDAEL_256;
		}
		
		return $this->mcrypt_cipher;
	}
	
	/**
	 * Get the mcrypt mode
	 *
	 * @access  protected
	 * @return  int
	 */
	protected function get_mode() {
		if ($this->mcrypt_mode == '') {
			$this->mcrypt_mode = MCRYPT_MODE_CBC;
		}
		
		return $this->mcrypt_mode;
	}
	
	/**
	 * Hash a string using the selected method
	 *
	 * @access  protected
	 * @param   string    the string to hash
	 * @return  string
	 */
	protected function hash($str) {
		return (($this->use_sha1) ? sha1($str) : md5($str));
	}
	
	/**
	 * Encrypt using mcrypt
	 *
	 * @access  protected
	 * @param   string    string data
	 * @param   string    encryption key
	 * @return  string
	 */
	protected function mcrypt_encode($data, $key) {
		// Initialize mcrypt
		$init_size = mcrypt_get_iv_size($this->get_cipher(), $this->get_mode());
		$init_vect = mcrypt_create_iv($init_size, MCRYPT_RAND);
		
		// Encrypt
		$data = $init_vect.mcrypt_encrypt($this->get_cipher(), $key, $data, $this->get_mode(), $init_vect);
		
		// Add noise
		return $this->add_cipher_noise($data, $key);
	}

	/**
	 * Decrypt using mcrypt
	 *
	 * @access  protected
	 * @param   string    string data
	 * @param   string    encryption key
	 * @return  string
	 */
	protected function mcrypt_decode($data, $key) {
		// Remove noise
		$data = $this->remove_cipher_noise($data, $key);
		
		// Initialize mcrypt
		$init_size = mcrypt_get_iv_size($this->get_cipher(), $this->get_mode());
		if ($init_size > strlen($data)) {
			return false;
		}
		$init_vect = substr($data, 0, $init_size);
		
		// Decrypt
		$data = substr($data, $init_size);
		$data = mcrypt_decrypt($this->get_cipher(), $key, $data, $this->get_mode(), $init_vect);
		return rtrim($data, "\0");
	}
	
	/**
	 * Adds permuted noise to the IV + encrypted data to protect
	 * against Man-in-the-middle attacks on CBC mode ciphers
	 * http://www.ciphersbyritter.com/GLOSSARY.HTM#IV
	 *
	 * @access  protected
	 * @param   string    string data
	 * @param   string    encryption key
	 * @return  string
	 */
	function add_cipher_noise($data, $key) {
		$keyhash = $this->hash($key);
		$keylen = strlen($keyhash);
		$str = '';

		for ($i = 0, $j = 0, $len = strlen($data); $i < $len; ++$i, ++$j) {
			if ($j >= $keylen) {
				$j = 0;
			}

			$str .= chr((ord($data[$i]) + ord($keyhash[$j])) % 256);
		}

		return $str;
	}
	
	/**
	 * Removes permuted noise from the IV + encrypted data
	 *
	 * @access  protected
	 * @param   string    string data
	 * @param   string    encryption key
	 * @return  string
	 */
	protected function remove_cipher_noise($data, $key) {
		$keyhash = $this->hash($key);
		$keylen = strlen($keyhash);
		$str = '';

		for ($i = 0, $j = 0, $len = strlen($data); $i < $len; ++$i, ++$j) {
			if ($j >= $keylen) {
				$j = 0;
			}

			$temp = ord($data[$i]) - ord($keyhash[$j]);

			if ($temp < 0) {
				$temp = $temp + 256;
			}

			$str .= chr($temp);
		}

		return $str;
	}
	
// ----------------------------------------------------------------------------
//   XOR Encryption Methods
	
	/**
	 * Takes a plain-text string and key as input and generates an
	 * encoded bit-string using XOR
	 *
	 * @access  protected
	 * @param   string    string data
	 * @param   string    encryption key
	 * @return  string
	 */
	protected function xor_encode($string, $key) {
		$rand = '';
		while (strlen($rand) < 32) {
			$rand .= mt_rand(0, mt_getrandmax());
		}

		$rand = $this->hash($rand);

		$enc = '';
		for ($i = 0; $i < strlen($string); $i++) {
			$enc .= substr($rand, ($i % strlen($rand)), 1).(substr($rand, ($i % strlen($rand)), 1) ^ substr($string, $i, 1));
		}

		return $this->xor_merge($enc, $key);
	}

	/**
	 * Takes an encoded string and key as input and generates the
	 * plain-text original message
	 *
	 * @access  protected
	 * @param   string    string data
	 * @param   string    encryption key
	 * @return  string
	 */
	function xor_decode($string, $key) {
		$string = $this->xor_merge($string, $key);

		$dec = '';
		for ($i = 0; $i < strlen($string); $i++) {
			$dec .= (substr($string, $i++, 1) ^ substr($string, $i, 1));
		}

		return $dec;
	}

	/**
	 * Takes a string and key as input and computes the difference using XOR
	 *
	 * @access  protected
	 * @param   string    string data
	 * @param   string    encryption key
	 * @return  string
	 */
	function xor_merge($string, $key) {
		$hash = $this->hash($key);
		$str = '';
		for ($i = 0; $i < strlen($string); $i++) {
			$str .= substr($string, $i, 1) ^ substr($hash, ($i % strlen($hash)), 1);
		}

		return $str;
	}
	
}

/* End of file crypto.php */
/* Location: ./lib/crypto.php */
