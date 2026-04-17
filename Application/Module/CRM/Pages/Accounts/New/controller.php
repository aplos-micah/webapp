<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error      = null;
$accountObj = Container::get('account');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = [
        'name'             => trim($_POST['name']             ?? ''),
        'account_number'   => trim($_POST['account_number']   ?? ''),
        'site'             => trim($_POST['site']             ?? ''),
        'parent_id'        => ($_POST['parent_id']      ?? '') !== '' ? (int) $_POST['parent_id']      : null,
        'industry'         => trim($_POST['industry']         ?? ''),
        'type'             => trim($_POST['type']             ?? ''),
        'billing_address'  => trim($_POST['billing_address']  ?? ''),
        'shipping_address' => trim($_POST['shipping_address'] ?? ''),
        'annual_revenue'   => ($_POST['annual_revenue']  ?? '') !== '' ? $_POST['annual_revenue']  : null,
        'employee_count'   => ($_POST['employee_count']  ?? '') !== '' ? (int) $_POST['employee_count']  : null,
        'ownership'        => trim($_POST['ownership']        ?? ''),
        'website'          => trim($_POST['website']          ?? ''),
        'status'           => trim($_POST['status']           ?? ''),
        'description'      => trim($_POST['description']      ?? ''),
        'owner_id'         => (int) ($_SESSION['user_id'] ?? 0),
    ];

    $result = $accountObj->create($data);

    if ($result['ok']) {
        return Response::redirect('/crm/accounts/details?id=' . $result['id']);
    }

    $error = $result['error'];
}
