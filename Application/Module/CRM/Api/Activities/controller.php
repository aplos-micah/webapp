<?php

/**
 * /api_v2/crm/activities
 *
 * GET    — list or single record
 *   id             int    — single record by ID
 *   account_id     int    — filter by account
 *   contact_id     int    — filter by contact
 *   opportunity_id int    — filter by opportunity
 *   search         string — text filter (type name, notes, outcome)
 *   limit          int    — max records (default 20, max 100)
 *   offset         int    — pagination offset (default 0)
 *
 * POST   — create an activity from a JSON body
 * PUT    — update an activity (requires ?id=) from a JSON body
 */

$obj = CRMContainer::get('activity');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
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
}

$body = Request::jsonBody();
if (!array_key_exists('owner_id', $body) || $body['owner_id'] === '' || $body['owner_id'] === null) {
    $body['owner_id'] = (int) ($_SESSION['user_id'] ?? 0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $obj->create($body);
    return $result['ok']
        ? Response::json(['ok' => true, 'data' => $obj->findById($result['id'])], 201)
        : Response::json(['ok' => false, 'error' => $result['error']], 422);
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id <= 0) {
        return Response::json(['ok' => false, 'error' => 'id is required.'], 400);
    }
    if (!$obj->findById($id)) {
        return Response::json(['ok' => false, 'error' => 'Activity not found.'], 404);
    }

    $result = $obj->update($id, $body);
    return $result['ok']
        ? Response::json(['ok' => true, 'data' => $obj->findById($id)])
        : Response::json(['ok' => false, 'error' => $result['error']], 422);
}

return Response::json(['ok' => false, 'error' => 'Method not allowed.'], 405);
