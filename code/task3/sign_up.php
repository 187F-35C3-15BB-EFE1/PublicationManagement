<?php

require 'utils.php';
require 'sql.utils.php';

function check_email ($email) {
	$ret = preg_match("/^\S+@(\S+)\.(\S+)$/", $email);
	return $ret === 1;
}

function db_add_user ($email, $password) {
	global $con;
	$query = "INSERT INTO users (email, password, role) VALUES ('$email', '$password', 'suggester');";
	// echo "<hr>$query<hr>";

	if ($rs = pg_query ($con, $query)) {
		$ret = true;
	} else {
		echo "<hr>".html (pg_last_error ())."<br>";
		echo html ("Cannot execute query: $query")."<hr>";
		$ret = false;
	}
	return $ret;
}

$error_message = '';
$email = $_GET['email'];
$password = $_GET['password'];
$redirect = false;
if (isset ($_GET['email']) && isset ($_GET['password'])
		&& 0 < strlen ($email)&& 0 < strlen ($password)) {
	if (check_email ($email)) {
		if ($con = sql_begin ()) {

			$email = pg_escape_string ($con, $email);
			$password = pg_escape_string ($con, $password);

			if (db_add_user ($email, $password)) {
				$redirect = true;
			} else {
				$error_message = '<b style="color:red">User already exists</b>';
			}

			sql_end ($con);

		} else {
			$error_message = '<b style="color:red">Can not connect to database</b>';
		}
	} else {
		$error_message = '<b style="color:red">Incorrect email format</b>';
	}
} else {
	if (isset ($_GET['email']) || isset ($_GET['password'])) {
		$error_message = '<b style="color:red">Fields email and password must be filled</b>';
	}
}

$email = $_GET['email'];
$password = $_GET['password'];

if (!$redirect) {
	$page = page_load ('sign_up.html');

	$page = page_replace ($page, '{{error_message}}', $error_message);

	$page = page_replace ($page, '{{email}}', html ($email));
	// $page = page_replace ($page, '{{password}}', html ($password));

	echo $page;
} else {
	utils_redirect ('log_in.php?email='.urlencode ($email).'&password='.urlencode ($password));
}

?>