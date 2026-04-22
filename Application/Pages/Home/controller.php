<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$contactsWidget  = CRMContainer::get('dash_contacts');
$openDealsWidget = CRMContainer::get('dash_deals');
$leadsWidget     = CRMContainer::get('dash_leads');

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
