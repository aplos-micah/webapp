<?php
/**
 * KB Module — Sidebar Navigation
 * Included by ControlPanel.php inside the sidebar nav list.
 */
?>

<li class="side-nav__module">
    <button class="side-nav__module-toggle" type="button" aria-expanded="false">
        <span>Knowledge Base</span>
        <i class="fa-solid fa-chevron-right side-nav__chevron" aria-hidden="true"></i>
    </button>
    <ul class="side-nav__module-links">
        <li>
            <a href="/kb/dashboard" class="side-nav__link<?= $currentSlug === 'kb/dashboard' ? ' is-active' : '' ?>">
                <i class="fa-solid fa-gauge-high side-nav__icon" aria-hidden="true"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="/kb/articles/list" class="side-nav__link<?= str_starts_with($currentSlug, 'kb/articles') ? ' is-active' : '' ?>">
                <i class="fa-solid fa-book side-nav__icon" aria-hidden="true"></i>
                <span>Articles</span>
            </a>
        </li>
        <li>
            <a href="/kb/guide" class="side-nav__link<?= $currentSlug === 'kb/guide' ? ' is-active' : '' ?>">
                <i class="fa-solid fa-book-open side-nav__icon" aria-hidden="true"></i>
                <span>User Guide</span>
            </a>
        </li>
    </ul>
</li>
