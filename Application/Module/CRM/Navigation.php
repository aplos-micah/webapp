<?php
/**
 * CRM Module — Sidebar Navigation
 * Included by ControlPanel.php inside the sidebar nav list.
 */
?>

<li class="app-sidebar__group-label"><span>CRM</span></li>

<li>
    <a href="/crm/accounts/list" class="app-sidebar__link<?= str_starts_with($currentSlug, 'crm/accounts') ? ' is-active' : '' ?>">
        <i class="fa-solid fa-building app-sidebar__icon" aria-hidden="true"></i>
        <span>Accounts</span>
    </a>
</li>

<li>
    <a href="/crm/contacts/list" class="app-sidebar__link<?= str_starts_with($currentSlug, 'crm/contacts') ? ' is-active' : '' ?>">
        <i class="fa-solid fa-address-card app-sidebar__icon" aria-hidden="true"></i>
        <span>Contacts</span>
    </a>
</li>

<li>
    <a href="/crm/opportunities/list" class="app-sidebar__link<?= str_starts_with($currentSlug, 'crm/opportunities') ? ' is-active' : '' ?>">
        <i class="fa-solid fa-handshake app-sidebar__icon" aria-hidden="true"></i>
        <span>Opportunities</span>
    </a>
</li>

<li>
    <a href="/crm/products/list" class="app-sidebar__link<?= str_starts_with($currentSlug, 'crm/products') ? ' is-active' : '' ?>">
        <i class="fa-solid fa-box app-sidebar__icon" aria-hidden="true"></i>
        <span>Products</span>
    </a>
</li>
