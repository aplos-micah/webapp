<?php

$error   = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email'] ?? '');
    $password = html_entity_decode(
        $_POST['password'] ?? '',
        ENT_QUOTES | ENT_HTML5,
        'UTF-8'
    );

    if ($email === '' || $password === '') {
        $error = 'Please enter your email address and password.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';

    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';

    } else {
        try {
            // Derive a display name from the email prefix (can be updated later)
            $name   = ucfirst(strtok($email, '@'));
            $result = Container::get('user')->register($name, $email, $password);

            if ($result['ok']) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                session_regenerate_id(true);
                $_SESSION['user_id']    = $result['user_id'];
                $_SESSION['user_name']  = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_type']  = 'free';
                $_SESSION['module_crm'] = 'Free';
                if (!empty($_SESSION['pending_invite'])) {
                    $pendingToken = $_SESSION['pending_invite'];
                    unset($_SESSION['pending_invite']);
                    return Response::redirect('/invite?token=' . urlencode($pendingToken));
                }
                return Response::redirect('/home');
            }

            $error = $result['error'];

        } catch (Throwable $e) {
            Logger::getInstance()->error('Registration exception', [
                'message' => $e->getMessage(),
                'email'   => $email,
            ]);
            $error = 'Unable to create your account right now. Please try again shortly.';
        }
    }
}
