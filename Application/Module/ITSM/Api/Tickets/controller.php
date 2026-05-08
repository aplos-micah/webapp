<?php
/**
 * GET /api_v2/itsm/tickets
 *
 * Params:
 *   id       int    — single ticket
 *   search   string — filter by title or ticket number
 *   status   string — filter by status
 *   priority string — filter by priority
 *   type     string — filter by type
 *   limit    int    — max records (default 20, max 100)
 *   offset   int    — pagination offset
 */

$obj = ITSMContainer::get('ticket');

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    $record = $obj->findById($id);
    return $record
        ? Response::json(['ok' => true, 'data' => $record])
        : Response::json(['ok' => false, 'error' => 'Ticket not found.'], 404);
}

$search   = trim($_GET['search']   ?? '');
$status   = trim($_GET['status']   ?? '');
$priority = trim($_GET['priority'] ?? '');
$type     = trim($_GET['type']     ?? '');
$limit    = min(100, max(1, (int) ($_GET['limit']  ?? 20)));
$offset   = max(0,            (int) ($_GET['offset'] ?? 0));

return Response::json([
    'ok'   => true,
    'data' => $obj->findAll($limit, $offset, $search, 'created_at', 'desc', $status, $priority, $type),
    'meta' => [
        'total'    => $obj->count($search, $status, $priority, $type),
        'limit'    => $limit,
        'offset'   => $offset,
    ],
]);
