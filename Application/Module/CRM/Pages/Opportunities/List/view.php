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
               autocomplete="off">
    </div>
    <button type="submit" class="btn btn--secondary">Search</button>
    <?php if ($search !== ''): ?>
    <a href="/crm/opportunities/list" class="btn btn--ghost">Clear</a>
    <?php endif; ?>
</form>

<?php if (empty($opportunities) && $search === ''): ?>

<div class="card dash-panel">
    <div class="dash-panel__empty">
        <i class="fa-regular fa-handshake" aria-hidden="true"></i>
        <p>No opportunities yet. <a href="/crm/opportunities/new">Create your first opportunity</a>.</p>
    </div>
</div>

<?php elseif (empty($opportunities)): ?>

<div class="card dash-panel">
    <div class="dash-panel__empty">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <p>No opportunities match <strong><?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?></strong>.</p>
    </div>
</div>

<?php else: ?>

<div class="card">
    <?= $paginationHtml ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th><a href="<?= $sortLink('opportunity_name') ?>" class="sort-link">Opportunity <?= $sortIcon('opportunity_name') ?></a></th>
                    <th>Account</th>
                    <th><a href="<?= $sortLink('stage') ?>" class="sort-link">Stage <?= $sortIcon('stage') ?></a></th>
                    <th><a href="<?= $sortLink('amount') ?>" class="sort-link">Amount <?= $sortIcon('amount') ?></a></th>
                    <th><a href="<?= $sortLink('probability') ?>" class="sort-link">Prob. <?= $sortIcon('probability') ?></a></th>
                    <th><a href="<?= $sortLink('close_date') ?>" class="sort-link">Close Date <?= $sortIcon('close_date') ?></a></th>
                    <th><a href="<?= $sortLink('forecast_category') ?>" class="sort-link">Forecast <?= $sortIcon('forecast_category') ?></a></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($opportunities as $opp): ?>
                <tr>
                    <td>
                        <a href="/crm/opportunities/details?id=<?= (int) $opp['id'] ?>" class="table-link">
                            <?= htmlspecialchars($opp['opportunity_name'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </td>
                    <td>
                        <?php if (!empty($opp['account_id']) && !empty($opp['account_name'])): ?>
                        <a href="/crm/accounts/details?id=<?= (int) $opp['account_id'] ?>" class="table-link">
                            <?= htmlspecialchars($opp['account_name'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($opp['stage'])): ?>
                        <?php $cls = $stageBadgeClass[$opp['stage']] ?? 'badge--neutral'; ?>
                        <span class="badge <?= $cls ?>"><?= htmlspecialchars($opp['stage'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td><?= $opp['amount'] !== null ? 'USD ' . number_format((float) $opp['amount'], 2) : '—' ?></td>
                    <td><?= $opp['probability'] !== null ? (int) $opp['probability'] . '%' : '—' ?></td>
                    <td><?= !empty($opp['close_date']) ? htmlspecialchars($opp['close_date'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
                    <td><?= !empty($opp['forecast_category']) ? htmlspecialchars($opp['forecast_category'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $paginationHtml ?>
</div>

<?php endif; ?>
