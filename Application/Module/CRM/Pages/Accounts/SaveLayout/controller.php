<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId = (int) ($_SESSION['user_id'] ?? 0);
if ($userId === 0) {
    return Response::json(['ok' => false, 'error' => 'Not authenticated.']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return Response::json(['ok' => false, 'error' => 'Invalid request method.']);
}

$body  = json_decode(file_get_contents('php://input'), true);
$order = $body['order'] ?? null;

$allowed = ['opportunities', 'contacts', 'locations', 'leads', 'performance'];

if (!is_array($order) || count($order) !== count($allowed)
    || array_diff($order, $allowed) !== []
) {
    return Response::json(['ok' => false, 'error' => 'Invalid tile order.']);
}

Container::get('user')->saveCrmSettings($userId, ['account_related_order' => array_values($order)]);

return Response::json(['ok' => true]);
