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
