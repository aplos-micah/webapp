<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$activityObj = CRMContainer::get('activity');

$weeklyData  = $activityObj->countByWeekThisQuarter();
$avgCost     = $activityObj->averageCostThisQuarter();
$quarterTotals = $activityObj->totalThisQuarter();

$chartLabels = json_encode(array_column($weeklyData, 'week_label'));
$chartCounts = json_encode(array_column($weeklyData, 'count'));

$pageTitle = 'CRM Dashboard';
