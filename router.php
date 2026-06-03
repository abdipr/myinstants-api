<?php
// router.php - Vercel routing simulation on local development

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// Route: ^/$ -> /api/index.php
if ($path === '/') {
    require __DIR__ . '/api/index.php';
    return true;
}

$path = rtrim($path, '/');

// Route: ^/(.*)$ -> /api/$1.php
$apiFile = __DIR__ . '/api' . $path . '.php';

if (file_exists($apiFile)) {
    require $apiFile;
    return true;
}

if (file_exists(__DIR__ . $path) && !is_dir(__DIR__ . $path)) {
    return false;
}
require __DIR__ . '/api/404.php';
return true;
