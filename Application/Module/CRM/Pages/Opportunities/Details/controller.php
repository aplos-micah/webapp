<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../Objects/Opportunity.php';
require_once __DIR__ . '/../../../Objects/OpportunityProductLineItem.php';
require_once __DIR__ . '/../../../Objects/Location.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id === 0) {
    header('Location: /crm/opportunities/list');
    exit;
}

$oppObj      = new Opportunity(new DB());
$lineItemObj = new OpportunityProductLineItem(new DB());

$opp = $oppObj->findById($id);

if (!$opp) {
    $_SESSION['_flash'] = ['type' => 'error', 'message' => 'Opportunity not found.'];
    header('Location: /crm/opportunities/list');
    exit;
}

$isClosed  = in_array($opp['stage'] ?? '', ['Closed Won', 'Closed Lost'], true);
$editMode  = isset($_GET['edit']) && !$isClosed;
$editError = null;

// Redirect away from edit mode if opportunity is closed
if (isset($_GET['edit']) && $isClosed) {
    header('Location: /crm/opportunities/details?id=' . $id);
    exit;
}

// ─── Line item mutations ────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($isClosed) {
        $_SESSION['_flash'] = ['type' => 'warning', 'message' => 'Closed opportunities cannot be edited.'];
        header('Location: /crm/opportunities/details?id=' . $id);
        exit;
    }

    $action = $_POST['_action'] ?? '';

    // Add line item
    if ($action === 'add_line_item') {
        $result = $lineItemObj->add($id, $_POST);
        if ($result['ok']) {
            // Auto-advance stage New → Building when the first line item is added
            if (($opp['stage'] ?? '') === 'New') {
                $oppObj->update($id, array_merge($opp, ['stage' => 'Building']));
                $opp['stage'] = 'Building';
            }
            $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Line item added.'];
        } else {
            $_SESSION['_flash'] = ['type' => 'warning', 'message' => $result['error']];
        }
        $redirect = '/crm/opportunities/details?id=' . $id;
        if ($editMode) {
            $redirect .= '&edit';
        }
        header('Location: ' . $redirect);
        exit;
    }

    // Remove line item
    if ($action === 'remove_line_item') {
        $lineItemId = (int) ($_POST['line_item_id'] ?? 0);
        $lineItemObj->remove($lineItemId, $id);
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Line item removed.'];
        $redirect = '/crm/opportunities/details?id=' . $id;
        if ($editMode) {
            $redirect .= '&edit';
        }
        header('Location: ' . $redirect);
        exit;
    }

    // Update line item
    if ($action === 'update_line_item') {
        $lineItemId = (int) ($_POST['line_item_id'] ?? 0);
        $result     = $lineItemObj->update($lineItemId, $id, $_POST);
        if ($result['ok']) {
            $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Line item updated.'];
        } else {
            $_SESSION['_flash'] = ['type' => 'warning', 'message' => $result['error']];
        }
        $redirect = '/crm/opportunities/details?id=' . $id;
        if ($editMode) {
            $redirect .= '&edit';
        }
        header('Location: ' . $redirect);
        exit;
    }

    // ─── Save opportunity fields ────────────────────────────────────────────

    $data = [
        'opportunity_name'       => trim($_POST['opportunity_name']       ?? ''),
        'opportunity_type'       => trim($_POST['opportunity_type']       ?? ''),
        'lead_source'            => trim($_POST['lead_source']            ?? ''),
        'account_id'             => ($_POST['account_id']   ?? '') !== '' ? (int) $_POST['account_id']   : null,
        'contact_id'             => ($_POST['contact_id']   ?? '') !== '' ? (int) $_POST['contact_id']   : null,
        'owner_id'               => ($_POST['owner_id']     ?? '') !== '' ? (int) $_POST['owner_id']     : null,
        'amount'                 => ($_POST['amount']        ?? '') !== '' ? $_POST['amount']             : null,
        'probability'            => ($_POST['probability']   ?? '') !== '' ? (int) $_POST['probability']  : null,
        'forecast_category'      => trim($_POST['forecast_category']      ?? ''),
        'close_date'             => trim($_POST['close_date']             ?? '') ?: null,
        'stage'                  => trim($_POST['stage']                  ?? 'Prospecting'),
        'loss_reason'            => trim($_POST['loss_reason']            ?? ''),
        'budget_confirmed'       => isset($_POST['budget_confirmed']) ? 1 : 0,
        'decision_timeline'      => trim($_POST['decision_timeline']      ?? ''),
        'stakeholders_identified'=> !empty($_POST['stakeholders_identified'])
                                        ? json_encode($_POST['stakeholders_identified'])
                                        : null,
        'competitor'             => !empty($_POST['competitor'])
                                        ? json_encode($_POST['competitor'])
                                        : null,
        'plan_type'              => trim($_POST['plan_type']              ?? ''),
        'billing_term'           => trim($_POST['billing_term']           ?? ''),
        'description'            => trim($_POST['description']            ?? ''),
        'bill_to_location_id'    => ($_POST['bill_to_location_id'] ?? '') !== '' ? (int) $_POST['bill_to_location_id'] : null,
    ];

    $result = $oppObj->update($id, $data);

    if ($result['ok']) {
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Opportunity updated successfully.'];
        header('Location: /crm/opportunities/details?id=' . $id);
        exit;
    }

    $editMode  = true;
    $editError = $result['error'];
    $opp       = array_merge($opp, $data);
}

// Decode JSON multi-select fields for display
$stakeholders = json_decode($opp['stakeholders_identified'] ?? '[]', true) ?: [];
$competitors  = json_decode($opp['competitor']              ?? '[]', true) ?: [];

// Load line items
$lineItems      = $lineItemObj->findByOpportunity($id);
$lineItemsTotal = $lineItemObj->totalForOpportunity($id);

// Load Bill To and Ship To locations for the opportunity's account
$locationObj  = new Location(new DB());
$accountId    = (int) ($opp['account_id'] ?? 0);
$billToLocs   = $accountId ? array_filter(
    $locationObj->findByAccount($accountId),
    fn($l) => $l['location_type'] === 'Bill To'
) : [];
$shipToLocs   = $accountId ? array_filter(
    $locationObj->findByAccount($accountId),
    fn($l) => $l['location_type'] === 'Ship To'
) : [];
$billToLocs   = array_values($billToLocs);
$shipToLocs   = array_values($shipToLocs);
