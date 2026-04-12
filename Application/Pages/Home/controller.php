<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../Module/CRM/Widgets/DashContacts.php';
require_once __DIR__ . '/../../Module/CRM/Widgets/DashOpenDeals.php';
require_once __DIR__ . '/../../Module/CRM/Widgets/DashLeads.php';

$db = new DB();

$contactsWidget  = new DashContacts($db);
$openDealsWidget = new DashOpenDeals($db);
$leadsWidget     = new DashLeads($db);

$data = [
    'user_name'        => $_SESSION['user_name']  ?? 'there',
    'user_email'       => $_SESSION['user_email'] ?? '',
    'contacts_count'   => $contactsWidget->count(),
    'open_deals_count' => $openDealsWidget->count(),
    'open_deals_value' => $openDealsWidget->totalValue(),
    'leads_count'      => $leadsWidget->count(),
    'contacts_tile'    => $contactsWidget->renderTile(),
    'open_deals_tile'  => $openDealsWidget->renderTile(),
    'leads_tile'       => $leadsWidget->renderTile(),
];
