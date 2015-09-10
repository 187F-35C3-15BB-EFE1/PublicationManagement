<?php

require 'utils.php';
require 'sql.utils.php';

utils_output_is_text ();

if (isset ($_GET["title"]) && isset ($_GET["author"])) {
	$get_title = $_GET["title"];
	$get_author = $_GET["author"];

	if ($con = sql_begin ()) {
		
		$get_title = pg_escape_string ($con, $get_title);
		$get_author = pg_escape_string ($con, $get_author);
		$query = "SELECT * FROM Publications WHERE LOWER (Title) = LOWER ('$get_title') AND LOWER (Author) = LOWER ('$get_author') LIMIT 1;";
		echo "Query: $query\r\n";
		
		if ($rs = pg_query ($con, $query)) {
			if (0 < pg_num_rows ($rs)) {
				echo "already exists\r\n";
			} else {
				$query = "INSERT INTO Publications (Title, Author) VALUES ('$get_title', '$get_author');"
					." ".$query;
				echo "Query: $query\r\n";
				
				if ($rs = pg_query ($con, $query)) {
					$row = pg_fetch_row ($rs);
					echo "inserted as $row[0]";
				} else {
					echo pg_last_error ()."\r\n";
					echo "Cannot execute query: $query\r\n";
				}
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
	if (!isset ($_GET["title"])) {
		echo "Missing `title` GET parameter\r\n";
	} else {
		echo "Missing `author` GET parameter\r\n";
	}
}

?>