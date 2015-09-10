<?php

require 'utils.php';
require 'sql.utils.php';

function html ($s) {
	return htmlspecialchars ($s);
}

if (isset ($_GET["q"])) {
	$get_q = strtolower ($_GET["q"]);

	if ($con = sql_begin ()) {
		
		$get_q = pg_escape_string ($con, $get_q);
		$query = "SELECT * FROM Publications WHERE LOWER (Title) LIKE '%$get_q%' OR LOWER (Author) LIKE '%$get_q%';";
		echo html ("Query: $query")."<br>";
		
		if ($rs = pg_query ($con, $query)) {
			echo pg_num_rows ($rs)." records<br>";
			$i = 1;
			$q_len = strlen ($get_q);
			while ($row = pg_fetch_row ($rs)) {
				for ($j = 1; $j < 3; $j ++) {
					$src = $row[$j];
					$low = strtolower ($src);
					$new = "";
					$p = 0;
					$op = $p;
					while (($p = strpos ($low, $get_q, $op)) !== false) {
						$new .= substr ($src, $op, $p - $op)."<mark>".html (substr ($src, $p, $q_len))."</mark>";
						$p += $q_len;
						$op = $p;
					}
					$new .= substr ($src, $op, strlen ($src) - $op);
					$row[$j] = $new;
				}
			
				echo "$i) $row[1] - $row[2] [$row[0]]<br>";
				$i ++;
			}
		} else {
			echo pg_last_error ()."<br>";
			echo html ("Cannot execute query: $query")."<br>";
		}

		sql_end ($con);
	} else {
		echo "Can not connect to database<br>";
	}
} else {
	echo "Missing `q` GET parameter<br>";
}

?>