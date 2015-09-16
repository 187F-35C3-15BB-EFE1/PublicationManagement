<?php
function utils_redirect ($to) {
	header ("Location: $to");
}
function html ($s) {
	return htmlspecialchars ($s);
}
function page_load ($p) {
	return $page = file_get_contents ($p);
}
function page_replace ($p, $m, $x) {
	return str_replace ($m, $x, $p);
}
function page_split ($p, $ml, $mr) {
	$il = strpos ($p, $ml);
	$ll = strlen ($ml);
	$ir = strpos ($p, $mr, $il + $ll);
	$lr = strlen ($mr);
	$b = substr ($p, 0, $il);
	$c = substr ($p, $il + $ll, $ir);
	$a = substr ($p, $ir + $lr, strlen ($p));
	return array ($b, $c, $a);
}
?>