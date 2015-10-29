<?php
error_reporting (E_ALL);
ini_set ('display_errors', 'On');
?><?php
require "../utils.php";

$page = page_load ("s_info.html");

$sid = param_get ("sid", "");
$action = param_get ("action", "");
$error_message = "";
$columns = array ("title", "authors", "publication_year", "research_field", "venue", "papertype", "link"/*, "keywords"*/);
$actions = array ("apply", "reject");

$loggedin_user = user_get_loggedin ();

if (param_get_ok ("action") && !in_array ($action, $actions)) {
	//utils_redirect ("s_info.php?sid=$sid");
} else {
	if (user_check_role_includes ($loggedin_user["role"], "moderator")) {
		if (!param_get_ok ("sid")) {
			utils_redirect ("s_list.php#s_info_no_sid");
		} else {
			$sug = suggestion_get_by_sid ($sid);
			if (!!$sug) {
				if (param_get_ok ("action")) {
					$done = FALSE;
					if ($action == "apply") {
						if (suggestion_apply ($sug)) {
							utils_redirect ("s_list.php#s_info_applied");
							$done = TRUE;
						}
					} else if ($action == "reject") {
						if (suggestion_reject ($sug)) {
							utils_redirect ("s_list.php#s_info_rejected");
							$done = TRUE;
						}
					}
					if (!$done) {
						//utils_redirect ("s_info.php?sid=$sid");
					}
				} else {
					$type = suggestion_get_type ($sug);
					if ($type != "new") {
						$old_pub = publication_get_by_pid ($sug["to_pid"]);
					}
					$sug["changes"] = json_decode ($sug["changes"]);
					$page = keep_or_omit ($page, "[[", "]]", $type == "new");
					$page = keep_or_omit ($page, "[[", "]]", $type == "change");
					$page = keep_or_omit ($page, "[[", "]]", $type == "delete");
					$page = keep_or_omit ($page, "{[", "]}", $type != "new");
					$page = keep_or_omit ($page, "{[", "]}", $type != "delete");
					$page = keep_or_omit ($page, "{[", "]}", $type == "change");

					$page = repeat_fill ($page, "[[", "]]", "{{column_name}}", $columns);

					$page = repeat_fill ($page, "[[", "]]", "{{column_name}}", $columns);

					if ($type == "change") {
						$page = repeat_fill ($page, "[[", "]]", "{{column_name}}", $columns);
						$page = repeat_fill ($page, "[[", "]]", "{{column_name}}", $columns);
					}

					if ($type != "new") {
						$page = page_replace_fields ($page, htmlem ($old_pub), "{{old_", "}}");
					}

					if ($type != "delete") {
						$new_pub = array ();
						for ($i = 0; $i < count ($columns); $i ++) {
							$new_pub[$columns[$i]] = "";
						}
						foreach ($sug["changes"] as $k => $v) {
							$new_pub[$k] = $v;
						}
						$page = page_replace_fields ($page, htmlem ($new_pub), "{{new_", "}}");
					}
					if ($type == "change") {
						$checked = array ();
						for ($i = 0; $i < count ($columns); $i ++) {
							$checked[$columns[$i]] = "";
						}
						foreach ($sug["changes"] as $k => $v) {
							$checked[$k] = "checked";
						}
						$page = page_replace_fields ($page, htmlem ($checked), "{{checked_", "}}");
					}
					$page = page_replace ($page, "{{sid}}", $sid);

					echo $page;
				}

				sql_release ();
			} else {
				utils_redirect ("s_list.php#s_info_no_such_suggestion");
			}
		}
	} else {
		utils_redirect ("../home.php#s_info_access_denied");
	}
}
?>
