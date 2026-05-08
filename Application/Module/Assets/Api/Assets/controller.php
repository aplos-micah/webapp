<?php
/**
 * GET /api_v2/assets/assets
 *
 * Params:
 *   id     int    — single asset
 *   search string — filter by name, asset tag, or serial number
 *   status string — filter by status
 *   type   string — filter by type
 *   limit  int    — max records (default 20, max 100)
 *   offset int    — pagination offset
 */

$obj = AssetsContainer::get('asset');

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    $record = $obj->findById($id);
    return $record
        ? Response::json(['ok' => true, 'data' => $record])
        : Response::json(['ok' => false, 'error' => 'Asset not found.'], 404);
}

$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');
$type   = trim($_GET['type']   ?? '');
$limit  = min(100, max(1, (int) ($_GET['limit']  ?? 20)));
$offset = max(0,           (int) ($_GET['offset'] ?? 0));

return Response::json([
    'ok'   => true,
    'data' => $obj->findAll($limit, $offset, $search, 'created_at', 'desc', $status, $type),
    'meta' => [
        'total'  => $obj->count($search, $status, $type),
        'limit'  => $limit,
        'offset' => $offset,
    ],
]);
