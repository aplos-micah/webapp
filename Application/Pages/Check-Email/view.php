<?php $pageTitle = 'Check Your Email'; ?>

<div class="login-wrap">

    <div class="login-card">

        <!-- Logo -->
        <div class="login-brand" aria-hidden="true">
            <div class="app-logo-mark login-brand__mark">
                <span class="app-logo-mark__bar app-logo-mark__bar--top"></span>
                <span class="app-logo-mark__bar app-logo-mark__bar--mid">
                    <span class="app-logo-mark__pip"></span>
                </span>
                <span class="app-logo-mark__bar app-logo-mark__bar--bot"></span>
            </div>
            <span class="login-brand__wordmark">Aplos<span class="login-brand__accent">CRM</span></span>
        </div>

        <h1 class="login-card__heading">Check your inbox</h1>

        <?php if (!empty($flash)): ?>
        <div class="alert alert--<?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?> login-alert" role="alert" aria-live="assertive">
            <span class="alert__icon" aria-hidden="true">
                <?php if ($flash['type'] === 'warning'): ?>
                <i class="fa-solid fa-triangle-exclamation"></i>
                <?php else: ?>
                <i class="fa-solid fa-envelope"></i>
                <?php endif; ?>
            </span>
            <div class="alert__body"><?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <?php else: ?>
        <p class="login-card__sub">We sent a verification link to your email address. Click it to activate your account.</p>
        <?php endif; ?>

        <p class="login-card__sub" style="margin-top:1.5rem;">Didn't receive an email? Check your spam folder, or request a new link.</p>

        <form class="login-form" method="POST" action="/resend-verification" novalidate style="margin-top:1rem;">
            <div class="form-group">
                <label class="form-label" for="email">Email address</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    class="input"
                    value="<?= htmlspecialchars($pendingEmail ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="you@yourcompany.com"
                    autocomplete="email"
                    required
                >
            </div>
            <button type="submit" class="btn btn--secondary login-form__submit">
                Resend verification email
                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
            </button>
        </form>

        <p class="login-card__switch">
            Already verified? <a href="/login">Sign in</a>
        </p>

    </div>

    <p class="login-footer">
        &copy; <?= date('Y') ?> AplosSuite &mdash;
        <span class="login-footer__tagline">Elegant by Design &middot; Powerful by Default</span>
    </p>

</div>
