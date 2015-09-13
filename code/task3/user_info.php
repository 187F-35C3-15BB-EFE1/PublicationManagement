<?php

require 'utils.php';
require 'sql.utils.php';

function db_get_user ($id, $password) {
	global $con;
	$query = "SELECT * FROM users WHERE id = '$id' AND password = '$password';";
	// echo "<hr>$query<hr>";

	$ret = false;
	if ($rs = pg_query ($con, $query)) {
		if ($row = pg_fetch_row ($rs)) {
			$ret = array ();
			$fields = array ('id', 'email', 'password', 'role');
			for ($i = 0; $i < count ($fields); $i ++) {
				$ret[$fields[$i]] = $row[$i];
			}
		}
	} else {
		echo "<hr>".html (pg_last_error ())."<br>";
		echo html ("Cannot execute query: $query")."<hr>";
	}
	return $ret;
}

function user_cookie () {
	$ret = false;
	if (isset ($_COOKIE['user'])) {
		$ret = explode ('|', base64_decode ($_COOKIE['user']), 2);
		$ret = array ('id' => $ret[0], 'password' => $ret[1]);
	}
	return $ret;
}

$error_message = '';
if ($user_cred = user_cookie ($user)) {
	if ($con = sql_begin ()) {

		foreach ($user_cred as $key => $value) {
			$user_cred[$key] = pg_escape_string ($con, $value);
		}

		if ($user = db_get_user ($user_cred['id'], $user_cred['password'])) {
		} else {
			$error_message = '<b style="color:red">Email or password is incorrect</b>';
		}

		sql_end ($con);

	} else {
		$error_message = '<b style="color:red">Can not connect to database</b>';
	}
}

if ($user_cred) {
	$page = page_load ('user_info.html');

	foreach ($user as $key => $value) {
		$page = page_replace ($page, '{{'.$key.'}}', html ($value));
	}

	echo $page;
} else {
	utils_redirect ('.');
}

?>