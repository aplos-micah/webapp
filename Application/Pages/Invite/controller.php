<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$token      = trim($_GET['token'] ?? '');
$invitation = $error = null;

if ($token === '') {
    $data = ['invitation' => null, 'error' => 'Invalid or missing invitation link.'];
    return;
}

$invitationObj = Container::get('invitation');
$invitation    = $invitationObj->findByToken($token);

// Redirect unauthenticated users to login or register, preserving the token
if (empty($_SESSION['user_id'])) {
    $_SESSION['pending_invite'] = $token;

    if ($invitation) {
        $existingUser = Container::get('user')->findByEmail($invitation['invited_email']);
        if ($existingUser) {
            $_SESSION['_flash'] = ['type' => 'info', 'message' => 'Please log in to accept your invitation.'];
            return Response::redirect('/login');
        }
    }

    $_SESSION['_flash'] = ['type' => 'info', 'message' => 'Create an account to accept your invitation.'];
    return Response::redirect('/register');
}

$userId      = (int) $_SESSION['user_id'];
$userRow     = Container::get('user')->findById($userId);
$userEmail   = strtolower(trim($userRow['email'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'accept_invite') {

    if (!$invitation) {
        $error = 'This invitation link is invalid.';
    } elseif ($invitation['accepted_at'] !== null) {
        $error = 'This invitation has already been accepted.';
    } elseif (strtotime($invitation['expires_at']) < time()) {
        $error = 'This invitation has expired.';
    } elseif ($userEmail !== strtolower($invitation['invited_email'])) {
        $error = 'This invitation was sent to a different email address (' . htmlspecialchars($invitation['invited_email'], ENT_QUOTES, 'UTF-8') . '). Please log in with that account.';
    } elseif ($userRow['company_id']) {
        $error = 'You are already associated with a company.';
    } else {
        $invitationObj->accept((int) $invitation['id']);
        Container::get('user')->setCompany($userId, (int) $invitation['company_id']);
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Welcome! You have joined ' . htmlspecialchars($invitation['company_name'], ENT_QUOTES, 'UTF-8') . '.'];
        return Response::redirect('/company');
    }
}

$data = compact('invitation', 'error', 'userEmail');
