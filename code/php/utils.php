<?php

ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);

ignore_user_abort (TRUE);
set_time_limit (30);

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
function param_get_array ($name, $default) {
	return isset ($_GET[$name]) ? $_GET[$name] : $default;
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

$user_roles = array ("null", "suggester", "moderator", "administrator");
function user_login ($email, $password) {
	$email = sqle ($email);
	$password = sqle ($password);
	$query = "SELECT uid FROM \"user\" WHERE email='$email' AND password='$password' LIMIT 1;";
	$uid = sql_query_one ($query);
	if ($uid) {
		setcookie ('user', $uid[0], 0, '/');
	}
	return $uid;
}
function user_logout () {
	setcookie ('user', '', 1, '/');
}
function user_signup ($email, $password) {
	$email = sqle ($email);
	$password = sqle ($password);
	$query = "INSERT INTO \"user\" (email, password, role) VALUES ('$email', '$password', ".user_role_to_num ("suggester").");";
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

	$query = "SELECT uid, email, role FROM \"user\" ORDER BY uid LIMIT $count OFFSET $offset;";
	$users = sql_query_array ($query);
	if ($users) {
		$users = map_fieldsm ($fields, $users);
		$users = user_num_to_rolem ($users);
	} else {
		$users = array ();
	}
	$total = sql_query_int ("SELECT COUNT (*) FROM \"user\";", 0);

	return wtflist ($users, $total, $offset);
}
// user: $fields = array ("uid", "email", "password", "role");
function user_get_by_uid ($uid) {
	$fields = array ("uid", "email", "role");
	$uid = sqle ($uid);
	$query = "SELECT uid, email, role FROM \"user\" WHERE uid='$uid' LIMIT 1;";
	$user = sql_query_one ($query);
	if ($user) {
		$user = map_fields ($fields, $user);
		$user["role"] = user_num_to_role ($user["role"]);
	}
	return $user;
}
function user_get_loggedin () {
	$user = NULL;
	if (isset ($_COOKIE['user'])) {
		$user = user_get_by_uid ($_COOKIE['user']);
	}
	if (!$user) {
		$user = array ("role" => "null");
	}
	return $user;
}
function user_set_role ($uid, $role) {
	$role = user_role_to_num ($role);
	$uid = sqle ($uid);
	$query = "UPDATE \"user\" SET role=$role WHERE uid='$uid';";
	return sql_query ($query);
}
function user_num_to_role ($num) {
	global $user_roles;
	return $user_roles[$num];
}
function user_num_to_rolem ($users) {
	for ($i = 0; $i < count ($users); $i ++) {
		$users[$i]["role"] = user_num_to_role ($users[$i]["role"]);
	}
	return $users;
}
function user_role_to_num ($role) {
	global $user_roles;
	return array_search ($role, $user_roles);
}
function user_check_role_includes ($role, $target) {
	return user_role_to_num ($target) <= user_role_to_num ($role);
}

// pubs: $fields = array ("pid", "title", "authors", "research_field", "publication_year", "venue", "papertype", "link", "keywords");
function publication_get_list ($offset, $count, $order, $condition = "") {
	$fields = array ("pid", "title", "authors", "research_field", "publication_year", "venue", "papertype", "link", "keywords");
	$query = " FROM publication NATURAL JOIN authors";
	$count ++;
	$query .= " WHERE $condition";
	$core = $query;
	if ($order == "year") {
		$order = "publication_year DESC,";
	} else if ($order == "field") {
		$order = "research_field,";
	} else {
		$order = "";
	}
	$order .= "pid";
	$query = "SELECT pid, title, authors, research_field, publication_year, venue, link, keywords $core";
	$query .= " ORDER BY $order LIMIT $count OFFSET $offset;";
	$pubs = sql_query_array ($query);
	$count --;
	$total = 0;
	if ($pubs) {
		$total += $offset * $count;
		$total += count ($pubs);
		if (11 <= count ($pubs)) {
			array_pop ($pubs);
		}
		$pubs = map_fieldsm ($fields, $pubs);
	} else {
		$pubs = array ();
	}
	$query = "SELECT COUNT (*)$core;";
	//$total = sql_query_int ($query, 0);

	return wtflist ($pubs, $total, $offset);
}
function publication_get_list_by_q ($offset, $count, $order, $q) {
	$q = sqle ($q);
	$parts = array ();
	if (is_numeric ($q)) {
		$parts[] = "publication_year = '$q'";
	}
	$parts[] = "searchable @@ to_tsquery ('".implode (" & ", explode (" ", $q))."')";
	$parts[] = "authors ILIKE '%$q%'";
	$condition = implode (" OR ", $parts);
	return publication_get_list ($offset, $count, $order, $condition);
}
function publication_get_related_list ($offset, $count, $order, $related, $pid) {
	$pub = publication_get_by_pid ($pid);
	if ($pub) {
		if ($related == "year") {
			$condition = "publication_year = ".$pub["publication_year"];
		} else if ($related == "authors") {
			$parts = array ();
			$authors = explode (",", $pub["authors"]);
			for ($i = 0; $i < count ($authors); $i ++) {
				$parts[] = "authors ILIKE '%".sqle ($authors[$i])."%'";
			}
			$condition = implode (" OR ", $parts);
		} else {
			$condition = "0 <> 0";
		}
		return publication_get_list ($offset, $count, $order, $condition);
	} else {
		return array ();
	}
}
function publication_get_by_pid ($pid) {
	$pid = sqle ($pid);
	$pubs = publication_get_list (0, 1, "", "pid=$pid");
	$pub = $pubs["data"];
	if (count ($pub) <= 0) {
		$pub = FALSE;
	}
	return $pub[0];
}
function publication_add ($pub) {
	$columns = array ();
	$values = array ();
	foreach ($pub as $k => $v) {
		if ($k == "authors") {
		} else {
			$columns[] = sqle ($k);
			$values[] = "'".sqle ($v)."'";
		}
	}
	$query = "INSERT INTO publication (".implode (",", $columns).") VALUES (".implode (",", $values).");";
	$result = sql_query ($query);
	$last_pid = sql_query_one ("SELECT lastval ();");
	$aids = author_get_aids (explode (',', $v));
	written_by_add ($last_pid, $aids);
	return $result;
}
function publication_delete_by_pid ($pid) {
	written_by_delete_by_pid ($pid);
	$pid = sqle ($pid);
	$query = "DELETE FROM publication WHERE pid=$pid;";
	return sql_query ($query);
}
function publication_change_using_suggestion ($sug) {
	$pid = sqle ($sug["to_pid"]);
	$changes = json_decode ($sug["changes"]);
	$assignments = array ();
	foreach ($changes as $k => $v) {
		if ($k == "authors") {
			written_by_delete_by_pid ($sug["to_pid"]);
			$aids = author_get_aids (explode (',', $v));
			written_by_add ($sug["to_pid"], $aids);
		} else {
			$assignments[] = sqle ($k)."='".sqle ($v)."'";
		}
	}
	$query = "UPDATE publication SET ".implode (",", $assignments)." WHERE pid=$pid;";
	return sql_query ($query);
}

function written_by_delete_by_pid ($pid) {
	$pid = sqle ($pid);
	$query = "DELETE FROM written_by WHERE pid=$pid;";
	return sql_query ($query);
}
function written_by_add ($pid, $aids) {
	$values = array ();
	$pid = sqle ($pid);
	for ($i = 0; $i < count ($names); $i ++) {
		$values[] = "($pid,".sqle ($names[$i]).")";
	}
	$query = "INSERT INTO written_by (pid,aid) VALUES ".implode (",", $values).";";
	return sql_query ($query);
}

function author_addm ($names) {
	$values = array ();
	for ($i = 0; $i < count ($names); $i ++) {
		$values[] = "('".sqle ($names[$i])."')";
	}
	$query = "INSERT INTO author (author_name) VALUES ".implode (",", $values).";";
	return sql_query ($query);
}
function author_get_aids ($names) {
	$aids = author_get_aids_actual ($names);
	if (count ($aids) != count ($names)) {
		$not_found = array ();
		for ($i = 0; $i < count ($names); $i ++) {
			$found = FALSE;
			for ($j = 0; $j < count ($aids) && !$found; $j ++) {
				if ($aids["author_name"] == $names[$i]) {
					$found = TRUE;
				}
			}
			if (!$found) {
				$not_found[] = $names[$i];
			}
		}
		author_addm ($not_found);
		$aids = author_get_aids_actual ($names);
	}
}
function author_get_aids_actual ($names) {
	$fields = array ("aid", "author_name");
	$query = "SELECT * FROM author WHERE ";
	$conditions = array ();
	for ($i = 0; $i < count ($names); $i ++) {
		$conditions[] = "author_name = '".$names[$i]."'";
	}
	$query .= implode (" OR ", $conditions);
	$aids = sql_query_array ($query);
	if ($aids) {
		$aids = map_fieldsm ($fields, $aids);
		for ($i = 0; $i < count ($aids); $i ++) {
			$type = suggestion_get_type ($aids[$i]);
			$aids[$i]["type"] = $type;
			if ($type == "new") {
				$aids[$i]["title"] = json_decode ($aids[$i]["changes"])->title;
			}
		}
	} else {
		$aids = array ();
	}

	return $aids;
}

function suggestion_get_list ($offset, $count) {
	// sid title email
	$fields = array ("sid", "from_uid", "to_pid", "changes", "title", "email");
	$query = "SELECT s.sid AS sid, s.from_uid AS from_uid, s.to_pid AS to_pid, s.changes AS changes, p.title AS title, u.email AS email FROM suggestion s LEFT OUTER JOIN \"user\" u ON s.from_uid = u.uid LEFT OUTER JOIN publication p ON s.to_pid = p.pid ORDER BY sid LIMIT $count OFFSET $offset;";
	$sugs = sql_query_array ($query);
	if ($sugs) {
		$sugs = map_fieldsm ($fields, $sugs);
		for ($i = 0; $i < count ($sugs); $i ++) {
			$type = suggestion_get_type ($sugs[$i]);
			$sugs[$i]["type"] = $type;
			if ($type == "new") {
				$sugs[$i]["title"] = json_decode ($sugs[$i]["changes"])->title;
			}
		}
	} else {
		$sugs = array ();
	}
	$total = sql_query_int ("SELECT COUNT (*) FROM suggestion;", 0);

	return wtflist ($sugs, $total, $offset);
}
function suggestion_get_by_sid ($sid) {
	$fields = array ("sid", "from_uid", "to_pid", "changes");
	$sid = sqle ($sid);
	$query = "SELECT * FROM suggestion WHERE sid='$sid' LIMIT 1;";
	$sug = sql_query_one ($query);
	if ($sug) {
		$sug = map_fields ($fields, $sug);
	}
	return $sug;
}
function suggestion_add_new ($new_pub, $uid) {
	$uid = sqle ($uid);
	$changes = json_encode ($new_pub);
	$changes = sqle ($changes);
	$query = "INSERT INTO suggestion (from_uid, to_pid, changes) VALUES ($uid, NULL, '$changes');";
	return sql_query ($query);
}
function suggestion_add_delete ($pid, $uid) {
	$uid = sqle ($uid);
	$pid = sqle ($pid);
	$changes = "delete";
	$query = "INSERT INTO suggestion (from_uid, to_pid, changes) VALUES ($uid, $pid, '$changes');";
	return sql_query ($query);
}
function suggestion_add_change ($pid, $new_pub, $columns, $uid) {
	$uid = sqle ($uid);
	$pid = sqle ($pid);
	$changes = array ();
	for ($i = 0; $i < count ($columns); $i ++) {
		$column = $columns[$i];
		$changes[$column] = $new_pub[$column];
	}
	$changes = json_encode ($changes);
	$changes = sqle ($changes);
	$query = "INSERT INTO suggestion (from_uid, to_pid, changes) VALUES ($uid, $pid, '$changes');";
	return sql_query ($query);
}
function suggestion_get_type ($sug) {
	if ($sug["to_pid"] == NULL) {
		return "new";
	} else if ($sug["changes"] == "delete") {
		return "delete";
	} else {
		return "change";
	}
}
function suggestion_apply ($sug) {
	$type = suggestion_get_type ($sug);
	$ret = suggestion_delete ($sug);
	if ($ret) {
		if ($type == "new") {
			$pub = json_decode ($sug["changes"]);
			$ret = $ret && publication_add ($pub);
		} else if ($type == "delete") {
			$ret = $ret && publication_delete_by_pid ($sug["to_pid"]);
		} else if ($type == "change") {
			$ret = $ret && publication_change_using_suggestion ($sug);
		}
	}
	return $ret;
}
function suggestion_reject ($sug) {
	return suggestion_delete ($sug);
}
function suggestion_delete ($sug) {
	$sid = sqle ($sug["sid"]);
	$query = "DELETE FROM suggestion WHERE sid=$sid;";
	return sql_query ($query);
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

function keep_or_omit ($page, $ml, $mr, $keep) {
	$parts = page_split ($page, $ml, $mr);
	$page = "";
	$page .= $parts[0];
	if ($keep) {
		$page .= $parts[1];
	}
	$page .= $parts[2];
	return $page;
}

function repeat_fill ($page, $ml, $mr, $m, $vals) {
	$parts = page_split ($page, $ml, $mr);
	$page = "";
	$page .= $parts[0];
	for ($i = 0; $i < count ($vals); $i ++) {
		$val = $vals[$i];
		$page .= page_replace ($parts[1], $m, htmle ($val));
	}
	$page .= $parts[2];
	return $page;
}
?>
