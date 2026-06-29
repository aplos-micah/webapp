<?php

/**
 * /api_v2/crm/lineitems
 *
 * Line items are always scoped to an opportunity. The opportunity's `amount`
 * is kept in sync automatically by DB triggers — no manual recalculation needed.
 *
 * GET    ?opportunity_id=        — list line items for an opportunity
 * POST   ?opportunity_id=        — create a line item from a JSON body
 * PUT    ?opportunity_id=&id=    — update a line item from a JSON body
 * DELETE ?opportunity_id=&id=    — remove a line item
 */

$obj = CRMContainer::get('line_item');

$opportunityId = (int) ($_GET['opportunity_id'] ?? 0);
if ($opportunityId <= 0) {
    return Response::json(['ok' => false, 'error' => 'opportunity_id is required.'], 400);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    return Response::json([
        'ok'   => true,
        'data' => $obj->findByOpportunity($opportunityId),
        'meta' => ['total' => $obj->totalForOpportunity($opportunityId)],
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body   = Request::jsonBody();
    $result = $obj->add($opportunityId, $body);
    if (!$result['ok']) {
        return Response::json(['ok' => false, 'error' => $result['error']], 422);
    }
    $created = array_values(array_filter(
        $obj->findByOpportunity($opportunityId),
        fn($row) => (int) $row['id'] === (int) $result['id']
    ));
    return Response::json(['ok' => true, 'data' => $created[0] ?? null], 201);
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id <= 0) {
        return Response::json(['ok' => false, 'error' => 'id is required.'], 400);
    }

    $body   = Request::jsonBody();
    $result = $obj->update($id, $opportunityId, $body);
    if (!$result['ok']) {
        return Response::json(['ok' => false, 'error' => $result['error']], 404);
    }
    $updated = array_values(array_filter(
        $obj->findByOpportunity($opportunityId),
        fn($row) => (int) $row['id'] === $id
    ));
    return Response::json(['ok' => true, 'data' => $updated[0] ?? null]);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id <= 0) {
        return Response::json(['ok' => false, 'error' => 'id is required.'], 400);
    }

    $removed = $obj->remove($id, $opportunityId);
    return $removed
        ? Response::json(['ok' => true])
        : Response::json(['ok' => false, 'error' => 'Line item not found.'], 404);
}

return Response::json(['ok' => false, 'error' => 'Method not allowed.'], 405);
