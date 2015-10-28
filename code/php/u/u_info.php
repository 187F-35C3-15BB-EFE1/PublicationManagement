<?php
require "../utils.php";

$page = page_load ("u_info.html");

$uid = param_get ("uid", "");
$role = param_get ("role", "");
$roles = ["suggester", "moderator", "administrator"];

if (param_get_ok ("uid")) {
	$user = user_get_by_uid ($uid);
	if ($user !== FALSE) {
		if (param_get_ok ("role")) {
			if (in_array ($role, $roles)) {
				$loggedin_user = user_get_loggedin ();
				if ($loggedin_user !== FALSE && $loggedin_user["role"] == "administrator") {
					user_set_role ($uid, $role);
				}
			}
			utils_redirect ("u_info.php?uid=$uid");
		} else {
			$page = page_replace_fields ($page, htmlem ($user), "{{", "}}");
			$page = page_replace_element_select ($page, "role", "_", $roles, $user["role"], "{{", "}}");
			echo $page;
		}
	} else {
		utils_redirect ("u_list.php#u_info_no_such_user");
	}
} else {
	utils_redirect ("u_list.php#u_info_no_uid");
}

?>
