<?php
function utils_redirect ($to) {
	header ("Location: $to");
}
function utils_output_is_text () {
	header ('Content-Type: text/plain');
}
?>