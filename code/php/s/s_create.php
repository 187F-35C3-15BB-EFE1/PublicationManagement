<?php
require "../utils.php";

$page = page_load ("s_create.html");

$types = array ("new", "change", "delete");
$type = param_get ("type", "new");
$pid = param_get ("pid", "");
$confirm = param_get ("confirm", "") == "confirm";
$changed = param_get_array ("changed", array ());
$error_message = "";
$redirect = FALSE;
$columns = array ("title", "authors", "publication_year", "research_field", "venue", "papertype", "link"/*, "keywords"*/);

$loggedin_user = user_get_loggedin ();

if (user_check_role_includes ($loggedin_user["role"], "suggester")) {
	if (!in_array ($type, $types) || !param_get_ok ("pid") && $type != "new") {
		//utils_redirect ("s_create.php?type=new");
		var_dump ($_GET);
	} else {
		if ($type != "new") {
			$old_pub = publication_get_by_pid ($pid);
		} else {
			$old_pub = TRUE;
		}
		if ($old_pub) {
			if ($type != "delete") {
				$new_pub = array ();
				for ($i = 0; $i < count ($columns); $i ++) {
					$column = $columns[$i];
					$new_pub[$column] = param_get ("new_$column", "");
					if ($type == "change") {
						if (!in_array ($column, $changed) && 0 < strlen ($new_pub[$column])) {
							$changed[] = $column;
							$confirm = FALSE;
						}
					}
				}
				$new_pub["publication_year"] = param_get_num ("new_"."publication_year", NULL);
				if ($type == "new") {
					$empty = strlen ($new_pub["title"]) <= 0;
				} else {
					$empty = count ($changed) <= 0;
				}
				if ($empty && $confirm) {
					if ($type == "new") {
						$error_message = "Trying to suggest creating empty publication. Title should be filled at least.";
					} else {
						$error_message = "No changes are suggested.";
					}
				}
			}
			if (!$empty && $confirm) {
				$error_message = "OK";
				if ($type == "new") {
					if (suggestion_add_new ($new_pub, $loggedin_user["uid"])) {
						$redirect = TRUE;
					} else {
						echo "<HR>".htmle (pg_last_error ())."<HR>";
						$error_message = "Can not create suggestion";
					}
				} else if ($type == "delete") {
					if (suggestion_add_delete ($pid, $loggedin_user["uid"])) {
						$redirect = TRUE;
					} else {
						echo "<HR>".htmle (pg_last_error ())."<HR>";
						$error_message = "Can not create suggestion";
					}
				} else if ($type == "change") {
					if (suggestion_add_change ($pid, $new_pub, $changed, $loggedin_user["uid"])) {
						$redirect = TRUE;
					}
				}
			}
			if ($redirect) {
				utils_redirect ("s_list.php#suggestion_created");
			} else {
				$page = keep_or_omit ($page, "[[", "]]", $type == "new");
				$page = keep_or_omit ($page, "[[", "]]", $type == "change");
				$page = keep_or_omit ($page, "[[", "]]", $type == "delete");
				$page = keep_or_omit ($page, "[[", "]]", $type != "new");
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
					$page = page_replace ($page, "{{pid}}", $pid);
					$page = page_replace_fields ($page, htmlem ($old_pub), "{{old_", "}}");
				}

				if ($type != "delete") {
					$page = page_replace_fields ($page, htmlem ($new_pub), "{{new_", "}}");
				}

				if (!empty ($error_message)) {
					$error_message = htmle ($error_message) . "<BR>";
				}
				$page = page_replace ($page, "{{error_message}}", $error_message);
				$page = keep_or_omit ($page, "[[", "]]", $type == "change");

				if ($type == "change") {
					$checked = array ();
					for ($i = 0; $i < count ($columns); $i ++) {
						$checked[$columns[$i]] = "";
					}
					for ($i = 0; $i < count ($changed); $i ++) {
						$checked[$changed[$i]] = "checked";
					}
					$page = page_replace_fields ($page, htmlem ($checked), "{{checked_", "}}");
				}

				$page = page_replace ($page, "{{type}}", $type);
				echo $page;
			}
		} else {
			utils_redirect ("s_list.php#s_create_no_such_publication");
		}
		sql_release ();
	}
} else {
	utils_redirect ("../home.php#s_create_access_denied");
}
?>
