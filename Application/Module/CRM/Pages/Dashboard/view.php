<?php $e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8'); ?>

<div class="dash-header">
    <div>
        <p class="eyebrow">CRM</p>
        <h1 class="dash-header__title">Dashboard</h1>
        <p class="dash-header__sub">Current quarter activity summary</p>
    </div>
    <div class="btn-group">
        <a href="/crm/activities/new" class="btn btn--primary">
            <i class="fa-solid fa-plus" aria-hidden="true"></i> Log Activity
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<!-- Stat cards -->
<div class="stats-grid mb-xl">
    <div class="stat-card">
        <div class="stat-card__label">Activities This Quarter</div>
        <div class="stat-card__value"><?= number_format($quarterTotals['count']) ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Total Spend This Quarter</div>
        <div class="stat-card__value">$<?= number_format($quarterTotals['total_cost'], 2) ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Avg Cost Per Activity</div>
        <div class="stat-card__value"><?= $avgCost > 0 ? '$' . number_format($avgCost, 2) : '—' ?></div>
    </div>
</div>

<!-- Activities over time chart -->
<div class="card profile-card mb-xl">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-chart-bar" aria-hidden="true"></i>
        Activities This Quarter (by Week)
    </h2>
    <?php if (empty($weeklyData)): ?>
    <div class="content-panel__empty">
        <i class="fa-regular fa-calendar-xmark" aria-hidden="true"></i>
        <p>No activities logged this quarter yet. <a href="/crm/activities/new">Log the first one.</a></p>
    </div>
    <?php else: ?>
    <div style="position:relative;height:280px">
        <canvas id="chart-activities-over-time" aria-label="Activities per week this quarter" role="img"></canvas>
    </div>
    <script type="application/json" id="chart-data">
    {"labels":<?= $chartLabels ?>,"counts":<?= $chartCounts ?>}
    </script>
    <?php endif; ?>
</div>

<!-- Quick links -->
<div class="card profile-card">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-bolt" aria-hidden="true"></i>
        Quick Links
    </h2>
    <ul class="record-list">
        <li class="record-list__item">
            <a href="/crm/activities/list" class="record-list__name">
                <i class="fa-solid fa-list-check" aria-hidden="true"></i> All Activities
            </a>
        </li>
        <li class="record-list__item">
            <a href="/crm/accounts/list" class="record-list__name">
                <i class="fa-solid fa-building" aria-hidden="true"></i> Accounts
            </a>
        </li>
        <li class="record-list__item">
            <a href="/crm/opportunities/list" class="record-list__name">
                <i class="fa-solid fa-handshake" aria-hidden="true"></i> Opportunities
            </a>
        </li>
        <li class="record-list__item">
            <a href="/crm/setup/activitytypes" class="record-list__name">
                <i class="fa-solid fa-sliders" aria-hidden="true"></i> Manage Activity Types
            </a>
        </li>
    </ul>
</div>
