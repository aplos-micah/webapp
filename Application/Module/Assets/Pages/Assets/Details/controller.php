<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = (int) ($_GET['id'] ?? 0);
if ($id < 1) {
    return Response::redirect('/assets/assets/list');
}

$assetObj = AssetsContainer::get('asset');
$asset    = $assetObj->findById($id);

if (!$asset) {
    $_SESSION['_flash'] = ['type' => 'error', 'message' => 'Asset not found.'];
    return Response::redirect('/assets/assets/list');
}

$editMode  = isset($_GET['edit']);
$editError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name'            => trim($_POST['name']            ?? ''),
        'type'            => $_POST['type']                 ?? $asset['type'],
        'category'        => trim($_POST['category']        ?? '') ?: null,
        'status'          => $_POST['status']               ?? $asset['status'],
        'manufacturer'    => trim($_POST['manufacturer']    ?? '') ?: null,
        'model'           => trim($_POST['model']           ?? '') ?: null,
        'serial_number'   => trim($_POST['serial_number']   ?? '') ?: null,
        'location'        => trim($_POST['location']        ?? '') ?: null,
        'assigned_to'     => Validator::nullableInt($_POST['assigned_to'] ?? ''),
        'owner_id'        => Validator::nullableInt($_POST['owner_id']    ?? ''),
        'purchase_date'   => trim($_POST['purchase_date']   ?? '') ?: null,
        'warranty_expires'=> trim($_POST['warranty_expires']?? '') ?: null,
        'cost'            => trim($_POST['cost']            ?? '') ?: null,
        'notes'           => trim($_POST['notes']           ?? '') ?: null,
    ];

    $result = $assetObj->update($id, $data);
    if ($result['ok']) {
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Asset updated.'];
        return Response::redirect('/assets/assets/details?id=' . $id);
    }
    $editMode  = true;
    $editError = $result['error'];
    $asset     = $assetObj->findById($id);
}

$assignableUsers = $assetObj->getAssignableUsers();
$pageTitle       = $asset['asset_tag'] ?: 'Asset';
