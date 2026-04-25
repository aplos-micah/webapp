<?php


// Entry point — all requests routed here via .htaccess

try {
    require_once __DIR__ . '/../Application/utilities.php';



} catch (Throwable $e) {
    if (class_exists('Logger')) {
        Logger::getInstance()->error('Bootstrap failed', [
            'exception' => get_class($e),
            'message'   => $e->getMessage(),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
        ]);
    }

    header('Location: /simplifying.html');
    exit;
}


