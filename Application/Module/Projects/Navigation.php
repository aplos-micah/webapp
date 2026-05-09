<?php
/**
 * Projects Module — Sidebar Navigation
 * Included by ControlPanel.php inside the sidebar nav list.
 */
?>

<li class="side-nav__module">
    <button class="side-nav__module-toggle" type="button" aria-expanded="false">
        <span>Projects</span>
        <i class="fa-solid fa-chevron-right side-nav__chevron" aria-hidden="true"></i>
    </button>
    <ul class="side-nav__module-links">
        <li>
            <a href="/projects/dashboard" class="side-nav__link<?= $currentSlug === 'projects/dashboard' ? ' is-active' : '' ?>">
                <i class="fa-solid fa-gauge-high side-nav__icon" aria-hidden="true"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="/projects/projects/list" class="side-nav__link<?= str_starts_with($currentSlug, 'projects/projects') ? ' is-active' : '' ?>">
                <i class="fa-solid fa-diagram-project side-nav__icon" aria-hidden="true"></i>
                <span>Projects</span>
            </a>
        </li>
        <li>
            <a href="/projects/guide" class="side-nav__link<?= $currentSlug === 'projects/guide' ? ' is-active' : '' ?>">
                <i class="fa-solid fa-book-open side-nav__icon" aria-hidden="true"></i>
                <span>User Guide</span>
            </a>
        </li>
    </ul>
</li>
