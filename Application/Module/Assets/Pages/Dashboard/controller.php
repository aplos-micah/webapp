<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$assetObj   = AssetsContainer::get('asset');
$stats      = $assetObj->getDashboardStats();
$expiring   = $assetObj->getExpiringWarranties(30, 20);
