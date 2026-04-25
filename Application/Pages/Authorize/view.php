<?php $pageTitle = 'Authorize Application'; ?>

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

        <?php if (!empty($data['oauth_error'])): ?>

            <h1 class="login-card__heading">Authorization Error</h1>
            <div class="alert alert--warning login-alert" role="alert">
                <span class="alert__icon" aria-hidden="true"><i class="fa-solid fa-triangle-exclamation"></i></span>
                <div class="alert__body"><?= htmlspecialchars($data['oauth_error'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>

        <?php else: ?>

            <h1 class="login-card__heading">Authorize Application</h1>
            <p class="login-card__sub">
                <strong><?= htmlspecialchars($data['client_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                is requesting access to your AplosCRM data.
            </p>

            <ul style="margin: 0 0 1.5rem; padding-left: 1.25rem; color: var(--color-text-secondary, #555);">
                <li>Read accounts, contacts, opportunities, and products</li>
            </ul>

            <form method="POST" action="/authorize">
                <input type="hidden" name="client_id"       value="<?= htmlspecialchars($data['client_id'],      ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="redirect_uri"    value="<?= htmlspecialchars($data['redirect_uri'],   ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="code_challenge"  value="<?= htmlspecialchars($data['code_challenge'], ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="state"           value="<?= htmlspecialchars($data['state'],          ENT_QUOTES, 'UTF-8') ?>">

                <div style="display: flex; gap: 0.75rem;">
                    <button type="submit" name="_action" value="allow" class="btn btn--primary" style="flex: 1;">
                        Allow Access
                        <i class="fa-solid fa-check" aria-hidden="true"></i>
                    </button>
                    <button type="submit" name="_action" value="deny" class="btn btn--secondary" style="flex: 1;">
                        Deny
                    </button>
                </div>
            </form>

        <?php endif; ?>

    </div>

    <p class="login-footer">
        &copy; <?= date('Y') ?> AplosSuite &mdash;
        <span class="login-footer__tagline">Elegant by Design &middot; Powerful by Default</span>
    </p>

</div>
