<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error      = null;
$contactObj = CRMContainer::get('contact');

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
        'owner_id'                 => (int) ($_SESSION['user_id'] ?? 0),
        'status'                   => trim($_POST['status']                   ?? 'Active'),
        'last_contact_at'          => trim($_POST['last_contact_at']          ?? '') ?: null,
        'last_activity'            => trim($_POST['last_activity']            ?? ''),
        'lead_score'               => ($_POST['lead_score']        ?? '') !== '' ? (int) $_POST['lead_score']        : 0,
        'interaction_history'      => trim($_POST['interaction_history']      ?? ''),
        'industry'                 => trim($_POST['industry']                 ?? ''),
        'buying_role'              => trim($_POST['buying_role']              ?? ''),
        'renewal_date'             => trim($_POST['renewal_date']             ?? '') ?: null,
    ];

    $result = $contactObj->create($data);

    if ($result['ok']) {
        return Response::redirect('/crm/contacts/details?id=' . $result['id']);
    }

    $error = $result['error'];
}
