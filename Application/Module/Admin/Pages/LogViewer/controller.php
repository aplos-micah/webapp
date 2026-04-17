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

$returnQs = http_build_query(array_filter([
    'level'    => $levelFilter,
    'per_page' => $perPage !== 100 ? $perPage : null,
    'page'     => $currentPage > 1 ? $currentPage : null,
]));
$returnUrl = '/admin/logviewer' . ($returnQs ? '?' . $returnQs : '');

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

    if ($action === 'delete_selected') {
        $selected = array_map('intval', $_POST['selected'] ?? []);
        if (!empty($selected) && file_exists($logFile)) {
            $lines    = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            $kept     = array_filter($lines, fn($i) => !in_array($i, $selected, true), ARRAY_FILTER_USE_KEY);
            file_put_contents($logFile, implode(PHP_EOL, $kept) . (empty($kept) ? '' : PHP_EOL));
            $count = count($selected);
            $_SESSION['_flash'] = ['type' => 'success', 'message' => $count . ' entr' . ($count === 1 ? 'y' : 'ies') . ' deleted.'];
        }
        header('Location: ' . $returnUrl);
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

    if ($action === 'delete_archives') {
        $filenames = $_POST['filenames'] ?? [];
        $deleted   = 0;
        foreach ($filenames as $filename) {
            $filename = basename((string) $filename);
            if (preg_match('/^app-\d{4}-\d{2}-\d{2}-\d{6}\.log$/', $filename)) {
                $filepath = $logDir . '/' . $filename;
                if (file_exists($filepath)) {
                    unlink($filepath);
                    $deleted++;
                }
            }
        }
        if ($deleted > 0) {
            $_SESSION['_flash'] = ['type' => 'success', 'message' => $deleted . ' archived log' . ($deleted === 1 ? '' : 's') . ' deleted.'];
        }
        header('Location: /admin/logviewer');
        exit;
    }
}

// Read & filter current log (newest first), tracking original line index for deletion
$allEntries = [];
if (file_exists($logFile) && filesize($logFile) > 0) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines) {
        $totalLines = count($lines);
        foreach (array_reverse($lines) as $reversedIdx => $line) {
            $entry = json_decode($line, true);
            if (!is_array($entry)) {
                continue;
            }
            if ($levelFilter !== '' && ($entry['level'] ?? '') !== $levelFilter) {
                continue;
            }
            $entry['_idx'] = $totalLines - 1 - $reversedIdx;
            $allEntries[]  = $entry;
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
    foreach (scandir($logDir, SCANDIR_SORT_DESCENDING) as $file) {
        if (preg_match('/^app-\d{4}-\d{2}-\d{2}-\d{6}\.log$/', $file)) {
            $path         = $logDir . '/' . $file;
            $archiveLines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $archivedFiles[] = [
                'name'    => $file,
                'size'    => filesize($path),
                'entries' => count($archiveLines ?: []),
                'mtime'   => filemtime($path),
            ];
        }
    }
}
