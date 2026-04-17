<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId = (int) ($_SESSION['user_id'] ?? 0);
if ($userId === 0) {
    return Response::json([]);
}

$q = trim($_GET['q'] ?? '');
if ($q === '') {
    return Response::json([]);
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

return Response::json(array_values($results));
