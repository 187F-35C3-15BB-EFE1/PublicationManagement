<?php
require 'utils.php';

$page = page_load ('home.html');
$loggedin_user = user_get_loggedin ();
$role = $loggedin_user["role"];

$is_user = user_check_role_includes ($role, "suggester");
$is_modr = user_check_role_includes ($role, "moderator");
$is_admr = user_check_role_includes ($role, "administrator");

$page = page_replace ($page, "{{role}}", $role);

$page = keep_or_omit ($page, "[[", "]]", !$is_user);
$page = keep_or_omit ($page, "[[", "]]", $is_user);
$page = keep_or_omit ($page, "[[", "]]", $is_admr);
$page = keep_or_omit ($page, "{[", "]}", $is_user);
if ($is_user) {
	$page = keep_or_omit ($page, "[[", "]]", $is_modr);
}
echo $page;
?>