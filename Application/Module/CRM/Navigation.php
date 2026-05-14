<?php
/**
 * CRM Module — Sidebar Navigation
 * Included by ControlPanel.php inside the sidebar nav list.
 */
?>

<li class="side-nav__module">
    <button class="side-nav__module-toggle" type="button" aria-expanded="false">
        <span>CRM</span>
        <i class="fa-solid fa-chevron-right side-nav__chevron" aria-hidden="true"></i>
    </button>
    <ul class="side-nav__module-links">
        <li>
            <a href="/crm/dashboard" class="side-nav__link<?= $currentSlug === 'crm/dashboard' ? ' is-active' : '' ?>">
                <i class="fa-solid fa-gauge-high side-nav__icon" aria-hidden="true"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="/crm/activities/list" class="side-nav__link<?= str_starts_with($currentSlug, 'crm/activities') ? ' is-active' : '' ?>">
                <i class="fa-solid fa-list-check side-nav__icon" aria-hidden="true"></i>
                <span>Activities</span>
            </a>
        </li>
        <li>
            <a href="/crm/accounts/list" class="side-nav__link<?= str_starts_with($currentSlug, 'crm/accounts') ? ' is-active' : '' ?>">
                <i class="fa-solid fa-building side-nav__icon" aria-hidden="true"></i>
                <span>Accounts</span>
            </a>
        </li>
        <li>
            <a href="/crm/contacts/list" class="side-nav__link<?= str_starts_with($currentSlug, 'crm/contacts') ? ' is-active' : '' ?>">
                <i class="fa-solid fa-address-card side-nav__icon" aria-hidden="true"></i>
                <span>Contacts</span>
            </a>
        </li>
        <li>
            <a href="/crm/opportunities/list" class="side-nav__link<?= str_starts_with($currentSlug, 'crm/opportunities') ? ' is-active' : '' ?>">
                <i class="fa-solid fa-handshake side-nav__icon" aria-hidden="true"></i>
                <span>Opportunities</span>
            </a>
        </li>
        <li>
            <a href="/crm/products/list" class="side-nav__link<?= str_starts_with($currentSlug, 'crm/products') ? ' is-active' : '' ?>">
                <i class="fa-solid fa-box side-nav__icon" aria-hidden="true"></i>
                <span>Products</span>
            </a>
        </li>
        <li>
            <a href="/crm/guide" class="side-nav__link<?= $currentSlug === 'crm/guide' ? ' is-active' : '' ?>">
                <i class="fa-solid fa-book-open side-nav__icon" aria-hidden="true"></i>
                <span>User Guide</span>
            </a>
        </li>
    </ul>
</li>

<li class="side-nav__module">
    <button class="side-nav__module-toggle" type="button" aria-expanded="false">
        <span>CRM Setup</span>
        <i class="fa-solid fa-chevron-right side-nav__chevron" aria-hidden="true"></i>
    </button>
    <ul class="side-nav__module-links">
        <li>
            <a href="/crm/setup/activitytypes" class="side-nav__link<?= str_starts_with($currentSlug, 'crm/setup') ? ' is-active' : '' ?>">
                <i class="fa-solid fa-sliders side-nav__icon" aria-hidden="true"></i>
                <span>Activity Types</span>
            </a>
        </li>
    </ul>
</li>
