<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return Response::redirect('/check-email');
}

$email   = strtolower(trim($_POST['email'] ?? ''));
$userObj = Container::get('user');

if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $user = $userObj->findByEmail($email);

    if ($user && $user['email_verified_at'] === null) {
        $plainToken = $userObj->createEmailVerificationToken($user['id']);
        $appUrl     = rtrim(getenv('APP_URL') ?: 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'), '/');
        $verifyUrl  = $appUrl . '/verify-email?token=' . urlencode($plainToken);
        $safeName   = htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8');
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
    <p style='margin:0 0 1rem;color:#444;font-size:0.95rem;'>Hi {$safeName}, here is a new verification link for your AplosCRM account.</p>
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

        $plain = "Hi {$user['name']}, here is a new verification link for your AplosCRM account.\n\n"
               . "Verify your email here:\n{$verifyUrl}\n\n"
               . "This link expires in 24 hours.";

        MailerFactory::make()->send(
            $email,
            $user['name'],
            'Verify your AplosCRM email address',
            $html,
            $plain
        );

        $_SESSION['pending_verify_email'] = $email;
    }
}

// Always show success to avoid email enumeration
$_SESSION['_flash'] = [
    'type'    => 'info',
    'message' => 'If that address matches an unverified account, a new verification link is on its way.',
];

return Response::redirect('/check-email');
