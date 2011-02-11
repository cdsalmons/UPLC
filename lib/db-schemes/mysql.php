<?php

class Mysql_scheme implements Database_scheme {

	public function open($host, $user, $pass) {
		return mysql_connect($host, $user, $pass);
	}
	
	public function close(&$link) {
		mysql_close($link);
	}
	
	public function query($query, &$link) {
		return mysql_query($query, $link);
	}
	
}

/* End of file mysql.php */
/* Location ./lib/db-schemes/mysql.php */
