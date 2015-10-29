<?php
require "../utils.php";

$page = page_load ("s_list.html");
$count = 10;
$page_num = param_get_num ("page", 1);
$offset = ($page_num - 1) * $count;
$redirect = FALSE;
$loggedin_user = user_get_loggedin ();

if (user_check_role_includes ($loggedin_user["role"], "moderator")) {
	$redirect = $page_num <= 0;
	if (!$redirect) {
		$sugs = suggestion_get_list ($offset, $count);
		$redirect = 1 < $page_num && count ($sugs["data"]) <= 0;
		$page_has_previous = 1 < $page_num;
		$page_has_next = $sugs["offset"] + $sugs["count"] < $sugs["total"];
	}
	if ($redirect) {
		$page_num = 1;
		utils_redirect ("s_list.php?page=".$page_num."&q=$q#invalid_page");
	} else {
		$page_parts = page_split ($page, "[[", "]]");
		$page = "";
		$page .= page_replace ($page_parts[0], "{{total}}", "".$sugs["total"]);
		$sugs = $sugs["data"];
		$mark = param_get_ok ("q");
		for ($j = 0; $j < count ($sugs); $j ++) {
			$sug = htmlem ($sugs[$j]);
			$page .= page_replace_fields ($page_parts[1], $sug, "{{", "}}");
		}
		$page_parts = page_split ($page_parts[2], "[[", "]]");
		$page .= $page_parts[0];
		if ($page_has_previous) {
			$page .= page_replace ($page_parts[1], "{{previous}}", "".($page_num - 1));
		}
		$page_parts = page_split ($page_parts[2], "[[", "]]");
		$page .= page_replace ($page_parts[0], "{{current}}", "".$page_num);
		if ($page_has_next) {
			$page .= page_replace ($page_parts[1], "{{next}}", "".($page_num + 1));
		}
		$page .= $page_parts[2];

		echo $page;
	}
} else {
	utils_redirect ("../home.php#s_list_access_denied");
}
?>