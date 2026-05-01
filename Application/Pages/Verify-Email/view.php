<?php $pageTitle = 'Email Verified'; ?>

<div class="login-wrap">

    <div class="login-card text-center">

        <!-- Logo -->
        <div class="login-brand" aria-hidden="true">
            <div class="logo-mark login-brand__mark">
                <span class="logo-mark__bar logo-mark__bar--top"></span>
                <span class="logo-mark__bar logo-mark__bar--mid">
                    <span class="logo-mark__pip"></span>
                </span>
                <span class="logo-mark__bar logo-mark__bar--bot"></span>
            </div>
            <span class="login-brand__wordmark">Aplos<span class="login-brand__accent">CRM</span></span>
        </div>

        <div class="mt-lg mb-md">
            <i class="fa-solid fa-circle-check icon-xl-green" aria-hidden="true"></i>
        </div>

        <h1 class="login-card__heading">Email verified!</h1>
        <p class="login-card__sub">
            Thanks<?= !empty($verifiedName) ? ', ' . htmlspecialchars($verifiedName, ENT_QUOTES, 'UTF-8') : '' ?>!
            Your email address has been confirmed and your account is now active.
        </p>
        <p class="login-card__sub mt-sm">
            Taking you to your dashboard in <strong id="countdown">3</strong> seconds&hellip;
        </p>

        <a id="verify-redirect"
           href="<?= htmlspecialchars($verifiedRedirect, ENT_QUOTES, 'UTF-8') ?>"
           data-redirect="<?= htmlspecialchars($verifiedRedirect, ENT_QUOTES, 'UTF-8') ?>"
           class="btn btn--primary mt-lg">
            Go to dashboard now
            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        </a>

    </div>

    <p class="login-footer">
        &copy; <?= date('Y') ?> AplosSuite &mdash;
        <span class="login-footer__tagline">Elegant by Design &middot; Powerful by Default</span>
    </p>

</div>
