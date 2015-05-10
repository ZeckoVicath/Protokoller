<?php

include_once("config.php");

date_default_timezone_set($TIMEZONE);

// TODO refine this to provide a gist generation API to the backends
function getAgenda ($protocol) {
	$i = 1; // the number of the TOP to search for
	$items = array ();
	foreach (explode("\n",$protocol) as $line) {
		if (strpos($line,"$i") !== false && preg_match("/<h3>top|<h3>item/i", $line)) {
			$line = strip_tags($line);
			$line = preg_replace ("/(top|item)(?U).*$i/i", "", $line, 1);
			$line = trim ($line, ": \t\n\r\0\x0B");
			$i = $i + 1;
			if (!empty($line)) {
				array_push ($items, $line);
			}
		}
	}
	return $items;
}

function startsWith($haystack, $needle) {
	return !strncmp($haystack, $needle, strlen($needle));
}

function endsWith($haystack, $needle) {
	$length = strlen($needle);
	if ($length == 0) {
		return true;
	}
	return (substr($haystack, -$length) === $needle);
}

?>
