<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'AplosCRM', ENT_QUOTES, 'UTF-8') ?> — AplosCRM</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">

    <!-- Preconnect for Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,600;0,700;1,400&family=Open+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="/assets/vendor/fontawesome/css/all.min.css">

    <!-- Styles -->
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>

    <!-- Accessibility: skip navigation -->
    <a class="skip-link" href="#main-content">Skip to main content</a>

    <?php
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    $currentSlug  = $slug ?? strtolower(trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/')) ?: 'home';
    $userName     = $_SESSION['user_name']  ?? '';
    $userEmail    = $_SESSION['user_email'] ?? '';
    $parts        = explode(' ', trim($userName));
    $userInitials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
    ?>

    <!-- =========================================================
         TOP NAVIGATION BAR
         ========================================================= -->
    <header class="app-nav" role="banner">
        <div class="app-nav__inner">

            <!-- Brand -->
            <div class="app-nav__brand">
                <!-- Desktop: decorative logo mark -->
                <div class="app-logo-mark app-logo-mark--desktop" aria-hidden="true">
                    <span class="app-logo-mark__bar app-logo-mark__bar--top"></span>
                    <span class="app-logo-mark__bar app-logo-mark__bar--mid">
                        <span class="app-logo-mark__pip"></span>
                    </span>
                    <span class="app-logo-mark__bar app-logo-mark__bar--bot"></span>
                </div>
                <!-- Mobile: logo mark doubles as sidebar toggle -->
                <button class="app-nav__sidebar-toggle" id="sidebar-toggle"
                        aria-label="Open navigation menu" aria-expanded="false" aria-controls="app-sidebar">
                    <div class="app-logo-mark" aria-hidden="true">
                        <span class="app-logo-mark__bar app-logo-mark__bar--top"></span>
                        <span class="app-logo-mark__bar app-logo-mark__bar--mid">
                            <span class="app-logo-mark__pip"></span>
                        </span>
                        <span class="app-logo-mark__bar app-logo-mark__bar--bot"></span>
                    </div>
                </button>
                <a href="/home" class="app-nav__wordmark" aria-label="AplosCRM — go to dashboard">
                    Aplos<span class="app-nav__wordmark-accent">CRM</span>
                </a>
            </div>

            <!-- Global nav links -->
            <nav class="app-nav__links" aria-label="Global">
                <a href="/home"     class="app-nav__link<?= $currentSlug === 'home'     ? ' is-active' : '' ?>">Dashboard</a>
                <!--
                    <a href="/contacts" class="app-nav__link<?= $currentSlug === 'contacts' ? ' is-active' : '' ?>">Contacts</a>
                    <a href="/accounts" class="app-nav__link<?= $currentSlug === 'accounts' ? ' is-active' : '' ?>">Accounts</a>
                    <a href="/pipeline" class="app-nav__link<?= $currentSlug === 'pipeline' ? ' is-active' : '' ?>">Pipeline</a>
                -->
            </nav>

            <!-- User menu -->
            <div class="app-nav__user">
                <?php if (!empty($userInitials)): ?>
                <a href="/profile" class="app-nav__avatar" aria-label="My profile — <?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($userInitials, ENT_QUOTES, 'UTF-8') ?>
                </a>
                <?php endif; ?>
                <a href="/logout" class="app-nav__logout" title="Sign out" style="color:#fff;">
                    Log Out
                </a>
            </div>

        </div>
    </header>

    <!-- =========================================================
         APP BODY — sidebar + main content
         ========================================================= -->
    <div class="app-body">

        <!-- Mobile overlay — closes sidebar when tapped -->
        <div class="sidebar-overlay" id="sidebar-overlay" aria-hidden="true"></div>

        <!-- Sidebar navigation -->
        <nav class="app-sidebar" id="app-sidebar" aria-label="Application">
            <ul class="app-sidebar__nav" role="list">

                <li>
                    <a href="/home" class="app-sidebar__link<?= $currentSlug === 'home' ? ' is-active' : '' ?>">
                        <i class="fa-solid fa-gauge-high app-sidebar__icon" aria-hidden="true"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <?php require __DIR__ . '/../Module/CRM/Navigation.php'; ?>

                <?php if (($_SESSION['user_type'] ?? '') === 'admin'): ?>
                <?php require __DIR__ . '/../Module/Admin/Navigation.php'; ?>
                <?php endif; ?>

                <li class="app-sidebar__group-label"><span>Insights</span></li>

                <li>
                    <a href="/profile" class="app-sidebar__link<?= $currentSlug === 'profile' ? ' is-active' : '' ?>">
                        <i class="fa-solid fa-circle-user app-sidebar__icon" aria-hidden="true"></i>
                        <span>My Profile</span>
                    </a>
                </li>


            </ul>
        </nav>

        <!-- Main content -->
        <main class="app-main" id="main-content" tabindex="-1">
            <div class="app-main__inner">

                <?= $content ?? '' ?>
            </div>
        </main>

    </div>

    <!-- =========================================================
         FLASH TOAST
         ========================================================= -->
    <?php if (!empty($_SESSION['_flash'])):
        $flash = $_SESSION['_flash'];
        unset($_SESSION['_flash']);
    ?>
    <div class="toast toast--<?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?>"
         role="alert" aria-live="assertive" id="app-toast">
        <span class="toast__icon" aria-hidden="true">
            <?php if ($flash['type'] === 'error'): ?>
                <i class="fa-solid fa-triangle-exclamation"></i>
            <?php elseif ($flash['type'] === 'success'): ?>
                <i class="fa-solid fa-circle-check"></i>
            <?php else: ?>
                <i class="fa-solid fa-circle-info"></i>
            <?php endif; ?>
        </span>
        <span class="toast__message"><?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?></span>
        <button class="toast__close" onclick="this.closest('.toast').remove()" aria-label="Dismiss">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </div>
    <script>
        (function () {
            var t = document.getElementById('app-toast');
            if (!t) return;
            setTimeout(function () {
                t.classList.add('toast--hiding');
                setTimeout(function () { t.remove(); }, 400);
            }, 5000);
        }());
    </script>
    <?php endif; ?>

<script>
(function () {
    var toggle  = document.getElementById('sidebar-toggle');
    var sidebar = document.getElementById('app-sidebar');
    var overlay = document.getElementById('sidebar-overlay');

    if (!toggle || !sidebar || !overlay) return;

    function openSidebar() {
        sidebar.classList.add('is-open');
        overlay.classList.add('is-visible');
        toggle.setAttribute('aria-expanded', 'true');
        toggle.setAttribute('aria-label', 'Close navigation menu');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar.classList.remove('is-open');
        overlay.classList.remove('is-visible');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Open navigation menu');
        document.body.style.overflow = '';
    }

    toggle.addEventListener('click', function () {
        if (sidebar.classList.contains('is-open')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });

    overlay.addEventListener('click', closeSidebar);

    // Close sidebar on nav link click (page navigation)
    sidebar.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 768) closeSidebar();
        });
    });

    // Close on resize back to desktop
    window.addEventListener('resize', function () {
        if (window.innerWidth > 768) closeSidebar();
    });
}());
</script>

</body>
</html>
