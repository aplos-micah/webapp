<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$plainToken = trim($_GET['token'] ?? '');

if ($plainToken === '') {
    $_SESSION['_flash'] = ['type' => 'warning', 'message' => 'Invalid verification link. Please request a new one.'];
    return Response::redirect('/check-email');
}

$user = Container::get('user')->verifyEmail($plainToken);

if (!$user) {
    $_SESSION['_flash'] = [
        'type'    => 'warning',
        'message' => 'This verification link has expired or already been used. Enter your email below to request a new one.',
    ];
    return Response::redirect('/check-email');
}

// Email verified — log the user in
session_regenerate_id(true);
$_SESSION['user_id']    = $user['id'];
$_SESSION['user_name']  = $user['name']      ?? '';
$_SESSION['user_email'] = $user['email']     ?? '';
$_SESSION['user_type']  = $user['user_type']  ?? 'free';
$_SESSION['module_crm'] = $user['Module_CRM'] ?? 'Free';
unset($_SESSION['pending_verify_email']);

$redirectTo = '/home';
if (!empty($_SESSION['pending_invite'])) {
    $pendingToken = $_SESSION['pending_invite'];
    unset($_SESSION['pending_invite']);
    $redirectTo = '/invite?token=' . urlencode($pendingToken);
}

// Fall through to the thank-you view; it handles the timed redirect.
$verifiedName = $user['name'];
$verifiedRedirect = $redirectTo;
