<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId    = (int) ($_SESSION['user_id'] ?? 0);
$userObj   = Container::get('user');
$companyObj = Container::get('company');

$userRow   = $userObj->findById($userId);
$companyId = (int) ($userRow['company_id'] ?? 0) ?: null;
$company   = $companyId ? $companyObj->getById($companyId) : null;

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['_action'] ?? '';
    $fields = [
        'name'    => $_POST['name']    ?? '',
        'phone'   => $_POST['phone']   ?? '',
        'email'   => $_POST['email']   ?? '',
        'address' => $_POST['address'] ?? '',
        'city'    => $_POST['city']    ?? '',
        'state'   => $_POST['state']   ?? '',
        'zip'     => $_POST['zip']     ?? '',
        'website' => $_POST['website'] ?? '',
    ];

    if ($err = Validator::required(trim($fields['name']), 'Company name')) {
        $error = $err;
    } elseif ($action === 'create_company') {
        $newId = $companyObj->create($fields);
        $userObj->setCompany($userId, $newId);
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Company created successfully.'];
        return Response::redirect('/company');
    } elseif ($action === 'update_company') {
        $companyObj->update($companyId, $fields);
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Company updated successfully.'];
        return Response::redirect('/company');
    }
}

$companyUsers = $companyId ? $userObj->findByCompany($companyId) : [];

$inviteError = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'send_invite') {
    if (!$companyId) {
        $inviteError = 'You must create a company before sending invitations.';
    } else {
        $result = Container::get('invitation')->invite(
            $companyId,
            $userId,
            $userRow['email'],
            $_POST['invited_email'] ?? ''
        );

        if ($result['ok']) {
            $appUrl    = rtrim(getenv('APP_URL') ?: 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'), '/');
            $inviteUrl = $appUrl . '/invite?token=' . urlencode($result['token']);

            $inviterName  = htmlspecialchars($userRow['name'],    ENT_QUOTES, 'UTF-8');
            $companyName  = htmlspecialchars($company['name'],    ENT_QUOTES, 'UTF-8');
            $invitedEmail = htmlspecialchars($_POST['invited_email'] ?? '', ENT_QUOTES, 'UTF-8');

            $html = "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body style='margin:0;padding:0;background:#f4f6f8;font-family:Arial,sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0' style='background:#f4f6f8;padding:2rem 0;'>
<tr><td align='center'>
<table width='520' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:8px;overflow:hidden;'>
  <tr><td style='background:#0B3D6B;padding:1.5rem 2rem;'>
    <span style='color:#2ECC71;font-size:1.4rem;font-weight:700;'>Aplos</span><span style='color:#ffffff;font-size:1.4rem;font-weight:300;'>CRM</span>
  </td></tr>
  <tr><td style='padding:2rem;'>
    <h1 style='margin:0 0 1rem;font-size:1.15rem;color:#0B3D6B;'>You've been invited to join {$companyName}</h1>
    <p style='margin:0 0 1rem;color:#444;font-size:0.95rem;'>{$inviterName} has invited you to join their company on AplosCRM.</p>
    <p style='margin:0 0 1.5rem;color:#444;font-size:0.95rem;'>Click the button below to accept the invitation. This link expires in 7 days.</p>
    <table cellpadding='0' cellspacing='0'><tr><td style='background:#2ECC71;border-radius:6px;'>
      <a href='{$inviteUrl}' style='display:inline-block;padding:0.75rem 1.75rem;color:#ffffff;font-weight:700;font-size:0.95rem;text-decoration:none;'>Accept Invitation</a>
    </td></tr></table>
    <p style='margin:1.5rem 0 0;color:#888;font-size:0.8rem;'>Or copy this link: <a href='{$inviteUrl}' style='color:#0B3D6B;'>{$inviteUrl}</a></p>
  </td></tr>
  <tr><td style='background:#f4f6f8;padding:1rem 2rem;text-align:center;'>
    <p style='margin:0;color:#aaa;font-size:0.78rem;'>You received this because {$inviterName} invited {$invitedEmail}.</p>
  </td></tr>
</table>
</td></tr></table>
</body></html>";

            $plain = "You've been invited to join {$companyName} on AplosCRM.\n\n"
                   . "{$inviterName} has invited you. Accept here:\n{$inviteUrl}\n\n"
                   . "This link expires in 7 days.";

            MailerFactory::make()->send(
                $_POST['invited_email'],
                '',
                "You're invited to join {$companyName} on AplosCRM",
                $html,
                $plain
            );

            $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Invitation sent to ' . htmlspecialchars($_POST['invited_email'], ENT_QUOTES, 'UTF-8') . '.'];
            return Response::redirect('/company');
        }

        $inviteError = $result['error'];
    }
}

$invitations = $companyId ? Container::get('invitation')->getByCompany($companyId) : [];

$data = compact('company', 'error', 'companyUsers', 'invitations', 'inviteError', 'userRow');
