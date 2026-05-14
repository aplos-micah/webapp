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
    'SELECT id, opportunity_name AS name
       FROM opportunities
      WHERE opportunity_name LIKE ?
      ORDER BY opportunity_name ASC
      LIMIT 10',
    [$like]
);

return Response::json(array_values($results));
