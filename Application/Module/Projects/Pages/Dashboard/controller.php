<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$projectObj = ProjectsContainer::get('project');
$stats      = $projectObj->getDashboardStats();
$myProjects = $projectObj->getMyProjects((int) ($_SESSION['user_id'] ?? 0));
$overdue    = $projectObj->getOverdue(20);
