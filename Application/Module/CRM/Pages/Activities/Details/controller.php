<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = (int) ($_GET['id'] ?? 0);
if ($id < 1) {
    Response::redirect('/crm/activities/list');
}

$activityObj = CRMContainer::get('activity');
$activity    = $activityObj->findById($id);

if (!$activity) {
    Response::redirect('/crm/activities/list');
}

$userId       = (int) ($_SESSION['user_id'] ?? 0);
$isCrmManager = ($_SESSION['module_crm'] ?? '') === 'Manager';
$isAdmin      = ($_SESSION['user_type'] ?? '') === 'admin';
$isOwner      = $activity['owner_id'] === $userId;
$canDelete    = $isCrmManager || $isAdmin;
$canEdit      = $isOwner || $canDelete;

$editError = null;
$editMode  = isset($_GET['edit']) && $canEdit;

$typeObj     = CRMContainer::get('activity_type');
$activeTypes = $typeObj->findAll(200, 0, '', 'name', 'asc', true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete' && $canDelete) {
        $activityObj->delete($id);
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Activity deleted.'];
        Response::redirect('/crm/activities/list');
    }

    if ($action === 'update' && $canEdit) {
        $data = array_merge($_POST, [
            'account_id'     => (int) ($_POST['account_id']     ?? 0) ?: null,
            'contact_id'     => (int) ($_POST['contact_id']     ?? 0) ?: null,
            'opportunity_id' => (int) ($_POST['opportunity_id'] ?? 0) ?: null,
        ]);

        $result = $activityObj->update($id, $data);
        if ($result['ok']) {
            $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Activity updated.'];
            Response::redirect('/crm/activities/details?id=' . $id);
        }
        $editError = $result['error'];
        $editMode  = true;
    }

    // Reload after failed update
    $activity = $activityObj->findById($id);
}

$pageTitle = $activity['type_name'] . ' — ' . $activity['activity_date'];
