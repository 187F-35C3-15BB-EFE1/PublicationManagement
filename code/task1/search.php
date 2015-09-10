<?php

require 'utils.php';
require 'sql.utils.php';

utils_output_is_text ();

if (isset ($_GET["q"])) {
	$get_q = strtolower ($_GET["q"]);

	if ($con = sql_begin ()) {
		
		$get_q = pg_escape_string ($con, $get_q);
		$query = "SELECT * FROM Publications WHERE LOWER (Title) LIKE '%$get_q%' OR LOWER (Author) LIKE '%$get_q%';";
		echo "Query: $query\r\n";
		
		if ($rs = pg_query ($con, $query)) {
			echo pg_num_rows ($rs)." records\r\n";
			$i = 1;
			while ($row = pg_fetch_row ($rs)) {
				echo "$i) $row[1] - $row[2] [$row[0]]\r\n";
				$i ++;
			}
		} else {
			echo pg_last_error ()."\r\n";
			echo "Cannot execute query: $query\r\n";
		}

		sql_end ($con);
	} else {
		echo "Can not connect to database\r\n";
	}
} else {
	echo "Missing `q` GET parameter\r\n";
}

?>