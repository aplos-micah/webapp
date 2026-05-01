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

    <!-- =========================================================
         TOP NAVIGATION BAR — brand only, no user chrome
         ========================================================= -->
    <header class="top-nav" role="banner">
        <div class="top-nav__inner">
            <div class="top-nav__brand">
                <div class="logo-mark" aria-hidden="true">
                    <span class="logo-mark__bar logo-mark__bar--top"></span>
                    <span class="logo-mark__bar logo-mark__bar--mid">
                        <span class="logo-mark__pip"></span>
                    </span>
                    <span class="logo-mark__bar logo-mark__bar--bot"></span>
                </div>
                <a href="/login" class="top-nav__wordmark" aria-label="AplosCRM">
                    Aplos<span class="top-nav__wordmark-accent">CRM</span>
                </a>
            </div>
        </div>
    </header>

    <!-- =========================================================
         MAIN CONTENT — full-height centred, no sidebar
         ========================================================= -->
    <main class="page-content page-content--centered" id="main-content" tabindex="-1">
        <?= $content ?? '' ?>
    </main>

    <!-- =========================================================
         FLASH TOAST
         ========================================================= -->
    <?php
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    if (!empty($_SESSION['_flash'])):
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
