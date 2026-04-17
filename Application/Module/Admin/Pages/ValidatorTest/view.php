<?php $pageTitle = 'Validator Tests'; ?>

<div class="dash-header">
    <div>
        <p class="eyebrow">Admin</p>
        <h1 class="dash-header__title">Validator Tests</h1>
        <p class="dash-header__sub">
            <?= $total ?> tests &mdash;
            <span style="color:<?= $failed === 0 ? 'var(--color-success,#2e7d32)' : 'var(--color-danger,#c62828)' ?>;font-weight:600;">
                <?= $passed ?> passed
            </span>
            <?php if ($failed > 0): ?>
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

<?php if ($failed === 0): ?>
<div class="card dash-panel mb-xl" style="border-left:4px solid var(--color-success,#2e7d32);">
    <div class="dash-panel__empty">
        <i class="fa-solid fa-circle-check" aria-hidden="true" style="color:var(--color-success,#2e7d32);"></i>
        <p>All <?= $total ?> assertions passed.</p>
    </div>
</div>
<?php endif; ?>

<?php foreach ($groups as $groupName => $groupTests): ?>

<?php
$groupPassed = count(array_filter($groupTests, fn($t) => $t['pass']));
$groupFailed = count($groupTests) - $groupPassed;
?>

<h2 style="font-size:1rem;font-weight:600;margin-bottom:0.6rem;display:flex;align-items:center;gap:0.6rem;">
    <code style="font-size:0.95rem;">Validator::<?= htmlspecialchars($groupName) ?>()</code>
    <?php if ($groupFailed === 0): ?>
    <span style="font-size:0.78rem;font-weight:600;color:var(--color-success,#2e7d32);background:#e8f5e9;padding:0.1rem 0.5rem;border-radius:20px;">
        <?= $groupPassed ?>/<?= count($groupTests) ?> passed
    </span>
    <?php else: ?>
    <span style="font-size:0.78rem;font-weight:600;color:var(--color-danger,#c62828);background:#ffebee;padding:0.1rem 0.5rem;border-radius:20px;">
        <?= $groupFailed ?> failed
    </span>
    <?php endif; ?>
</h2>

<div class="card mb-xl">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:2.5rem;"></th>
                    <th>Test</th>
                    <th style="width:14rem;">Expected</th>
                    <th style="width:14rem;">Actual</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groupTests as $t): ?>
                <tr style="<?= $t['pass'] ? '' : 'background:#fff8f8;' ?>">
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
