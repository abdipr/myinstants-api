<?php
header("Content-Type: application/json");
require "helper.php";

$query = $_GET['q'] ?? "";
if (!$query) output_error("Query parameter 'q' is required, example: ?q=id");

$page = get_page_param();
list($with_duration, $min_duration, $max_duration) = get_duration_params();

$html = fetch_html(append_page_param("https://www.myinstants.com/en/best_of_all_time/" . urlencode($query), $page));
if (!$html) output_error("Page not found");

$sounds = parse_sounds($html);
$total_pages = parse_total_pages($html);
$sounds = apply_duration_filter($sounds, $with_duration, $min_duration, $max_duration);
output_json($sounds, "200", pagination_meta($page, $total_pages, count($sounds)));
?>
