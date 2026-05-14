<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const ACTIVITIES_PER_PAGE = 20;

$activityObj = CRMContainer::get('activity');

$search = trim($_GET['search'] ?? '');
$sort   = in_array($_GET['sort'] ?? '', Activity::SORTABLE, true) ? $_GET['sort'] : 'activity_date';
$dir    = strtolower($_GET['dir'] ?? '') === 'asc' ? 'asc' : 'desc';

$totalCount  = $activityObj->count($search);
$totalPages  = max(1, (int) ceil($totalCount / ACTIVITIES_PER_PAGE));
$currentPage = max(1, min((int) ($_GET['page'] ?? 1), $totalPages));
$offset      = ($currentPage - 1) * ACTIVITIES_PER_PAGE;
$activities  = $activityObj->findAll(ACTIVITIES_PER_PAGE, $offset, $search, $sort, $dir);

$pageTitle = 'Activities';
