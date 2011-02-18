<?php

/**
 * The hashing library class
 */
class Hashing_library extends Uplc_library {
	
/**
 * These constants control the way the hashing functions operate.
 *
 * NOTE: Changing these values will compromise cross-compatability
 * and will invalidate any previously hashed values.
 */
	
	/**
	 * The main pattern delimiter; seperates the meta data from the hash.
	 *
	 * @access  public
	 * @const   MAIN_DELIM
	 * @type    string
	 */
	const MAIN_DELIM = '$';
	
	/**
	 * The sub-delimiter; seperates meta data values.
	 *
	 * @access  public
	 * @const   SUB_DELIM
	 * @type    string
	 */
	const SUB_DELIM = '&';
	
	/**
	 * The minimum allowed seed; seeds below this will be incremented until
	 * they are valid.
	 *
	 * @access  public
	 * @const   MIN_SEED
	 * @type    int
	 */
	const MIN_SEED = 10000;
	
	/**
	 * The amount by which the seed is modified per increment.
	 *
	 * @access  public
	 * @const   SEED_INC
	 * @type    int
	 */
	const SEED_INC = 1234;
	
	/**
	 * The default hashing algorithm if one is not specified.
	 *
	 * @access  public
	 * @const   DEFAULT_ALG
	 * @type    string
	 */
	const DEFAULT_ALG = 'md5';
	
// ----------------------------------------------------------------------------
	
	/**
	 * Constructor
	 */
	public function construct() { }
	
	/**
	 * A weaker alternative to shash() for when speed is important; Just
	 * runs a regular hash
	 *
	 * @access  public
	 * @param   string    the string to hash
	 * @param   string    the algorithm to use
	 * @return  string
	 */
	public function hash($str, $alg) {
		if (! is_callable($alg)) { return false; }
		return $alg($str);
	}
	
	/**
	 * Run a regular hash using the hmac function
	 *
	 * @access  public
	 * @param   string    the string to hash
	 * @param   string    the algorithm to use
	 * @param   string    the hmac key
	 * @return  string
	 */
	public function hash_hmac($str, $alg, $key) {
		if (! is_callable($alg)) { return false; }
		return hash_hmac($alg, $str, $key);
	}

	/**
	 * Hashes a password super securely (very hard to brute-force)
	 *
	 * @access  public
	 * @param   string    the string to hash
	 * @param   bool      prepend meta data to the hash?
	 * @param   int       a seed value
	 * @param   callback  the hashing function to use
	 * @param   string    added strength key/hmac key
	 * @return  string
	 */
	public function shash($str, $store_meta = false, $seed = self::MIN_SEED, $hash_alg = self::DEFAULT_ALG, $key = '') {
		// Test the params
		if ($seed === null) { $seed = self::MIN_SEED; }
		if ($hash_alg === null) { $hash_alg = self::DEFAULT_ALG; }
		
		// Parse the hash algorithm for an hmac prefix
		$alg = $hash_alg;
		if ($use_hmac = preg_match('/^hmac\.(.+)$/', $hash_alg, $match)) {
			$hash_alg = $match[1];
		}
		
		// Test some more params
		if (! is_string($str) || ! is_int($seed) || ! (@function_exists($hash_alg) || is_callable($hash_alg))) {
			return false;
		}
	
		// Make sure the seed is large enough to be worth-while
		$orig_seed = $seed;
		do {
			$seed += self::SEED_INC;
		} while ($seed < self::MIN_SEED);
	
		// Do the hashing
		if ($use_hmac) {
			for ($i = 0; $i <= $seed; $i++) {
				$str = hash_hmac($hash_alg, $str, $key);
			}
		} else {
			for ($i = 0; $i <= $seed; $i++) {
				$str = $hash_alg($str.$key);
			}
		}
	
		// Add meta data
		if ($store_meta) {
			$str = self::MAIN_DELIM.$alg.self::SUB_DELIM.$orig_seed.self::MAIN_DELIM.$str;
		}
	
		return $str;
	}

	/**
	 * Tests a string against a shash value
	 *
	 * @access  public
	 * @param   string    the string to test
	 * @param   string    the shash to test against
	 * @param   int       the seed value to use
	 * @param   callback  the hashing function to use
	 * @return  bool
	 */
	function test_shash($str, $shash, $seed = null, $hash_alg = null) {
		// Parse any shash meta data
		if (strpos($shash, self::MAIN_DELIM) !== false) {
			$shash = explode(self::MAIN_DELIM, $shash);
			if (count($shash) !== 3) {
				return false;
			}
		
			// Seperate out the meta data
			$shash_meta = $shash[1];
			$shash = $shash[2];
		
			// Parse the meta data
			$shash_meta = explode(self::SUB_DELIM, $shash_meta);
			if (count($shash_meta) !== 2) {
				return false;
			}
		
			$hash_alg = $shash_meta[0];
			$seed = (int) $shash_meta[1];
		}
	
		// Check that we have all the needed data
		if (! $seed || ! $hash_alg || ! (@function_exists($hash_alg) || is_callable($hash_alg))) {
			return false;
		}
	
		// Check that the values match
		$test_shash = $this->shash($str, $seed, $hash_alg, false);
		return ($shash === $test_shash);
	}
	
	/**
	 * The classic shash function, but using hash_hmac instead of
	 * a direct hash.
	 *
	 * @access  public
	 * @param   string    the string to hash
	 * @param   string    the secret key
	 * @param   bool      prepend meta data to the hash?
	 * @param   int       a seed value
	 * @param   callback  the hashing function to use
	 * @return  string
	 */
	public function shash_hmac($str, $key, $meta, $seed = self::MIN_SEED, $hash_alg = self::DEFAULT_ALG) {
		return $this->shash($str, $meta, $seed, 'hmac.'.$hash_alg, $key);
	}
	
}



// ----------------------------------------------------------------------------
//   Build the hash_hmac function if it does not exist



if (! function_exists('hash_hmac')) :
/**
 * In case hash_hmac doesn't exist, define a replacement
 *
 * @link  http://us2.php.net/manual/en/function.hash-hmac.php#93440
 *
 * @access  global
 * @param   string    the algorithm
 * @param   string    hash data
 * @param   stirng    secret key
 * @param   bool      raw output?
 * @return  string
 */
function hash_hmac($algo, $data, $key, $raw_output = false)
{
    $algo = strtolower($algo);
    $pack = 'H'.strlen($algo('test'));
    $size = 64;
    $opad = str_repeat(chr(0x5C), $size);
    $ipad = str_repeat(chr(0x36), $size);

    if (strlen($key) > $size)
    {
        $key = str_pad(pack($pack, $algo($key)), $size, chr(0x00));
    }
    else
    {
        $key = str_pad($key, $size, chr(0x00));
    }

    for ($i = 0; $i < strlen($key) - 1; $i++)
    {
        $opad[$i] = $opad[$i] ^ $key[$i];
        $ipad[$i] = $ipad[$i] ^ $key[$i];
    }

    $output = $algo($opad.pack($pack, $algo($ipad.$data)));

    return ($raw_output) ? pack($pack, $output) : $output;
}
endif;



// ----------------------------------------------------------------------------
//   Build the SHA1 hash function if it does not exist



if (! function_exists('sha1')) :
/**
 * Run an SHA1 hash
 *
 * @access  global
 * @param   string    the string to hash
 * @return  string
 */
function sha1($str) {
	return Hashing_sha1::hash($str);
}
/**
 * A class to help in SHA1 hashing
 *
 * Strongly influenced by the CodeIgniter Sha1 library
 * @link  http://www.codeigniter.com
 */
class Hashing_sha1 {

	/**
	 * Is mhash enabled?
	 *
	 * @access  protected
	 * @type    bool
	 */
	protected static $mhash_enabled = null;

	/**
	 * Generate the hash
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public static function hash($str) {
		if (self::$mhash_enabled === null) {
			self::$mhash_enabled = function_exists('mhash');
		}
		if (self::$mhash_enabled) {
			return self::mhash($str);
		} else {
			return self::sha1($str);
		}
	}

// ----------------------------------------------------------------------------
	
	/**
	 * Run an SHA1 hash using mhash
	 *
	 * @access  protected
	 * @param   string    the string to hash
	 * @return  string
	 */
	protected static function mhash($str) {
		return bin2hex(mhash(MHASH_SHA1, $str));
	}
	
	/**
	 * Run an SHA1 hash using the CI model
	 *
	 * @access  protected
	 * @param   string    the string to hash
	 * @return  string
	 */
	protected static function sha1($str) {
		$n = ((strlen($str) + 8) >> 6) + 1;

		for ($i = 0; $i < $n * 16; $i++) {
			$x[$i] = 0;
		}

		for ($i = 0; $i < strlen($str); $i++) {
			$x[$i >> 2] |= ord(substr($str, $i, 1)) << (24 - ($i % 4) * 8);
		}

		$x[$i >> 2] |= 0x80 << (24 - ($i % 4) * 8);

		$x[$n * 16 - 1] = strlen($str) * 8;

		$a =  1732584193;
		$b = -271733879;
		$c = -1732584194;
		$d =  271733878;
		$e = -1009589776;

		for ($i = 0; $i < count($x); $i += 16) {
			$olda = $a;
			$oldb = $b;
			$oldc = $c;
			$oldd = $d;
			$olde = $e;

			for($j = 0; $j < 80; $j++) {
				if ($j < 16) {
					$w[$j] = $x[$i + $j];
				} else {
					$w[$j] = self::rol($w[$j - 3] ^ $w[$j - 8] ^ $w[$j - 14] ^ $w[$j - 16], 1);
				}

				$t = self::safe_add(self::safe_add(self::rol($a, 5), self::ft($j, $b, $c, $d)),
					self::safe_add(self::safe_add($e, $w[$j]), self::kt($j)));

				$e = $d;
				$d = $c;
				$c = self::rol($b, 30);
				$b = $a;
				$a = $t;
			}

			$a = self::safe_add($a, $olda);
			$b = self::safe_add($b, $oldb);
			$c = self::safe_add($c, $oldc);
			$d = self::safe_add($d, $oldd);
			$e = self::safe_add($e, $olde);
		}

		return self::hex($a).self::hex($b).self::hex($c).self::hex($d).self::hex($e);
	}
	
	/**
	 * Convert a decimal to hex
	 *
	 * @access	protected
	 * @param	string
	 * @return	string
	 */
	protected static function hex($str) {
		$str = dechex($str);

		if (strlen($str) == 7) {
			$str = '0'.$str;
		}

		return $str;
	}

	/**
	 * Return result based on iteration
	 *
	 * @access	protected
	 * @return	string
	 */
	protected static function ft($t, $b, $c, $d) {
		if ($t < 20)
			return ($b & $c) | ((~$b) & $d);
		if ($t < 40)
			return $b ^ $c ^ $d;
		if ($t < 60)
			return ($b & $c) | ($b & $d) | ($c & $d);

		return $b ^ $c ^ $d;
	}

	/**
	 * Determine the additive constant
	 *
	 * @access	protected
	 * @return	string
	 */
	protected static function kt($t) {
		if ($t < 20) {
			return 1518500249;
		} else if ($t < 40) {
			return 1859775393;
		} else if ($t < 60) {
			return -1894007588;
		} else {
			return -899497514;
		}
	}

	/**
	 * Add integers, wrapping at 2^32
	 *
	 * @access	protected
	 * @return	string
	 */
	protected static function safe_add($x, $y) {
		$lsw = ($x & 0xFFFF) + ($y & 0xFFFF);
		$msw = ($x >> 16) + ($y >> 16) + ($lsw >> 16);

		return ($msw << 16) | ($lsw & 0xFFFF);
	}

	/**
	 * Bitwise rotate a 32-bit number
	 *
	 * @access	protected
	 * @return	int
	 */
	protected static function rol($num, $cnt) {
		return ($num << $cnt) | self::zero_fill($num, 32 - $cnt);
	}

	/**
	 * Pad string with zero
	 *
	 * @access	protected
	 * @return	string
	 */
	protected static function zero_fill($a, $b) {
		$bin = decbin($a);

		if (strlen($bin) < $b) {
			$bin = 0;
		} else {
			$bin = substr($bin, 0, strlen($bin) - $b);
		}

		for ($i=0; $i < $b; $i++) {
			$bin = "0".$bin;
		}

		return bindec($bin);
	}
}
endif;

/* End of file hashing.php */
/* Location: ./lib/hashing.php */
