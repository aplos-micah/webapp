<?php

$logDir  = Logger::logDir();
$logFile = Logger::logFile();

$validPerPage = [100, 200, 300, 500, 1000];
$perPage      = (int) ($_GET['per_page'] ?? 100);
if (!in_array($perPage, $validPerPage, true)) {
    $perPage = 100;
}

$validLevels = ['', 'ERROR', 'WARNING', 'INFO'];
$levelFilter = strtoupper($_GET['level'] ?? '');
if (!in_array($levelFilter, $validLevels, true)) {
    $levelFilter = '';
}

$currentPage = max(1, (int) ($_GET['page'] ?? 1));

// POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'archive') {
        if (file_exists($logFile) && filesize($logFile) > 0) {
            $archiveName = $logDir . '/app-' . date('Y-m-d-His') . '.log';
            rename($logFile, $archiveName);
            file_put_contents($logFile, '');
            $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Log archived successfully.'];
        } else {
            $_SESSION['_flash'] = ['type' => 'warning', 'message' => 'Log file is empty — nothing to archive.'];
        }
        header('Location: /admin/logviewer');
        exit;
    }

    if ($action === 'clear') {
        file_put_contents($logFile, '');
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Log cleared.'];
        header('Location: /admin/logviewer');
        exit;
    }

    if ($action === 'delete_archive') {
        $filename = basename($_POST['filename'] ?? '');
        if (preg_match('/^app-\d{4}-\d{2}-\d{2}-\d{6}\.log$/', $filename)) {
            $filepath = $logDir . '/' . $filename;
            if (file_exists($filepath)) {
                unlink($filepath);
                $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Archived log deleted.'];
            }
        }
        header('Location: /admin/logviewer');
        exit;
    }
}

// Read & filter current log (newest first)
$allEntries = [];
if (file_exists($logFile) && filesize($logFile) > 0) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines) {
        foreach (array_reverse($lines) as $line) {
            $entry = json_decode($line, true);
            if (!is_array($entry)) {
                continue;
            }
            if ($levelFilter !== '' && ($entry['level'] ?? '') !== $levelFilter) {
                continue;
            }
            $allEntries[] = $entry;
        }
    }
}

$totalCount  = count($allEntries);
$totalPages  = max(1, (int) ceil($totalCount / $perPage));
$currentPage = min($currentPage, $totalPages);
$offset      = ($currentPage - 1) * $perPage;
$entries     = array_slice($allEntries, $offset, $perPage);

// List archived log files (newest first)
$archivedFiles = [];
if (is_dir($logDir)) {
    foreach (scandir($logDir, SCANDIR_FLAG_DESCENDING) as $file) {
        if (preg_match('/^app-\d{4}-\d{2}-\d{2}-\d{6}\.log$/', $file)) {
            $path          = $logDir . '/' . $file;
            $archiveLines  = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $archivedFiles[] = [
                'name'    => $file,
                'size'    => filesize($path),
                'entries' => count($archiveLines ?: []),
                'mtime'   => filemtime($path),
            ];
        }
    }
}
