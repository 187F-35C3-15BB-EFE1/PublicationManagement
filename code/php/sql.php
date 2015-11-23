<?php

$sql_con = FALSE;

function sql_query ($query) {
	global $sql_con;
	sql_require ();
	//echo "<HR>".htmle ($query)."<HR>";
	return pg_query ($sql_con, $query);
}

function sql_next ($rows) {
	return pg_fetch_row ($rows);
}

function sql_query_one ($query) {
	global $sql_con;
	sql_require ();
	$res = sql_query ($query);
	if ($res) {
		$res = sql_next ($res);
	}
	return $res;
}

function sql_query_array ($query) {
	$rows = FALSE;
	if ($res = sql_query ($query)) {
		$rows = array ();
		while ($row = sql_next ($res)) {
			$rows[] = $row;
		}
	}
	return $rows;
}

function sql_query_int ($query, $def) {
	$res = sql_query ($query);
	return $res ? sql_next ($res)[0] : $def;
}

function sqle ($s) {
	global $sql_con;
	sql_require ();
	return pg_escape_string ($sql_con, $s);
}
function sqlem ($ss) {
	$res = array ();
	foreach ($ss as $k => $v) {
		$res[$k] = sqle ($v);
	}
	return $res;
}

function sql_require () {
	global $sql_con;

	if (!$sql_con) {
		$host = "localhost";
		$user = "DMD";
		$pass = "dmd";
		$db = "DMD";

		$sql_con = pg_connect ("host=$host dbname=$db user=$user password=$pass");
	}
}

function sql_release () {
	global $sql_con;

	if ($sql_con) {
		pg_close ($sql_con);
		$sql_con = FALSE;
	}
}

?>
