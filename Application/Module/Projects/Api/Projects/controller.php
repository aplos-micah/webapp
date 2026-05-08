<?php
/**
 * GET /api_v2/projects/projects
 *
 * Params:
 *   id       int    — single project
 *   search   string — filter by name or description
 *   status   string — filter by status
 *   phase    string — filter by phase
 *   priority string — filter by priority
 *   limit    int    — max records (default 20, max 100)
 *   offset   int    — pagination offset
 */

$obj = ProjectsContainer::get('project');

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    $record = $obj->findById($id);
    return $record
        ? Response::json(['ok' => true, 'data' => $record])
        : Response::json(['ok' => false, 'error' => 'Project not found.'], 404);
}

$search   = trim($_GET['search']   ?? '');
$status   = trim($_GET['status']   ?? '');
$phase    = trim($_GET['phase']    ?? '');
$priority = trim($_GET['priority'] ?? '');
$limit    = min(100, max(1, (int) ($_GET['limit']  ?? 20)));
$offset   = max(0,           (int) ($_GET['offset'] ?? 0));

return Response::json([
    'ok'   => true,
    'data' => $obj->findAll($limit, $offset, $search, 'created_at', 'desc', $status, $phase, $priority),
    'meta' => [
        'total'  => $obj->count($search, $status, $phase, $priority),
        'limit'  => $limit,
        'offset' => $offset,
    ],
]);
