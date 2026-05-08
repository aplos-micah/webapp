<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const ASSETS_PER_PAGE = 20;

$assetObj = AssetsContainer::get('asset');

$search = trim($_GET['search'] ?? '');
$sort   = in_array($_GET['sort'] ?? '', Asset::SORTABLE, true) ? $_GET['sort'] : 'created_at';
$dir    = strtolower($_GET['dir'] ?? '') === 'asc' ? 'asc' : 'desc';
$status = in_array($_GET['status'] ?? '', array_merge([''], Asset::STATUSES), true) ? ($_GET['status'] ?? '') : '';
$type   = in_array($_GET['type']   ?? '', array_merge([''], Asset::TYPES),    true) ? ($_GET['type']   ?? '') : '';

$totalCount  = $assetObj->count($search, $status, $type);
$totalPages  = max(1, (int) ceil($totalCount / ASSETS_PER_PAGE));
$currentPage = max(1, min((int) ($_GET['page'] ?? 1), $totalPages));
$offset      = ($currentPage - 1) * ASSETS_PER_PAGE;
$assets      = $assetObj->findAll(ASSETS_PER_PAGE, $offset, $search, $sort, $dir, $status, $type);
