<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../Objects/Account.php';

const PER_PAGE = 20;

$accountObj = new Account(new DB());

$search = trim($_GET['search'] ?? '');
$sort   = in_array($_GET['sort'] ?? '', Account::SORTABLE, true) ? $_GET['sort'] : 'name';
$dir    = strtolower($_GET['dir'] ?? '') === 'desc' ? 'desc' : 'asc';

$totalCount  = $accountObj->count($search);
$totalPages  = max(1, (int) ceil($totalCount / PER_PAGE));
$currentPage = max(1, min((int) ($_GET['page'] ?? 1), $totalPages));
$offset      = ($currentPage - 1) * PER_PAGE;
$accounts    = $accountObj->findAll(PER_PAGE, $offset, $search, $sort, $dir);
