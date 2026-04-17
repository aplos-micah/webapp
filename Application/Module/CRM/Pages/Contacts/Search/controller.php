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
    "SELECT id,
            CONCAT(first_name, ' ', last_name) AS name
       FROM contacts
      WHERE first_name LIKE ?
         OR last_name  LIKE ?
         OR email      LIKE ?
      ORDER BY first_name, last_name ASC
      LIMIT 10",
    [$like, $like, $like]
);

echo json_encode(array_values($results));
exit;
