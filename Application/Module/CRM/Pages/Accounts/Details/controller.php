<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = (int) ($_GET['id'] ?? 0);

if ($id === 0) {
    header('Location: /crm/accounts/list');
    exit;
}

$accountObj = Container::get('account');
$account    = $accountObj->findById($id);

if (!$account) {
    $_SESSION['_flash'] = ['type' => 'error', 'message' => 'Account not found.'];
    header('Location: /crm/accounts/list');
    exit;
}

$editMode   = isset($_GET['edit']);
$editError  = null;

$locationObj = Container::get('location');
$locBaseUrl  = '/crm/accounts/details?id=' . $id;

// ─── Location mutations ───────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['_action'] ?? '';

    if ($action === 'add_location') {
        $result = $locationObj->add($id, $_POST);
        $_SESSION['_flash'] = $result['ok']
            ? ['type' => 'success', 'message' => 'Location added.']
            : ['type' => 'warning', 'message' => $result['error']];
        header('Location: ' . $locBaseUrl);
        exit;
    }

    if ($action === 'update_location') {
        $locId  = (int) ($_POST['location_id'] ?? 0);
        $result = $locationObj->update($locId, $id, $_POST);
        $_SESSION['_flash'] = $result['ok']
            ? ['type' => 'success', 'message' => 'Location updated.']
            : ['type' => 'warning', 'message' => $result['error']];
        header('Location: ' . $locBaseUrl);
        exit;
    }

    if ($action === 'remove_location') {
        $locId = (int) ($_POST['location_id'] ?? 0);
        $locationObj->remove($locId, $id);
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Location removed.'];
        header('Location: ' . $locBaseUrl);
        exit;
    }
}

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name'             => trim($_POST['name']             ?? ''),
        'account_number'   => trim($_POST['account_number']   ?? ''),
        'site'             => trim($_POST['site']             ?? ''),
        'parent_id'        => ($_POST['parent_id']      ?? '') !== '' ? (int) $_POST['parent_id']     : null,
        'industry'         => trim($_POST['industry']         ?? ''),
        'type'             => trim($_POST['type']             ?? ''),
        'billing_address'  => trim($_POST['billing_address']  ?? ''),
        'shipping_address' => trim($_POST['shipping_address'] ?? ''),
        'annual_revenue'   => ($_POST['annual_revenue']  ?? '') !== '' ? $_POST['annual_revenue']  : null,
        'employee_count'   => ($_POST['employee_count']  ?? '') !== '' ? (int) $_POST['employee_count'] : null,
        'ownership'        => trim($_POST['ownership']        ?? ''),
        'website'          => trim($_POST['website']          ?? ''),
        'owner_id'         => ($_POST['owner_id']       ?? '') !== '' ? (int) $_POST['owner_id']       : null,
        'status'           => trim($_POST['status']           ?? ''),
        'last_activity_at' => trim($_POST['last_activity_at'] ?? '') ?: null,
        'description'      => trim($_POST['description']      ?? ''),
    ];

    $result = $accountObj->update($id, $data);

    if ($result['ok']) {
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Account updated successfully.'];
        header('Location: /crm/accounts/details?id=' . $id);
        exit;
    }

    // Stay in edit mode on failure, repopulate with submitted values
    $editMode  = true;
    $editError = $result['error'];
    $account   = array_merge($account, $data);
}

// Load the logged-in user's saved tile order
$defaultTileOrder = ['opportunities', 'contacts', 'locations', 'leads', 'performance'];
$userObj          = Container::get('user');
$currentUser      = $userObj->findById((int) ($_SESSION['user_id'] ?? 0));
$crmSettings      = json_decode($currentUser['module_crm_settings'] ?? '{}', true) ?: [];
$savedOrder       = $crmSettings['account_related_order'] ?? [];

// Validate saved order — fill in any missing tiles at the end
$allowed   = $defaultTileOrder;
$tileOrder = array_values(array_intersect($savedOrder, $allowed));
foreach ($allowed as $tile) {
    if (!in_array($tile, $tileOrder, true)) {
        $tileOrder[] = $tile;
    }
}

// Load widget data
$contactsWidget       = Container::get('account_contacts');
$performanceWidget    = Container::get('account_performance');
$opportunitiesWidget  = Container::get('account_opportunities');
$locationsWidget      = Container::get('account_locations');
