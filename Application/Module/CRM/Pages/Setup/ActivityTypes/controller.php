<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Manager or Admin required
$isCrmManager = ($_SESSION['module_crm'] ?? '') === 'Manager';
$isAdmin      = ($_SESSION['user_type'] ?? '') === 'admin';
if (!$isCrmManager && !$isAdmin) {
    $_SESSION['_flash'] = ['type' => 'error', 'message' => 'Manager access is required to manage activity types.'];
    Response::redirect('/crm/dashboard');
}

$typeObj   = CRMContainer::get('activity_type');
$editError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $result = $typeObj->create($_POST);
        if ($result['ok']) {
            $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Activity type created.'];
            Response::redirect('/crm/setup/activitytypes');
        }
        $editError = $result['error'];
    }

    if ($action === 'update') {
        $id     = (int) ($_POST['id'] ?? 0);
        $result = $typeObj->update($id, $_POST);
        if ($result['ok']) {
            $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Activity type updated.'];
            Response::redirect('/crm/setup/activitytypes');
        }
        $editError = $result['error'];
    }

    if ($action === 'toggle_active') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $typeObj->toggleActive($id);
        }
        Response::redirect('/crm/setup/activitytypes');
    }
}

$pageTitle  = 'Activity Types';
$types      = $typeObj->findAll(200);
