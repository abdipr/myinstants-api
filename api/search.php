<?php
header("Content-Type: application/json");
require "helper.php";

$query = $_GET['q'] ?? "";
if (!$query) output_error("Query parameter 'q' is required, example: ?q=vine boom");

$html = fetch_html("https://www.myinstants.com/en/search/?name=" . urlencode($query));
if (!$html) output_error("Page not found");

output_json(parse_sounds($html));
?>
