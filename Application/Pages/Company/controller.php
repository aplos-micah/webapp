<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId    = (int) ($_SESSION['user_id'] ?? 0);
$userObj   = Container::get('user');
$companyObj = Container::get('company');

$userRow   = $userObj->findById($userId);
$companyId = (int) ($userRow['company_id'] ?? 0) ?: null;
$company   = $companyId ? $companyObj->getById($companyId) : null;

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['_action'] ?? '';
    $fields = [
        'name'    => $_POST['name']    ?? '',
        'phone'   => $_POST['phone']   ?? '',
        'email'   => $_POST['email']   ?? '',
        'address' => $_POST['address'] ?? '',
        'city'    => $_POST['city']    ?? '',
        'state'   => $_POST['state']   ?? '',
        'zip'     => $_POST['zip']     ?? '',
        'website' => $_POST['website'] ?? '',
    ];

    if ($err = Validator::required(trim($fields['name']), 'Company name')) {
        $error = $err;
    } elseif ($action === 'create_company') {
        $newId = $companyObj->create($fields);
        $userObj->setCompany($userId, $newId);
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Company created successfully.'];
        return Response::redirect('/company');
    } elseif ($action === 'update_company') {
        $companyObj->update($companyId, $fields);
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Company updated successfully.'];
        return Response::redirect('/company');
    }
}

$data = compact('company', 'error');
