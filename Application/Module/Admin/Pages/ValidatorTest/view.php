<?php $pageTitle = 'Validator Tests'; ?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">Admin</p>
        <h1 class="dash-header__title">Validator Tests</h1>
        <p class="dash-header__sub">
            <?= $total ?> assertions &mdash;
            <?php if ($failed === 0): ?>
            <span style="color:var(--color-success,#2e7d32);font-weight:600;"><?= $passed ?> passed</span>
            <?php else: ?>
            <span style="color:var(--color-success,#2e7d32);font-weight:600;"><?= $passed ?> passed</span>
            &mdash; <span style="color:var(--color-danger,#c62828);font-weight:600;"><?= $failed ?> failed</span>
            <?php endif; ?>
        </p>
    </div>
    <div>
        <a href="/admin/validatortest" class="btn btn--ghost">
            <i class="fa-solid fa-rotate-right" aria-hidden="true"></i> Re-run
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<!-- Description -->
<div class="card mb-xl">
    <div style="padding:1rem 1.25rem;font-size:0.9rem;line-height:1.7;color:#333;">
        <p style="margin:0 0 0.5rem;">
            This page validates the <code>Validator</code> class at <code>Application/Validator.php</code>, which
            provides stateless in-memory validation helpers used by all Object classes (Account, Contact, User, etc.).
        </p>
        <p style="margin:0 0 0.5rem;">
            Each assertion compares the method's actual return value against what is expected.
            A <strong style="color:var(--color-success,#2e7d32);">green check</strong> means the result matched.
            A <strong style="color:var(--color-danger,#c62828);">red X</strong> means the method returned something unexpected — the Actual column shows what it returned instead.
        </p>
        <p style="margin:0;">
            These are in-memory checks only. DB-dependent rules (duplicate email, token validity) are not tested here — those require a live database connection and will be covered under PHPUnit (#10).
        </p>
    </div>
</div>

<!-- Overall pass banner -->
<?php if ($failed === 0): ?>
<div class="card mb-xl" style="border-left:4px solid var(--color-success,#2e7d32);">
    <div class="dash-panel__empty">
        <i class="fa-solid fa-circle-check" aria-hidden="true" style="color:var(--color-success,#2e7d32);"></i>
        <p>All <?= $total ?> assertions passed.</p>
    </div>
</div>
<?php endif; ?>

<!-- Test groups -->
<?php foreach ($groups as $groupName => $groupTests):
    $groupPassed = count(array_filter($groupTests, fn($t) => $t['pass']));
    $groupFailed = count($groupTests) - $groupPassed;
?>

<div class="card mb-xl">

    <!-- Group header -->
    <div style="display:flex;align-items:center;justify-content:space-between;padding:0.75rem 1.25rem;border-bottom:1px solid var(--color-border,#e0e0e0);">
        <h2 style="margin:0;font-size:0.95rem;font-weight:600;">
            <code>Validator::<?= htmlspecialchars($groupName, ENT_QUOTES, 'UTF-8') ?>()</code>
        </h2>
        <?php if ($groupFailed === 0): ?>
        <span style="font-size:0.78rem;font-weight:600;color:var(--color-success,#2e7d32);background:#e8f5e9;padding:0.15rem 0.6rem;border-radius:20px;">
            <?= $groupPassed ?>/<?= count($groupTests) ?> passed
        </span>
        <?php else: ?>
        <span style="font-size:0.78rem;font-weight:600;color:var(--color-danger,#c62828);background:#ffebee;padding:0.15rem 0.6rem;border-radius:20px;">
            <?= $groupFailed ?> failed
        </span>
        <?php endif; ?>
    </div>

    <!-- Test rows -->
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:2.5rem;"></th>
                    <th>Assertion</th>
                    <th style="width:13rem;">Expected</th>
                    <th style="width:13rem;">Actual</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groupTests as $t): ?>
                <tr<?= $t['pass'] ? '' : ' style="background:#fff8f8;"' ?>>
                    <td style="text-align:center;">
                        <?php if ($t['pass']): ?>
                        <i class="fa-solid fa-circle-check" style="color:var(--color-success,#2e7d32);" aria-label="Pass"></i>
                        <?php else: ?>
                        <i class="fa-solid fa-circle-xmark" style="color:var(--color-danger,#c62828);" aria-label="Fail"></i>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:0.88rem;color:#222;">
                        <?= htmlspecialchars($t['label'], ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td style="font-family:monospace;font-size:0.82rem;color:#333;">
                        <?= htmlspecialchars($t['expected'], ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td style="font-family:monospace;font-size:0.82rem;color:<?= $t['pass'] ? '#333' : 'var(--color-danger,#c62828)' ?>;">
                        <?= htmlspecialchars($t['actual'], ENT_QUOTES, 'UTF-8') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<?php endforeach; ?>
