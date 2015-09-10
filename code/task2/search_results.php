<?php

require 'utils.php';
require 'sql.utils.php';

function html ($s) {
	return htmlspecialchars ($s);
}
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
function pageBegin () {
	global $pageBegin;
	echo $pageBegin;
}
function injectTableRow ($row) {
	global $tableRow;
	return str_replace ("{{title}}", $row[1], str_replace ("{{author}}", $row[2], $tableRow));
}
function endPage () {
	global $pageEnd;
	echo $pageEnd;
}

function sql_query ($q) {
	$highlight = true;
	if ($con = sql_begin ()) {
		
		$q = pg_escape_string ($con, $q);
		$query = "SELECT * FROM Publications WHERE LOWER (Title) LIKE '%$q%' OR LOWER (Author) LIKE '%$q%';";
		
		$rows = false;
		if ($rs = pg_query ($con, $query)) {
			$rows = array ();
			$q_len = strlen ($q);
			while ($row = pg_fetch_row ($rs)) {
				if ($highlight) {
					for ($j = 1; $j < 3; $j ++) {
						$src = $row[$j];
						$low = strtolower ($src);
						$new = "";
						$p = 0;
						$op = $p;
						while (($p = strpos ($low, $q, $op)) !== false) {
							$new .= substr ($src, $op, $p - $op)."<mark>".html (substr ($src, $p, $q_len))."</mark>";
							$p += $q_len;
							$op = $p;
						}
						$new .= substr ($src, $op, strlen ($src) - $op);
						$row[$j] = $new;
					}
				}
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

if (!isset ($_GET["q"])) {
	$_GET["q"] = "";
}
$get_q = strtolower ($_GET["q"]);
$rows = sql_query ($get_q);

pageLoad ();
pageBegin ();
for ($i = 0; $i < count ($rows); $i ++) {
	echo injectTableRow ($rows[$i]);
}
pageEnd ();

?>