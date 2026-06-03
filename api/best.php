<?php
header("Content-Type: application/json");
require "helper.php";

$query = $_GET['q'] ?? "";
if (!$query) output_error("Query parameter 'q' is required, example: ?q=id");

$html = fetch_html("https://www.myinstants.com/en/best_of_all_time/" . urlencode($query));
if (!$html) output_error("Page not found");

output_json(parse_sounds($html));
?>
