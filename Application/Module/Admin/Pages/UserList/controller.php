<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

const USERS_PER_PAGE = 25;

$userObj = Container::get('admin_user');

// ── Handle edit POST ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'update_user') {
    $result = $userObj->updateByAdmin((int) ($_POST['user_id'] ?? 0), $_POST);
    $_SESSION['_flash'] = $result['ok']
        ? ['type' => 'success', 'message' => 'User updated.']
        : ['type' => 'warning', 'message' => $result['error']];
    return Response::redirect('/admin/userlist?' . http_build_query(array_filter([
        'search' => trim($_POST['_search'] ?? ''),
        'sort'   => $_POST['_sort']   ?? '',
        'dir'    => $_POST['_dir']    ?? '',
        'page'   => $_POST['_page']   ?? '',
    ])));
}

$search = trim($_GET['search'] ?? '');
$sort   = in_array($_GET['sort'] ?? '', AdminUser::SORTABLE, true) ? $_GET['sort'] : 'name';
$dir    = strtolower($_GET['dir'] ?? '') === 'desc' ? 'desc' : 'asc';

$totalCount  = $userObj->count($search);
$totalPages  = max(1, (int) ceil($totalCount / USERS_PER_PAGE));
$currentPage = max(1, min((int) ($_GET['page'] ?? 1), $totalPages));
$offset      = ($currentPage - 1) * USERS_PER_PAGE;
$users       = $userObj->findAll(USERS_PER_PAGE, $offset, $search, $sort, $dir);
