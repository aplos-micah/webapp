<?php $pageTitle = 'Page Not Found'; ?>

<div class="error-wrap">

    <div class="error-card">

        <div class="error-card__eyebrow eyebrow">Error 404</div>

        <h1 class="error-card__heading">Page not found</h1>

        <p class="error-card__body">
            The page you're looking for doesn't exist or may have been moved.
            Double-check the URL, or head back to a place you know.
        </p>

        <div class="error-card__actions">
            <?php if (!empty($_SESSION['user_id'])): ?>
                <a href="/home" class="btn btn--primary">
                    <i class="fa-solid fa-gauge-high" aria-hidden="true"></i>
                    Go to Dashboard
                </a>
                <a href="javascript:history.back()" class="btn btn--secondary">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                    Go Back
                </a>
            <?php else: ?>
                <a href="/login" class="btn btn--primary">
                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    Sign In
                </a>
            <?php endif; ?>
        </div>

    </div>

</div>

