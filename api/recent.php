<?php
header("Content-Type: application/json");
require "helper.php";

$html = fetch_html("https://www.myinstants.com/en/recent");
if (!$html) output_error("Page not found");

output_json(parse_sounds($html));
?>
