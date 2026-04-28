<?php

/**
 * GET /api/products
 *
 * Query params:
 *   id        int     — return a single product definition
 *   search    string  — filter by name / sku / family / type / lifecycle status
 *   active    bool    — if "1", restrict to is_active = 1 (default: no filter)
 *   limit     int     — records per page (default 20, max 100)
 *   offset    int     — pagination offset (default 0)
 */

$obj = CRMContainer::get('product');

// Single record
$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    $record = $obj->findById($id);
    return $record
        ? Response::json(['ok' => true, 'data' => $record])
        : Response::json(['ok' => false, 'error' => 'Product not found.'], 404);
}

// List
$search = trim($_GET['search'] ?? '');
$limit  = min(100, max(1, (int) ($_GET['limit']  ?? 20)));
$offset = max(0,             (int) ($_GET['offset'] ?? 0));

$total   = $obj->count($search);
$records = $obj->findAll($limit, $offset, $search);

return Response::json([
    'ok'   => true,
    'data' => $records,
    'meta' => ['total' => $total, 'limit' => $limit, 'offset' => $offset],
]);
