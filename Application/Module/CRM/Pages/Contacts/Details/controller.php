<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../Objects/Contact.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id === 0) {
    header('Location: /crm/contacts/list');
    exit;
}

$contactObj = new Contact(new DB());
$contact    = $contactObj->findById($id);

if (!$contact) {
    $_SESSION['_flash'] = ['type' => 'error', 'message' => 'Contact not found.'];
    header('Location: /crm/contacts/list');
    exit;
}

$editMode  = isset($_GET['edit']);
$editError = null;

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'first_name'               => trim($_POST['first_name']               ?? ''),
        'last_name'                => trim($_POST['last_name']                ?? ''),
        'job_title'                => trim($_POST['job_title']                ?? ''),
        'account_id'               => ($_POST['account_id']        ?? '') !== '' ? (int) $_POST['account_id']        : null,
        'linkedin_url'             => trim($_POST['linkedin_url']             ?? ''),
        'email'                    => trim($_POST['email']                    ?? ''),
        'work_phone'               => trim($_POST['work_phone']               ?? ''),
        'mobile_phone'             => trim($_POST['mobile_phone']             ?? ''),
        'mailing_address'          => trim($_POST['mailing_address']          ?? ''),
        'communication_preference' => trim($_POST['communication_preference'] ?? ''),
        'lifecycle_stage'          => trim($_POST['lifecycle_stage']          ?? ''),
        'lead_source'              => trim($_POST['lead_source']              ?? ''),
        'owner_id'                 => ($_POST['owner_id']          ?? '') !== '' ? (int) $_POST['owner_id']          : null,
        'status'                   => trim($_POST['status']                   ?? 'Active'),
        'last_contact_at'          => trim($_POST['last_contact_at']          ?? '') ?: null,
        'last_activity'            => trim($_POST['last_activity']            ?? ''),
        'lead_score'               => ($_POST['lead_score']        ?? '') !== '' ? (int) $_POST['lead_score']        : 0,
        'interaction_history'      => trim($_POST['interaction_history']      ?? ''),
        'industry'                 => trim($_POST['industry']                 ?? ''),
        'buying_role'              => trim($_POST['buying_role']              ?? ''),
        'renewal_date'             => trim($_POST['renewal_date']             ?? '') ?: null,
    ];

    $result = $contactObj->update($id, $data);

    if ($result['ok']) {
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Contact updated successfully.'];
        header('Location: /crm/contacts/details?id=' . $id);
        exit;
    }

    // Stay in edit mode on failure, repopulate with submitted values
    $editMode  = true;
    $editError = $result['error'];
    $contact   = array_merge($contact, $data);
}

// Resolve the linked account name for display
$accountName = null;
if (!empty($contact['account_id'])) {
    $accountRow  = (new DB())->queryOne(
        'SELECT name FROM accounts WHERE id = ? LIMIT 1',
        [(int) $contact['account_id']]
    );
    $accountName = $accountRow['name'] ?? null;
}

// Load the logged-in user's saved tile order
$defaultTileOrder = ['opportunities', 'accounts', 'activities', 'notes'];
$userObj          = new User(new DB());
$currentUser      = $userObj->findById((int) ($_SESSION['user_id'] ?? 0));
$crmSettings      = json_decode($currentUser['module_crm_settings'] ?? '{}', true) ?: [];
$savedOrder       = $crmSettings['contact_related_order'] ?? [];

// Validate saved order — fill in any missing tiles at the end
$allowed   = $defaultTileOrder;
$tileOrder = array_values(array_intersect($savedOrder, $allowed));
foreach ($allowed as $tile) {
    if (!in_array($tile, $tileOrder, true)) {
        $tileOrder[] = $tile;
    }
}
