<?php
require_once "simple_html_dom.php";

// Never leak warnings/deprecations into JSON responses.
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', '0');

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

function output_json($data, $status = "200", $meta = []) {
    http_response_code((int)$status);
    header("Access-Control-Allow-Origin: *");
    header("Cache-Control: s-maxage=3600, stale-while-revalidate");
    $response = array_merge(["status" => $status, "author" => "abdipr"], $meta, ["data" => $data]);
    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

// --- Pagination helpers (mirrors myinstants.com ?page=N) ---

function get_page_param() {
    if (!isset($_GET['page']) || $_GET['page'] === "") return 1;
    $raw = $_GET['page'];
    if (is_array($raw) || !preg_match('/^\d+$/', (string)$raw)) {
        output_error("Query parameter 'page' must be a positive integer, example: ?page=4", "400");
    }
    $page = (int)$raw;
    if ($page < 1) output_error("Query parameter 'page' must be >= 1, example: ?page=4", "400");
    return $page;
}

function append_page_param($url, $page) {
    if ($page <= 1) return $url;
    $sep = (strpos($url, '?') === false) ? '?' : '&';
    return $url . $sep . 'page=' . $page;
}

function parse_total_pages($html) {
    $texts = [];
    $titleEl = $html->find('title', 0);
    if ($titleEl) $texts[] = $titleEl->plaintext;
    foreach ($html->find('meta[name=description]') as $meta) {
        $c = $meta->getAttribute('content');
        if ($c) $texts[] = $c;
    }
    foreach ($texts as $t) {
        if (preg_match('/Page\s+\d+\s+of\s+(\d+)/i', $t, $m)) {
            return (int)$m[1];
        }
    }
    return null;
}

function pagination_meta($page, $total_pages, $count) {
    $meta = ["page" => $page, "count" => $count];
    $meta["total_pages"] = $total_pages;
    $meta["has_next"] = ($total_pages !== null) ? ($page < $total_pages) : null;
    return $meta;
}

// --- Duration helpers (myinstants.com exposes no durations, so probe MP3s) ---

function get_duration_params() {
    $with_duration = isset($_GET['with_duration']) && in_array(strtolower((string)$_GET['with_duration']), ["1", "true", "yes"], true);
    $min = null;
    $max = null;
    if (isset($_GET['min_duration']) && $_GET['min_duration'] !== "") {
        if (!is_numeric($_GET['min_duration']) || (float)$_GET['min_duration'] < 0) {
            output_error("Query parameter 'min_duration' must be a number >= 0 (seconds), example: ?min_duration=2", "400");
        }
        $min = (float)$_GET['min_duration'];
        $with_duration = true;
    }
    if (isset($_GET['max_duration']) && $_GET['max_duration'] !== "") {
        if (!is_numeric($_GET['max_duration']) || (float)$_GET['max_duration'] < 0) {
            output_error("Query parameter 'max_duration' must be a number >= 0 (seconds), example: ?max_duration=6", "400");
        }
        $max = (float)$_GET['max_duration'];
        $with_duration = true;
    }
    if ($min !== null && $max !== null && $min > $max) {
        output_error("Query parameter 'min_duration' must be <= 'max_duration'", "400");
    }
    return [$with_duration, $min, $max];
}

function mp3_duration_from_data($data) {
    $len = strlen($data);
    if ($len < 5) return null;
    $pos = 0;
    // Skip ID3v2 header
    if ($len > 10 && substr($data, 0, 3) === "ID3") {
        $size = ((ord($data[6]) & 0x7F) << 21) | ((ord($data[7]) & 0x7F) << 14) | ((ord($data[8]) & 0x7F) << 7) | (ord($data[9]) & 0x7F);
        $pos = 10 + $size;
    }
    $bitrates_mpeg1 = [0, 32, 40, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320, 0];
    $bitrates_mpeg2 = [0, 8, 16, 24, 32, 40, 48, 56, 64, 80, 96, 112, 128, 144, 160, 0];
    $samplerates = [
        [44100, 48000, 32000, 0],
        [22050, 24000, 16000, 0],
        [11025, 12000, 8000, 0]
    ];
    $duration = 0.0;
    $frames = 0;
    $guard = 0;
    while ($pos + 4 <= $len && $guard < 10000) {
        $guard++;
        if (ord($data[$pos]) !== 0xFF || (ord($data[$pos + 1]) & 0xE0) !== 0xE0) {
            $pos++;
            continue;
        }
        $b1 = ord($data[$pos + 1]);
        $b2 = ord($data[$pos + 2]);
        $b3 = ord($data[$pos + 3]);
        $verBits = ($b1 >> 3) & 0x03;
        $layerBits = ($b1 >> 1) & 0x03;
        $bitrateIdx = ($b2 >> 4) & 0x0F;
        $sampleIdx = ($b2 >> 2) & 0x03;
        $pad = ($b2 >> 1) & 0x01;
        // Only MPEG Layer III with valid indices
        if ($layerBits !== 1 || $bitrateIdx === 0 || $bitrateIdx === 15 || $sampleIdx === 3 || $verBits === 1) {
            $pos++;
            continue;
        }
        if ($verBits === 3) {
            $bitrate = $bitrates_mpeg1[$bitrateIdx] * 1000;
            $verIdx = 0;
        } elseif ($verBits === 2) {
            $bitrate = $bitrates_mpeg2[$bitrateIdx] * 1000;
            $verIdx = 1;
        } else {
            $bitrate = $bitrates_mpeg2[$bitrateIdx] * 1000;
            $verIdx = 2;
        }
        if ($bitrate <= 0) { $pos++; continue; }
        $samplerate = $samplerates[$verIdx][$sampleIdx];
        if ($samplerate <= 0) { $pos++; continue; }
        if ($verBits === 3) {
            $frameLen = (int)(144 * $bitrate / $samplerate + $pad);
            $samples = 1152;
        } else {
            $frameLen = (int)(72 * $bitrate / $samplerate + $pad);
            $samples = 576;
        }
        if ($frameLen < 1 || $pos + $frameLen > $len + 1) {
            // Last truncated frame: count it and stop
            if ($pos + $frameLen > $len && $frames > 0) {
                $duration += $samples / $samplerate;
                $frames++;
            } else {
                $pos++;
                continue;
            }
            break;
        }
        $duration += $samples / $samplerate;
        $frames++;
        $pos += $frameLen;
    }
    if ($frames === 0) return null;
    return $duration;
}

function fetch_mp3_durations($urls) {
    $map = [];
    $urls = array_values(array_unique($urls));
    if (empty($urls)) return $map;
    $mh = curl_multi_init();
    $handles = [];
    foreach ($urls as $i => $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_multi_add_handle($mh, $ch);
        $handles[$i] = $ch;
    }
    $running = null;
    do {
        $mrc = curl_multi_exec($mh, $running);
        if ($running) curl_multi_select($mh, 1.0);
    } while ($running && $mrc == CURLM_OK);
    foreach ($urls as $i => $url) {
        $ch = $handles[$i];
        $data = curl_multi_getcontent($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $duration = null;
        if ($code >= 200 && $code < 300 && $data) {
            $d = mp3_duration_from_data($data);
            if ($d !== null) $duration = round($d, 2);
        }
        $map[$url] = $duration;
        curl_multi_remove_handle($mh, $ch);
        // Note: curl_close() / curl_multi_close() intentionally omitted —
        // deprecated since PHP 8.5 (no-ops since PHP 8.0, handles auto-freed).
    }
    return $map;
}

function apply_duration_filter($sounds, $with_duration, $min_duration, $max_duration) {
    if (!$with_duration && $min_duration === null && $max_duration === null) return $sounds;
    $urls = [];
    foreach ($sounds as $s) { if (!empty($s['mp3'])) $urls[] = $s['mp3']; }
    $durations = fetch_mp3_durations($urls);
    $out = [];
    foreach ($sounds as $s) {
        $d = isset($s['mp3'], $durations[$s['mp3']]) ? $durations[$s['mp3']] : null;
        if ($min_duration !== null && ($d === null || $d < $min_duration)) continue;
        if ($max_duration !== null && ($d === null || $d > $max_duration)) continue;
        $s['duration'] = $d;
        $out[] = $s;
    }
    return $out;
}
