<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

const PER_PAGE = 20;

$productObj = Container::get('product');

$search = trim($_GET['search'] ?? '');
$sort   = in_array($_GET['sort'] ?? '', ProductDefinition::SORTABLE, true) ? $_GET['sort'] : 'product_name';
$dir    = strtolower($_GET['dir'] ?? '') === 'desc' ? 'desc' : 'asc';

$totalCount  = $productObj->count($search);
$totalPages  = max(1, (int) ceil($totalCount / PER_PAGE));
$currentPage = max(1, min((int) ($_GET['page'] ?? 1), $totalPages));
$offset      = ($currentPage - 1) * PER_PAGE;
$products    = $productObj->findAll(PER_PAGE, $offset, $search, $sort, $dir);
