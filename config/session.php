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
