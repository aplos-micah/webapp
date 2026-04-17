<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userObj = Container::get('user');
$user    = $userObj->findById((int) ($_SESSION['user_id'] ?? 0));

if (!$user) {
    return Response::redirect('/login');
}

// Session snapshot — exclude the flash bag (it's transient)
$sessionData = array_filter(
    $_SESSION,
    fn($key) => $key !== '_flash',
    ARRAY_FILTER_USE_KEY
);
