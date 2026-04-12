<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../../../Objects/Opportunity.php';

const PER_PAGE = 20;

$oppObj = new Opportunity(new DB());

$search = trim($_GET['search'] ?? '');
$sort   = in_array($_GET['sort'] ?? '', Opportunity::SORTABLE, true) ? $_GET['sort'] : 'opportunity_name';
$dir    = strtolower($_GET['dir'] ?? '') === 'desc' ? 'desc' : 'asc';

$totalCount  = $oppObj->count($search);
$totalPages  = max(1, (int) ceil($totalCount / PER_PAGE));
$currentPage = max(1, min((int) ($_GET['page'] ?? 1), $totalPages));
$offset      = ($currentPage - 1) * PER_PAGE;
$opportunities = $oppObj->findAll(PER_PAGE, $offset, $search, $sort, $dir);
