<?php

require_once __DIR__ . '/../../../../Module/Admin/Container.php';

/** @var MigrationRunner $runner */
$runner = AdminContainer::get('migration_runner');
$runner->ensureTable();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action    = $_POST['action'] ?? '';
    $module    = strtolower(preg_replace('/[^a-zA-Z0-9_\-]/', '', $_POST['module'] ?? ''));
    $file      = basename($_POST['file'] ?? '');
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

    if ($action === 'run_module') {
        $results = $runner->runModule($module, $adminName);
        $failed  = array_filter($results, fn($r) => !$r['ok']);
        if (empty($results)) {
            $_SESSION['_flash'] = ['type' => 'warning', 'message' => 'No pending migrations for ' . ucfirst($module) . '.'];
        } elseif (empty($failed)) {
            $n = count($results);
            $_SESSION['_flash'] = ['type' => 'success', 'message' => $n . ' ' . ucfirst($module) . ' migration' . ($n === 1 ? '' : 's') . ' applied successfully.'];
        } else {
            $f = reset($failed);
            $_SESSION['_flash'] = ['type' => 'error', 'message' => 'Migration failed: ' . htmlspecialchars($f['file']) . ' — ' . htmlspecialchars($f['error'] ?? '')];
        }
        return Response::redirect('/admin/migrations');
    }

    if ($action === 'run_one') {
        $result = $runner->runFile($module, $file, $adminName);
        if ($result['ok']) {
            $_SESSION['_flash'] = ['type' => 'success', 'message' => htmlspecialchars($file) . ' applied successfully.'];
        } else {
            $_SESSION['_flash'] = ['type' => 'error', 'message' => 'Failed: ' . htmlspecialchars($result['error'] ?? '')];
        }
        return Response::redirect('/admin/migrations');
    }

    if ($action === 'mark_applied') {
        if ($runner->markApplied($module, $file, $adminName)) {
            $_SESSION['_flash'] = ['type' => 'success', 'message' => htmlspecialchars($file) . ' marked as applied.'];
        } else {
            $_SESSION['_flash'] = ['type' => 'error', 'message' => 'Could not mark migration as applied.'];
        }
        return Response::redirect('/admin/migrations');
    }
}

$pendingAll   = $runner->getPendingAll();
$appliedAll   = $runner->getAppliedAll();
$totalPending = $runner->pendingCount();
$platformVersion = getenv('PLATFORM_VERSION') ?: '0.0.0';
