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

return Response::json(array_values($results));
