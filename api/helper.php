<?php
require_once "simple_html_dom.php";

function fetch_html($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    $htmlString = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    if ($httpCode >= 400 || !$htmlString) {
        output_error("Fetch failed: HTTP $httpCode, cURL Error: $error");
    }
    return str_get_html($htmlString);
}

function parse_sounds($html) {
    $sounds = [];
    $web = "https://www.myinstants.com";
    foreach ($html->find("div.instant") as $instant) {
        $link = $instant->find("a.instant-link", 0);
        if (!$link) continue;
        
        $title = $link->plaintext;
        $url = $web . $link->href;
        $id = trim(str_replace("/en/instant/", "", $link->href), "/");
        
        $btn = $instant->find("button.small-button", 0);
        $soundmp3 = $btn ? $btn->onclick : "";
        if (preg_match("/play\\('(.*?)'/", $soundmp3, $matches)) {
            $sounds[] = [
                "id" => $id,
                "title" => $title,
                "url" => $url,
                "mp3" => $web . $matches[1]
            ];
        }
    }
    return $sounds;
}

function output_error($msg, $status = "404") {
    http_response_code((int)$status);
    header("Access-Control-Allow-Origin: *");
    echo json_encode(["status" => $status, "author" => "abdipr", "message" => $msg], JSON_PRETTY_PRINT);
    exit;
}

function output_json($data, $status = "200") {
    http_response_code((int)$status);
    header("Access-Control-Allow-Origin: *");
    header("Cache-Control: s-maxage=3600, stale-while-revalidate");
    echo json_encode(["status" => $status, "author" => "abdipr", "data" => $data], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}
