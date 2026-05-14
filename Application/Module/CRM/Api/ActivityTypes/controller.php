<?php

/**
 * GET /api_v2/crm/activitytypes
 *
 * Params:
 *   id     int    — single record by ID
 *   search string — filter by name
 *   limit  int    — max records (default 100, max 100)
 *   offset int    — pagination offset (default 0)
 */

$obj = CRMContainer::get('activity_type');

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    $record = $obj->findById($id);
    return $record
        ? Response::json(['ok' => true, 'data' => $record])
        : Response::json(['ok' => false, 'error' => 'Activity type not found.'], 404);
}

$search  = trim($_GET['search'] ?? '');
$limit   = min(100, max(1, (int) ($_GET['limit']  ?? 100)));
$offset  = max(0,          (int) ($_GET['offset'] ?? 0));
$total   = $obj->count($search);
$records = $obj->findAll($limit, $offset, $search);

return Response::json([
    'ok'   => true,
    'data' => $records,
    'meta' => ['total' => $total, 'limit' => $limit, 'offset' => $offset],
]);
