<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$profileError   = null;
$passwordError  = null;

$userObj = Container::get('user');
$profile = $userObj->findById((int) ($_SESSION['user_id'] ?? 0));

if (!$profile) {
    header('Location: /login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['_action'] ?? '';

    if ($action === 'update_profile') {
        $result = $userObj->updateProfile((int) $profile['id'], [
            'name'      => $_POST['name']      ?? '',
            'phone'     => $_POST['phone']     ?? '',
            'job_title' => $_POST['job_title'] ?? '',
            'timezone'  => $_POST['timezone']  ?? '',
        ]);

        if ($result['ok']) {
            // Refresh session name in case it changed
            $_SESSION['user_name'] = trim($_POST['name'] ?? $_SESSION['user_name']);
            $profile = $userObj->findById((int) $profile['id']);

            if (session_status() === PHP_SESSION_NONE) { session_start(); }
            $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Profile updated successfully.'];
            header('Location: /profile');
            exit;
        }

        $profileError = $result['error'];

    } elseif ($action === 'change_password') {
        $current = html_entity_decode($_POST['current_password'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $new     = html_entity_decode($_POST['new_password']     ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $confirm = html_entity_decode($_POST['confirm_password'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if ($new !== $confirm) {
            $passwordError = 'New password and confirmation do not match.';
        } else {
            $result = $userObj->changePassword((int) $profile['id'], $current, $new);

            if ($result['ok']) {
                $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Password changed successfully.'];
                header('Location: /profile');
                exit;
            }

            $passwordError = $result['error'];
        }
    }
}
