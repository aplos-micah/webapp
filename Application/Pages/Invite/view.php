<?php
$invitation = $data['invitation'] ?? null;
$error      = $data['error']      ?? null;
$userEmail  = $data['userEmail']  ?? null;
$token      = trim($_GET['token'] ?? '');

$isExpired  = $invitation && $invitation['accepted_at'] === null && strtotime($invitation['expires_at']) < time();
$isAccepted = $invitation && $invitation['accepted_at'] !== null;
$isValid    = $invitation && !$isExpired && !$isAccepted;
?>

<div style="min-height:60vh;display:flex;align-items:center;justify-content:center;padding:2rem;">
<div class="card" style="max-width:480px;width:100%;padding:2rem;">

    <div style="text-align:center;margin-bottom:1.5rem;">
        <i class="fa-solid fa-building" style="font-size:2rem;color:var(--color-green,#2ECC71);" aria-hidden="true"></i>
    </div>

    <?php if ($error): ?>
        <h1 style="font-size:1.15rem;font-weight:700;margin:0 0 0.5rem;">Invalid Invitation</h1>
        <p style="color:var(--color-text-muted,#666);font-size:0.9rem;margin:0 0 1.5rem;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <a href="/login" class="btn btn--primary" style="width:100%;text-align:center;">Go to Login</a>

    <?php elseif ($isAccepted): ?>
        <h1 style="font-size:1.15rem;font-weight:700;margin:0 0 0.5rem;">Already Accepted</h1>
        <p style="color:var(--color-text-muted,#666);font-size:0.9rem;margin:0 0 1.5rem;">This invitation has already been accepted.</p>
        <a href="/company" class="btn btn--primary" style="width:100%;text-align:center;">Go to My Company</a>

    <?php elseif ($isExpired): ?>
        <h1 style="font-size:1.15rem;font-weight:700;margin:0 0 0.5rem;">Invitation Expired</h1>
        <p style="color:var(--color-text-muted,#666);font-size:0.9rem;margin:0;">This invitation link expired on <?= date('F j, Y', strtotime($invitation['expires_at'])) ?>. Ask the sender to invite you again.</p>

    <?php elseif ($isValid): ?>
        <h1 style="font-size:1.15rem;font-weight:700;margin:0 0 0.25rem;">You've Been Invited</h1>
        <p style="color:var(--color-text-muted,#666);font-size:0.9rem;margin:0 0 1.25rem;">
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
        <a href="/login" class="btn btn--ghost" style="width:100%;text-align:center;">Switch Account</a>
        <?php else: ?>
        <form method="POST" action="/invite?token=<?= htmlspecialchars(urlencode($token), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="_action" value="accept_invite">
            <button type="submit" class="btn btn--primary" style="width:100%;text-align:center;">
                <i class="fa-solid fa-check" aria-hidden="true"></i>
                Accept &amp; Join <?= htmlspecialchars($invitation['company_name'], ENT_QUOTES, 'UTF-8') ?>
            </button>
        </form>
        <?php endif; ?>

    <?php else: ?>
        <h1 style="font-size:1.15rem;font-weight:700;margin:0 0 0.5rem;">Invalid Invitation</h1>
        <p style="color:var(--color-text-muted,#666);font-size:0.9rem;">This invitation link is not valid.</p>
    <?php endif; ?>

</div>
</div>
