<?php
header("Content-Type: application/json");
require "helper.php";

$id = $_GET['id'] ?? null;
if (!$id) output_error("Query parameter 'id' is required, example: ?id=akh-26815", "400");

$html = fetch_html("https://www.myinstants.com/en/instant/$id");
if (!$html) output_error("Page not found");

$web = "https://www.myinstants.com";
$title = $html->find("h1#instant-page-title", 0)->plaintext;
$soundUrl = $html->find("button#instant-page-button-element", 0)->getAttribute("data-url");
$description = $html->find("div#instant-page-description p", 0)->plaintext ?? "";

$tags = [];
foreach ($html->find("div#instant-page-tags a") as $tag) {
    $tags[] = $tag->plaintext;
}

$favorites = str_replace(" users", "", $html->find("div#instant-page-likes b", 0)->plaintext);
$authorElement = $html->find("div#instant-page-likes ~ div", 1);
$uploader = $authorElement->find("a", 0)->plaintext;
$uploaderUrl = $web . $authorElement->find("a", 0)->href;
$viewsText = trim(str_replace("views", "", $authorElement->plaintext));
$views = trim(str_replace("Uploaded by " . $uploader . " - ", "", $viewsText));

$mp3 = $web . $soundUrl;
$data = [
    "id" => $id,
    "url" => "https://www.myinstants.com/en/instant/$id",
    "title" => $title,
    "mp3" => $mp3,
    "description" => $description,
    "tags" => $tags,
    "favorites" => $favorites,
    "views" => $views,
    "uploader" => [
        "username" => $uploader,
        "url" => $uploaderUrl
    ]
];

list($with_duration, $_min, $_max) = get_duration_params();
if ($with_duration) {
    $durations = fetch_mp3_durations([$mp3]);
    $data["duration"] = $durations[$mp3] ?? null;
}

output_json($data);
?>
