<?php


// Entry point — all requests routed here via .htaccess

try {
    require_once __DIR__ . '/../Application/utilities.php';

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
} catch (Throwable $e) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    echo '<pre style="background:#1e1e2e;color:#f38ba8;padding:1rem;font:13px/1.6 monospace;">'
       . '<strong>' . htmlspecialchars(get_class($e), ENT_QUOTES, 'UTF-8') . ': '
       . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</strong>' . "\n\n"
       . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8')
       . '</pre>';
    
    //    header('Location: /simplifying.html');

       exit;


}


