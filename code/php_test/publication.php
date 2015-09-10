<?php

require 'utils.php';
require 'sql.utils.php';

utils_output_is_text ();

if (isset ($_GET["pid"])) {
	$get_pid = $_GET["pid"];

	if ($con = sql_begin ()) {
		
		$get_pid = pg_escape_string ($con, $get_pid);
		$query = "SELECT * FROM Publications WHERE PID = '$get_pid';";
		echo "Query: $query\r\n";
		
		if ($rs = pg_query ($con, $query)) {
			if (0 < pg_num_rows ($rs)) {
				$row = pg_fetch_row ($rs);
				echo "[$row[0]]\r\n`$row[1]` by $row[2]\r\n";
			} else {
				echo "not found\r\n";
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
	echo "Missing `pid` GET parameter\r\n";
}

?>