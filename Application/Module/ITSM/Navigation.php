<?php
/**
 * ITSM Module — Sidebar Navigation
 * Included by ControlPanel.php inside the sidebar nav list.
 */
?>

<li class="side-nav__group"><span>ITSM</span></li>

<li>
    <a href="/itsm/dashboard"
       class="side-nav__link<?= $currentSlug === 'itsm/dashboard' ? ' is-active' : '' ?>">
        <i class="fa-solid fa-gauge-high side-nav__icon" aria-hidden="true"></i>
        <span>Dashboard</span>
    </a>
</li>

<li>
    <a href="/itsm/tickets/list"
       class="side-nav__link<?= str_starts_with($currentSlug, 'itsm/tickets') ? ' is-active' : '' ?>">
        <i class="fa-solid fa-ticket side-nav__icon" aria-hidden="true"></i>
        <span>Tickets</span>
    </a>
</li>

<li>
    <a href="/itsm/guide"
       class="side-nav__link<?= $currentSlug === 'itsm/guide' ? ' is-active' : '' ?>">
        <i class="fa-solid fa-book-open side-nav__icon" aria-hidden="true"></i>
        <span>User Guide</span>
    </a>
</li>
