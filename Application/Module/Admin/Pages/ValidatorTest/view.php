<?php $pageTitle = 'Validator Tests'; ?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">Admin</p>
        <h1 class="dash-header__title">Validator Tests</h1>
        <p class="dash-header__sub">
            <?= $total ?> assertions &mdash;
            <?php if ($failed === 0): ?>
            <span class="stat-pass"><?= $passed ?> passed</span>
            <?php else: ?>
            <span class="stat-pass"><?= $passed ?> passed</span>
            &mdash; <span class="stat-fail"><?= $failed ?> failed</span>
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
    <div class="desc-box">
        <p class="instructions__p">
            This page validates the <code>Validator</code> class at <code>Application/Validator.php</code>, which
            provides stateless in-memory validation helpers used by all Object classes (Account, Contact, User, etc.).
        </p>
        <p class="instructions__p">
            Each assertion compares the method's actual return value against what is expected.
            A <strong class="icon-pass">green check</strong> means the result matched.
            A <strong class="icon-fail">red X</strong> means the method returned something unexpected — the Actual column shows what it returned instead.
        </p>
        <p>
            These are in-memory checks only. DB-dependent rules (duplicate email, token validity) are not tested here — those require a live database connection and will be covered under PHPUnit (#10).
        </p>
    </div>
</div>

<!-- Overall pass banner -->
<?php if ($failed === 0): ?>
<div class="card mb-xl pass-card">
    <div class="dash-panel__empty">
        <i class="fa-solid fa-circle-check icon-pass" aria-hidden="true"></i>
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
    <div class="group-header">
        <h2 class="group-header__title">
            <code>Validator::<?= htmlspecialchars($groupName, ENT_QUOTES, 'UTF-8') ?>()</code>
        </h2>
        <?php if ($groupFailed === 0): ?>
        <span class="group-badge--pass">
            <?= $groupPassed ?>/<?= count($groupTests) ?> passed
        </span>
        <?php else: ?>
        <span class="group-badge--fail">
            <?= $groupFailed ?> failed
        </span>
        <?php endif; ?>
    </div>

    <!-- Test rows -->
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-rownum"></th>
                    <th>Assertion</th>
                    <th class="col-w-13">Expected</th>
                    <th class="col-w-13">Actual</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groupTests as $t): ?>
                <tr<?= $t['pass'] ? '' : ' class="row--fail"' ?>>
                    <td class="text-center">
                        <?php if ($t['pass']): ?>
                        <i class="fa-solid fa-circle-check icon-pass" aria-label="Pass"></i>
                        <?php else: ?>
                        <i class="fa-solid fa-circle-xmark icon-fail" aria-label="Fail"></i>
                        <?php endif; ?>
                    </td>
                    <td class="td-assertion">
                        <?= htmlspecialchars($t['label'], ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td class="td-mono">
                        <?= htmlspecialchars($t['expected'], ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td class="<?= $t['pass'] ? 'td-mono' : 'td-mono--fail' ?>">
                        <?= htmlspecialchars($t['actual'], ENT_QUOTES, 'UTF-8') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<?php endforeach; ?>
