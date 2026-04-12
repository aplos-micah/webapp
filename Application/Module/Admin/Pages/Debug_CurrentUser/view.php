<?php $pageTitle = 'Debug: Current User'; ?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">Admin / Debug</p>
        <h1 class="dash-header__title">Current User</h1>
        <p class="dash-header__sub">Live database record and session state for the authenticated user.</p>
    </div>
</div>

<hr class="divider--green mb-xl">

<div class="profile-layout">

    <!-- ── Database Record ──────────────────────────────────────────────── -->
    <div class="card profile-card">

        <h2 class="profile-card__title">
            <i class="fa-solid fa-database" aria-hidden="true"></i>
            Database Record
        </h2>

        <table class="debug-table">
            <thead>
                <tr>
                    <th>Field</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $dbFields = [
                    'id', 'name', 'email', 'user_type', 'Module_CRM',
                    'phone', 'job_title', 'timezone', 'is_active',
                    'created_at', 'updated_at',
                ];
                foreach ($dbFields as $field):
                    $raw   = $user[$field] ?? null;
                    $value = $raw === null ? '<span class="debug-null">null</span>'
                                          : htmlspecialchars((string) $raw, ENT_QUOTES, 'UTF-8');
                ?>
                <tr>
                    <td class="debug-key"><?= htmlspecialchars($field, ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="debug-val"><?= $value ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>

    <!-- ── Session State ────────────────────────────────────────────────── -->
    <div class="card profile-card">

        <h2 class="profile-card__title">
            <i class="fa-solid fa-key" aria-hidden="true"></i>
            Session State
        </h2>

        <table class="debug-table">
            <thead>
                <tr>
                    <th>Key</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sessionData as $key => $val):
                    $value = $val === null ? '<span class="debug-null">null</span>'
                                          : htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8');
                ?>
                <tr>
                    <td class="debug-key"><?= htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="debug-val"><?= $value ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>

</div>
