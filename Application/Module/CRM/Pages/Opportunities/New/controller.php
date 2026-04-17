<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error  = null;
$oppObj = Container::get('opportunity');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'opportunity_name'       => trim($_POST['opportunity_name']       ?? ''),
        'opportunity_type'       => trim($_POST['opportunity_type']       ?? ''),
        'lead_source'            => trim($_POST['lead_source']            ?? ''),
        'account_id'             => ($_POST['account_id']   ?? '') !== '' ? (int) $_POST['account_id']   : null,
        'contact_id'             => ($_POST['contact_id']   ?? '') !== '' ? (int) $_POST['contact_id']   : null,
        'owner_id'               => (int) ($_SESSION['user_id'] ?? 0),
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
    ];

    $result = $oppObj->create($data);

    if ($result['ok']) {
        header('Location: /crm/opportunities/details?id=' . $result['id']);
        exit;
    }

    $error = $result['error'];
}
