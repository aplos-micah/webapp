<?php

/**
 * /api_v2/crm/locations
 *
 * Locations are always scoped to an account.
 *
 * GET    ?account_id=        — list locations for an account
 * GET    ?account_id=&id=    — single location
 * POST   ?account_id=        — create a location from a JSON body
 * PUT    ?account_id=&id=    — update a location from a JSON body
 * DELETE ?account_id=&id=    — remove a location
 */

$obj = CRMContainer::get('location');

$accountId = (int) ($_GET['account_id'] ?? 0);
if ($accountId <= 0) {
    return Response::json(['ok' => false, 'error' => 'account_id is required.'], 400);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id > 0) {
        $record = $obj->findById($id, $accountId);
        return $record
            ? Response::json(['ok' => true, 'data' => $record])
            : Response::json(['ok' => false, 'error' => 'Location not found.'], 404);
    }

    return Response::json(['ok' => true, 'data' => $obj->findByAccount($accountId)]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body   = Request::jsonBody();
    $result = $obj->add($accountId, $body);
    return $result['ok']
        ? Response::json(['ok' => true, 'data' => $obj->findById($result['id'], $accountId)], 201)
        : Response::json(['ok' => false, 'error' => $result['error']], 422);
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id <= 0) {
        return Response::json(['ok' => false, 'error' => 'id is required.'], 400);
    }

    $body   = Request::jsonBody();
    $result = $obj->update($id, $accountId, $body);
    return $result['ok']
        ? Response::json(['ok' => true, 'data' => $obj->findById($id, $accountId)])
        : Response::json(['ok' => false, 'error' => $result['error']], 404);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id <= 0) {
        return Response::json(['ok' => false, 'error' => 'id is required.'], 400);
    }

    $removed = $obj->remove($id, $accountId);
    return $removed
        ? Response::json(['ok' => true])
        : Response::json(['ok' => false, 'error' => 'Location not found.'], 404);
}

return Response::json(['ok' => false, 'error' => 'Method not allowed.'], 405);
