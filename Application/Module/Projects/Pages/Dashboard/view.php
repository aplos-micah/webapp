<?php
$pageTitle = 'Projects Dashboard';
$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

$statusBadge = [
    'Draft'     => 'badge--neutral',
    'Active'    => 'badge--success',
    'On Hold'   => 'badge--warning',
    'Completed' => 'badge--info',
    'Cancelled' => 'badge--neutral',
];
$priorityBadge = [
    'Critical' => 'badge--warning',
    'High'     => 'badge--info',
    'Medium'   => 'badge--neutral',
    'Low'      => 'badge--neutral',
];

$byStatus   = array_column($stats['by_status'],   'cnt', 'status');
$byPhase    = array_column($stats['by_phase'],    'cnt', 'phase');
$byPriority = array_column($stats['by_priority'], 'cnt', 'priority');
$openTotal  = $stats['open_total'];
$pct = fn(int $n) => $openTotal > 0 ? round(($n / $openTotal) * 100) : 0;
?>

<div class="dash-header">
    <div>
        <p class="eyebrow">Projects</p>
        <h1 class="dash-header__title">Dashboard</h1>
        <p class="dash-header__sub"><?= date('l, F j, Y') ?></p>
    </div>
    <div>
        <a href="/projects/projects/new" class="btn btn--primary">
            <i class="fa-solid fa-plus" aria-hidden="true"></i> New Project
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<!-- ROW 1 — Stat cards -->
<div class="stats-grid mb-xl">

    <a href="/projects/projects/list?status=Active" class="stat-card stat-card--link">
        <div class="stat-card__icon icon-circle icon-circle--green">
            <i class="fa-solid fa-diagram-project" aria-hidden="true"></i>
        </div>
        <div class="stat-card__body">
            <span class="stat-card__label eyebrow">Active Projects</span>
            <span class="stat-card__value"><?= number_format($stats['active']) ?></span>
            <span class="stat-card__sub">currently in progress</span>
        </div>
    </a>

    <a href="/projects/projects/list?status=On+Hold" class="stat-card stat-card--link">
        <div class="stat-card__icon icon-circle icon-circle--orange">
            <i class="fa-solid fa-pause-circle" aria-hidden="true"></i>
        </div>
        <div class="stat-card__body">
            <span class="stat-card__label eyebrow">On Hold</span>
            <span class="stat-card__value"><?= number_format($stats['on_hold']) ?></span>
            <span class="stat-card__sub">paused projects</span>
        </div>
    </a>

    <a href="/projects/projects/list" class="stat-card stat-card--link">
        <div class="stat-card__icon icon-circle icon-circle--navy">
            <i class="fa-solid fa-list-check" aria-hidden="true"></i>
        </div>
        <div class="stat-card__body">
            <span class="stat-card__label eyebrow">Open Total</span>
            <span class="stat-card__value"><?= number_format($stats['open_total']) ?></span>
            <span class="stat-card__sub">not completed or cancelled</span>
        </div>
    </a>

    <div class="stat-card">
        <div class="stat-card__icon icon-circle icon-circle--orange">
            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
        </div>
        <div class="stat-card__body">
            <span class="stat-card__label eyebrow">Overdue</span>
            <span class="stat-card__value"><?= number_format($stats['overdue']) ?></span>
            <span class="stat-card__sub">past due date</span>
        </div>
    </div>

</div>

<!-- ROW 2 — Breakdowns -->
<div class="content-panels content-panels--three mb-xl">

    <!-- By Status -->
    <div class="card content-panel">
        <h2 class="content-panel__title">
            <i class="fa-solid fa-circle-half-stroke" aria-hidden="true"></i>
            Open by Status
        </h2>
        <?php if ($openTotal === 0): ?>
        <div class="content-panel__empty">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            <p>No open projects.</p>
        </div>
        <?php else: ?>
        <ul class="breakdown-list">
            <?php foreach (['Draft', 'Active', 'On Hold'] as $s):
                $cnt = (int) ($byStatus[$s] ?? 0);
                if ($cnt === 0) continue;
            ?>
            <li class="breakdown-list__item">
                <a href="/projects/projects/list?status=<?= urlencode($s) ?>" class="table-link">
                    <span class="badge <?= $statusBadge[$s] ?? 'badge--neutral' ?>"><?= $e($s) ?></span>
                </a>
                <span class="breakdown-list__count"><?= $cnt ?> project<?= $cnt !== 1 ? 's' : '' ?></span>
                <span class="breakdown-list__value"><?= $pct($cnt) ?>%</span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>

    <!-- By Phase -->
    <div class="card content-panel">
        <h2 class="content-panel__title">
            <i class="fa-solid fa-arrows-turn-right" aria-hidden="true"></i>
            Open by Phase
        </h2>
        <?php if ($openTotal === 0): ?>
        <div class="content-panel__empty">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            <p>No open projects.</p>
        </div>
        <?php else: ?>
        <ul class="breakdown-list">
            <?php foreach (Project::PHASES as $ph):
                $cnt = (int) ($byPhase[$ph] ?? 0);
                if ($cnt === 0) continue;
            ?>
            <li class="breakdown-list__item">
                <a href="/projects/projects/list?phase=<?= urlencode($ph) ?>" class="table-link">
                    <span class="badge badge--neutral"><?= $e($ph) ?></span>
                </a>
                <span class="breakdown-list__count"><?= $cnt ?> project<?= $cnt !== 1 ? 's' : '' ?></span>
                <span class="breakdown-list__value"><?= $pct($cnt) ?>%</span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>

    <!-- By Priority -->
    <div class="card content-panel">
        <h2 class="content-panel__title">
            <i class="fa-solid fa-gauge-high" aria-hidden="true"></i>
            Open by Priority
        </h2>
        <?php if ($openTotal === 0): ?>
        <div class="content-panel__empty">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            <p>No open projects.</p>
        </div>
        <?php else: ?>
        <ul class="breakdown-list">
            <?php foreach (Project::PRIORITIES as $pr):
                $cnt = (int) ($byPriority[$pr] ?? 0);
                if ($cnt === 0) continue;
            ?>
            <li class="breakdown-list__item">
                <a href="/projects/projects/list?priority=<?= urlencode($pr) ?>" class="table-link">
                    <span class="badge <?= $priorityBadge[$pr] ?? 'badge--neutral' ?>"><?= $e($pr) ?></span>
                </a>
                <span class="breakdown-list__count"><?= $cnt ?> project<?= $cnt !== 1 ? 's' : '' ?></span>
                <span class="breakdown-list__value"><?= $pct($cnt) ?>%</span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>

</div>

<!-- ROW 3 — My Projects + Overdue -->
<div class="content-panels mb-xl">

    <!-- My Projects -->
    <div class="card content-panel">
        <h2 class="content-panel__title">
            <i class="fa-solid fa-circle-user" aria-hidden="true"></i>
            My Open Projects
        </h2>
        <?php if (empty($myProjects)): ?>
        <div class="content-panel__empty">
            <i class="fa-solid fa-inbox" aria-hidden="true"></i>
            <p>No open projects assigned to you.</p>
        </div>
        <?php else: ?>
        <ul class="record-list">
            <?php foreach ($myProjects as $p): ?>
            <li class="record-list__item">
                <div class="record-list__main">
                    <a href="/projects/projects/details?id=<?= (int) $p['id'] ?>" class="record-list__name">
                        <?= $e($p['name']) ?>
                    </a>
                    <span class="record-list__meta"><?= $e($p['phase'] ?? '') ?> · <?= $e(substr($p['due_date'] ?? '', 0, 10) ?: 'No due date') ?></span>
                </div>
                <div>
                    <span class="badge <?= $priorityBadge[$p['priority']] ?? 'badge--neutral' ?>"><?= $e($p['priority']) ?></span>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <div class="widget-footer">
            <a href="/projects/projects/list" class="record-list__all">View all projects →</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Overdue -->
    <div class="card content-panel">
        <h2 class="content-panel__title">
            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
            Overdue
            <?php if (!empty($overdue)): ?>
            <span class="badge badge--warning" style="margin-left:0.5rem;"><?= count($overdue) ?></span>
            <?php endif; ?>
        </h2>
        <?php if (empty($overdue)): ?>
        <div class="content-panel__empty">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            <p>No overdue projects.</p>
        </div>
        <?php else: ?>
        <ul class="record-list">
            <?php foreach ($overdue as $p): ?>
            <li class="record-list__item">
                <div class="record-list__main">
                    <a href="/projects/projects/details?id=<?= (int) $p['id'] ?>" class="record-list__name">
                        <?= $e($p['name']) ?>
                    </a>
                    <span class="record-list__meta">
                        <?= $e($p['owner_name'] ?? 'Unassigned') ?>
                        · Due <?= $e(substr($p['due_date'], 0, 10)) ?>
                    </span>
                </div>
                <div>
                    <span class="badge <?= $priorityBadge[$p['priority']] ?? 'badge--neutral' ?>"><?= $e($p['priority']) ?></span>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <div class="widget-footer">
            <a href="/projects/projects/list" class="record-list__all">View all projects →</a>
        </div>
        <?php endif; ?>
    </div>

</div>
