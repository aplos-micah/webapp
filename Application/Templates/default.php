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
</head>
<body<?php if (Config::instance() === 'Test'): ?> class="has-test-banner"<?php endif; ?>>

    <!-- =========================================================
         TEST INSTANCE BANNER
         ========================================================= -->
    <?php if (Config::instance() === 'Test'): ?>
    <div class="app-test-banner">
        <i class="fa-solid fa-flask" aria-hidden="true"></i>
        Test Instance — data entered here is not production data.
    </div>
    <?php endif; ?>

    <!-- Accessibility: skip navigation -->
    <a class="skip-link" href="#main-content">Skip to main content</a>

    <!-- =========================================================
         TOP NAVIGATION BAR — brand only, no user chrome
         ========================================================= -->
    <header class="app-nav" role="banner">
        <div class="app-nav__inner">
            <div class="app-nav__brand">
                <div class="app-logo-mark" aria-hidden="true">
                    <span class="app-logo-mark__bar app-logo-mark__bar--top"></span>
                    <span class="app-logo-mark__bar app-logo-mark__bar--mid">
                        <span class="app-logo-mark__pip"></span>
                    </span>
                    <span class="app-logo-mark__bar app-logo-mark__bar--bot"></span>
                </div>
                <a href="/login" class="app-nav__wordmark" aria-label="AplosCRM">
                    Aplos<span class="app-nav__wordmark-accent">CRM</span>
                </a>
            </div>
        </div>
    </header>

    <!-- =========================================================
         MAIN CONTENT — full-height centred, no sidebar
         ========================================================= -->
    <main class="app-main app-main--centered" id="main-content" tabindex="-1">
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

</body>
</html>
