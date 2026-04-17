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
    "SELECT id, product_name, sku, list_price, unit_of_measure
       FROM product_definitions
      WHERE is_active = 1
        AND (product_name LIKE ? OR sku LIKE ?)
      ORDER BY product_name ASC
      LIMIT 10",
    [$like, $like]
);

echo json_encode(array_values($results));
exit;
