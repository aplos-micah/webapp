<?php
/**
 * Admin Module — Sidebar Navigation
 * Included by ControlPanel.php inside the sidebar nav list.
 * Only rendered for users with the 'admin' user type.
 */
?>

<li class="side-nav__group"><span>Admin</span></li>

<li>
    <a href="/admin/userlist" class="side-nav__link<?= str_starts_with($currentSlug, 'admin/userlist') ? ' is-active' : '' ?>">
        <i class="fa-solid fa-users side-nav__icon" aria-hidden="true"></i>
        <span>Users</span>
    </a>
</li>

<li>
    <a href="/admin/companylist" class="side-nav__link<?= str_starts_with($currentSlug, 'admin/companylist') ? ' is-active' : '' ?>">
        <i class="fa-solid fa-building side-nav__icon" aria-hidden="true"></i>
        <span>Companies</span>
    </a>
</li>

<li>
    <a href="/admin/debug_currentuser" class="side-nav__link<?= str_starts_with($currentSlug, 'admin/debug_currentuser') ? ' is-active' : '' ?>">
        <i class="fa-solid fa-bug side-nav__icon" aria-hidden="true"></i>
        <span>Debug: Current User</span>
    </a>
</li>

<li>
    <a href="/admin/manageoauth" class="side-nav__link<?= str_starts_with($currentSlug, 'admin/manageoauth') ? ' is-active' : '' ?>">
        <i class="fa-solid fa-key side-nav__icon" aria-hidden="true"></i>
        <span>OAuth Sessions</span>
    </a>
</li>

<li>
    <a href="/admin/logviewer" class="side-nav__link<?= str_starts_with($currentSlug, 'admin/logviewer') ? ' is-active' : '' ?>">
        <i class="fa-solid fa-file-lines side-nav__icon" aria-hidden="true"></i>
        <span>System Logs</span>
    </a>
</li>

<li>
    <a href="/admin/migrations" class="side-nav__link<?= str_starts_with($currentSlug, 'admin/migrations') ? ' is-active' : '' ?>">
        <i class="fa-solid fa-database side-nav__icon" aria-hidden="true"></i>
        <span>Migrations</span>
    </a>
</li>

<li>
    <a href="/admin/validatortest" class="side-nav__link<?= str_starts_with($currentSlug, 'admin/validatortest') ? ' is-active' : '' ?>">
        <i class="fa-solid fa-flask side-nav__icon" aria-hidden="true"></i>
        <span>Validator Tests</span>
    </a>
</li>
