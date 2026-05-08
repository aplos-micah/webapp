<?php

$ticketObj      = ITSMContainer::get('ticket');
$assignableUsers = $ticketObj->getAssignableUsers();
$error          = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title'               => trim($_POST['title']               ?? ''),
        'description'         => trim($_POST['description']         ?? ''),
        'type'                => $_POST['type']                     ?? 'Incident',
        'priority'            => $_POST['priority']                 ?? 'Medium',
        'category'            => trim($_POST['category']            ?? '') ?: null,
        'assigned_to'         => Validator::nullableInt($_POST['assigned_to'] ?? ''),
        'reported_by_name'    => trim($_POST['reported_by_name']    ?? '') ?: null,
        'reported_by_email'   => trim($_POST['reported_by_email']   ?? '') ?: null,
        'owner_id'            => (int) ($_SESSION['user_id'] ?? 0) ?: null,
        'status'              => 'New',
    ];

    $result = $ticketObj->create($data);
    if ($result['ok']) {
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Ticket created successfully.'];
        return Response::redirect('/itsm/tickets/details?id=' . $result['id']);
    }
    $error = $result['error'];
}
