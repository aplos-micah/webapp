<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$flash = $_SESSION['_flash'] ?? null;
unset($_SESSION['_flash']);

$pendingEmail = $_SESSION['pending_verify_email'] ?? null;
