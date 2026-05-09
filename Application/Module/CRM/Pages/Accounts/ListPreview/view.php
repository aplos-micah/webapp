<?php
$pageTitle = 'Accounts (Preview)';

$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

$qs = fn(array $overrides) => '?' . http_build_query(array_filter(
    array_merge(['search' => $search, 'sort' => $sort, 'dir' => $dir, 'page' => $currentPage], $overrides),
    fn($v) => $v !== '' && $v !== null
));

// Pagination
$paginationHtml = '';
if ($totalPages > 1) {
    ob_start(); ?>
    <div class="pagination">
        <span class="pagination__info">
            <?= number_format($offset + 1) ?>–<?= number_format(min($offset + 20, $totalCount)) ?> of <?= number_format($totalCount) ?>
        </span>
        <div class="pagination__controls">
            <?php if ($currentPage > 1): ?>
            <a href="<?= $qs(['page' => $currentPage - 1]) ?>" class="btn btn--secondary btn--sm">
                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Previous
            </a>
            <?php endif; ?>
            <?php
            $start = max(1, $currentPage - 2);
            $end   = min($totalPages, $currentPage + 2);
            if ($start > 1): ?><a href="<?= $qs(['page' => 1]) ?>" class="btn btn--ghost btn--sm">1</a><?php
                if ($start > 2): ?><span class="pagination__ellipsis">…</span><?php endif;
            endif;
            for ($p = $start; $p <= $end; $p++): ?>
            <a href="<?= $qs(['page' => $p]) ?>" class="btn btn--sm <?= $p === $currentPage ? 'btn--primary' : 'btn--ghost' ?>"><?= $p ?></a>
            <?php endfor;
            if ($end < $totalPages):
                if ($end < $totalPages - 1): ?><span class="pagination__ellipsis">…</span><?php endif; ?>
                <a href="<?= $qs(['page' => $totalPages]) ?>" class="btn btn--ghost btn--sm"><?= $totalPages ?></a>
            <?php endif; ?>
            <?php if ($currentPage < $totalPages): ?>
            <a href="<?= $qs(['page' => $currentPage + 1]) ?>" class="btn btn--secondary btn--sm">
                Next <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php $paginationHtml = ob_get_clean();
}
?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">CRM / Accounts</p>
        <h1 class="dash-header__title">Accounts <span class="badge badge--warning">DataTable Preview</span></h1>
        <p class="dash-header__sub"><?= number_format($totalCount) ?> account<?= $totalCount !== 1 ? 's' : '' ?></p>
    </div>
    <div class="btn-group">
        <a href="/crm/accounts/list" class="btn btn--ghost">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to Live List
        </a>
        <a href="/crm/accounts/new" class="btn btn--primary">
            <i class="fa-solid fa-plus" aria-hidden="true"></i> New Account
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<!-- Search -->
<form method="GET" action="/crm/accounts/listpreview" class="list-search mb-md">
    <input type="hidden" name="sort" value="<?= $e($sort) ?>">
    <input type="hidden" name="dir"  value="<?= $e($dir) ?>">
    <div class="list-search__field">
        <i class="fa-solid fa-magnifying-glass list-search__icon" aria-hidden="true"></i>
        <input
            type="search"
            name="search"
            class="input list-search__input"
            placeholder="Search accounts…"
            value="<?= $e($search) ?>"
            autocomplete="off"
            hx-get="/crm/accounts/listpreview"
            hx-trigger="input delay:300ms"
            hx-target="#search-results"
            hx-select="#search-results"
            hx-swap="outerHTML"
            hx-include="closest form"
        >
    </div>
    <button type="submit" class="btn btn--secondary">Search</button>
    <?php if ($search !== ''): ?>
    <a href="/crm/accounts/listpreview" class="btn btn--ghost">Clear</a>
    <?php endif; ?>
</form>

<div id="search-results">

<div class="card<?= empty($accounts) ? ' content-panel' : '' ?>">
    <?php if (!empty($accounts)): ?><?= $paginationHtml ?><?php endif; ?>
    <?= DataTable::render([
        'columns' => [
            ['label' => 'Account Name', 'key' => 'name',           'sort' => 'name',           'primary' => true,
             'href'  => fn($r) => '/crm/accounts/details?id=' . (int) $r['id']],
            ['label' => 'Account #',    'key' => 'account_number', 'sort' => 'account_number'],
            ['label' => 'Type',         'key' => 'type',           'sort' => 'type'],
            ['label' => 'Industry',     'key' => 'industry',       'sort' => 'industry'],
            ['label' => 'Status',       'key' => 'status',         'sort' => 'status',
             'badge' => ['Active' => 'badge--info', 'Demo' => 'badge--neutral', 'Inactive' => 'badge--neutral']],
            ['label' => 'Website',      'sort' => 'website',
             'render' => fn($r, $e) => !empty($r['website'])
                 ? '<a href="' . $e($r['website']) . '" target="_blank" rel="noopener noreferrer" class="table-link">'
                   . $e($r['website']) . '</a>'
                 : '—'],
        ],
        'rows'           => $accounts,
        'sort'           => $sort,
        'dir'            => $dir,
        'qs'             => $qs,
        'has_filters'    => $search !== '',
        'empty'          => ['icon' => 'fa-regular fa-building', 'message' => 'No accounts yet.',
                             'link' => ['href' => '/crm/accounts/new', 'text' => 'Add your first account']],
        'filtered_empty' => 'No accounts match your search.',
    ]) ?>
    <?php if (!empty($accounts)): ?><?= $paginationHtml ?><?php endif; ?>
</div>

</div>
