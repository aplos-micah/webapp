<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$articleObj = KBContainer::get('article');
$db         = Container::db();

// Status counts
$statusRows = $db->query(
    "SELECT status, COUNT(*) AS cnt FROM kb_articles GROUP BY status"
);
$byStatus = array_column($statusRows, 'cnt', 'status');

$totalPublished = (int) ($byStatus['Published'] ?? 0);
$totalDraft     = (int) ($byStatus['Draft']     ?? 0);
$totalArchived  = (int) ($byStatus['Archived']  ?? 0);
$totalAll       = $totalPublished + $totalDraft + $totalArchived;

// Category breakdown (published only)
$categoryRows = $db->query(
    "SELECT category, COUNT(*) AS cnt
       FROM kb_articles
      WHERE status = 'Published'
        AND category IS NOT NULL
      GROUP BY category
      ORDER BY cnt DESC"
);

// Top viewed articles
$topViewed = $articleObj->findAll(5, 0, '', 'view_count', 'desc', 'Published');

// Recently updated published articles
$recentlyUpdated = $articleObj->findAll(5, 0, '', 'updated_at', 'desc', 'Published');

// Daily views for the last 14 days, split by source
$dailyViews  = $articleObj->dailyViewsLastTwoWeeks();
$chartLabels = json_encode($dailyViews['labels']);
$chartWeb    = json_encode($dailyViews['web']);
$chartRemote = json_encode($dailyViews['remote']);
$chartAI     = json_encode($dailyViews['ai']);

$pageTitle = 'Knowledge Base Dashboard';
