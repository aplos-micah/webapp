<?php $pageTitle = 'Migrations'; ?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">Admin</p>
        <h1 class="dash-header__title">DB Migrations</h1>
        <p class="dash-header__sub">
            <?= count($applied) ?> applied &mdash; <?= count($pending) ?> pending
        </p>
    </div>
    <?php if (!empty($pending)): ?>
    <div>
        <form method="POST" action="/admin/migrations"
              onsubmit="return confirm('Run all <?= count($pending) ?> pending migration(s)? This cannot be undone.')">
            <input type="hidden" name="action" value="run_all">
            <button type="submit" class="btn btn--primary">
                <i class="fa-solid fa-play" aria-hidden="true"></i>
                Run All Pending (<?= count($pending) ?>)
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

        <!-- BLUF -->
        <h3 class="instructions__h3">The Short Version</h3>
        <ol class="instructions__list">
            <li>Create <code>YYYYMMDD_description.sql</code> in <code>Application/sql/interimUpdates/</code></li>
            <li>Deploy the file to the server</li>
            <li>Click <strong>Run</strong> here</li>
            <li>Done — it is recorded and will not run again</li>
        </ol>
        <p class="instructions__p--lg"><strong>Rule:</strong> Always deploy code before running migrations. Never the reverse.</p>

        <hr class="instructions__rule">

        <!-- Detailed Instructions -->
        <h3 class="instructions__h3">Detailed Instructions</h3>

        <h4 class="instructions__h4">Overview</h4>
        <p class="instructions__p">Migrations track incremental database changes after the initial schema was installed. Each migration is a <code>.sql</code> file that runs once and is permanently recorded in the <code>migrations</code> table.</p>

        <h4 class="instructions__h4">Adding a New Migration</h4>
        <ol class="instructions__list">
            <li>Create a new <code>.sql</code> file in <code>Application/sql/interimUpdates/</code></li>
            <li>Name it using the format <code>YYYYMMDD_description.sql</code><br><span class="instructions__note">e.g. <code>20260501_add_notes_to_contacts.sql</code></span></li>
            <li>Write your SQL — <code>ALTER TABLE</code>, <code>CREATE INDEX</code>, <code>INSERT</code> for reference data, etc.</li>
            <li>Deploy the file to the server</li>
            <li>Run it here</li>
        </ol>

        <h4 class="instructions__h4">Deployment Order</h4>
        <p class="instructions__p">Always deploy code before running migrations. New PHP files must be on the server before the schema changes — never the reverse.</p>

        <h4 class="instructions__h4">Run vs Mark Applied</h4>
        <ul class="instructions__list">
            <li><strong>Run</strong> — executes the SQL and records it. Use for new migrations.</li>
            <li><strong>Mark Applied</strong> — records without executing. Use for changes already applied manually (e.g. files from the old <code>applied.log</code> process).</li>
        </ul>

        <h4 class="instructions__h4">What Happens on Failure</h4>
        <p class="instructions__p">If a migration fails it stops immediately. Already-applied migrations in that run are not rolled back. The failed file is not recorded — fix the SQL and run it again.</p>

        <h4 class="instructions__h4">Do Not</h4>
        <ul class="instructions__list">
            <li>Rename or delete applied migration files — the filename is the permanent record</li>
            <li>Edit a migration after it has been applied — write a new one instead</li>
            <li>Run seed files through the migration runner — seeds live in <code>Application/sql/SeedFiles/</code> and are separate</li>
        </ul>

    </div>
</details>

<?php if (!empty($pending)): ?>

<h2 class="migration-heading">
    <i class="fa-solid fa-clock" aria-hidden="true"></i> Pending
</h2>

<div class="card mb-xl">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>File</th>
                    <th class="col-timestamp"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending as $file): ?>
                <tr>
                    <td class="td-migration-file">
                        <?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td>
                        <div class="action-group">
                            <form method="POST" action="/admin/migrations"
                                  onsubmit="return confirm('Run <?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>?')">
                                <input type="hidden" name="action" value="run_one">
                                <input type="hidden" name="file"   value="<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" class="btn btn--primary btn--sm">
                                    <i class="fa-solid fa-play" aria-hidden="true"></i> Run
                                </button>
                            </form>
                            <form method="POST" action="/admin/migrations"
                                  onsubmit="return confirm('Mark <?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?> as already applied without running it?')">
                                <input type="hidden" name="action" value="mark_applied">
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

<?php else: ?>

<div class="card dash-panel mb-xl">
    <div class="dash-panel__empty">
        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
        <p>All migrations have been applied.</p>
    </div>
</div>

<?php endif; ?>

<?php if (!empty($applied)): ?>

<h2 class="migration-heading">
    <i class="fa-solid fa-circle-check" aria-hidden="true"></i> Applied
</h2>

<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>File</th>
                    <th class="col-w-13">Applied At</th>
                    <th class="col-w-10">Applied By</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_reverse($applied) as $row): ?>
                <tr>
                    <td class="td-migration-file">
                        <?= htmlspecialchars($row['filename'], ENT_QUOTES, 'UTF-8') ?>
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
