<?php
require "../utils.php";

$page = page_load ("p_info.html");

$pid = param_get ("pid", "");
$loggedin_user = user_get_loggedin ();
$is_user = user_check_role_includes ($loggedin_user["role"], "suggester");
$page = keep_or_omit ($page, "[[", "]]", $is_user);

if (param_get_ok ("pid")) {
	$pub = publication_get_by_pid ($pid);
	if ($pub !== FALSE) {
		$page = page_replace_fields ($page, htmlem ($pub), "{{", "}}");
		echo $page;
	} else {
		utils_redirect ("p_list.php#p_info_no_such_publication");
	}
} else {
	utils_redirect ("p_list.php#p_info_no_pid");
}

?>
