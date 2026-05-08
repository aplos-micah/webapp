<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const PROJECTS_PER_PAGE = 20;

$projectObj = ProjectsContainer::get('project');

$search   = trim($_GET['search']   ?? '');
$sort     = in_array($_GET['sort'] ?? '', Project::SORTABLE, true) ? $_GET['sort'] : 'created_at';
$dir      = strtolower($_GET['dir'] ?? '') === 'asc' ? 'asc' : 'desc';
$status   = in_array($_GET['status']   ?? '', array_merge([''], Project::STATUSES),   true) ? ($_GET['status']   ?? '') : '';
$phase    = in_array($_GET['phase']    ?? '', array_merge([''], Project::PHASES),      true) ? ($_GET['phase']    ?? '') : '';
$priority = in_array($_GET['priority'] ?? '', array_merge([''], Project::PRIORITIES),  true) ? ($_GET['priority'] ?? '') : '';

$totalCount  = $projectObj->count($search, $status, $phase, $priority);
$totalPages  = max(1, (int) ceil($totalCount / PROJECTS_PER_PAGE));
$currentPage = max(1, min((int) ($_GET['page'] ?? 1), $totalPages));
$offset      = ($currentPage - 1) * PROJECTS_PER_PAGE;
$projects    = $projectObj->findAll(PROJECTS_PER_PAGE, $offset, $search, $sort, $dir, $status, $phase, $priority);
