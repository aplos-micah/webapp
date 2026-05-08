<?php

$id = (int) ($_GET['id'] ?? 0);
if ($id < 1) {
    return Response::redirect('/itsm/tickets/list');
}

$ticketObj = ITSMContainer::get('ticket');
$ticket    = $ticketObj->findById($id);

if (!$ticket) {
    $_SESSION['_flash'] = ['type' => 'error', 'message' => 'Ticket not found.'];
    return Response::redirect('/itsm/tickets/list');
}

$editMode  = isset($_GET['edit']);
$editError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title'               => trim($_POST['title']               ?? ''),
        'description'         => trim($_POST['description']         ?? ''),
        'type'                => $_POST['type']                     ?? $ticket['type'],
        'priority'            => $_POST['priority']                 ?? $ticket['priority'],
        'status'              => $_POST['status']                   ?? $ticket['status'],
        'category'            => trim($_POST['category']            ?? '') ?: null,
        'assigned_to'         => Validator::nullableInt($_POST['assigned_to'] ?? ''),
        'reported_by_name'    => trim($_POST['reported_by_name']    ?? '') ?: null,
        'reported_by_email'   => trim($_POST['reported_by_email']   ?? '') ?: null,
        'resolution'          => trim($_POST['resolution']          ?? '') ?: null,
    ];

    $result = $ticketObj->update($id, $data);
    if ($result['ok']) {
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Ticket updated.'];
        return Response::redirect('/itsm/tickets/details?id=' . $id);
    }
    $editMode  = true;
    $editError = $result['error'];
    $ticket    = $ticketObj->findById($id);
}

$assignableUsers = $ticketObj->getAssignableUsers();

// Compute step-progress states for the workflow bar
$statusOrder = ['New', 'In Progress', 'Pending', 'Resolved', 'Closed'];
$currentIdx  = array_search($ticket['status'], $statusOrder, true);
$stepStates  = [];
foreach ($statusOrder as $i => $step) {
    if ($currentIdx === false) {
        $stepStates[$step] = '';
    } elseif ($i < $currentIdx) {
        $stepStates[$step] = 'is-done';
    } elseif ($i === $currentIdx) {
        $stepStates[$step] = 'is-active';
    } else {
        $stepStates[$step] = '';
    }
}

$pageTitle = $ticket['ticket_number'] ?: 'Ticket';
