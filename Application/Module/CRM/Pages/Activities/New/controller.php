<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$activityObj = CRMContainer::get('activity');
$typeObj     = CRMContainer::get('activity_type');
$activeTypes = $typeObj->findAll(200, 0, '', 'name', 'asc', true);

$editError = null;

// Pre-fill entity links from query string (from "Log Activity" buttons on detail pages)
$preAccountId     = (int) ($_GET['account_id']     ?? 0) ?: null;
$preContactId     = (int) ($_GET['contact_id']     ?? 0) ?: null;
$preOpportunityId = (int) ($_GET['opportunity_id'] ?? 0) ?: null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = array_merge($_POST, [
        'owner_id'       => $_SESSION['user_id'] ?? 0,
        'account_id'     => (int) ($_POST['account_id']     ?? 0) ?: null,
        'contact_id'     => (int) ($_POST['contact_id']     ?? 0) ?: null,
        'opportunity_id' => (int) ($_POST['opportunity_id'] ?? 0) ?: null,
    ]);

    $result = $activityObj->create($data);

    if ($result['ok']) {
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Activity logged.'];
        Response::redirect('/crm/activities/list');
    }

    $editError = $result['error'];
}

$pageTitle = 'Log Activity';

// For pre-filling account/contact/opportunity names, fetch them if IDs provided
$preAccountName     = null;
$preContactName     = null;
$preOpportunityName = null;

if ($preAccountId) {
    $acc = CRMContainer::get('account')->findById($preAccountId);
    $preAccountName = $acc['name'] ?? null;
}
if ($preContactId) {
    $con = CRMContainer::get('contact')->findById($preContactId);
    $preContactName = $con ? trim(($con['first_name'] ?? '') . ' ' . ($con['last_name'] ?? '')) : null;
}
if ($preOpportunityId) {
    $opp = CRMContainer::get('opportunity')->findById($preOpportunityId);
    $preOpportunityName = $opp['opportunity_name'] ?? null;
}
