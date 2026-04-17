<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

const PER_PAGE = 20;

$contactObj = Container::get('contact');

$search = trim($_GET['search'] ?? '');
$sort   = in_array($_GET['sort'] ?? '', Contact::SORTABLE, true) ? $_GET['sort'] : 'last_name';
$dir    = strtolower($_GET['dir'] ?? '') === 'desc' ? 'desc' : 'asc';

$totalCount  = $contactObj->count($search);
$totalPages  = max(1, (int) ceil($totalCount / PER_PAGE));
$currentPage = max(1, min((int) ($_GET['page'] ?? 1), $totalPages));
$offset      = ($currentPage - 1) * PER_PAGE;
$contacts    = $contactObj->findAll(PER_PAGE, $offset, $search, $sort, $dir);
