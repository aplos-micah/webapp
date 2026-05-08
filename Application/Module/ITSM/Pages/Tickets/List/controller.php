<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const ITSM_PER_PAGE = 20;

$ticketObj = ITSMContainer::get('ticket');

$search   = trim($_GET['search']   ?? '');
$sort     = in_array($_GET['sort'] ?? '', Ticket::SORTABLE, true) ? $_GET['sort'] : 'created_at';
$dir      = strtolower($_GET['dir'] ?? '') === 'asc' ? 'asc' : 'desc';
$status   = in_array($_GET['status']   ?? '', array_merge([''], Ticket::STATUSES),   true) ? ($_GET['status'] ?? '')   : '';
$priority = in_array($_GET['priority'] ?? '', array_merge([''], Ticket::PRIORITIES), true) ? ($_GET['priority'] ?? '') : '';
$type     = in_array($_GET['type']     ?? '', array_merge([''], Ticket::TYPES),      true) ? ($_GET['type'] ?? '')     : '';

$totalCount  = $ticketObj->count($search, $status, $priority, $type);
$totalPages  = max(1, (int) ceil($totalCount / ITSM_PER_PAGE));
$currentPage = max(1, min((int) ($_GET['page'] ?? 1), $totalPages));
$offset      = ($currentPage - 1) * ITSM_PER_PAGE;
$tickets     = $ticketObj->findAll(ITSM_PER_PAGE, $offset, $search, $sort, $dir, $status, $priority, $type);
