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
<details style="margin-bottom:1.5rem;">
    <summary style="cursor:pointer;font-size:0.95rem;font-weight:600;padding:0.6rem 0.75rem;background:var(--color-surface-raised,#f5f5f5);border-radius:6px;list-style:none;display:flex;align-items:center;gap:0.5rem;">
        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
        Migration Instructions
    </summary>

    <div style="padding:1.25rem 1rem 0.5rem;border:1px solid var(--color-border,#e0e0e0);border-top:none;border-radius:0 0 6px 6px;font-size:0.9rem;line-height:1.7;">

        <!-- BLUF -->
        <h3 style="font-size:0.95rem;font-weight:700;margin:0 0 0.5rem;">The Short Version</h3>
        <ol style="margin:0 0 0.5rem 1.25rem;padding:0;">
            <li>Create <code>YYYYMMDD_description.sql</code> in <code>Application/sql/interimUpdates/</code></li>
            <li>Deploy the file to the server</li>
            <li>Click <strong>Run</strong> here</li>
            <li>Done — it is recorded and will not run again</li>
        </ol>
        <p style="margin:0 0 1.25rem;"><strong>Rule:</strong> Always deploy code before running migrations. Never the reverse.</p>

        <hr style="border:none;border-top:1px solid var(--color-border,#e0e0e0);margin:0 0 1.25rem;">

        <!-- Detailed Instructions -->
        <h3 style="font-size:0.95rem;font-weight:700;margin:0 0 0.75rem;">Detailed Instructions</h3>

        <h4 style="font-size:0.88rem;font-weight:700;margin:0 0 0.25rem;">Overview</h4>
        <p style="margin:0 0 1rem;">Migrations track incremental database changes after the initial schema was installed. Each migration is a <code>.sql</code> file that runs once and is permanently recorded in the <code>migrations</code> table.</p>

        <h4 style="font-size:0.88rem;font-weight:700;margin:0 0 0.25rem;">Adding a New Migration</h4>
        <ol style="margin:0 0 1rem 1.25rem;padding:0;">
            <li>Create a new <code>.sql</code> file in <code>Application/sql/interimUpdates/</code></li>
            <li>Name it using the format <code>YYYYMMDD_description.sql</code><br><span style="color:#555;font-size:0.85rem;">e.g. <code>20260501_add_notes_to_contacts.sql</code></span></li>
            <li>Write your SQL — <code>ALTER TABLE</code>, <code>CREATE INDEX</code>, <code>INSERT</code> for reference data, etc.</li>
            <li>Deploy the file to the server</li>
            <li>Run it here</li>
        </ol>

        <h4 style="font-size:0.88rem;font-weight:700;margin:0 0 0.25rem;">Deployment Order</h4>
        <p style="margin:0 0 1rem;">Always deploy code before running migrations. New PHP files must be on the server before the schema changes — never the reverse.</p>

        <h4 style="font-size:0.88rem;font-weight:700;margin:0 0 0.25rem;">Run vs Mark Applied</h4>
        <ul style="margin:0 0 1rem 1.25rem;padding:0;">
            <li><strong>Run</strong> — executes the SQL and records it. Use for new migrations.</li>
            <li><strong>Mark Applied</strong> — records without executing. Use for changes already applied manually (e.g. files from the old <code>applied.log</code> process).</li>
        </ul>

        <h4 style="font-size:0.88rem;font-weight:700;margin:0 0 0.25rem;">What Happens on Failure</h4>
        <p style="margin:0 0 1rem;">If a migration fails it stops immediately. Already-applied migrations in that run are not rolled back. The failed file is not recorded — fix the SQL and run it again.</p>

        <h4 style="font-size:0.88rem;font-weight:700;margin:0 0 0.25rem;">Do Not</h4>
        <ul style="margin:0 0 0.5rem 1.25rem;padding:0;">
            <li>Rename or delete applied migration files — the filename is the permanent record</li>
            <li>Edit a migration after it has been applied — write a new one instead</li>
            <li>Run seed files through the migration runner — seeds live in <code>Application/sql/SeedFiles/</code> and are separate</li>
        </ul>

    </div>
</details>

<?php if (!empty($pending)): ?>

<h2 style="font-size:1.05rem;font-weight:600;margin-bottom:0.75rem;">
    <i class="fa-solid fa-clock" aria-hidden="true"></i> Pending
</h2>

<div class="card mb-xl">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>File</th>
                    <th style="width:14rem;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending as $file): ?>
                <tr>
                    <td style="font-family:monospace;font-size:0.88rem;">
                        <?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:0.4rem;justify-content:flex-end;">
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

<h2 style="font-size:1.05rem;font-weight:600;margin-bottom:0.75rem;">
    <i class="fa-solid fa-circle-check" aria-hidden="true"></i> Applied
</h2>

<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>File</th>
                    <th style="width:13rem;">Applied At</th>
                    <th style="width:10rem;">Applied By</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_reverse($applied) as $row): ?>
                <tr>
                    <td style="font-family:monospace;font-size:0.88rem;">
                        <?= htmlspecialchars($row['filename'], ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td style="font-size:0.85rem;">
                        <?= htmlspecialchars($row['applied_at'], ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td style="font-size:0.85rem;">
                        <?= htmlspecialchars($row['applied_by'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>
