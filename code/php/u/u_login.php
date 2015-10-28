<?php
require "../utils.php";

$page = page_load ("u_login.html");

$email = param_get ("email", "");
$password = param_get ("password", "");
$user = array ("email" => $email);
$error_message = "";
$redirect = FALSE;

if (param_get_ok ("email") || param_get_ok ("password")) {
	if (param_get_ok ("email") && param_get_ok ("password")) {
		if (user_login ($email, $password)) {
			$error_message = "OK";
			$redirect = TRUE;
		} else {
			$error_message = "Invalid email or password";
		}
	} else {
		$error_message = "Password or email is not specified";
	}
}

if ($redirect) {
	utils_redirect ("../home.php#login_successful");
} else {
	$page = page_replace_fields ($page, htmlem ($user), "{{", "}}");
	if (!empty ($error_message)) {
		$error_message = htmle ($error_message) . "<BR>";
	}
	$page = page_replace ($page, "{{error_message}}", $error_message);

	echo $page;
}

?>
