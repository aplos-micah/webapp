<?php $pageTitle = 'Migrations'; ?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">Admin</p>
        <h1 class="dash-header__title">DB Migrations</h1>
        <p class="dash-header__sub">
            <?= $totalPending ?> pending &mdash;
            <?= array_sum(array_map('count', $appliedAll)) ?> applied &mdash;
            <span class="badge badge--info">v<?= htmlspecialchars($platformVersion, ENT_QUOTES, 'UTF-8') ?></span>
        </p>
    </div>
    <?php if ($totalPending > 0): ?>
    <div>
        <form method="POST" action="/admin/migrations"
              onsubmit="return confirm('Run all <?= $totalPending ?> pending migration(s) across all modules? This cannot be undone.')">
            <input type="hidden" name="action" value="run_all">
            <button type="submit" class="btn btn--primary">
                <i class="fa-solid fa-play" aria-hidden="true"></i>
                Run All Pending (<?= $totalPending ?>)
            </button>
        </form>
    </div>
    <?php endif; ?>
</div>

<hr class="divider--green mb-xl">

<!-- Migration Instructions -->
<details class="instructions">
    <summary class="instructions__summary">
        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
        Migration Instructions
    </summary>

    <div class="instructions__body">

        <h3 class="instructions__h3">The Short Version</h3>
        <ol class="instructions__list">
            <li>Create <code>YYYYMMDD_description.sql</code> in the appropriate module's <code>SQL/InterimUpdates/</code> folder</li>
            <li>Update the module's <code>SQL/base.sql</code> to fold the change in</li>
            <li>Deploy the files to the server</li>
            <li>Click <strong>Run</strong> here</li>
            <li>Done — it is recorded and will not run again</li>
        </ol>
        <p class="instructions__p--lg"><strong>Rule:</strong> Always deploy code before running migrations. Never the reverse.</p>

        <hr class="instructions__rule">

        <h3 class="instructions__h3">Where to Put Migration Files</h3>
        <ul class="instructions__list">
            <li><strong>Platform migrations</strong> (users, oauth, company) → <code>Application/sql/interimUpdates/</code></li>
            <li><strong>CRM module migrations</strong> → <code>Application/Module/CRM/SQL/InterimUpdates/</code></li>
            <li><strong>Admin module migrations</strong> → <code>Application/Module/Admin/SQL/InterimUpdates/</code></li>
            <li><strong>New module migrations</strong> → <code>Application/Module/{Name}/SQL/InterimUpdates/</code></li>
        </ul>
        <p class="instructions__p">The tool discovers all modules automatically — no registration required.</p>

        <hr class="instructions__rule">

        <h3 class="instructions__h3">Keeping base.sql Current</h3>
        <p class="instructions__p">Every module has a <code>SQL/base.sql</code> that represents the complete schema for a fresh install. Whenever you add an interim update, fold the same change into <code>base.sql</code> so new installations do not need to run patches.</p>

        <hr class="instructions__rule">

        <h3 class="instructions__h3">Run vs Mark Applied</h3>
        <ul class="instructions__list">
            <li><strong>Run</strong> — executes the SQL and records it. Use for new migrations.</li>
            <li><strong>Mark Applied</strong> — records without executing. Use for changes already applied manually.</li>
        </ul>

        <h3 class="instructions__h3">What Happens on Failure</h3>
        <p class="instructions__p">If a migration fails it stops immediately. Already-applied migrations in that run are not rolled back. The failed file is not recorded — fix the SQL and run it again.</p>

        <h3 class="instructions__h3">Do Not</h3>
        <ul class="instructions__list">
            <li>Rename or delete applied migration files — the filename is the permanent record</li>
            <li>Edit a migration after it has been applied — write a new one instead</li>
            <li>Bump <code>PLATFORM_VERSION</code> in <code>.env</code> mid-deployment — bump it once per release</li>
        </ul>

    </div>
</details>

<!-- =========================================================================
     PENDING MIGRATIONS — grouped by module
     ========================================================================= -->

<?php if ($totalPending > 0): ?>

<h2 class="migration-heading">
    <i class="fa-solid fa-clock" aria-hidden="true"></i> Pending
</h2>

<?php foreach ($pendingAll as $module => $files): ?>

<div class="card mb-lg">
    <div class="migration-module-header">
        <span class="migration-module-label">
            <span class="badge badge--neutral"><?= htmlspecialchars(ucfirst($module), ENT_QUOTES, 'UTF-8') ?></span>
            <span class="migration-module-count"><?= count($files) ?> pending</span>
        </span>
        <form method="POST" action="/admin/migrations"
              onsubmit="return confirm('Run all <?= count($files) ?> pending <?= htmlspecialchars(ucfirst($module), ENT_QUOTES, 'UTF-8') ?> migration(s)?')">
            <input type="hidden" name="action" value="run_module">
            <input type="hidden" name="module" value="<?= htmlspecialchars($module, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="btn btn--secondary btn--sm">
                <i class="fa-solid fa-play" aria-hidden="true"></i>
                Run <?= htmlspecialchars(ucfirst($module), ENT_QUOTES, 'UTF-8') ?>
            </button>
        </form>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>File</th>
                    <th class="col-timestamp"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($files as $file): ?>
                <tr>
                    <td class="td-migration-file">
                        <?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td>
                        <div class="action-group">
                            <form method="POST" action="/admin/migrations"
                                  onsubmit="return confirm('Run <?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>?')">
                                <input type="hidden" name="action" value="run_one">
                                <input type="hidden" name="module" value="<?= htmlspecialchars($module, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="file"   value="<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" class="btn btn--primary btn--sm">
                                    <i class="fa-solid fa-play" aria-hidden="true"></i> Run
                                </button>
                            </form>
                            <form method="POST" action="/admin/migrations"
                                  onsubmit="return confirm('Mark <?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?> as already applied without running it?')">
                                <input type="hidden" name="action" value="mark_applied">
                                <input type="hidden" name="module" value="<?= htmlspecialchars($module, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="file"   value="<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" class="btn btn--ghost btn--sm">
                                    <i class="fa-solid fa-check" aria-hidden="true"></i> Mark Applied
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endforeach; ?>

<?php else: ?>

<div class="card content-panel mb-xl">
    <div class="content-panel__empty">
        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
        <p>All migrations have been applied.</p>
    </div>
</div>

<?php endif; ?>

<!-- =========================================================================
     APPLIED MIGRATIONS — grouped by module
     ========================================================================= -->

<?php if (!empty($appliedAll)): ?>

<h2 class="migration-heading">
    <i class="fa-solid fa-circle-check" aria-hidden="true"></i> Applied
</h2>

<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>File</th>
                    <th class="col-w-8">Module</th>
                    <th class="col-w-8">Version</th>
                    <th class="col-w-13">Applied At</th>
                    <th class="col-w-10">Applied By</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $allApplied = [];
                foreach ($appliedAll as $rows) {
                    foreach ($rows as $row) {
                        $allApplied[] = $row;
                    }
                }
                usort($allApplied, fn($a, $b) => strcmp($b['applied_at'], $a['applied_at']));
                foreach ($allApplied as $row):
                ?>
                <tr>
                    <td class="td-migration-file">
                        <?= htmlspecialchars($row['filename'], ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td>
                        <span class="badge badge--neutral">
                            <?= htmlspecialchars(ucfirst($row['module']), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge--info">
                            v<?= htmlspecialchars($row['platform_version'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td class="td-sm">
                        <?= htmlspecialchars($row['applied_at'], ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td class="td-sm">
                        <?= htmlspecialchars($row['applied_by'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>
