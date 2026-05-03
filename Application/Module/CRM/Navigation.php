<?php
/**
 * CRM Module — Sidebar Navigation
 * Included by ControlPanel.php inside the sidebar nav list.
 */
?>

<li class="side-nav__group"><span>CRM</span></li>

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
