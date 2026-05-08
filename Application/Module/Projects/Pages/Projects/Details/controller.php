<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = (int) ($_GET['id'] ?? 0);
if ($id < 1) {
    return Response::redirect('/projects/projects/list');
}

$projectObj = ProjectsContainer::get('project');
$project    = $projectObj->findById($id);

if (!$project) {
    $_SESSION['_flash'] = ['type' => 'error', 'message' => 'Project not found.'];
    return Response::redirect('/projects/projects/list');
}

$editMode  = isset($_GET['edit']);
$editError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name'        => trim($_POST['name']        ?? ''),
        'description' => trim($_POST['description'] ?? '') ?: null,
        'status'      => $_POST['status']            ?? $project['status'],
        'phase'       => $_POST['phase']             ?? $project['phase'],
        'priority'    => $_POST['priority']          ?? $project['priority'],
        'owner_id'    => Validator::nullableInt($_POST['owner_id'] ?? ''),
        'start_date'  => trim($_POST['start_date']   ?? '') ?: null,
        'due_date'    => trim($_POST['due_date']      ?? '') ?: null,
        'budget'      => trim($_POST['budget']        ?? '') ?: null,
        'notes'       => trim($_POST['notes']         ?? '') ?: null,
    ];

    $result = $projectObj->update($id, $data);
    if ($result['ok']) {
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Project updated.'];
        return Response::redirect('/projects/projects/details?id=' . $id);
    }
    $editMode  = true;
    $editError = $result['error'];
    $project   = $projectObj->findById($id);
}

$assignableUsers = $projectObj->getAssignableUsers();

// Step-progress states for phase workflow bar
$phaseOrder  = Project::PHASES;
$currentIdx  = array_search($project['phase'], $phaseOrder, true);
$stepStates  = [];
foreach ($phaseOrder as $i => $step) {
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

$pageTitle = $project['name'];
