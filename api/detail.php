<?php
header("Content-Type: application/json");
require "simple_html_dom.php";

$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    echo json_encode(["status" => "400", "author" => "abdiputranar", "message" => "Query parameter 'id' is required, example: ?id=akh-26815"], JSON_PRETTY_PRINT);
    exit;
}

$url = "https://www.myinstants.com/en/instant/$id";
$html = file_get_html($url);

if (!$html) {
    echo json_encode(["status" => "404", "author" => "abdiputranar", "message" => "Page not found"], JSON_PRETTY_PRINT);
    exit;
}

$web = "https://www.myinstants.com";
$title = $html->find("h1#instant-page-title", 0)->plaintext;
$soundUrl = $html->find("button#instant-page-button-element", 0)->getAttribute("data-url");
$mp3Url = $web . $soundUrl;
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

echo str_replace("\\", "", json_encode([
    "status" => "200",
    "author" => "abdiputranar",
    "data" => [
        "id" => $id,
        "url" => $url,
        "title" => $title,
        "mp3" => $mp3Url,
        "description" => $description,
        "tags" => $tags,
        "favorites" => $favorites,
        "views" => $views,
        "uploader" => [
            "text" => $uploader,
            "url" => $uploaderUrl
        ]
    ]
], JSON_PRETTY_PRINT));
?>
