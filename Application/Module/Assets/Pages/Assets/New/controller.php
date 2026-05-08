<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$assetObj       = AssetsContainer::get('asset');
$assignableUsers = $assetObj->getAssignableUsers();
$error          = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name'            => trim($_POST['name']            ?? ''),
        'type'            => $_POST['type']                 ?? 'Hardware',
        'category'        => trim($_POST['category']        ?? '') ?: null,
        'status'          => $_POST['status']               ?? 'Active',
        'manufacturer'    => trim($_POST['manufacturer']    ?? '') ?: null,
        'model'           => trim($_POST['model']           ?? '') ?: null,
        'serial_number'   => trim($_POST['serial_number']   ?? '') ?: null,
        'location'        => trim($_POST['location']        ?? '') ?: null,
        'assigned_to'     => Validator::nullableInt($_POST['assigned_to'] ?? ''),
        'owner_id'        => (int) ($_SESSION['user_id'] ?? 0) ?: null,
        'purchase_date'   => trim($_POST['purchase_date']   ?? '') ?: null,
        'warranty_expires'=> trim($_POST['warranty_expires']?? '') ?: null,
        'cost'            => trim($_POST['cost']            ?? '') ?: null,
        'notes'           => trim($_POST['notes']           ?? '') ?: null,
    ];

    $result = $assetObj->create($data);
    if ($result['ok']) {
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Asset created successfully.'];
        return Response::redirect('/assets/assets/details?id=' . $result['id']);
    }
    $error = $result['error'];
}
