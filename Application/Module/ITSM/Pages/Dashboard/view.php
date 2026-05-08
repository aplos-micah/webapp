<?php
$pageTitle = 'ITSM Dashboard';
$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

$priorityBadge = [
    'Critical' => 'badge--warning',
    'High'     => 'badge--info',
    'Medium'   => 'badge--neutral',
    'Low'      => 'badge--neutral',
];
$statusBadge = [
    'New'         => 'badge--neutral',
    'In Progress' => 'badge--info',
    'Pending'     => 'badge--warning',
    'Resolved'    => 'badge--success',
    'Closed'      => 'badge--neutral',
];

// Pre-key breakdowns for easy lookup
$byStatus   = array_column($stats['by_status'],   'cnt', 'status');
$byPriority = array_column($stats['by_priority'], 'cnt', 'priority');
$byType     = array_column($stats['by_type'],     'cnt', 'type');
$openTotal  = $stats['open_total'];

$pct = fn(int $n) => $openTotal > 0 ? round(($n / $openTotal) * 100) : 0;
?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">ITSM</p>
        <h1 class="dash-header__title">Dashboard</h1>
        <p class="dash-header__sub"><?= date('l, F j, Y') ?></p>
    </div>
    <div>
        <a href="/itsm/tickets/new" class="btn btn--primary">
            <i class="fa-solid fa-plus" aria-hidden="true"></i> New Ticket
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- ROW 1 — Stat cards                                                     -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="stats-grid mb-xl">

    <a href="/itsm/tickets/list" class="stat-card stat-card--link">
        <div class="stat-card__icon icon-circle icon-circle--navy">
            <i class="fa-solid fa-ticket" aria-hidden="true"></i>
        </div>
        <div class="stat-card__body">
            <span class="stat-card__label eyebrow">Open Tickets</span>
            <span class="stat-card__value"><?= number_format($stats['open_total']) ?></span>
            <span class="stat-card__sub">not resolved or closed</span>
        </div>
    </a>

    <a href="/itsm/tickets/list?priority=Critical" class="stat-card stat-card--link">
        <div class="stat-card__icon icon-circle icon-circle--orange">
            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
        </div>
        <div class="stat-card__body">
            <span class="stat-card__label eyebrow">Critical / High</span>
            <span class="stat-card__value"><?= number_format($stats['critical_high']) ?></span>
            <span class="stat-card__sub">open high-priority tickets</span>
        </div>
    </a>

    <a href="/itsm/tickets/list" class="stat-card stat-card--link">
        <div class="stat-card__icon icon-circle icon-circle--mid-blue">
            <i class="fa-solid fa-user-slash" aria-hidden="true"></i>
        </div>
        <div class="stat-card__body">
            <span class="stat-card__label eyebrow">Unassigned</span>
            <span class="stat-card__value"><?= number_format($stats['unassigned']) ?></span>
            <span class="stat-card__sub">open tickets with no owner</span>
        </div>
    </a>

    <div class="stat-card">
        <div class="stat-card__icon icon-circle icon-circle--green">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
        </div>
        <div class="stat-card__body">
            <span class="stat-card__label eyebrow">Resolved Today</span>
            <span class="stat-card__value"><?= number_format($stats['resolved_today']) ?></span>
            <span class="stat-card__sub">tickets closed in last 24 h</span>
        </div>
    </div>

</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- ROW 2 — Breakdowns (three panels)                                      -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
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
            <p>No open tickets.</p>
        </div>
        <?php else: ?>
        <ul class="breakdown-list">
            <?php foreach (['New', 'In Progress', 'Pending'] as $s):
                $cnt = (int) ($byStatus[$s] ?? 0);
                if ($cnt === 0) continue;
            ?>
            <li class="breakdown-list__item">
                <a href="/itsm/tickets/list?status=<?= urlencode($s) ?>" class="table-link">
                    <span class="badge <?= $statusBadge[$s] ?? 'badge--neutral' ?>"><?= $e($s) ?></span>
                </a>
                <span class="breakdown-list__count"><?= $cnt ?> ticket<?= $cnt !== 1 ? 's' : '' ?></span>
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
            <p>No open tickets.</p>
        </div>
        <?php else: ?>
        <ul class="breakdown-list">
            <?php foreach (Ticket::PRIORITIES as $p):
                $cnt = (int) ($byPriority[$p] ?? 0);
                if ($cnt === 0) continue;
            ?>
            <li class="breakdown-list__item">
                <a href="/itsm/tickets/list?priority=<?= urlencode($p) ?>" class="table-link">
                    <span class="badge <?= $priorityBadge[$p] ?? 'badge--neutral' ?>"><?= $e($p) ?></span>
                </a>
                <span class="breakdown-list__count"><?= $cnt ?> ticket<?= $cnt !== 1 ? 's' : '' ?></span>
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
            Open by Type
        </h2>
        <?php if ($openTotal === 0): ?>
        <div class="content-panel__empty">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            <p>No open tickets.</p>
        </div>
        <?php else: ?>
        <ul class="breakdown-list">
            <?php foreach (Ticket::TYPES as $t):
                $cnt = (int) ($byType[$t] ?? 0);
                if ($cnt === 0) continue;
            ?>
            <li class="breakdown-list__item">
                <a href="/itsm/tickets/list?type=<?= urlencode($t) ?>" class="table-link">
                    <span class="badge badge--neutral"><?= $e($t) ?></span>
                </a>
                <span class="breakdown-list__count"><?= $cnt ?> ticket<?= $cnt !== 1 ? 's' : '' ?></span>
                <span class="breakdown-list__value"><?= $pct($cnt) ?>%</span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>

</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- ROW 3 — My Tickets + Recent Activity                                   -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="content-panels mb-xl">

    <!-- My Open Tickets -->
    <div class="card content-panel">
        <h2 class="content-panel__title">
            <i class="fa-solid fa-circle-user" aria-hidden="true"></i>
            My Open Tickets
        </h2>
        <?php if (empty($myTickets)): ?>
        <div class="content-panel__empty">
            <i class="fa-solid fa-inbox" aria-hidden="true"></i>
            <p>No open tickets assigned to you.</p>
        </div>
        <?php else: ?>
        <ul class="record-list">
            <?php foreach ($myTickets as $t): ?>
            <li class="record-list__item">
                <div class="record-list__main">
                    <a href="/itsm/tickets/details?id=<?= (int) $t['id'] ?>" class="record-list__name">
                        <?= $e($t['ticket_number']) ?> — <?= $e($t['title']) ?>
                    </a>
                    <span class="record-list__meta"><?= $e(substr($t['created_at'] ?? '', 0, 10)) ?></span>
                </div>
                <div>
                    <span class="badge <?= $priorityBadge[$t['priority']] ?? 'badge--neutral' ?>"><?= $e($t['priority']) ?></span>
                    <span class="badge <?= $statusBadge[$t['status']] ?? 'badge--neutral' ?>"><?= $e($t['status']) ?></span>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <div class="widget-footer">
            <a href="/itsm/tickets/list" class="record-list__all">View all tickets →</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Recent Activity -->
    <div class="card content-panel">
        <h2 class="content-panel__title">
            <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
            Recent Activity
        </h2>
        <?php if (empty($recent)): ?>
        <div class="content-panel__empty">
            <i class="fa-regular fa-ticket" aria-hidden="true"></i>
            <p>No tickets yet. <a href="/itsm/tickets/new">Create the first one</a>.</p>
        </div>
        <?php else: ?>
        <ul class="record-list">
            <?php foreach ($recent as $t): ?>
            <li class="record-list__item">
                <div class="record-list__main">
                    <a href="/itsm/tickets/details?id=<?= (int) $t['id'] ?>" class="record-list__name">
                        <?= $e($t['ticket_number']) ?> — <?= $e($t['title']) ?>
                    </a>
                    <span class="record-list__meta">
                        <?= $e(!empty($t['assigned_name']) ? $t['assigned_name'] : 'Unassigned') ?>
                        · <?= $e(substr($t['updated_at'] ?? '', 0, 10)) ?>
                    </span>
                </div>
                <div>
                    <span class="badge <?= $statusBadge[$t['status']] ?? 'badge--neutral' ?>"><?= $e($t['status']) ?></span>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <div class="widget-footer">
            <a href="/itsm/tickets/list" class="record-list__all">View all tickets →</a>
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- ROW 4 — Unassigned Tickets (full width)                                -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="card content-panel">
    <h2 class="content-panel__title">
        <i class="fa-solid fa-user-slash" aria-hidden="true"></i>
        Unassigned Tickets
        <?php if (!empty($unassigned)): ?>
        <span class="badge badge--warning" style="margin-left:0.5rem;"><?= count($unassigned) ?></span>
        <?php endif; ?>
    </h2>

    <?php if (empty($unassigned)): ?>
    <div class="content-panel__empty">
        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
        <p>All open tickets are assigned.</p>
    </div>
    <?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ticket #</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unassigned as $t): ?>
                <tr>
                    <td>
                        <a href="/itsm/tickets/details?id=<?= (int) $t['id'] ?>" class="table-link">
                            <?= $e($t['ticket_number'] ?: 'TKT-??????') ?>
                        </a>
                    </td>
                    <td><?= $e($t['title']) ?></td>
                    <td><?= $e($t['type'] ?? '—') ?></td>
                    <td><span class="badge <?= $priorityBadge[$t['priority']] ?? 'badge--neutral' ?>"><?= $e($t['priority']) ?></span></td>
                    <td><span class="badge <?= $statusBadge[$t['status']] ?? 'badge--neutral' ?>"><?= $e($t['status']) ?></span></td>
                    <td><?= $e(substr($t['created_at'] ?? '', 0, 10)) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
