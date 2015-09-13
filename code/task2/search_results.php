<?php

require 'utils.php';
require 'sql.utils.php';

// escapes special html characters
function html ($s) {
	return htmlspecialchars ($s);
}

// reads html template, parses it
function pageLoad () {
	global $page, $pageBegin, $pageEnd, $tableRow;
	$page = file_get_contents ("search_results.html");
	
	$p = strpos ($page, "[[");
	$pageBegin = substr ($page, 0, $p);
	$page = substr ($page, $p + 2);
	
	$p = strpos ($page, "]]");
	$pageEnd = substr ($page, $p + 2);
	$tableRow = substr ($page, 0, $p);
}

// prints page content before the table rows
function pageBegin () {
	global $pageBegin;
	echo $pageBegin;
}

// returns filled template of table row
function injectTableRow ($row) {
	global $tableRow;
	return str_replace ("{{title}}", $row[1],
		str_replace ("{{author}}", $row[2],
			str_replace ("{{link}}", $row[3], $tableRow)
		)
	);
}

// prints page content after the table rows
function endPage () {
	global $pageEnd;
	echo $pageEnd;
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
			for ($i = 0; $i < count ($qs); $i ++) {
				$qw = $qs[$i];
				$qws[] = $qw;
				$qs[$i] = "LOWER (Title) LIKE '%$qw%' OR LOWER (Author) LIKE '%$qw%'";
			}
			$query .= implode (" OR ", $qs);
		}
		$query .= ";";

		$rows = false;
		// try to perform query
		if ($rs = pg_query ($con, $query)) {
			$rows = array ();
			// parse rows one by one
			while ($row = pg_fetch_row ($rs)) {
				// highlight occurences
				if ($highlight && 0 < strlen ($q)) {
					// for each keyword
					for ($k = 0; $k < count ($qws); $k ++) {
						$qw = $qws[$k];
						$qw_len = strlen ($qw);
						for ($j = 1; $j < 3; $j ++) {
							$src = $row[$j];
							$low = strtolower ($src);
							$new = "";
							$p = 0;
							$op = $p;
							while (($p = strpos ($low, $qw, $op)) !== false) {
								$new .= substr ($src, $op, $p - $op)."<mark>".html (substr ($src, $p, $qw_len))."</mark>";
								$p += $qw_len;
								$op = $p;
							}
							$new .= substr ($src, $op, strlen ($src) - $op);
							$row[$j] = $new;
						}
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