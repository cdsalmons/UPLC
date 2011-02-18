<?php

// Import the cryptography library
import('crypto');

// The initial string value
$str = 'Hello World!';
echo 'Base string: '.$str."\n";

// The encrypted string value
$str = Crypto()->encrypt($str);
echo 'Encrypted string: '.$str."\n";

// The decrypted string value
$str = Crypto()->decrypt($str);
echo 'Decrypted string: '.$str."\n";

/* End of file crypto.php */
/* Location: ./test/tests/crypto.php */
