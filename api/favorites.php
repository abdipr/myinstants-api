<?php
header("Content-Type: application/json");
require "helper.php";

$username = $_GET['username'] ?? null;
if (!$username) output_error("Query parameter 'username' is required, example: ?username=hellmouz", "400");

$html = fetch_html("https://www.myinstants.com/en/profile/" . urlencode($username));
if (!$html) output_error("Page not found");

output_json(parse_sounds($html));
?>
