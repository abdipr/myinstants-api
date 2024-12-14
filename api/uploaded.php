<?php
header("Content-Type: application/json");
require "simple_html_dom.php";

$username = isset($_GET['username']) ? $_GET['username'] : null;

if (!$username) {
    echo json_encode(["status" => "400", "author" => "abdiputranar", "message" => "Query parameter 'username' is required, example: ?username=hellmouz"], JSON_PRETTY_PRINT);
    exit;
}

$url = "https://www.myinstants.com/en/profile/$username/uploaded/";

$html = file_get_html($url);
$web = "https://www.myinstants.com";

if (!$html) {
    echo json_encode(["status" => "404", "author" => "abdiputranar", "message" => "Page not found"], JSON_PRETTY_PRINT);
    exit;
}
    $sounds = [];

    foreach ($html->find("div.instant") as $instant) {
        $title = $instant->find("a.instant-link", 0)->plaintext;
        $url = $web . $instant->find("a.instant-link", 0)->href;
        $id = $instant->find("a.instant-link", 0)->href;
        $soundmp3 = $web . $instant->find("button.small-button", 0)->onclick;
        
        if (preg_match("/play\\('(.*?)'/", $soundmp3, $matches)) {
            $mp3Url = $web . $matches[1];
            $sounds[] = [
                "id" => $id,
                "title" => $title,
                "url" => $url,
                "mp3" => $mp3Url
            ];
        }
    }

    $result = str_replace("/\",", "\",", str_replace("\"/en/instant/", "\"", str_replace("\\", "", json_encode([
        "status" => "200",
        "author" => "abdiputranar",
        "data" => $sounds
    ], JSON_PRETTY_PRINT))));

echo $result;
?>
