<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$accountObj = CRMContainer::get('account');

$search = trim($_GET['search'] ?? '');
$sort   = in_array($_GET['sort'] ?? '', Account::SORTABLE, true) ? $_GET['sort'] : 'name';
$dir    = strtolower($_GET['dir'] ?? '') === 'desc' ? 'desc' : 'asc';

$totalCount  = $accountObj->count($search);
$totalPages  = max(1, (int) ceil($totalCount / 20));
$currentPage = max(1, min((int) ($_GET['page'] ?? 1), $totalPages));
$offset      = ($currentPage - 1) * 20;
$accounts    = $accountObj->findAll(20, $offset, $search, $sort, $dir);
