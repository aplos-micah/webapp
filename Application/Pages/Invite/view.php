<?php
$invitation = $data['invitation'] ?? null;
$error      = $data['error']      ?? null;
$userEmail  = $data['userEmail']  ?? null;
$token      = trim($_GET['token'] ?? '');

$isExpired  = $invitation && $invitation['accepted_at'] === null && strtotime($invitation['expires_at']) < time();
$isAccepted = $invitation && $invitation['accepted_at'] !== null;
$isValid    = $invitation && !$isExpired && !$isAccepted;
?>

<div class="page-center">
<div class="card card--narrow">

    <div class="icon-header">
        <i class="fa-solid fa-building icon-hero" aria-hidden="true"></i>
    </div>

    <?php if ($error): ?>
        <h1 class="invite-title">Invalid Invitation</h1>
        <p class="invite-body"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <a href="/login" class="btn btn--primary btn--block">Go to Login</a>

    <?php elseif ($isAccepted): ?>
        <h1 class="invite-title">Already Accepted</h1>
        <p class="invite-body">This invitation has already been accepted.</p>
        <a href="/company" class="btn btn--primary btn--block">Go to My Company</a>

    <?php elseif ($isExpired): ?>
        <h1 class="invite-title">Invitation Expired</h1>
        <p class="invite-body--flush">This invitation link expired on <?= date('F j, Y', strtotime($invitation['expires_at'])) ?>. Ask the sender to invite you again.</p>

    <?php elseif ($isValid): ?>
        <h1 class="invite-title">You've Been Invited</h1>
        <p class="invite-body">
            <strong><?= htmlspecialchars($invitation['inviter_name'], ENT_QUOTES, 'UTF-8') ?></strong>
            has invited you to join
            <strong><?= htmlspecialchars($invitation['company_name'], ENT_QUOTES, 'UTF-8') ?></strong>.
        </p>

        <?php if ($userEmail !== strtolower($invitation['invited_email'])): ?>
        <div class="alert alert--warning mb-md" role="alert">
            <span class="alert__icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
            <div class="alert__body">
                This invite was sent to <strong><?= htmlspecialchars($invitation['invited_email'], ENT_QUOTES, 'UTF-8') ?></strong>.
                You are logged in as <strong><?= htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8') ?></strong>.
                Please log in with the correct account to accept.
            </div>
        </div>
        <a href="/login" class="btn btn--ghost btn--block">Switch Account</a>
        <?php else: ?>
        <form method="POST" action="/invite?token=<?= htmlspecialchars(urlencode($token), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="_action" value="accept_invite">
            <button type="submit" class="btn btn--primary btn--block">
                <i class="fa-solid fa-check" aria-hidden="true"></i>
                Accept &amp; Join <?= htmlspecialchars($invitation['company_name'], ENT_QUOTES, 'UTF-8') ?>
            </button>
        </form>
        <?php endif; ?>

    <?php else: ?>
        <h1 class="invite-title">Invalid Invitation</h1>
        <p class="invite-body--flush">This invitation link is not valid.</p>
    <?php endif; ?>

</div>
</div>
