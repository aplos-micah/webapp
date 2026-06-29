<?php

/**
 * /api_v2/crm/opportunities
 *
 * GET    — list or single record
 *   id      int     — return a single opportunity (includes line items)
 *   search  string  — filter by name / stage / forecast / account name
 *   limit   int     — records per page (default 20, max 100)
 *   offset  int     — pagination offset (default 0)
 *
 * POST   — create an opportunity from a JSON body
 * PUT    — update an opportunity (requires ?id=) from a JSON body.
 *          Blocked (409) once the opportunity's stage is Closed Won/Closed Lost.
 */

const CLOSED_STAGES = ['Closed Won', 'Closed Lost'];

$obj = CRMContainer::get('opportunity');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Single record
    $id = (int) ($_GET['id'] ?? 0);
    if ($id > 0) {
        $record = $obj->findById($id);
        if (!$record) {
            return Response::json(['ok' => false, 'error' => 'Opportunity not found.'], 404);
        }
        $record['line_items'] = CRMContainer::get('line_item')->findByOpportunity($id);
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
    $existing = $obj->findById($id);
    if (!$existing) {
        return Response::json(['ok' => false, 'error' => 'Opportunity not found.'], 404);
    }
    if (in_array($existing['stage'], CLOSED_STAGES, true)) {
        return Response::json(['ok' => false, 'error' => 'Closed opportunities cannot be edited.'], 409);
    }

    $result = $obj->update($id, $body);
    return $result['ok']
        ? Response::json(['ok' => true, 'data' => $obj->findById($id)])
        : Response::json(['ok' => false, 'error' => $result['error']], 422);
}

return Response::json(['ok' => false, 'error' => 'Method not allowed.'], 405);
