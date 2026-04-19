<?php $pageTitle = 'My Profile'; ?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">Account</p>
        <h1 class="dash-header__title">My Profile</h1>
        <p class="dash-header__sub">Manage your personal information and password.</p>
    </div>
</div>

<hr class="divider--green mb-xl">

<div class="profile-layout">

    <!-- ── Profile Information ───────────────────────────────────────── -->
    <div class="card profile-card">

        <h2 class="profile-card__title">
            <i class="fa-solid fa-circle-user" aria-hidden="true"></i>
            Profile Information
        </h2>

        <?php if (!empty($profileError)): ?>
        <div class="alert alert--warning mb-md" role="alert">
            <span class="alert__icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
            <div class="alert__body"><?= htmlspecialchars($profileError, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <?php endif; ?>

        <form method="POST" action="/profile" novalidate>
            <input type="hidden" name="_action" value="update_profile">

            <div class="form-group">
                <label class="form-label" for="name">Full Name</label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    class="input"
                    value="<?= htmlspecialchars($profile['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Your full name"
                    autocomplete="name"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input
                    id="email"
                    type="email"
                    class="input"
                    value="<?= htmlspecialchars($profile['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    disabled
                    aria-describedby="email-hint"
                >
                <span class="form-hint" id="email-hint">Contact an administrator to change your email.</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="job_title">Job Title</label>
                <input
                    id="job_title"
                    type="text"
                    name="job_title"
                    class="input"
                    value="<?= htmlspecialchars($profile['job_title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="e.g. Sales Manager"
                    autocomplete="organization-title"
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">Phone Number</label>
                <input
                    id="phone"
                    type="tel"
                    name="phone"
                    class="input"
                    value="<?= htmlspecialchars($profile['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="e.g. +1 (555) 000-0000"
                    autocomplete="tel"
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="timezone">Timezone</label>
                <select id="timezone" name="timezone" class="input">
                    <?php
                    $timezones = [
                        'America/New_York'    => 'Eastern Time (ET)',
                        'America/Chicago'     => 'Central Time (CT)',
                        'America/Denver'      => 'Mountain Time (MT)',
                        'America/Phoenix'     => 'Mountain Time — Arizona (no DST)',
                        'America/Los_Angeles' => 'Pacific Time (PT)',
                        'America/Anchorage'   => 'Alaska Time (AKT)',
                        'Pacific/Honolulu'    => 'Hawaii Time (HT)',
                        'UTC'                 => 'UTC',
                        'Europe/London'       => 'London (GMT/BST)',
                        'Europe/Paris'        => 'Central European (CET)',
                        'Asia/Dubai'          => 'Dubai (GST)',
                        'Asia/Singapore'      => 'Singapore (SGT)',
                        'Australia/Sydney'    => 'Sydney (AEDT)',
                    ];
                    $current_tz = $profile['timezone'] ?? 'America/Chicago';
                    foreach ($timezones as $tz => $label):
                    ?>
                        <option value="<?= htmlspecialchars($tz, ENT_QUOTES, 'UTF-8') ?>"
                            <?= $current_tz === $tz ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="profile-card__footer">
                <span class="profile-card__meta">
                    Member since <?= date('F j, Y', strtotime($profile['created_at'] ?? 'now')) ?>
                    &nbsp;&middot;&nbsp;
                    <span class="badge badge--info"><?= htmlspecialchars(ucfirst($profile['user_type'] ?? 'free'), ENT_QUOTES, 'UTF-8') ?></span>
                </span>
                <button type="submit" class="btn btn--primary">
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                    Save Changes
                </button>
            </div>

        </form>
    </div>

    <!-- ── Right column: tabbed Company + Password ──────────────────── -->
    <?php $defaultTab = !empty($passwordError) ? 'password' : 'company'; ?>
    <div class="card profile-card" id="profile-right-card">

        <!-- Tab bar -->
        <div style="display:flex;gap:0;border-bottom:1px solid var(--color-sky);margin-bottom:var(--space-md);">
            <button type="button" class="profile-tab<?= $defaultTab === 'company' ? ' profile-tab--active' : '' ?>"
                    onclick="switchTab('company')" id="tab-btn-company">
                <i class="fa-solid fa-building" aria-hidden="true"></i> Company
            </button>
            <button type="button" class="profile-tab<?= $defaultTab === 'password' ? ' profile-tab--active' : '' ?>"
                    onclick="switchTab('password')" id="tab-btn-password">
                <i class="fa-solid fa-lock" aria-hidden="true"></i> Password
            </button>
        </div>

        <!-- Company panel -->
        <div id="tab-panel-company" <?= $defaultTab !== 'company' ? 'hidden' : '' ?>>
            <?php if (!empty($company)): ?>
            <dl style="display:grid;grid-template-columns:max-content 1fr;gap:0.3rem 1rem;font-size:0.88rem;margin:0;">
                <?php
                $rows = [
                    'Name'    => $company['name']    ?? '',
                    'Phone'   => $company['phone']   ?? '',
                    'City'    => $company['city']    ?? '',
                    'State'   => $company['state']   ?? '',
                    'ZIP'     => $company['zip']     ?? '',
                    'Website' => $company['website'] ?? '',
                    'Email'   => $company['email']   ?? '',
                ];
                foreach ($rows as $label => $value):
                    if ($value === '') continue;
                ?>
                <dt style="color:var(--color-text-muted,#666);font-weight:600;white-space:nowrap;"><?= $label ?></dt>
                <dd style="margin:0;"><?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?></dd>
                <?php endforeach; ?>
            </dl>
            <div style="margin-top:0.85rem;">
                <a href="/company" class="btn btn--ghost btn--sm">
                    <i class="fa-solid fa-pen" aria-hidden="true"></i> Edit
                </a>
            </div>
            <?php else: ?>
            <p style="font-size:0.88rem;color:var(--color-text-muted,#666);margin:0;">
                No company linked. <a href="/company">Define your company &rarr;</a>
            </p>
            <?php endif; ?>
        </div>

        <!-- Password panel -->
        <div id="tab-panel-password" <?= $defaultTab !== 'password' ? 'hidden' : '' ?>>

            <?php if (!empty($passwordError)): ?>
            <div class="alert alert--warning mb-md" role="alert">
                <span class="alert__icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
                <div class="alert__body"><?= htmlspecialchars($passwordError, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <?php endif; ?>

            <form method="POST" action="/profile" novalidate>
                <input type="hidden" name="_action" value="change_password">

                <div class="form-group">
                    <label class="form-label" for="current_password">Current Password</label>
                    <input id="current_password" type="password" name="current_password" class="input"
                        placeholder="••••••••" autocomplete="current-password" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="new_password">New Password</label>
                    <input id="new_password" type="password" name="new_password" class="input"
                        placeholder="Minimum 8 characters" autocomplete="new-password" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm New Password</label>
                    <input id="confirm_password" type="password" name="confirm_password" class="input"
                        placeholder="Repeat new password" autocomplete="new-password" required>
                </div>

                <div class="profile-card__footer">
                    <span class="form-hint">Passwords are encrypted and cannot be recovered.</span>
                    <button type="submit" class="btn btn--secondary">
                        <i class="fa-solid fa-key" aria-hidden="true"></i>
                        Update Password
                    </button>
                </div>

            </form>
        </div>

    </div>

</div>

<style>
.profile-tab {
    padding: 0.45rem 1rem;
    font-size: 0.88rem;
    font-weight: 600;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    color: var(--color-mid-blue, #647D92);
    margin-bottom: -1px;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}
.profile-tab--active {
    color: var(--color-green, #2ECC71);
    border-bottom-color: var(--color-green, #2ECC71);
}
</style>

<script>
function switchTab(name) {
    ['company','password'].forEach(function(t) {
        document.getElementById('tab-panel-' + t).hidden = (t !== name);
        document.getElementById('tab-btn-' + t).classList.toggle('profile-tab--active', t === name);
    });
}
</script>
