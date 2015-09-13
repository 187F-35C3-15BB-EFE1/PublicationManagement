<?php

require 'utils.php';
require 'sql.utils.php';

function db_get_user ($email, $password) {
	global $con;
	$query = "SELECT * FROM users WHERE email = '$email' AND password = '$password';";
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

function user_cookie ($user) {
	setcookie (
		'user', base64_encode ($user['id'].'|'.$user['password']),
		time () + 1 * 60 * 60,
		'/' , '127.0.0.1',
		false, true);
}

$error_message = '';
$email = $_GET['email'];
$password = $_GET['password'];
$redirect = false;
if (isset ($_GET['email']) && isset ($_GET['password'])
		&& 0 < strlen ($email)&& 0 < strlen ($password)) {
	if ($con = sql_begin ()) {

		$email = pg_escape_string ($con, $email);
		$password = pg_escape_string ($con, $password);

		if ($user = db_get_user ($email, $password)) {
			$redirect = true;
			user_cookie ($user);
		} else {
			$error_message = '<b style="color:red">Email or password is incorrect</b>';
		}

		sql_end ($con);

	} else {
		$error_message = '<b style="color:red">Can not connect to database</b>';
	}
} else {
	if (isset ($_GET['email']) || isset ($_GET['password'])) {
		$error_message = '<b style="color:red">Fields email and password must be filled</b>';
	}
}

$email = $_GET['email'];
$password = $_GET['password'];

if (!$redirect) {
	$page = page_load ('log_in.html');

	$page = page_replace ($page, '{{error_message}}', $error_message);

	$page = page_replace ($page, '{{email}}', html ($email));
	// $page = page_replace ($page, '{{password}}', html ($password));

	echo $page;
} else {
	utils_redirect ('user_info.php');
}

?>