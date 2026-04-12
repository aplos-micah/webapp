<?php $pageTitle = 'Dashboard'; ?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">Control Panel</p>
        <h1 class="dash-header__title">
            Welcome<?= !empty($data['user_name']) ? ', ' . htmlspecialchars($data['user_name'], ENT_QUOTES, 'UTF-8') : '' ?>.
        </h1>
        <?php if (!empty($data['user_email'])): ?>
        <p class="dash-header__sub"><?= htmlspecialchars($data['user_email'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </div>
    <div class="dash-header__date">
        <i class="fa-regular fa-calendar" aria-hidden="true"></i>
        <?= date('l, F j, Y') ?>
    </div>
</div>

<?php
require_once __DIR__ . '/../../Module/CRM/Widgets/AccountPerformance.php';
$perfWidget = new AccountPerformance(new DB());
?>
<div class="card related-card mb-xl">
    <div class="related-card__header">
        <h2 class="related-card__title">
            <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
            CRM Performance
        </h2>
    </div>
    <?= $perfWidget->render() ?>
</div>

<hr class="divider--green mb-xl">

<!-- Stat cards -->
<div class="dash-stats">

    <a href="/crm/contacts/list" class="dash-stat dash-stat--link">
        <div class="dash-stat__icon icon-circle icon-circle--navy">
            <i class="fa-solid fa-address-book" aria-hidden="true"></i>
        </div>
        <div class="dash-stat__body">
            <span class="dash-stat__label eyebrow">Contacts</span>
            <span class="dash-stat__value"><?= number_format($data['contacts_count']) ?></span>
        </div>
    </a>

    <a href="/crm/opportunities/list" class="dash-stat dash-stat--link">
        <div class="dash-stat__icon icon-circle icon-circle--green">
            <i class="fa-solid fa-handshake" aria-hidden="true"></i>
        </div>
        <div class="dash-stat__body">
            <span class="dash-stat__label eyebrow">Open Deals</span>
            <span class="dash-stat__value"><?= number_format($data['open_deals_count']) ?></span>
            <?php if ($data['open_deals_value'] > 0): ?>
            <span class="dash-stat__sub">USD <?= number_format($data['open_deals_value'], 0) ?></span>
            <?php endif; ?>
        </div>
    </a>

    <a href="/crm/contacts/list" class="dash-stat dash-stat--link">
        <div class="dash-stat__icon icon-circle icon-circle--mid-blue">
            <i class="fa-solid fa-bolt" aria-hidden="true"></i>
        </div>
        <div class="dash-stat__body">
            <span class="dash-stat__label eyebrow">Leads</span>
            <span class="dash-stat__value"><?= number_format($data['leads_count']) ?></span>
        </div>
    </a>

    <div class="dash-stat">
        <div class="dash-stat__icon icon-circle icon-circle--orange">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
        </div>
        <div class="dash-stat__body">
            <span class="dash-stat__label eyebrow">Tasks Due</span>
            <span class="dash-stat__value">—</span>
        </div>
    </div>

</div>

<!-- CRM Widget tiles -->
<div class="dash-panels dash-panels--three mb-lg">

    <div class="card dash-panel">
        <h2 class="dash-panel__title">
            <i class="fa-solid fa-address-book" aria-hidden="true"></i>
            Recent Contacts
        </h2>
        <?= $data['contacts_tile'] ?>
    </div>

    <div class="card dash-panel">
        <h2 class="dash-panel__title">
            <i class="fa-solid fa-handshake" aria-hidden="true"></i>
            Open Deals
        </h2>
        <?= $data['open_deals_tile'] ?>
    </div>

    <div class="card dash-panel">
        <h2 class="dash-panel__title">
            <i class="fa-solid fa-bolt" aria-hidden="true"></i>
            Leads
        </h2>
        <?= $data['leads_tile'] ?>
    </div>

</div>

<!-- Lower panels -->
<div class="dash-panels">

    <div class="card dash-panel">
        <h2 class="dash-panel__title">
            <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
            Recent Activity
        </h2>
        <div class="dash-panel__empty">
            <i class="fa-regular fa-folder-open" aria-hidden="true"></i>
            <p>No activity yet. Start by adding a contact or creating a deal.</p>
        </div>
    </div>

    <div class="card dash-panel">
        <h2 class="dash-panel__title">
            <i class="fa-solid fa-bolt" aria-hidden="true"></i>
            Quick Actions
        </h2>
        <ul class="dash-actions">
            <li>
                <a href="/crm/contacts/new" class="dash-action">
                    <span class="icon-circle icon-circle--ice dash-action__icon">
                        <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                    </span>
                    <span class="dash-action__label">Add Contact</span>
                    <i class="fa-solid fa-chevron-right dash-action__arrow" aria-hidden="true"></i>
                </a>
            </li>
            <li>
                <a href="/crm/opportunities/new" class="dash-action">
                    <span class="icon-circle icon-circle--ice dash-action__icon">
                        <i class="fa-solid fa-handshake" aria-hidden="true"></i>
                    </span>
                    <span class="dash-action__label">New Opportunity</span>
                    <i class="fa-solid fa-chevron-right dash-action__arrow" aria-hidden="true"></i>
                </a>
            </li>
            <li>
                <a href="/crm/accounts/new" class="dash-action">
                    <span class="icon-circle icon-circle--ice dash-action__icon">
                        <i class="fa-solid fa-building" aria-hidden="true"></i>
                    </span>
                    <span class="dash-action__label">New Account</span>
                    <i class="fa-solid fa-chevron-right dash-action__arrow" aria-hidden="true"></i>
                </a>
            </li>
            <li>
                <a href="/reports" class="dash-action">
                    <span class="icon-circle icon-circle--ice dash-action__icon">
                        <i class="fa-solid fa-chart-bar" aria-hidden="true"></i>
                    </span>
                    <span class="dash-action__label">View Reports</span>
                    <i class="fa-solid fa-chevron-right dash-action__arrow" aria-hidden="true"></i>
                </a>
            </li>
        </ul>
    </div>

</div>
