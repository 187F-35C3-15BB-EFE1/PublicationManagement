<?php

require 'utils.php';
require 'sql.utils.php';

// reads html template, parses it
function pageLoad () {
	global $pageBegin, $pageEnd, $tableRow;
	$page = page_load ("search_results.html");

	$tableRow = page_split ($page, "[[", "]]");
	$pageBegin = $tableRow[0];
	$pageEnd = $tableRow[2];
	$tableRow = $tableRow[1];
}

// prints page content before the table rows
function pageBegin () {
	global $pageBegin;
	echo $pageBegin;
}

// returns filled template of table row
function injectTableRow ($row) {
	global $tableRow;
	$injected = $tableRow;
	$injected = page_replace ($injected, "{{title}}",  ($row[1]));
	$injected = page_replace ($injected, "{{author}}",  ($row[2]));
	$injected = page_replace ($injected, "{{link}}",  ($row[3]));
	return $injected;
}

// prints page content after the table rows
function endPage () {
	global $pageEnd;
	echo $pageEnd;
}

function highlight ($src, $key) {
	$key_len = strlen ($key);
	$low = strtolower ($src);
	$new = "";
	$p = 0;
	$op = $p;
	while (($p = strpos ($low, $key, $op)) !== false) {
		$new .= substr ($src, $op, $p - $op)."<mark>".html (substr ($src, $p, $key_len))."</mark>";
		$p += $key_len;
		$op = $p;
	}
	$new .= substr ($src, $op, strlen ($src) - $op);
	return $new;
}

// makes the query
// returns array of rows
function sql_query ($q) {
	// specifies if query string occurences should be highlighted
	$highlight = true;

	// try to open database connection
	if ($con = sql_begin ()) {

		// escape string to prevent injections
		$q = pg_escape_string ($con, $q);
		// build sql query
		// uses LOWER (...) and search query string in lowercase in order to perform case independent search
		// uses LIKE '%q%' to determine if 'q' is in column
		$query = "SELECT * FROM Publications";
		$qws = array ();
		// if search query string is empty search anything
		// otherwise add conditions
		if (0 < strlen ($q)) {
			$query .= " WHERE ";
			// split search query string to keywords
			$qs = explode (" ", $q);
			// remove duplicates
			$qs = array_unique ($qs);
			$qwxas = array ();
			$qwxts = array ();
			for ($i = 0; $i < count ($qs); $i ++) {
				$qw = $qs[$i];
				$qwx = explode (":", $qw);
				if (count ($qwx) != 2) {
					if (strpos ($qw, ":") === false) {
						$qws[] = $qw;
					}
					// else FALSE, ignore keyword of undefined format
				} else {
					$qw = $qwx[1];
					switch ($qwx[0]) {
					case "author":
						$qwxas[] = $qwx[1];
						break;
					case "title":
						$qwxts[] = $qwx[1];
						break;
					// else FALSE, ignore keyword of undefined format
					}
				}
			}
			// $qs[$i] = "LOWER (Author) LIKE '%$qw%'";
			// $qs[$i] = "LOWER (Title) LIKE '%$qw%' OR LOWER (Author) LIKE '%$qw%'";
			$conditions = array ();
			for ($i = 0; $i < count ($qws); $i ++) {
				$qw = $qws[$i];
				$conditions[] = "(LOWER (Title) LIKE '%$qw%' OR LOWER (Author) LIKE '%$qw%')";
			}
			for ($i = 0; $i < count ($qwxas); $i ++) {
				$qw = $qwxas[$i];
				$conditions[] = "LOWER (Author) LIKE '%$qw%'";
			}
			for ($i = 0; $i < count ($qwxts); $i ++) {
				$qw = $qwxts[$i];
				$conditions[] = "LOWER (Title) LIKE '%$qw%'";
			}
			$query .= implode (" AND ", $conditions);
		}
		$query .= ";";
		echo "<hr>$query<hr>";

		$rows = false;
		// try to perform query
		if ($rs = pg_query ($con, $query)) {
			$rows = array ();
			// parse rows one by one
			while ($row = pg_fetch_row ($rs)) {
				// highlight occurences
				if ($highlight && 0 < strlen ($q)) {
					// for each single keyword
					for ($k = 0; $k < count ($qws); $k ++) {
						for ($j = 1; $j < 3; $j ++) {
							$row[$j] = highlight ($row[$j], $qws[$k]);
						}
					}
					// for each title keyword
					for ($k = 0; $k < count ($qwxts); $k ++) {
						$row[1] = highlight ($row[1], $qwxts[$k]);
					}
					// for each author keyword
					for ($k = 0; $k < count ($qwxas); $k ++) {
						$row[2] = highlight ($row[2], $qwxas[$k]);
					}
				}
				// add `row` to `rows` array
				$rows[] = $row;
			}
		} else {
			echo pg_last_error ()."<br>";
			echo html ("Cannot execute query: $query")."<br>";
		}

		sql_end ($con);
	} else {
		echo "Can not connect to database<br>";
	}
	return $rows;
}

// if search query string is not set, make it empty
if (!isset ($_GET["q"])) {
	$_GET["q"] = "";
}
// make search query string lowercase
$get_q = strtolower ($_GET["q"]);

// remove whitespace duplicates
$get_q = preg_replace ("/\s+/", " ", $get_q);
// remove whitespace at the begin and the end
$get_q = trim ($get_q);

// do the query
$rows = sql_query ($get_q);

pageLoad ();
pageBegin ();
// insert table rows one by one
for ($i = 0; $i < count ($rows); $i ++) {
	echo injectTableRow ($rows[$i]);
}
pageEnd ();

?>