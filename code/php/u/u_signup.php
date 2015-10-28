<?php
require "../utils.php";

$page = page_load ("u_signup.html");

$email = param_get ("email", "");
$user = array ("email" => $email, "password" => $password);
$password = param_get ("password", "");
$error_message = "";
$redirect = FALSE;

if (param_get_ok ("email") || param_get_ok ("password")) {
	if (param_get_ok ("email") && param_get_ok ("password")) {
		if (user_signup_check ($email, $password)) {
			if (user_signup ($email, $password)) {
				$error_message = "OK";
				$redirect = TRUE;
			} else {
				$error_message = "User with this email already exists";
			}
		} else {
			$error_message = "Invalid format";
		}
	} else {
		$error_message = "Password or email is not specified";
	}
}

if ($redirect) {
	utils_redirect ("../home.php#signup_successful");
} else {
	$page = page_replace_fields ($page, htmlem ($user), "{{", "}}");
	if (!empty ($error_message)) {
		$error_message = htmle ($error_message) . "<BR>";
	}
	$page = page_replace ($page, "{{error_message}}", $error_message);

	echo $page;
}

sql_release ();

?>
