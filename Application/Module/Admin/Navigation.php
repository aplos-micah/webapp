<?php
/**
 * Admin Module — Sidebar Navigation
 * Included by ControlPanel.php inside the sidebar nav list.
 * Only rendered for users with the 'admin' user type.
 */
?>

<li class="app-sidebar__group-label"><span>Admin</span></li>

<li>
    <a href="/admin/userlist" class="app-sidebar__link<?= str_starts_with($currentSlug, 'admin/userlist') ? ' is-active' : '' ?>">
        <i class="fa-solid fa-users app-sidebar__icon" aria-hidden="true"></i>
        <span>Users</span>
    </a>
</li>

<li>
    <a href="/admin/debug_currentuser" class="app-sidebar__link<?= str_starts_with($currentSlug, 'admin/debug_currentuser') ? ' is-active' : '' ?>">
        <i class="fa-solid fa-bug app-sidebar__icon" aria-hidden="true"></i>
        <span>Debug: Current User</span>
    </a>
</li>

<li>
    <a href="/admin/logviewer" class="app-sidebar__link<?= str_starts_with($currentSlug, 'admin/logviewer') ? ' is-active' : '' ?>">
        <i class="fa-solid fa-file-lines app-sidebar__icon" aria-hidden="true"></i>
        <span>System Logs</span>
    </a>
</li>
