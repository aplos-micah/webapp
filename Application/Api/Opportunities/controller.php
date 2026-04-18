<?php

/**
 * GET /api/opportunities
 *
 * Query params:
 *   id      int     — return a single opportunity (includes line items)
 *   search  string  — filter by name / stage / forecast / account name
 *   limit   int     — records per page (default 20, max 100)
 *   offset  int     — pagination offset (default 0)
 */

$obj = Container::get('opportunity');

// Single record
$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    $record = $obj->findById($id);
    if (!$record) {
        return Response::json(['ok' => false, 'error' => 'Opportunity not found.'], 404);
    }
    $record['line_items'] = Container::get('line_item')->findByOpportunity($id);
    return Response::json(['ok' => true, 'data' => $record]);
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
