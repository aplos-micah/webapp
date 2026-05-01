<?php $pageTitle = 'Create Account'; ?>

<div class="login-wrap">

    <div class="login-card">

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

        <h1 class="login-card__heading">Create your account</h1>
        <p class="login-card__sub">Enter your work email to get started.</p>

        <?php if (!empty($error)): ?>
        <div class="alert alert--warning login-alert" role="alert" aria-live="assertive">
            <span class="alert__icon" aria-hidden="true"><i class="fa-solid fa-triangle-exclamation"></i></span>
            <div class="alert__body"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <?php endif; ?>

        <form class="login-form" method="POST" action="/register" novalidate>

            <div class="form-group">
                <label class="form-label" for="email">Email address</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    class="input<?= !empty($error) ? ' input--error' : '' ?>"
                    value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="you@yourcompany.com"
                    autocomplete="email"
                    autofocus
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="input<?= !empty($error) ? ' input--error' : '' ?>"
                    placeholder="Minimum 8 characters"
                    autocomplete="new-password"
                    required
                >
                <span class="form-hint">At least 8 characters.</span>
            </div>

            <button type="submit" class="btn btn--primary login-form__submit">
                Create account
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </button>

        </form>

        <p class="login-card__switch">
            Already have an account? <a href="/login">Sign in</a>
        </p>

    </div>

    <p class="login-footer">
        &copy; <?= date('Y') ?> AplosSuite &mdash;
        <span class="login-footer__tagline">Elegant by Design &middot; Powerful by Default</span>
    </p>

</div>

