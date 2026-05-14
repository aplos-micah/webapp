<?php
/**
 * GET /api_v2/kb/articles
 *
 * Params:
 *   id             int    — single article
 *   search         string — filter by title, content, or tags
 *   status         string — filter by status (default: Published)
 *   category       string — filter by category
 *   include_drafts bool   — include Draft articles
 *   limit          int    — max records (default 20, max 100)
 *   offset         int    — pagination offset
 */

$obj = KBContainer::get('article');

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    $record = $obj->findById($id);
    if (!$record) {
        return Response::json(['ok' => false, 'error' => 'Article not found.'], 404);
    }
    $obj->incrementViewCount($id, 'Remote Application');
    return Response::json(['ok' => true, 'data' => $record]);
}

$search        = trim($_GET['search']   ?? '');
$category      = trim($_GET['category'] ?? '');
$includeDrafts = filter_var($_GET['include_drafts'] ?? false, FILTER_VALIDATE_BOOLEAN);
$status        = $includeDrafts ? '' : trim($_GET['status'] ?? 'Published');
$limit         = min(100, max(1, (int) ($_GET['limit']  ?? 20)));
$offset        = max(0,           (int) ($_GET['offset'] ?? 0));

return Response::json([
    'ok'   => true,
    'data' => $obj->findAll($limit, $offset, $search, 'updated_at', 'desc', $status, $category),
    'meta' => [
        'total'  => $obj->count($search, $status, $category),
        'limit'  => $limit,
        'offset' => $offset,
    ],
]);
