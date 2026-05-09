<?php
/**
 * Assets Module — Sidebar Navigation
 * Included by ControlPanel.php inside the sidebar nav list.
 */
?>

<li class="side-nav__module">
    <button class="side-nav__module-toggle" type="button" aria-expanded="false">
        <span>Assets</span>
        <i class="fa-solid fa-chevron-right side-nav__chevron" aria-hidden="true"></i>
    </button>
    <ul class="side-nav__module-links">
        <li>
            <a href="/assets/dashboard" class="side-nav__link<?= $currentSlug === 'assets/dashboard' ? ' is-active' : '' ?>">
                <i class="fa-solid fa-gauge-high side-nav__icon" aria-hidden="true"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="/assets/assets/list" class="side-nav__link<?= str_starts_with($currentSlug, 'assets/assets') ? ' is-active' : '' ?>">
                <i class="fa-solid fa-laptop side-nav__icon" aria-hidden="true"></i>
                <span>Assets</span>
            </a>
        </li>
        <li>
            <a href="/assets/guide" class="side-nav__link<?= $currentSlug === 'assets/guide' ? ' is-active' : '' ?>">
                <i class="fa-solid fa-book-open side-nav__icon" aria-hidden="true"></i>
                <span>User Guide</span>
            </a>
        </li>
    </ul>
</li>
