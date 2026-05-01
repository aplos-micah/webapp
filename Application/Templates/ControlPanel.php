<?php
$_currentSlug  = $slug ?? strtolower(trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/')) ?: 'home';
$_moduleSegment = strstr($_currentSlug, '/', true) ?: null;
$_moduleJs      = $_moduleSegment
    ? ($_SERVER['DOCUMENT_ROOT'] . '/assets/js/' . $_moduleSegment . '.js')
    : null;
$_hasModuleJs   = $_moduleJs && file_exists($_moduleJs);
?>
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

    <!-- htmx -->
    <script src="/assets/vendor/htmx.min.js" defer></script>

    <!-- App -->
    <script src="/assets/js/app.js" defer></script>
    <?php if ($_hasModuleJs): ?>
    <script src="/assets/js/<?= htmlspecialchars($_moduleSegment, ENT_QUOTES, 'UTF-8') ?>.js" defer></script>
    <?php endif; ?>
</head>
<body<?php if (Config::instance() === 'Test'): ?> class="has-env-banner"<?php endif; ?>>

    <!-- =========================================================
         TEST INSTANCE BANNER
         ========================================================= -->
    <?php if (Config::instance() === 'Test'): ?>
    <div class="env-banner">
        <i class="fa-solid fa-flask" aria-hidden="true"></i>
        Test Instance — data entered here is not production data.
    </div>
    <?php endif; ?>

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
    <header class="top-nav" role="banner">
        <div class="top-nav__inner">

            <!-- Brand -->
            <div class="top-nav__brand">
                <!-- Desktop: decorative logo mark -->
                <div class="logo-mark logo-mark--desktop" aria-hidden="true">
                    <span class="logo-mark__bar logo-mark__bar--top"></span>
                    <span class="logo-mark__bar logo-mark__bar--mid">
                        <span class="logo-mark__pip"></span>
                    </span>
                    <span class="logo-mark__bar logo-mark__bar--bot"></span>
                </div>
                <!-- Mobile: logo mark doubles as sidebar toggle -->
                <button class="top-nav__menu-toggle" id="sidebar-toggle"
                        aria-label="Open navigation menu" aria-expanded="false" aria-controls="side-nav">
                    <div class="logo-mark" aria-hidden="true">
                        <span class="logo-mark__bar logo-mark__bar--top"></span>
                        <span class="logo-mark__bar logo-mark__bar--mid">
                            <span class="logo-mark__pip"></span>
                        </span>
                        <span class="logo-mark__bar logo-mark__bar--bot"></span>
                    </div>
                </button>
                <a href="/home" class="top-nav__wordmark" aria-label="AplosCRM — go to dashboard">
                    Aplos<span class="top-nav__wordmark-accent">CRM</span>
                </a>
            </div>

            <!-- Global nav links -->
            <nav class="top-nav__links" aria-label="Global">
                <a href="/home"     class="top-nav__link<?= $currentSlug === 'home'     ? ' is-active' : '' ?>">Dashboard</a>
                <!--
                    <a href="/contacts" class="top-nav__link<?= $currentSlug === 'contacts' ? ' is-active' : '' ?>">Contacts</a>
                    <a href="/accounts" class="top-nav__link<?= $currentSlug === 'accounts' ? ' is-active' : '' ?>">Accounts</a>
                    <a href="/pipeline" class="top-nav__link<?= $currentSlug === 'pipeline' ? ' is-active' : '' ?>">Pipeline</a>
                -->
            </nav>

            <!-- User menu -->
            <div class="top-nav__user">
                <?php if (!empty($userInitials)): ?>
                <a href="/profile" class="top-nav__avatar" aria-label="My profile — <?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($userInitials, ENT_QUOTES, 'UTF-8') ?>
                </a>
                <?php endif; ?>
                <a href="/logout" class="top-nav__logout" title="Sign out">
                    Log Out
                </a>
            </div>

        </div>
    </header>



    <!-- =========================================================
         APP BODY — sidebar + main content
         ========================================================= -->
    <div class="page-layout">

        <!-- Mobile overlay — closes sidebar when tapped -->
        <div class="nav-overlay" id="nav-overlay" aria-hidden="true"></div>

        <!-- Sidebar navigation -->
        <nav class="side-nav" id="side-nav" aria-label="Application">
            <ul class="side-nav__list" role="list">

                <li>
                    <a href="/home" class="side-nav__link<?= $currentSlug === 'home' ? ' is-active' : '' ?>">
                        <i class="fa-solid fa-gauge-high side-nav__icon" aria-hidden="true"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <?php require __DIR__ . '/../Module/CRM/Navigation.php'; ?>

                <?php if (($_SESSION['user_type'] ?? '') === 'admin'): ?>
                <?php require __DIR__ . '/../Module/Admin/Navigation.php'; ?>
                <?php endif; ?>

                <li class="side-nav__group"><span>Insights</span></li>

                <li>
                    <a href="/company" class="side-nav__link<?= $currentSlug === 'company' ? ' is-active' : '' ?>">
                        <i class="fa-solid fa-building side-nav__icon" aria-hidden="true"></i>
                        <span>My Company</span>
                    </a>
                </li>

                <li>
                    <a href="/profile" class="side-nav__link<?= $currentSlug === 'profile' ? ' is-active' : '' ?>">
                        <i class="fa-solid fa-circle-user side-nav__icon" aria-hidden="true"></i>
                        <span>My Profile</span>
                    </a>
                </li>


            </ul>
        </nav>

        <!-- Main content -->
        <main class="page-content" id="main-content" tabindex="-1">
            <div class="page-content__inner">

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
        <button class="toast__close" aria-label="Dismiss">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </div>
    <?php endif; ?>

</body>
</html>
