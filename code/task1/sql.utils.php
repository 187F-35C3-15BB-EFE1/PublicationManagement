<?php

function sql_begin () {
	$host = "localhost";
	$user = "DMD";
	$pass = "dmd";
	$db = "DMD";

	$con = pg_connect ("host=$host dbname=$db user=$user password=$pass");
	
	return $con ? $con : false;
}

function sql_end ($con) {
	if ($con) {
		pg_close ($con);
	}
}

?>