<?php

/**
 * GET /api/contacts
 *
 * Query params:
 *   id      int     — return a single contact
 *   search  string  — filter by name / email / job title / account name
 *   limit   int     — records per page (default 20, max 100)
 *   offset  int     — pagination offset (default 0)
 */

$obj = CRMContainer::get('contact');

// Single record
$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    $record = $obj->findById($id);
    return $record
        ? Response::json(['ok' => true, 'data' => $record])
        : Response::json(['ok' => false, 'error' => 'Contact not found.'], 404);
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
