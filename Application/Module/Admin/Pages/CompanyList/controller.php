<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

const COMPANIES_PER_PAGE = 25;

$companyObj = AdminContainer::get('admin_company');

// ── Handle edit POST ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'update_company') {
    $result = $companyObj->updateByAdmin((int) ($_POST['company_id'] ?? 0), $_POST);
    $_SESSION['_flash'] = $result['ok']
        ? ['type' => 'success', 'message' => 'Company updated.']
        : ['type' => 'warning', 'message' => $result['error']];
    return Response::redirect('/admin/companylist?' . http_build_query(array_filter([
        'search' => trim($_POST['_search'] ?? ''),
        'sort'   => $_POST['_sort']   ?? '',
        'dir'    => $_POST['_dir']    ?? '',
        'page'   => $_POST['_page']   ?? '',
    ])));
}

$search = trim($_GET['search'] ?? '');
$sort   = in_array($_GET['sort'] ?? '', AdminCompany::SORTABLE, true) ? $_GET['sort'] : 'name';
$dir    = strtolower($_GET['dir'] ?? '') === 'desc' ? 'desc' : 'asc';

$totalCount  = $companyObj->count($search);
$totalPages  = max(1, (int) ceil($totalCount / COMPANIES_PER_PAGE));
$currentPage = max(1, min((int) ($_GET['page'] ?? 1), $totalPages));
$offset      = ($currentPage - 1) * COMPANIES_PER_PAGE;
$companies   = $companyObj->findAll(COMPANIES_PER_PAGE, $offset, $search, $sort, $dir);
