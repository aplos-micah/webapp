<?php
$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$pct = fn(int $n) => $totalPublished > 0 ? round(($n / $totalPublished) * 100) : 0;
?>

<div class="dash-header">
    <div>
        <p class="eyebrow">Knowledge Base</p>
        <h1 class="dash-header__title">Dashboard</h1>
        <p class="dash-header__sub"><?= date('l, F j, Y') ?></p>
    </div>
    <div class="btn-group">
        <a href="/kb/articles/new" class="btn btn--primary">
            <i class="fa-solid fa-plus" aria-hidden="true"></i> New Article
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<!-- Stat cards -->
<div class="stats-grid mb-xl">

    <a href="/kb/articles/list" class="stat-card stat-card--link">
        <div class="stat-card__icon icon-circle icon-circle--blue">
            <i class="fa-solid fa-book" aria-hidden="true"></i>
        </div>
        <div class="stat-card__body">
            <span class="stat-card__label eyebrow">Total Articles</span>
            <span class="stat-card__value"><?= number_format($totalAll) ?></span>
            <span class="stat-card__sub">across all statuses</span>
        </div>
    </a>

    <a href="/kb/articles/list?status=Published" class="stat-card stat-card--link">
        <div class="stat-card__icon icon-circle icon-circle--green">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
        </div>
        <div class="stat-card__body">
            <span class="stat-card__label eyebrow">Published</span>
            <span class="stat-card__value"><?= number_format($totalPublished) ?></span>
            <span class="stat-card__sub">live and visible</span>
        </div>
    </a>

    <a href="/kb/articles/list?status=Draft" class="stat-card stat-card--link">
        <div class="stat-card__icon icon-circle icon-circle--orange">
            <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
        </div>
        <div class="stat-card__body">
            <span class="stat-card__label eyebrow">Drafts</span>
            <span class="stat-card__value"><?= number_format($totalDraft) ?></span>
            <span class="stat-card__sub">in progress</span>
        </div>
    </a>

    <a href="/kb/articles/list?status=Archived" class="stat-card stat-card--link">
        <div class="stat-card__icon icon-circle icon-circle--neutral">
            <i class="fa-solid fa-box-archive" aria-hidden="true"></i>
        </div>
        <div class="stat-card__body">
            <span class="stat-card__label eyebrow">Archived</span>
            <span class="stat-card__value"><?= number_format($totalArchived) ?></span>
            <span class="stat-card__sub">retired content</span>
        </div>
    </a>

</div>

<!-- Daily reads chart -->
<div class="card profile-card mb-xl">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-chart-bar" aria-hidden="true"></i>
        Daily Article Reads — Last 14 Days
    </h2>
    <div style="position:relative;height:260px">
        <canvas id="chart-kb-daily-views" aria-label="Daily article reads over the last 14 days" role="img"></canvas>
    </div>
    <script type="application/json" id="kb-chart-data">
    {"labels":<?= $chartLabels ?>,"web":<?= $chartWeb ?>,"remote":<?= $chartRemote ?>,"ai":<?= $chartAI ?>}
    </script>
</div>

<div class="detail-layout mb-xl">

    <!-- Category breakdown -->
    <div class="card profile-card">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
            Published by Category
        </h2>
        <?php if (empty($categoryRows)): ?>
        <div class="content-panel__empty">
            <i class="fa-regular fa-folder-open" aria-hidden="true"></i>
            <p>No published articles yet.</p>
        </div>
        <?php else: ?>
        <dl class="field-list">
            <?php foreach ($categoryRows as $row):
                $cnt = (int) $row['cnt'];
                $p   = $pct($cnt);
            ?>
            <div class="field-list__row" style="flex-direction:column;align-items:flex-start;gap:0.3rem">
                <div style="display:flex;justify-content:space-between;width:100%">
                    <dt>
                        <a href="/kb/articles/list?category=<?= urlencode($row['category']) ?>&status=Published">
                            <?= $e($row['category']) ?>
                        </a>
                    </dt>
                    <dd><?= $cnt ?> <span class="text-muted" style="font-size:0.8rem">(<?= $p ?>%)</span></dd>
                </div>
                <div class="proportion-bar" style="width:100%">
                    <div class="proportion-bar__segment proportion-bar__segment--blue" data-pct="<?= $p ?>"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </dl>
        <?php endif; ?>
    </div>

    <!-- Top viewed -->
    <div class="card profile-card">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-fire" aria-hidden="true"></i>
            Most Viewed
        </h2>
        <?php if (empty($topViewed)): ?>
        <div class="content-panel__empty">
            <i class="fa-regular fa-eye-slash" aria-hidden="true"></i>
            <p>No published articles yet.</p>
        </div>
        <?php else: ?>
        <ul class="record-list">
            <?php foreach ($topViewed as $a): ?>
            <li class="record-list__item">
                <div class="record-list__main">
                    <a href="/kb/articles/details?id=<?= (int)$a['id'] ?>" class="record-list__name">
                        <?= $e($a['title']) ?>
                    </a>
                    <?php if ($a['category']): ?>
                    <span class="record-list__meta"><?= $e($a['category']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="record-list__meta">
                    <span class="record-list__detail">
                        <i class="fa-regular fa-eye" aria-hidden="true"></i>
                        <?= number_format((int)$a['view_count']) ?>
                    </span>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>

</div>

<!-- Recently updated -->
<div class="card profile-card">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
        Recently Updated
    </h2>
    <?php if (empty($recentlyUpdated)): ?>
    <div class="content-panel__empty">
        <i class="fa-regular fa-folder-open" aria-hidden="true"></i>
        <p>No published articles yet.</p>
    </div>
    <?php else: ?>
    <ul class="record-list">
        <?php foreach ($recentlyUpdated as $a): ?>
        <li class="record-list__item">
            <div class="record-list__main">
                <a href="/kb/articles/details?id=<?= (int)$a['id'] ?>" class="record-list__name">
                    <?= $e($a['title']) ?>
                </a>
                <span class="record-list__meta">
                    <?= $e($a['category'] ?? '') ?>
                    <?php if ($a['author_name']): ?>
                    &middot; <?= $e($a['author_name']) ?>
                    <?php endif; ?>
                </span>
            </div>
            <div class="record-list__meta">
                <span class="record-list__detail"><?= $e(substr($a['updated_at'], 0, 10)) ?></span>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</div>
