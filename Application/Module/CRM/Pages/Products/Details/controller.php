<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = (int) ($_GET['id'] ?? 0);

if ($id === 0) {
    return Response::redirect('/crm/products/list');
}

$productObj = Container::get('product');
$product    = $productObj->findById($id);

if (!$product) {
    $_SESSION['_flash'] = ['type' => 'error', 'message' => 'Product not found.'];
    return Response::redirect('/crm/products/list');
}

$editMode  = isset($_GET['edit']);
$editError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'product_name'             => trim($_POST['product_name']             ?? ''),
        'sku'                      => trim($_POST['sku']                      ?? ''),
        'product_description'      => trim($_POST['product_description']      ?? ''),
        'product_family'           => trim($_POST['product_family']           ?? ''),
        'product_type'             => trim($_POST['product_type']             ?? ''),
        'is_active'                => isset($_POST['is_active']) ? 1 : 0,
        'lifecycle_status'         => trim($_POST['lifecycle_status']         ?? 'Draft'),
        'list_price'               => ($_POST['list_price']               ?? '') !== '' ? $_POST['list_price']               : null,
        'currency'                 => trim($_POST['currency']                 ?? 'USD'),
        'unit_cost'                => ($_POST['unit_cost']                ?? '') !== '' ? $_POST['unit_cost']                : null,
        'unit_of_measure'          => trim($_POST['unit_of_measure']          ?? ''),
        'pricing_model'            => trim($_POST['pricing_model']            ?? ''),
        'tax_category'             => trim($_POST['tax_category']             ?? ''),
        'subscription_term_months' => ($_POST['subscription_term_months'] ?? '') !== '' ? (int) $_POST['subscription_term_months'] : null,
        'weight'                   => ($_POST['weight']                   ?? '') !== '' ? $_POST['weight']                   : null,
        'dimensions'               => trim($_POST['dimensions']               ?? ''),
        'material'                 => trim($_POST['material']                 ?? ''),
        'usage_metrics'            => trim($_POST['usage_metrics']            ?? ''),
        'competitive_notes'        => trim($_POST['competitive_notes']        ?? ''),
        'owner_id'                 => ($_POST['owner_id'] ?? '') !== '' ? (int) $_POST['owner_id'] : null,
    ];

    $result = $productObj->update($id, $data);

    if ($result['ok']) {
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Product updated successfully.'];
        return Response::redirect('/crm/products/details?id=' . $id);
    }

    $editMode  = true;
    $editError = $result['error'];
    $product   = array_merge($product, $data);
}
