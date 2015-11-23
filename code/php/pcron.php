<?php
require "utils.php";

go_offline (";", "application/javascript");
if (need_update_authors_view ()) {
	update_authors_view ();
}

?>