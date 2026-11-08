<?php
// Router for PHP's built-in server. Example: php -S 127.0.0.1:8080 router.php
$public = __DIR__ . '/public';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = realpath($public . $uri);
if ($file && str_starts_with($file, realpath($public)) && is_file($file)) {
    return false;
}
require $public . '/index.php';
