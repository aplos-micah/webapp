<?php
$pageTitle = 'Assets';

$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

$qs = fn(array $overrides) => '?' . http_build_query(array_filter(
    array_merge([
        'search' => $search,
        'sort'   => $sort,
        'dir'    => $dir,
        'status' => $status,
        'type'   => $type,
        'page'   => $currentPage,
    ], $overrides),
    fn($v) => $v !== '' && $v !== null
));

$sortLink = fn(string $col) => $qs(['sort' => $col, 'dir' => ($sort === $col && $dir === 'asc') ? 'desc' : 'asc', 'page' => 1]);
$sortIcon = function(string $col) use ($sort, $dir): string {
    if ($sort !== $col) return '<i class="fa-solid fa-sort sort-icon sort-icon--idle" aria-hidden="true"></i>';
    return '<i class="fa-solid ' . ($dir === 'asc' ? 'fa-sort-up' : 'fa-sort-down') . ' sort-icon sort-icon--active" aria-hidden="true"></i>';
};

$statusBadge = [
    'Active'      => 'badge--success',
    'In Stock'    => 'badge--info',
    'In Repair'   => 'badge--warning',
    'Retired'     => 'badge--neutral',
    'Lost/Stolen' => 'badge--neutral',
];

$paginationHtml = '';
if ($totalPages > 1) {
    ob_start(); ?>
    <div class="pagination">
        <span class="pagination__info">
            <?= number_format($offset + 1) ?>–<?= number_format(min($offset + ASSETS_PER_PAGE, $totalCount)) ?> of <?= number_format($totalCount) ?>
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
        <p class="eyebrow">Assets</p>
        <h1 class="dash-header__title">Assets</h1>
        <p class="dash-header__sub"><?= number_format($totalCount) ?> asset<?= $totalCount !== 1 ? 's' : '' ?></p>
    </div>
    <div>
        <a href="/assets/assets/new" class="btn btn--primary">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            New Asset
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<!-- Search + Filters -->
<form method="GET" action="/assets/assets/list" class="mb-md">
    <div class="form-row">
        <div class="form-group form-group--grow">
            <div class="list-search__field">
                <i class="fa-solid fa-magnifying-glass list-search__icon" aria-hidden="true"></i>
                <input
                    type="search"
                    name="search"
                    class="input list-search__input"
                    placeholder="Search by name, asset tag, or serial number…"
                    value="<?= $e($search) ?>"
                    autocomplete="off"
                    hx-get="/assets/assets/list"
                    hx-trigger="input delay:300ms"
                    hx-target="#asset-results"
                    hx-select="#asset-results"
                    hx-swap="outerHTML"
                    hx-include="closest form"
                >
            </div>
        </div>
        <div class="form-group">
            <select name="status" class="input" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <?php foreach (Asset::STATUSES as $s): ?>
                <option value="<?= $e($s) ?>"<?= $status === $s ? ' selected' : '' ?>><?= $e($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <select name="type" class="input" onchange="this.form.submit()">
                <option value="">All Types</option>
                <?php foreach (Asset::TYPES as $t): ?>
                <option value="<?= $e($t) ?>"<?= $type === $t ? ' selected' : '' ?>><?= $e($t) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <input type="hidden" name="sort" value="<?= $e($sort) ?>">
        <input type="hidden" name="dir"  value="<?= $e($dir) ?>">
        <div class="form-group">
            <button type="submit" class="btn btn--secondary">Search</button>
        </div>
        <?php if ($search !== '' || $status !== '' || $type !== ''): ?>
        <div class="form-group">
            <a href="/assets/assets/list" class="btn btn--ghost">Clear</a>
        </div>
        <?php endif; ?>
    </div>
</form>

<div id="asset-results">
<?php if (empty($assets) && $search === '' && $status === '' && $type === ''): ?>

<div class="card content-panel">
    <div class="content-panel__empty">
        <i class="fa-regular fa-laptop" aria-hidden="true"></i>
        <p>No assets yet. <a href="/assets/assets/new">Add the first asset</a>.</p>
    </div>
</div>

<?php elseif (empty($assets)): ?>

<div class="card content-panel">
    <div class="content-panel__empty">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <p>No assets match your filters.</p>
    </div>
</div>

<?php else: ?>

<div class="card<?= empty($assets) ? ' content-panel' : '' ?>">
    <?php if (!empty($assets)): ?><?= $paginationHtml ?><?php endif; ?>
    <?= DataTable::render([
        'columns' => [
            ['label' => 'Asset Tag',       'sort' => 'asset_tag',       'primary' => true,
             'render' => fn($r, $e) => '<a href="/assets/assets/details?id=' . (int) $r['id'] . '" class="table-link">' . $e($r['asset_tag'] ?: 'ASSET-??????') . '</a>'],
            ['label' => 'Name',            'key' => 'name',             'sort' => 'name'],
            ['label' => 'Type',            'key' => 'type',             'sort' => 'type'],
            ['label' => 'Status',          'key' => 'status',           'sort' => 'status',          'badge' => $statusBadge],
            ['label' => 'Assigned To',     'key' => 'assigned_name'],
            ['label' => 'Location',        'key' => 'location'],
            ['label' => 'Warranty Expires','key' => 'warranty_expires', 'sort' => 'warranty_expires', 'date' => true],
        ],
        'rows'           => $assets,
        'sort'           => $sort, 'dir' => $dir, 'qs' => $qs,
        'has_filters'    => $search !== '' || $status !== '' || $type !== '',
        'empty'          => ['icon' => 'fa-regular fa-laptop', 'message' => 'No assets yet.',
                             'link' => ['href' => '/assets/assets/new', 'text' => 'Add the first asset']],
        'filtered_empty' => 'No assets match your filters.',
    ]) ?>
    <?php if (!empty($assets)): ?><?= $paginationHtml ?><?php endif; ?>
</div>

<?php endif; ?>
</div>
