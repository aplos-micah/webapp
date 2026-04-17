<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$userId = (int) ($_SESSION['user_id'] ?? 0);
if ($userId === 0) {
    echo json_encode([]);
    exit;
}

$q = trim($_GET['q'] ?? '');
if ($q === '') {
    echo json_encode([]);
    exit;
}

$like    = '%' . $q . '%';
$results = Container::db()->query(
    'SELECT id, name
       FROM accounts
      WHERE name LIKE ?
      ORDER BY name ASC
      LIMIT 10',
    [$like]
);

echo json_encode(array_values($results));
exit;
