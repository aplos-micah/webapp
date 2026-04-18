<?php
$pageTitle = 'Product Definitions';

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
        <p class="eyebrow">CRM / Products</p>
        <h1 class="dash-header__title">Product Definitions</h1>
        <p class="dash-header__sub"><?= number_format($totalCount) ?> product<?= $totalCount !== 1 ? 's' : '' ?></p>
    </div>
    <div>
        <a href="/crm/products/new" class="btn btn--primary">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            New Product
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<form method="GET" action="/crm/products/list" class="list-search mb-md">
    <input type="hidden" name="sort" value="<?= htmlspecialchars($sort, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="dir"  value="<?= htmlspecialchars($dir,  ENT_QUOTES, 'UTF-8') ?>">
    <div class="list-search__field">
        <i class="fa-solid fa-magnifying-glass list-search__icon" aria-hidden="true"></i>
        <input type="search" name="search" class="input list-search__input"
               placeholder="Search by name, SKU, family, type, or status…"
               value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
               autocomplete="off"
               hx-get="/crm/products/list"
               hx-trigger="input delay:300ms"
               hx-target="#search-results"
               hx-select="#search-results"
               hx-swap="outerHTML"
               hx-include="closest form">
    </div>
    <button type="submit" class="btn btn--secondary">Search</button>
    <?php if ($search !== ''): ?>
    <a href="/crm/products/list" class="btn btn--ghost">Clear</a>
    <?php endif; ?>
</form>

<div id="search-results">
<?php if (empty($products) && $search === ''): ?>

<div class="card dash-panel">
    <div class="dash-panel__empty">
        <i class="fa-regular fa-box-open" aria-hidden="true"></i>
        <p>No products yet. <a href="/crm/products/new">Add your first product</a>.</p>
    </div>
</div>

<?php elseif (empty($products)): ?>

<div class="card dash-panel">
    <div class="dash-panel__empty">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <p>No products match <strong><?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?></strong>.</p>
    </div>
</div>

<?php else: ?>

<div class="card">
    <?= $paginationHtml ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th><a href="<?= $sortLink('product_name') ?>" class="sort-link">Product Name <?= $sortIcon('product_name') ?></a></th>
                    <th><a href="<?= $sortLink('sku') ?>" class="sort-link">SKU <?= $sortIcon('sku') ?></a></th>
                    <th><a href="<?= $sortLink('product_family') ?>" class="sort-link">Family <?= $sortIcon('product_family') ?></a></th>
                    <th><a href="<?= $sortLink('product_type') ?>" class="sort-link">Type <?= $sortIcon('product_type') ?></a></th>
                    <th><a href="<?= $sortLink('list_price') ?>" class="sort-link">List Price <?= $sortIcon('list_price') ?></a></th>
                    <th><a href="<?= $sortLink('lifecycle_status') ?>" class="sort-link">Status <?= $sortIcon('lifecycle_status') ?></a></th>
                    <th>Active</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td>
                        <a href="/crm/products/details?id=<?= (int) $p['id'] ?>" class="table-link">
                            <?= htmlspecialchars($p['product_name'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($p['sku'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($p['product_family'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($p['product_type'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <?php if ($p['list_price'] !== null): ?>
                        <?= htmlspecialchars($p['currency'] ?? 'USD', ENT_QUOTES, 'UTF-8') ?>
                        <?= number_format((float) $p['list_price'], 2) ?>
                        <?php else: ?>
                        —
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($p['lifecycle_status'])): ?>
                        <span class="badge badge--info"><?= htmlspecialchars($p['lifecycle_status'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td>
                        <?php if ($p['is_active']): ?>
                        <span class="badge badge--success">Yes</span>
                        <?php else: ?>
                        <span class="badge badge--neutral">No</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $paginationHtml ?>
</div>

<?php endif; ?>
</div>
