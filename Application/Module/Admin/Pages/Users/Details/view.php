<?php
$e   = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$val = fn($v) => ($v !== null && $v !== '')
    ? htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8')
    : '<span class="text-muted">—</span>';

$userTypeBadge = [
    'admin'   => 'badge--warning',
    'manager' => 'badge--info',
    'user'    => 'badge--neutral',
    'free'    => 'badge--neutral',
];
$tierBadge = [
    'Manager' => 'badge--warning',
    'User'    => 'badge--info',
    'Free'    => 'badge--neutral',
];
?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">Admin / <a href="/admin/userlist">Users</a></p>
        <h1 class="dash-header__title"><?= $e($user['name']) ?></h1>
        <p class="dash-header__sub"><?= $e($user['email']) ?></p>
    </div>
    <div class="btn-group">
        <?php if ($editMode): ?>
        <a href="/admin/users/details?id=<?= $user['id'] ?>" class="btn btn--ghost">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i> Cancel
        </a>
        <button type="submit" form="user-edit-form" class="btn btn--primary">
            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save Changes
        </button>
        <?php else: ?>
        <a href="/admin/users/details?id=<?= $user['id'] ?>&edit" class="btn btn--secondary">
            <i class="fa-solid fa-pen" aria-hidden="true"></i> Edit
        </a>
        <a href="/admin/userlist" class="btn btn--ghost">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back
        </a>
        <?php endif; ?>
    </div>
</div>

<hr class="divider--green mb-xl">

<?php if ($editError): ?>
<div class="alert alert--warning mb-md" role="alert">
    <span class="alert__icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
    <div class="alert__body"><?= $e($editError) ?></div>
</div>
<?php endif; ?>

<?php if ($editMode): ?>
<form id="user-edit-form" method="POST"
      action="/admin/users/details?id=<?= $user['id'] ?>&edit"
      novalidate>
    <input type="hidden" name="action" value="update_user">
<?php endif; ?>

<div class="detail-layout">

    <!-- ── Left 60% — Identity + Record ──────────────────────────────────── -->
    <div class="detail-layout__primary">

        <!-- Identity -->
        <div class="card profile-card">
            <h2 class="profile-card__title">
                <i class="fa-solid fa-circle-user" aria-hidden="true"></i>
                Identity
            </h2>

            <?php if ($editMode): ?>
            <div class="edit-section">
                <div class="form-row">
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="name">Name <span class="form-required">*</span></label>
                        <input id="name" type="text" name="name" class="input"
                               value="<?= $e($user['name']) ?>" required>
                    </div>
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="email">Email <span class="form-required">*</span></label>
                        <input id="email" type="email" name="email" class="input"
                               value="<?= $e($user['email']) ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="phone">Phone</label>
                        <input id="phone" type="text" name="phone" class="input"
                               value="<?= $e($user['phone'] ?? '') ?>">
                    </div>
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="job_title">Job Title</label>
                        <input id="job_title" type="text" name="job_title" class="input"
                               value="<?= $e($user['job_title'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="timezone">Timezone</label>
                    <input id="timezone" type="text" name="timezone" class="input"
                           value="<?= $e($user['timezone'] ?? '') ?>">
                </div>
            </div>
            <?php else: ?>
            <dl class="field-list">
                <div class="field-list__row"><dt>Name</dt><dd><?= $val($user['name']) ?></dd></div>
                <div class="field-list__row"><dt>Email</dt><dd><?= $val($user['email']) ?></dd></div>
                <div class="field-list__row"><dt>Phone</dt><dd><?= $val($user['phone'] ?? null) ?></dd></div>
                <div class="field-list__row"><dt>Job Title</dt><dd><?= $val($user['job_title'] ?? null) ?></dd></div>
                <div class="field-list__row"><dt>Timezone</dt><dd><?= $val($user['timezone'] ?? null) ?></dd></div>
            </dl>
            <?php endif; ?>
        </div>

        <!-- Record -->
        <div class="card profile-card">
            <h2 class="profile-card__title">
                <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
                Record
            </h2>
            <dl class="field-list">
                <div class="field-list__row"><dt>User ID</dt><dd><?= (int) $user['id'] ?></dd></div>
                <div class="field-list__row">
                    <dt>Email Verified</dt>
                    <dd><?= !empty($user['email_verified_at']) ? $val($user['email_verified_at']) : '<span class="badge badge--warning">Unverified</span>' ?></dd>
                </div>
                <div class="field-list__row"><dt>Created</dt><dd><?= $val($user['created_at']) ?></dd></div>
                <div class="field-list__row"><dt>Last Updated</dt><dd><?= $val($user['updated_at']) ?></dd></div>
            </dl>
        </div>

    </div>

    <!-- ── Right 40% — Tabbed panel ──────────────────────────────────────── -->
    <div class="detail-layout__aside">
        <div class="card profile-card">

            <div class="tab-bar">
                <button type="button" class="profile-tab profile-tab--active"
                        data-tab-target="panel-module-access">
                    Module Access
                </button>
                <button type="button" class="profile-tab"
                        data-tab-target="panel-account-access">
                    Account Access
                </button>
            </div>

            <!-- ── Account Access ─────────────────────────────────────── -->
            <div id="panel-account-access" hidden>
                <?php if ($editMode): ?>
                <div class="edit-section">
                    <div class="form-row">
                        <div class="form-group form-group--grow">
                            <label class="form-label" for="user_type">User Type</label>
                            <select id="user_type" name="user_type" class="input">
                                <?php foreach (['admin', 'manager', 'user', 'free'] as $t): ?>
                                <option value="<?= $t ?>"<?= ($user['user_type'] === $t) ? ' selected' : '' ?>><?= ucfirst($t) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group form-group--grow">
                            <label class="form-label" for="is_active">Status</label>
                            <select id="is_active" name="is_active" class="input">
                                <option value="1"<?= $user['is_active'] ? ' selected' : '' ?>>Active</option>
                                <option value="0"<?= !$user['is_active'] ? ' selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <dl class="field-list">
                    <div class="field-list__row">
                        <dt>User Type</dt>
                        <dd>
                            <span class="badge <?= $userTypeBadge[$user['user_type'] ?? ''] ?? 'badge--neutral' ?>">
                                <?= $e(ucfirst($user['user_type'] ?? '—')) ?>
                            </span>
                        </dd>
                    </div>
                    <div class="field-list__row">
                        <dt>Status</dt>
                        <dd>
                            <?php if ($user['is_active']): ?>
                            <span class="badge badge--success">Active</span>
                            <?php else: ?>
                            <span class="badge badge--neutral">Inactive</span>
                            <?php endif; ?>
                        </dd>
                    </div>
                </dl>
                <?php endif; ?>
            </div>

            <!-- ── Module Access ──────────────────────────────────────── -->
            <div id="panel-module-access">
                <?php if (empty($managedModules)): ?>
                <div class="content-panel__empty">
                    <i class="fa-regular fa-circle-question" aria-hidden="true"></i>
                    <p>No modules with access control are installed.</p>
                </div>
                <?php else: ?>
                <form method="POST" action="/admin/users/details?id=<?= $user['id'] ?>">
                    <input type="hidden" name="action" value="update_access">
                    <div class="edit-section">
                        <?php foreach ($managedModules as $moduleName => $moduleCfg):
                            $modKey    = strtolower($moduleName);
                            $access    = $moduleAccess[$modKey] ?? null;
                            $current   = $access['tier'] ?? '';
                            $grantedBy = $access['granted_by_name'] ?? null;
                            $grantedAt = $access['granted_at'] ?? null;
                        ?>
                        <div class="form-group">
                            <label class="form-label">
                                <span class="badge badge--neutral"><?= $e($moduleName) ?></span>
                                <?php if ($grantedBy && $grantedAt): ?>
                                <span class="text-muted" style="font-size:0.78rem; margin-left:0.35rem;">
                                    <?= $e($grantedBy) ?> · <?= $e(substr($grantedAt, 0, 10)) ?>
                                </span>
                                <?php endif; ?>
                            </label>
                            <select name="module_access[<?= $e($modKey) ?>]" class="input">
                                <option value="none"<?= $current === '' ? ' selected' : '' ?>>— No Access —</option>
                                <?php foreach (['Free', 'User', 'Manager'] as $tier): ?>
                                <option value="<?= $tier ?>"<?= $current === $tier ? ' selected' : '' ?>><?= $tier ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endforeach; ?>
                        <div class="form-actions--sm">
                            <button type="submit" class="btn btn--primary btn--sm">
                                <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save
                            </button>
                        </div>
                    </div>
                </form>
                <?php endif; ?>
            </div>

        </div>
    </div>

</div>

<?php if ($editMode): ?>
<div class="form-actions mt-md">
    <a href="/admin/users/details?id=<?= $user['id'] ?>" class="btn btn--ghost">Cancel</a>
    <button type="submit" form="user-edit-form" class="btn btn--primary">
        <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save Changes
    </button>
</div>
</form>
<?php endif; ?>
