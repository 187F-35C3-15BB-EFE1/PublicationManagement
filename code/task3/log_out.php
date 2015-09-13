<?php

require 'utils.php';

function user_cookie () {
	setcookie ('user', '', 1, '/');
}

user_cookie ();
utils_redirect ('user_info.php');

?>