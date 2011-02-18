<?php

/*
| -------------------------------------------------------------------
| Session library config
| -------------------------------------------------------------------
|
*/

/*
|------------------------------------------------
| Database configuration
|------------------------------------------------
|
| This is the database config string. For more direct control, an
| array may be used instead.
|
*/
$config['database'] = 'mysql://testuser:testpass@localhost/testdb';

/*
|------------------------------------------------
| Database session table
|------------------------------------------------
|
| The table in which session data should be stored.
|
*/
$config['db_table'] = 'session';

/*
|------------------------------------------------
| Session encryption key
|------------------------------------------------
|
| Select a unique string key to help in securing your sessions.
|
| NOTE: Changing this value will invalidate all previous hashes, meaning
| that all sessions will immediately expire.
|
*/
$config['encryption_key'] = 'Your unique key goes here.';

/*
|------------------------------------------------
| Hashing seed
|------------------------------------------------
|
| An integer seed used during hashing. The larger the seed, the longer
| the hash will take. You should select a seed between 0-30000, although
| any integer value will work.
|
| NOTE: Changing this value will invalidate all previous hashes, meaning
| that all sessions will immediately expire.
|
*/
$config['hashing_seed'] = 12345;

/*
|------------------------------------------------
| Session expiration
|------------------------------------------------
|
| How long before a session auto-expires (in seconds).
|
*/
$config['expiration'] = 7200;

/*
|------------------------------------------------
| Expiration on window close
|------------------------------------------------
|
| Should the session cookie expire when the browser window is closed?
|
*/
$config['expire_on_close'] = true;

/* End of file session.php */
/* Location: ./config/session.php */
