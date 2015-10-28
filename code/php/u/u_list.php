<?php

require "../utils.php";

$page = page_load ("u_list.html");
$count = 2;
$page_num = param_get_num ("page", 1);
$offset = ($page_num - 1) * $count;
$redirect = FALSE;

$redirect = $page_num <= 0;
if (!$redirect) {
	$users = user_get_list ($offset, $count);
	$redirect = 1 < $page_num && count ($users["data"]) <= 0;
	$page_has_previous = 1 < $page_num;
	$page_has_next = $users["offset"] + $users["count"] < $users["total"];
}
if ($redirect) {
	$page_num = 1;
	utils_redirect ("u_list.php?page=".$page_num."#invalid_page");
} else {
	$page_parts = page_split ($page, "[[", "]]");
	$page = "";
	$page .= page_replace ($page_parts[0], "{{total}}", "".$users["total"]);
	$users = $users["data"];
	for ($j = 0; $j < count ($users); $j ++) {
		$page .= page_replace_fields ($page_parts[1], htmlem ($users[$j]), "{{", "}}");
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
?>