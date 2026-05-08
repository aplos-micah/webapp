<?php

$ticketObj  = ITSMContainer::get('ticket');
$stats      = $ticketObj->getDashboardStats();
$myTickets  = $ticketObj->getMyTickets((int) ($_SESSION['user_id'] ?? 0));
$recent     = $ticketObj->getRecentActivity(8);
$unassigned = $ticketObj->getUnassigned(20);

$pageTitle = 'ITSM Dashboard';
