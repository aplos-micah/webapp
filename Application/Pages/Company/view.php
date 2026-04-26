<?php
$isNew        = empty($data['company']);
$company      = $data['company']      ?? [];
$error        = $data['error']        ?? null;
$companyUsers = $data['companyUsers'] ?? [];
$invitations  = $data['invitations']  ?? [];
$inviteError  = $data['inviteError']  ?? null;
$userRow      = $data['userRow']      ?? [];
$action       = $isNew ? 'create_company' : 'update_company';
$pageTitle = $isNew ? 'Define Your Company' : 'My Company';
$val       = fn(string $key) => htmlspecialchars($company[$key] ?? '', ENT_QUOTES, 'UTF-8');
?>

<div class="dash-header">
    <div>
        <p class="eyebrow">Account</p>
        <h1 class="dash-header__title"><?= $pageTitle ?></h1>
        <p class="dash-header__sub">
            <?= $isNew ? 'Add the company you represent.' : 'Manage your company information.' ?>
        </p>
    </div>
</div>

<hr class="divider--green mb-xl">

<div class="profile-layout">

    <div class="card profile-card">

        <h2 class="profile-card__title">
            <i class="fa-solid fa-building" aria-hidden="true"></i>
            Company Information
        </h2>

        <?php if ($error): ?>
        <div class="alert alert--warning mb-md" role="alert">
            <span class="alert__icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
            <div class="alert__body"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <?php endif; ?>

        <form method="POST" action="/company" novalidate>
            <input type="hidden" name="_action" value="<?= $action ?>">

            <div class="form-grid-2col">

                <!-- Left: Identity -->
                <div>
                    <div class="form-group">
                        <label class="form-label" for="name">Company Name <span aria-hidden="true">*</span></label>
                        <input id="name" type="text" name="name" class="input"
                            value="<?= $val('name') ?>"
                            placeholder="e.g. Acme Corporation"
                            autocomplete="organization"
                            required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone</label>
                        <input id="phone" type="tel" name="phone" class="input"
                            value="<?= $val('phone') ?>"
                            placeholder="e.g. +1 (555) 000-0000"
                            autocomplete="tel">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input id="email" type="email" name="email" class="input"
                            value="<?= $val('email') ?>"
                            placeholder="e.g. info@company.com"
                            autocomplete="email">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="website">Website</label>
                        <input id="website" type="url" name="website" class="input"
                            value="<?= $val('website') ?>"
                            placeholder="https://example.com"
                            autocomplete="url">
                    </div>
                </div>

                <!-- Right: Address -->
                <div>
                    <div class="form-group">
                        <label class="form-label" for="address">Address</label>
                        <input id="address" type="text" name="address" class="input"
                            value="<?= $val('address') ?>"
                            placeholder="Street address"
                            autocomplete="street-address">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="city">City</label>
                        <input id="city" type="text" name="city" class="input"
                            value="<?= $val('city') ?>"
                            placeholder="City"
                            autocomplete="address-level2">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="state">State</label>
                        <input id="state" type="text" name="state" class="input"
                            value="<?= $val('state') ?>"
                            placeholder="State / Province"
                            autocomplete="address-level1">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="zip">ZIP / Postal Code</label>
                        <input id="zip" type="text" name="zip" class="input"
                            value="<?= $val('zip') ?>"
                            placeholder="ZIP or postal code"
                            autocomplete="postal-code">
                    </div>
                </div>

            </div>

            <div class="profile-card__footer">
                <span></span>
                <button type="submit" class="btn btn--primary">
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                    <?= $isNew ? 'Create Company' : 'Save Changes' ?>
                </button>
            </div>

        </form>
    </div>

    <!-- ── Users ─────────────────────────────────────────────────────── -->
    <div class="card profile-card">

        <h2 class="profile-card__title">
            <i class="fa-solid fa-users" aria-hidden="true"></i>
            Users
        </h2>

        <?php if (!empty($companyUsers)): ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Job Title</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($companyUsers as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="td-sm"><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="td-sm"><?= htmlspecialchars($u['job_title'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <span class="badge <?= $u['is_active'] ? 'badge--success' : 'badge--muted' ?>">
                                <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="empty-msg">
            No users are associated with this company yet.
        </p>
        <?php endif; ?>

        <?php if (!$isNew): ?>
        <hr class="section-rule">

        <h3 class="section-subheading">
            <i class="fa-solid fa-envelope" aria-hidden="true"></i> Invite a Team Member
        </h3>

        <?php if (!empty($inviteError)): ?>
        <div class="alert alert--warning mb-md" role="alert">
            <span class="alert__icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
            <div class="alert__body"><?= htmlspecialchars($inviteError, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <?php endif; ?>

        <form method="POST" action="/company" class="inline-form">
            <input type="hidden" name="_action" value="send_invite">
            <div class="form-group inline-form__field">
                <label class="form-label" for="invited_email">Email Address</label>
                <input id="invited_email" type="email" name="invited_email" class="input"
                    placeholder="colleague@<?= htmlspecialchars(substr($userRow['email'] ?? '@company.com', strpos($userRow['email'] ?? '@company.com', '@') + 1), ENT_QUOTES, 'UTF-8') ?>"
                    required>
            </div>
            <button type="submit" class="btn btn--primary btn--nowrap">
                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Send Invite
            </button>
        </form>

        <?php if (!empty($invitations)): ?>
        <hr class="section-rule">
        <h3 class="section-subheading">Invitation Log</h3>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invited Email</th>
                        <th>Invited By</th>
                        <th>Sent</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invitations as $inv):
                        $now = time();
                        if ($inv['accepted_at'] !== null) {
                            $status = ['label' => 'Accepted', 'class' => 'badge--success'];
                        } elseif (strtotime($inv['expires_at']) < $now) {
                            $status = ['label' => 'Expired', 'class' => 'badge--muted'];
                        } else {
                            $status = ['label' => 'Pending', 'class' => 'badge--info'];
                        }
                    ?>
                    <tr>
                        <td class="td-sm"><?= htmlspecialchars($inv['invited_email'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="td-sm"><?= htmlspecialchars($inv['inviter_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="td-muted"><?= date('M j, Y', strtotime($inv['created_at'])) ?></td>
                        <td><span class="badge <?= $status['class'] ?>"><?= $status['label'] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        <?php endif; ?>

    </div>

</div>
