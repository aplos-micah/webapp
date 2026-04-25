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
                $userObj    = Container::get('user');
                $plainToken = $userObj->createEmailVerificationToken($result['user_id']);
                $appUrl     = rtrim(getenv('APP_URL') ?: 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'), '/');
                $verifyUrl  = $appUrl . '/verify-email?token=' . urlencode($plainToken);
                $safeName   = htmlspecialchars($name,  ENT_QUOTES, 'UTF-8');
                $safeEmail  = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

                $html = "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body style='margin:0;padding:0;background:#f4f6f8;font-family:Arial,sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0' style='background:#f4f6f8;padding:2rem 0;'>
<tr><td align='center'>
<table width='520' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:8px;overflow:hidden;'>
  <tr><td style='background:#0B3D6B;padding:1.5rem 2rem;'>
    <span style='color:#2ECC71;font-size:1.4rem;font-weight:700;'>Aplos</span><span style='color:#ffffff;font-size:1.4rem;font-weight:300;'>CRM</span>
  </td></tr>
  <tr><td style='padding:2rem;'>
    <h1 style='margin:0 0 1rem;font-size:1.15rem;color:#0B3D6B;'>Confirm your email address</h1>
    <p style='margin:0 0 1rem;color:#444;font-size:0.95rem;'>Hi {$safeName}, thanks for creating an AplosCRM account. Click the button below to verify your email and activate your account.</p>
    <p style='margin:0 0 1.5rem;color:#444;font-size:0.95rem;'>This link expires in 24 hours.</p>
    <table cellpadding='0' cellspacing='0'><tr><td style='background:#2ECC71;border-radius:6px;'>
      <a href='{$verifyUrl}' style='display:inline-block;padding:0.75rem 1.75rem;color:#ffffff;font-weight:700;font-size:0.95rem;text-decoration:none;'>Verify Email Address</a>
    </td></tr></table>
    <p style='margin:1.5rem 0 0;color:#888;font-size:0.8rem;'>Or copy this link: <a href='{$verifyUrl}' style='color:#0B3D6B;'>{$verifyUrl}</a></p>
  </td></tr>
  <tr><td style='background:#f4f6f8;padding:1rem 2rem;text-align:center;'>
    <p style='margin:0;color:#aaa;font-size:0.78rem;'>You received this because {$safeEmail} was used to create an AplosCRM account.</p>
  </td></tr>
</table>
</td></tr></table>
</body></html>";

                $plain = "Hi {$name}, thanks for creating an AplosCRM account.\n\n"
                       . "Verify your email address here:\n{$verifyUrl}\n\n"
                       . "This link expires in 24 hours.";

                MailerFactory::make()->send(
                    $email,
                    $name,
                    'Verify your AplosCRM email address',
                    $html,
                    $plain
                );

                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['_flash'] = [
                    'type'    => 'info',
                    'message' => 'We sent a verification link to ' . $email . '. Please check your inbox to activate your account.',
                ];
                $_SESSION['pending_verify_email'] = $email;
                return Response::redirect('/check-email');
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
