<?php

// Application utilities

require_once __DIR__ . '/Sanitization.php';
require_once __DIR__ . '/../configuration/config.php';
require_once __DIR__ . '/DB.php';
require_once __DIR__ . '/Objects/User.php';
require_once __DIR__ . '/router.php';

Sanitization::sanitizeAll();
Router::dispatch();
