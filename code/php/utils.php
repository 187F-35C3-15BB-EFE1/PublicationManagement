<?php

include "sql.php";

function utils_redirect ($to) {
	header ("Location: $to");
}
function htmle ($s) {
	return htmlspecialchars ($s);
}
function param_get_ok ($name) {
	return isset ($_GET[$name]) && 0 < strlen ($_GET[$name]);
}
function param_get ($name, $default) {
	return param_get_ok ($name) ? $_GET[$name] : $default;
}
function param_get_num_ok ($name) {
	return param_get_ok ($name) && is_numeric (param_get ($name, NULL));
}
function param_get_num ($name, $default) {
	return param_get_ok ($name) ? intval (param_get ($name, NULL)) : $default;
}
function htmlem ($ss) {
	$res = array ();
	foreach ($ss as $k => $v) {
		$res[$k] = htmle ($v);
	}
	return $res;
}
function page_load ($p) {
	return $page = file_get_contents ($p);
}
function page_replace ($p, $m, $x) {
	return str_replace ($m, $x, $p);
}
function page_replace_fields ($p, $f, $ml, $mr) {
	$res = $p;
	foreach ($f as $k => $v) {
		$res = page_replace ($res, $ml.$k.$mr, $v);
	}
	return $res;
}
function page_replace_element_select ($p, $name, $delimiter, $values, $selected, $ml, $mr) {
	$fields = array ();
	for ($i = 0; $i < count ($values); $i ++) {
		$fields[$name.$delimiter.$values[$i]] = '';
	}
	$fields[$name.$delimiter.$selected] = 'selected';
	return page_replace_fields ($p, $fields, $ml, $mr);
}
function page_split ($p, $ml, $mr) {
	$il = strpos ($p, $ml);
	$ll = strlen ($ml);
	$ir = strpos ($p, $mr, $il + $ll);
	$lr = strlen ($mr);
	$b = substr ($p, 0, $il);
	$c = substr ($p, $il + $ll, $ir - ($il + $ll));
	$a = substr ($p, $ir + $lr, strlen ($p));
	return array ($b, $c, $a);
}

function user_login ($email, $password) {
	return $password == "1234" && $email == "@";
}
function user_signup ($email, $password) {
	$email = sqle ($email);
	$password = sqle ($password);
	$query = "INSERT INTO users (email, password, role) VALUES ('$email', '$password', 'suggester');";
	return sql_query ($query);
}
function user_signup_check ($email, $password) {
	return preg_match ("/^\S+@(\S+)\.(\S+)$/", $email) === 1;
}
function wtflist ($data, $total, $offset) {
	$list = array ();
	$list["data"] = $data;
	$list["offset"] = $offset;
	$list["total"] = $total;
	$list["count"] = count ($list["data"]);
	return $list;
}
function user_get_list ($offset, $count) {
	$fields = array ("uid", "email", "role");

	$query = "SELECT uid, email, role FROM users LIMIT $count OFFSET $offset;";
	$users = sql_query_array ($query);
	if ($users) {
		$users = map_fieldsm ($fields, $users);
	} else {
		$users = array ();
	}
	$total = sql_query_int ("SELECT COUNT (*) FROM users;", 0);

	return wtflist ($users, $total, $offset);
}
// user: $fields = array ("uid", "email", "password", "role");
function user_get_by_uid ($uid) {
	$fields = array ("uid", "email", "role");
	$uid = sqle ($uid);
	$query = "SELECT uid, email, role FROM users WHERE uid='$uid' LIMIT 1;";
	$user = sql_query_one ($query);
	if ($user) {
		$user = map_fields ($fields, $user);
	}
	return $user;
}
function user_get_loggedin () {
	return FALSE;
	//return array ("role" => "administrator");
}
function user_set_role ($uid, $role) {

}
// pubs: $fields = array ("pid", "title", "authors", "research_field", "publication_year", "venue", "papertype", "link", "keywords");
function publication_get_list ($offset, $count, $condition = "") {
	$fields = array ("pid", "title", "authors", "research_field", "publication_year", "venue", "papertype", "link", /*"keywords"*/);
	$query = "SELECT * FROM publications";
	if (0 < strlen ($condition)) {
		$query .= " WHERE $condition";
	}
	$query .= " LIMIT $count OFFSET $offset;";
	$pubs = sql_query_array ($query);
	if ($pubs) {
		$pubs = map_fieldsm ($fields, $pubs);
	} else {
		$pubs = array ();
	}
	$query = "SELECT COUNT (*) FROM publications";
	if (0 < strlen ($condition)) {
		$query .= " WHERE $condition";
	}
	$total = sql_query_int ($query.";", 0);

	return wtflist ($pubs, $total, $offset);
}
function publication_get_list_by_q ($offset, $count, $q) {
	$q = sqle ($q);
	$fields = array ("title", "authors", "venue", "papertype"/*, "keywords"*/);
	$mask = "'%$q%'";
	$parts = array ();
	for ($i = 0; $i < count ($fields); $i ++) {
		$parts[] = $fields[$i]." LIKE $mask";
	}
	if (is_numeric ($q)) {
		$parts[] = "publication_year = $q";
	}
	$condition = implode (" OR ", $parts);
	return publication_get_list ($offset, $count, $condition);
}
function publication_get_by_pid ($pid) {
	$fields = array ("pid", "title", "authors", "research_field", "publication_year", "venue", "papertype", "link"/*, "keywords"*/);
	$pid = sqle ($pid);
	$query = "SELECT * FROM publications WHERE pid='$pid' LIMIT 1;";
	$pub = sql_query_one ($query);
	if ($pub) {
		$pub = map_fields ($fields, $pub);
	}
	return $pub;
}

function publication_get_list ($offset, $count, $condition = "") {
	$fields = array ("pid", "title", "authors", "research_field", "publication_year", "venue", "papertype", "link", /*"keywords"*/);
	$query = "SELECT * FROM publications";
	if (0 < strlen ($condition)) {
		$query .= " WHERE $condition";
	}
	$query .= " LIMIT $count OFFSET $offset;";
	$pubs = sql_query_array ($query);
	if ($pubs) {
		$pubs = map_fieldsm ($fields, $pubs);
	} else {
		$pubs = array ();
	}
	$query = "SELECT COUNT (*) FROM publications";
	if (0 < strlen ($condition)) {
		$query .= " WHERE $condition";
	}
	$total = sql_query_int ($query.";", 0);

	return wtflist ($pubs, $total, $offset);
}

function map_fields ($fields, $row) {
	$res = array ();
	for ($j = 0; $j < count ($fields) && $j < count ($row); $j ++) {
		$res[$fields[$j]] = $row[$j];
	}
	return $res;
}
function map_fieldsm ($fields, $rows) {
	$res = array ();
	for ($i = 0; $i < count ($rows); $i ++) {
		$res[] = map_fields ($fields, $rows[$i]);
	}
	return $res;
}
?>
