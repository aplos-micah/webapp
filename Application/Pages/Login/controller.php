<?php

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Email is already sanitized by Sanitization::sanitizeAll() before dispatch.
    // Password: reverse htmlspecialchars so special characters survive the
    // global sanitizer and still match the stored bcrypt hash.
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

    } else {
        try {
            $user = Container::get('user')->authenticate($email, $password);

            if ($user) {
                if ($user['email_verified_at'] === null) {
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    $_SESSION['_flash'] = [
                        'type'    => 'warning',
                        'message' => 'Please verify your email address before signing in. Check your inbox or request a new link below.',
                    ];
                    $_SESSION['pending_verify_email'] = $user['email'];
                    return Response::redirect('/check-email');
                }

                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                session_regenerate_id(true);
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_name']  = $user['name']      ?? '';
                $_SESSION['user_email'] = $user['email']     ?? '';
                $_SESSION['user_type']  = $user['user_type']  ?? 'free';
                $tiers = $user['module_tiers'] ?? [];
                $_SESSION['module_access'] = $tiers;
                foreach ($tiers as $mod => $tier) {
                    $_SESSION['module_' . $mod] = $tier;
                }
                if (!empty($_SESSION['pending_invite'])) {
                    $pendingToken = $_SESSION['pending_invite'];
                    unset($_SESSION['pending_invite']);
                    return Response::redirect('/invite?token=' . urlencode($pendingToken));
                }
                if (!empty($_SESSION['oauth_pending'])) {
                    return Response::redirect('/authorize');
                }
                return Response::redirect('/home');
            }

            // Intentionally vague — do not reveal whether the email exists.
            Logger::getInstance()->warning('Authentication failed', [
                'email' => $email,
                'ip'    => $_SERVER['REMOTE_ADDR'] ?? '',
            ]);
            $error = 'The email address or password you entered is incorrect.';

        } catch (Throwable $e) {
            Logger::getInstance()->error('Login exception', [
                'message' => $e->getMessage(),
                'email'   => $email,
            ]);
            $error = 'Unable to sign in right now. Please try again shortly.';
        }
    }
}
