<?php
require "../utils.php";

$page = page_load ("p_info.html");

$pid = param_get ("pid", "");

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
