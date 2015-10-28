<?php
require "../utils.php";

$page = page_load ("p_list.html");
$count = 10;
$page_num = param_get_num ("page", 1);
$q = param_get ("q", "");
$page = page_replace ($page, "{{q}}", $q);
$offset = ($page_num - 1) * $count;
$redirect = FALSE;

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
	if (param_get_ok ("q")) {
		$pubs = publication_get_list_by_q ($offset, $count, $q);
	} else {
		$pubs = publication_get_list ($offset, $count);
	}
	$redirect = 1 < $page_num && count ($pubs["data"]) <= 0;
	$page_has_previous = 1 < $page_num;
	$page_has_next = $pubs["offset"] + $pubs["count"] < $pubs["total"];
}
if ($redirect) {
	$page_num = 1;
	utils_redirect ("p_list.php?page=".$page_num."&q=$q#invalid_page");
} else {
	$page_parts = page_split ($page, "[[", "]]");
	$page = "";
	$page .= page_replace ($page_parts[0], "{{total}}", "".$pubs["total"]);
	$pubs = $pubs["data"];
	$mark = param_get_ok ("q");
	for ($j = 0; $j < count ($pubs); $j ++) {
		$pub = htmlem ($pubs[$j]);
		if ($mark) {
			$pub = markm ($pub, $q);
		}
		$page .= page_replace_fields ($page_parts[1], $pub, "{{", "}}");
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