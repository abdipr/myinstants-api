<?php
header("Content-Type: application/json");
echo json_encode(["status" => "200","author" => "abdipr","message" => "Check https://github.com/abdipr/myinstants-api for documentation"], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>
