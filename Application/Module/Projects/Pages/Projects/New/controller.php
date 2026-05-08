<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$projectObj      = ProjectsContainer::get('project');
$assignableUsers = $projectObj->getAssignableUsers();
$error           = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name'        => trim($_POST['name']        ?? ''),
        'description' => trim($_POST['description'] ?? '') ?: null,
        'status'      => $_POST['status']            ?? 'Draft',
        'phase'       => $_POST['phase']             ?? 'Initiation',
        'priority'    => $_POST['priority']          ?? 'Medium',
        'owner_id'    => Validator::nullableInt($_POST['owner_id'] ?? ''),
        'start_date'  => trim($_POST['start_date']   ?? '') ?: null,
        'due_date'    => trim($_POST['due_date']      ?? '') ?: null,
        'budget'      => trim($_POST['budget']        ?? '') ?: null,
        'notes'       => trim($_POST['notes']         ?? '') ?: null,
    ];

    $result = $projectObj->create($data);
    if ($result['ok']) {
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Project created successfully.'];
        return Response::redirect('/projects/projects/details?id=' . $result['id']);
    }
    $error = $result['error'];
}
