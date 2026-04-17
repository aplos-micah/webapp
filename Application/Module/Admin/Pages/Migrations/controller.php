<?php

require_once __DIR__ . '/../../../../Migrations/Runner.php';

$runner = new Runner(new DB());
$runner->ensureTable();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action']   ?? '';
    $adminName = $_SESSION['user_name'] ?? 'admin';

    if ($action === 'run_all') {
        $results = $runner->runAll($adminName);
        $failed  = array_filter($results, fn($r) => !$r['ok']);
        if (empty($results)) {
            $_SESSION['_flash'] = ['type' => 'warning', 'message' => 'No pending migrations to run.'];
        } elseif (empty($failed)) {
            $n = count($results);
            $_SESSION['_flash'] = ['type' => 'success', 'message' => $n . ' migration' . ($n === 1 ? '' : 's') . ' applied successfully.'];
        } else {
            $f = reset($failed);
            $_SESSION['_flash'] = ['type' => 'error', 'message' => 'Migration failed: ' . htmlspecialchars($f['file']) . ' — ' . htmlspecialchars($f['error'] ?? '')];
        }
        return Response::redirect('/admin/migrations');
    }

    if ($action === 'run_one') {
        $file   = basename($_POST['file'] ?? '');
        $result = $runner->runFile($file, $adminName);
        if ($result['ok']) {
            $_SESSION['_flash'] = ['type' => 'success', 'message' => htmlspecialchars($file) . ' applied successfully.'];
        } else {
            $_SESSION['_flash'] = ['type' => 'error', 'message' => 'Failed: ' . htmlspecialchars($result['error'] ?? '')];
        }
        return Response::redirect('/admin/migrations');
    }

    if ($action === 'mark_applied') {
        $file = basename($_POST['file'] ?? '');
        if ($runner->markApplied($file, $adminName)) {
            $_SESSION['_flash'] = ['type' => 'success', 'message' => htmlspecialchars($file) . ' marked as applied.'];
        } else {
            $_SESSION['_flash'] = ['type' => 'error', 'message' => 'Could not mark migration as applied.'];
        }
        return Response::redirect('/admin/migrations');
    }
}

$pending = $runner->getPending();
$applied = $runner->getApplied();
