<?php
$pageTitle = 'Assets Dashboard';
$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

$statusBadge = [
    'Active'      => 'badge--success',
    'In Stock'    => 'badge--info',
    'In Repair'   => 'badge--warning',
    'Retired'     => 'badge--neutral',
    'Lost/Stolen' => 'badge--neutral',
];

$total      = array_sum(array_column($stats['by_status'], 'cnt'));
$byStatus   = array_column($stats['by_status'], 'cnt', 'status');
$byType     = array_column($stats['by_type'],   'cnt', 'type');
$pct = fn(int $n) => $total > 0 ? round(($n / $total) * 100) : 0;
?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">Assets</p>
        <h1 class="dash-header__title">Dashboard</h1>
        <p class="dash-header__sub"><?= date('l, F j, Y') ?></p>
    </div>
    <div>
        <a href="/assets/assets/new" class="btn btn--primary">
            <i class="fa-solid fa-plus" aria-hidden="true"></i> New Asset
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- ROW 1 — Stat cards                                                     -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="stats-grid mb-xl">

    <a href="/assets/assets/list?status=Active" class="stat-card stat-card--link">
        <div class="stat-card__icon icon-circle icon-circle--green">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
        </div>
        <div class="stat-card__body">
            <span class="stat-card__label eyebrow">Total Active</span>
            <span class="stat-card__value"><?= number_format($stats['active']) ?></span>
            <span class="stat-card__sub">assets in service</span>
        </div>
    </a>

    <a href="/assets/assets/list?status=In+Repair" class="stat-card stat-card--link">
        <div class="stat-card__icon icon-circle icon-circle--orange">
            <i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>
        </div>
        <div class="stat-card__body">
            <span class="stat-card__label eyebrow">In Repair</span>
            <span class="stat-card__value"><?= number_format($stats['in_repair']) ?></span>
            <span class="stat-card__sub">currently being serviced</span>
        </div>
    </a>

    <a href="/assets/assets/list" class="stat-card stat-card--link">
        <div class="stat-card__icon icon-circle icon-circle--mid-blue">
            <i class="fa-solid fa-user-slash" aria-hidden="true"></i>
        </div>
        <div class="stat-card__body">
            <span class="stat-card__label eyebrow">Unassigned</span>
            <span class="stat-card__value"><?= number_format($stats['unassigned']) ?></span>
            <span class="stat-card__sub">active assets with no owner</span>
        </div>
    </a>

    <div class="stat-card">
        <div class="stat-card__icon icon-circle icon-circle--navy">
            <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
        </div>
        <div class="stat-card__body">
            <span class="stat-card__label eyebrow">Warranties Expiring</span>
            <span class="stat-card__value"><?= number_format($stats['expiring_soon']) ?></span>
            <span class="stat-card__sub">expiring within 30 days</span>
        </div>
    </div>

</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- ROW 2 — Breakdowns                                                     -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="content-panels mb-xl">

    <!-- By Status -->
    <div class="card content-panel">
        <h2 class="content-panel__title">
            <i class="fa-solid fa-circle-half-stroke" aria-hidden="true"></i>
            By Status
        </h2>
        <?php if ($total === 0): ?>
        <div class="content-panel__empty">
            <i class="fa-solid fa-laptop" aria-hidden="true"></i>
            <p>No assets yet.</p>
        </div>
        <?php else: ?>
        <ul class="breakdown-list">
            <?php foreach (Asset::STATUSES as $s):
                $cnt = (int) ($byStatus[$s] ?? 0);
                if ($cnt === 0) continue;
            ?>
            <li class="breakdown-list__item">
                <a href="/assets/assets/list?status=<?= urlencode($s) ?>" class="table-link">
                    <span class="badge <?= $statusBadge[$s] ?? 'badge--neutral' ?>"><?= $e($s) ?></span>
                </a>
                <span class="breakdown-list__count"><?= $cnt ?> asset<?= $cnt !== 1 ? 's' : '' ?></span>
                <span class="breakdown-list__value"><?= $pct($cnt) ?>%</span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>

    <!-- By Type -->
    <div class="card content-panel">
        <h2 class="content-panel__title">
            <i class="fa-solid fa-shapes" aria-hidden="true"></i>
            By Type
        </h2>
        <?php if ($total === 0): ?>
        <div class="content-panel__empty">
            <i class="fa-solid fa-laptop" aria-hidden="true"></i>
            <p>No assets yet.</p>
        </div>
        <?php else: ?>
        <ul class="breakdown-list">
            <?php foreach (Asset::TYPES as $t):
                $cnt = (int) ($byType[$t] ?? 0);
                if ($cnt === 0) continue;
            ?>
            <li class="breakdown-list__item">
                <a href="/assets/assets/list?type=<?= urlencode($t) ?>" class="table-link">
                    <span class="badge badge--neutral"><?= $e($t) ?></span>
                </a>
                <span class="breakdown-list__count"><?= $cnt ?> asset<?= $cnt !== 1 ? 's' : '' ?></span>
                <span class="breakdown-list__value"><?= $pct($cnt) ?>%</span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>

</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- ROW 3 — Expiring / Expired Warranties (full width)                     -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="card content-panel">
    <h2 class="content-panel__title">
        <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
        Expiring Warranties
        <?php if (!empty($expiring)): ?>
        <span class="badge badge--warning" style="margin-left:0.5rem;"><?= count($expiring) ?></span>
        <?php endif; ?>
    </h2>

    <?php if (empty($expiring)): ?>
    <div class="content-panel__empty">
        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
        <p>No warranties expiring in the next 30 days.</p>
    </div>
    <?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Asset Tag</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Warranty Expires</th>
                    <th>Assigned To</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expiring as $a): ?>
                <tr>
                    <td>
                        <a href="/assets/assets/details?id=<?= (int) $a['id'] ?>" class="table-link">
                            <?= $e($a['asset_tag'] ?: 'ASSET-??????') ?>
                        </a>
                    </td>
                    <td><?= $e($a['name']) ?></td>
                    <td><?= $e($a['type'] ?? '—') ?></td>
                    <td><?= $e(substr($a['warranty_expires'] ?? '', 0, 10)) ?></td>
                    <td><?= $e($a['assigned_name'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
