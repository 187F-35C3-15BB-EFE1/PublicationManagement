<?php
require "../utils.php";

$page = page_load ("p_list.html");
$count = 10;
$page_num = param_get_num ("page", 1);
$q = param_get ("q", "");
$orders = array ("none", "year", "field");
$order = param_get ("order", "none");
if (!in_array ($order, $orders)) {
	$order = "none";
}
$relateds = array ("authors", "year");
$related = param_get ("related", "none");
$pid = param_get ("pid", "");
$page = page_replace ($page, "{{q}}", htmle ($q));
$offset = ($page_num - 1) * $count;
$redirect = FALSE;
$loggedin_user = user_get_loggedin ();
$is_user = user_check_role_includes ($loggedin_user["role"], "suggester");

function mark ($s, $m) {
	$p = 0;
	$o = 0;
	$res = "";
	$s = "".$s;
	while (($p = stripos ($s, $m, $p)) !== FALSE) {
		$res .= substr ($s, $o, $p - $o);
		$res .= "<b><mark>".substr ($s, $p, strlen ($m))."</mark></b>";
		$p += strlen ($m);
		$o = $p;
	}
	$res .= substr ($s, $o, strlen ($s) - $o);
	return $res;
}

function markm ($ss, $m) {
	$res = array ();
	foreach ($ss as $k => $v) {
		$res[$k] = mark ($v, $m);
	}
	return $res;
}

$redirect = $page_num <= 0;
if (!$redirect) {
	if (param_get_ok ("related") && in_array ($related, $relateds) && param_get_ok ("pid")) {
		$pubs = publication_get_related_list ($offset, $count, $order, $related, $pid);
	} else {
		if (param_get_ok ("q")) {
			$pubs = publication_get_list_by_q ($offset, $count, $order, $q);
		} else {
			$pubs = publication_get_list ($offset, $count, $order);
		}
	}
	$redirect = 1 < $page_num && count ($pubs["data"]) <= 0;
	$page_has_previous = 1 < $page_num;
	$page_has_next = $pubs["offset"] + $pubs["count"] < $pubs["total"];
}
if ($redirect) {
	$page_num = 1;
	utils_redirect ("p_list.php?page=".$page_num."&q=$q#invalid_page");
} else {
	$page = keep_or_omit ($page, "[[", "]]", $order != "none");
	$page = keep_or_omit ($page, "[[", "]]", $order == "none");
	$page = keep_or_omit ($page, "[[", "]]", $order != "year");
	$page = keep_or_omit ($page, "[[", "]]", $order == "year");
	$page = keep_or_omit ($page, "[[", "]]", $order != "field");
	$page = keep_or_omit ($page, "[[", "]]", $order == "field");

	$page = page_replace ($page, "{{total}}", "".$pubs["total"]);
	$page_parts = page_split ($page, "[[", "]]");
	$page = "";
	$page .= $page_parts[0];
	$pubs = $pubs["data"];
	$mark = param_get_ok ("q");
	for ($j = 0; $j < count ($pubs); $j ++) {
		$pub = htmlem ($pubs[$j]);
		if ($mark) {
			$pub = markm ($pub, $q);
		}
		$page .= page_replace_fields ($page_parts[1], $pub, "{{", "}}");
	}
	$page .= $page_parts[2];

	$page = keep_or_omit ($page, "[[", "]]", $page_has_previous);
	$page = keep_or_omit ($page, "[[", "]]", $page_has_next);

	if ($page_has_previous) {
		$page = page_replace ($page, "{{previous}}", "".($page_num - 1));
	}
	$page = page_replace ($page, "{{current}}", "".$page_num);
	if ($page_has_next) {
		$page = page_replace ($page, "{{next}}", "".($page_num + 1));
	}

	$page = keep_or_omit ($page, "[[", "]]", $is_user);

	// keep_or_omit ($page, $ml, $mr, $keep)
	// repeat_fill ($page, $ml, $mr, $m, $vals)

	$page = page_replace ($page, "{{order}}", $order);
	$page = page_replace ($page, "{{q}}", $q);
	$page = page_replace ($page, "{{related}}", $related);
	$page = page_replace ($page, "{{pid}}", $pid);

	echo $page;
}
?>