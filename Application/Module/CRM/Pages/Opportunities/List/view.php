<?php
$pageTitle = 'Opportunities';

$qs = fn(array $overrides) => '?' . http_build_query(array_filter(
    array_merge(['search' => $search, 'sort' => $sort, 'dir' => $dir, 'page' => $currentPage], $overrides),
    fn($v) => $v !== '' && $v !== null
));

$sortLink = function(string $col) use ($sort, $dir, $qs): string {
    $newDir = ($sort === $col && $dir === 'asc') ? 'desc' : 'asc';
    return $qs(['sort' => $col, 'dir' => $newDir, 'page' => 1]);
};

$sortIcon = function(string $col) use ($sort, $dir): string {
    if ($sort !== $col) return '<i class="fa-solid fa-sort sort-icon sort-icon--idle" aria-hidden="true"></i>';
    $icon = $dir === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
    return "<i class=\"fa-solid {$icon} sort-icon sort-icon--active\" aria-hidden=\"true\"></i>";
};

$stageBadgeClass = [
    'New'         => 'badge--neutral',
    'Building'    => 'badge--info',
    'Review'      => 'badge--info',
    'Quote'       => 'badge--warning',
    'Negotiating' => 'badge--purple',
    'Closed Won'  => 'badge--success',
    'Closed Lost' => 'badge--neutral',
];

$paginationHtml = '';
if ($totalPages > 1) {
    ob_start(); ?>
    <div class="pagination">
        <span class="pagination__info">
            <?= number_format($offset + 1) ?>–<?= number_format(min($offset + PER_PAGE, $totalCount)) ?> of <?= number_format($totalCount) ?>
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
            if ($start > 1): ?>
                <a href="<?= $qs(['page' => 1]) ?>" class="btn btn--ghost btn--sm">1</a>
                <?php if ($start > 2): ?><span class="pagination__ellipsis">…</span><?php endif; ?>
            <?php endif; ?>
            <?php for ($p = $start; $p <= $end; $p++): ?>
            <a href="<?= $qs(['page' => $p]) ?>" class="btn btn--sm <?= $p === $currentPage ? 'btn--primary' : 'btn--ghost' ?>"><?= $p ?></a>
            <?php endfor; ?>
            <?php if ($end < $totalPages): ?>
                <?php if ($end < $totalPages - 1): ?><span class="pagination__ellipsis">…</span><?php endif; ?>
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

<div class="dash-header">
    <div>
        <p class="eyebrow">CRM / Opportunities</p>
        <h1 class="dash-header__title">Opportunities</h1>
        <p class="dash-header__sub"><?= number_format($totalCount) ?> opportunit<?= $totalCount !== 1 ? 'ies' : 'y' ?></p>
    </div>
    <div>
        <a href="/crm/opportunities/new" class="btn btn--primary">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            New Opportunity
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<form method="GET" action="/crm/opportunities/list" class="list-search mb-md">
    <input type="hidden" name="sort" value="<?= htmlspecialchars($sort, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="dir"  value="<?= htmlspecialchars($dir,  ENT_QUOTES, 'UTF-8') ?>">
    <div class="list-search__field">
        <i class="fa-solid fa-magnifying-glass list-search__icon" aria-hidden="true"></i>
        <input type="search" name="search" class="input list-search__input"
               placeholder="Search by name, stage, forecast category, or account…"
               value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
               autocomplete="off"
               hx-get="/crm/opportunities/list"
               hx-trigger="input delay:300ms"
               hx-target="#search-results"
               hx-select="#search-results"
               hx-swap="outerHTML"
               hx-include="closest form">
    </div>
    <button type="submit" class="btn btn--secondary">Search</button>
    <?php if ($search !== ''): ?>
    <a href="/crm/opportunities/list" class="btn btn--ghost">Clear</a>
    <?php endif; ?>
</form>

<div id="search-results">
<?php if (empty($opportunities) && $search === ''): ?>

<div class="card content-panel">
    <div class="content-panel__empty">
        <i class="fa-regular fa-handshake" aria-hidden="true"></i>
        <p>No opportunities yet. <a href="/crm/opportunities/new">Create your first opportunity</a>.</p>
    </div>
</div>

<?php elseif (empty($opportunities)): ?>

<div class="card content-panel">
    <div class="content-panel__empty">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <p>No opportunities match <strong><?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?></strong>.</p>
    </div>
</div>

<?php else: ?>

<div class="card">
    <?= $paginationHtml ?>
    <?= DataTable::render([
        'columns' => [
            ['label' => 'Opportunity', 'sort' => 'opportunity_name', 'primary' => true,
             'render' => fn($r, $e) => '<a href="/crm/opportunities/details?id=' . (int) $r['id'] . '" class="table-link">' . $e($r['opportunity_name']) . '</a>'],
            ['label' => 'Account',
             'render' => fn($r, $e) => !empty($r['account_id']) && !empty($r['account_name'])
                 ? '<a href="/crm/accounts/details?id=' . (int) $r['account_id'] . '" class="table-link">' . $e($r['account_name']) . '</a>'
                 : '—'],
            ['label' => 'Stage',    'key' => 'stage',            'sort' => 'stage',            'badge' => $stageBadgeClass],
            ['label' => 'Amount',   'sort' => 'amount',
             'render' => fn($r, $e) => $r['amount'] !== null ? 'USD ' . number_format((float) $r['amount'], 2) : '—'],
            ['label' => 'Prob.',    'sort' => 'probability',
             'render' => fn($r, $e) => $r['probability'] !== null ? (int) $r['probability'] . '%' : '—'],
            ['label' => 'Close Date','key' => 'close_date',      'sort' => 'close_date',       'date' => true],
            ['label' => 'Forecast', 'key' => 'forecast_category','sort' => 'forecast_category'],
        ],
        'rows' => $opportunities, 'sort' => $sort, 'dir' => $dir, 'qs' => $qs,
    ]) ?>
    <?= $paginationHtml ?>
</div>

<?php endif; ?>
</div>
