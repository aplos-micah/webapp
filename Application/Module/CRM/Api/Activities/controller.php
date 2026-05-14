<?php

/**
 * GET /api_v2/crm/activities
 *
 * Params:
 *   id             int    — single record by ID
 *   account_id     int    — filter by account
 *   contact_id     int    — filter by contact
 *   opportunity_id int    — filter by opportunity
 *   search         string — text filter (type name, notes, outcome)
 *   limit          int    — max records (default 20, max 100)
 *   offset         int    — pagination offset (default 0)
 */

$obj = CRMContainer::get('activity');

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    $record = $obj->findById($id);
    return $record
        ? Response::json(['ok' => true, 'data' => $record])
        : Response::json(['ok' => false, 'error' => 'Activity not found.'], 404);
}

$search  = trim($_GET['search'] ?? '');
$limit   = min(100, max(1, (int) ($_GET['limit']  ?? 20)));
$offset  = max(0,          (int) ($_GET['offset'] ?? 0));
$filters = array_filter([
    'account_id'     => (int) ($_GET['account_id']     ?? 0) ?: null,
    'contact_id'     => (int) ($_GET['contact_id']     ?? 0) ?: null,
    'opportunity_id' => (int) ($_GET['opportunity_id'] ?? 0) ?: null,
]);

$records = $obj->findAll($limit, $offset, $search, 'activity_date', 'desc', $filters);

return Response::json([
    'ok'   => true,
    'data' => $records,
    'meta' => ['total' => count($records), 'limit' => $limit, 'offset' => $offset],
]);
