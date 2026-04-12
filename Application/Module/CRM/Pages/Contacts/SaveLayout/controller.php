<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$userId = (int) ($_SESSION['user_id'] ?? 0);
if ($userId === 0) {
    echo json_encode(['ok' => false, 'error' => 'Not authenticated.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Invalid request method.']);
    exit;
}

$body  = json_decode(file_get_contents('php://input'), true);
$order = $body['order'] ?? null;

$allowed = ['opportunities', 'accounts', 'activities', 'notes'];

if (!is_array($order) || count($order) !== count($allowed)
    || array_diff($order, $allowed) !== []
) {
    echo json_encode(['ok' => false, 'error' => 'Invalid tile order.']);
    exit;
}

(new User(new DB()))->saveCrmSettings($userId, ['contact_related_order' => array_values($order)]);

echo json_encode(['ok' => true]);
exit;
