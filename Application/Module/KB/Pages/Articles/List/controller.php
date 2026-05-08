<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const KB_PER_PAGE = 20;

$articleObj  = KBContainer::get('article');
$currentUser = (int) ($_SESSION['user_id'] ?? 0);

$search   = trim($_GET['search']   ?? '');
$sort     = in_array($_GET['sort'] ?? '', Article::SORTABLE, true) ? $_GET['sort'] : 'updated_at';
$dir      = strtolower($_GET['dir'] ?? '') === 'asc' ? 'asc' : 'desc';
$status   = in_array($_GET['status']   ?? '', array_merge([''], Article::STATUSES),   true) ? ($_GET['status']   ?? '') : '';
$category = in_array($_GET['category'] ?? '', array_merge([''], Article::CATEGORIES), true) ? ($_GET['category'] ?? '') : '';

// Default to Published-only unless a status filter is explicitly set
$effectiveStatus = $status !== '' ? $status : 'Published';

$totalCount  = $articleObj->count($search, $effectiveStatus, $category);
$totalPages  = max(1, (int) ceil($totalCount / KB_PER_PAGE));
$currentPage = max(1, min((int) ($_GET['page'] ?? 1), $totalPages));
$offset      = ($currentPage - 1) * KB_PER_PAGE;
$articles    = $articleObj->findAll(KB_PER_PAGE, $offset, $search, $sort, $dir, $effectiveStatus, $category);
