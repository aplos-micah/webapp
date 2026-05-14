<?php
$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

$qs = fn(array $overrides) => '?' . http_build_query(array_filter(
    array_merge(['search' => $search, 'sort' => $sort, 'dir' => $dir, 'page' => $currentPage], $overrides),
    fn($v) => $v !== '' && $v !== null
));

$sortLink = fn(string $col) => $qs(['sort' => $col, 'dir' => ($sort === $col && $dir === 'desc') ? 'asc' : 'desc', 'page' => 1]);
$sortIcon = function(string $col) use ($sort, $dir): string {
    if ($sort !== $col) return '<i class="fa-solid fa-sort sort-icon sort-icon--idle" aria-hidden="true"></i>';
    return '<i class="fa-solid ' . ($dir === 'asc' ? 'fa-sort-up' : 'fa-sort-down') . ' sort-icon sort-icon--active" aria-hidden="true"></i>';
};

$outcomeBadge = [
    'Positive'           => 'badge--success',
    'Neutral'            => 'badge--neutral',
    'Negative'           => 'badge--danger',
    'Completed'          => 'badge--success',
    'No Response'        => 'badge--neutral',
    'Follow-up Required' => 'badge--warning',
    'Cancelled'          => 'badge--neutral',
];
?>

<div class="dash-header">
    <div>
        <p class="eyebrow">CRM</p>
        <h1 class="dash-header__title">Activities</h1>
        <p class="dash-header__sub"><?= number_format($totalCount) ?> activit<?= $totalCount !== 1 ? 'ies' : 'y' ?></p>
    </div>
    <div class="btn-group">
        <a href="/crm/activities/new" class="btn btn--primary">
            <i class="fa-solid fa-plus" aria-hidden="true"></i> Log Activity
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<form method="GET" action="/crm/activities/list" class="list-search mb-md">
    <input type="hidden" name="sort" value="<?= $e($sort) ?>">
    <input type="hidden" name="dir"  value="<?= $e($dir) ?>">
    <div class="list-search__inner">
        <i class="fa-solid fa-magnifying-glass list-search__icon" aria-hidden="true"></i>
        <input type="search" name="search" class="list-search__input"
               value="<?= $e($search) ?>" placeholder="Search activities…" autocomplete="off">
        <?php if ($search): ?>
        <a href="/crm/activities/list" class="list-search__clear" aria-label="Clear search">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </a>
        <?php endif; ?>
        <button type="submit" class="btn btn--secondary btn--sm">Search</button>
    </div>
</form>

<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th><a href="<?= $sortLink('activity_date') ?>" class="sort-link">Date <?= $sortIcon('activity_date') ?></a></th>
                    <th>Type</th>
                    <th>Linked To</th>
                    <th class="col-num">Duration</th>
                    <th><a href="<?= $sortLink('cost') ?>" class="sort-link">Cost <?= $sortIcon('cost') ?></a></th>
                    <th><a href="<?= $sortLink('outcome') ?>" class="sort-link">Outcome <?= $sortIcon('outcome') ?></a></th>
                    <th>Owner</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($activities)): ?>
                <tr>
                    <td colspan="7" class="data-table__empty">
                        <?php if ($search): ?>
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        <p>No activities match "<?= $e($search) ?>".</p>
                        <?php else: ?>
                        <i class="fa-regular fa-calendar-xmark" aria-hidden="true"></i>
                        <p>No activities logged yet. <a href="/crm/activities/new">Log the first one.</a></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>

                <?php foreach ($activities as $a): ?>
                <?php
                    $links = [];
                    if ($a['account_name'])    $links[] = '<a href="/crm/accounts/details?id='     . (int)$a['account_id']     . '">' . $e($a['account_name'])    . '</a>';
                    if ($a['contact_name'])    $links[] = '<a href="/crm/contacts/details?id='     . (int)$a['contact_id']     . '">' . $e($a['contact_name'])    . '</a>';
                    if ($a['opportunity_name']) $links[] = '<a href="/crm/opportunities/details?id=' . (int)$a['opportunity_id'] . '">' . $e($a['opportunity_name']) . '</a>';
                    $badgeCls = $outcomeBadge[$a['outcome'] ?? ''] ?? 'badge--neutral';
                ?>
                <tr onclick="window.location='/crm/activities/details?id=<?= (int)$a['id'] ?>'" style="cursor:pointer">
                    <td data-label="Date"><?= $e($a['activity_date']) ?></td>
                    <td data-label="Type"><span class="badge badge--info"><?= $e($a['type_name']) ?></span></td>
                    <td data-label="Linked To"><?= implode(', ', $links) ?></td>
                    <td data-label="Duration" class="col-num">
                        <?= $a['duration_minutes'] ? $e($a['duration_minutes']) . ' min' : '—' ?>
                    </td>
                    <td data-label="Cost" class="col-num">
                        <?= $a['cost'] !== null ? '$' . number_format((float)$a['cost'], 2) : '—' ?>
                    </td>
                    <td data-label="Outcome">
                        <?php if ($a['outcome']): ?>
                        <span class="badge <?= $badgeCls ?>"><?= $e($a['outcome']) ?></span>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td data-label="Owner"><?= $e($a['owner_name']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($totalPages > 1): ?>
<div class="pagination mt-md">
    <span class="pagination__info">
        <?= number_format($offset + 1) ?>–<?= number_format(min($offset + ACTIVITIES_PER_PAGE, $totalCount)) ?> of <?= number_format($totalCount) ?>
    </span>
    <div class="pagination__controls">
        <?php if ($currentPage > 1): ?>
        <a href="<?= $qs(['page' => $currentPage - 1]) ?>" class="btn btn--secondary btn--sm">
            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Previous
        </a>
        <?php endif; ?>
        <?php for ($p = max(1, $currentPage - 2); $p <= min($totalPages, $currentPage + 2); $p++): ?>
        <a href="<?= $qs(['page' => $p]) ?>" class="btn btn--sm <?= $p === $currentPage ? 'btn--primary' : 'btn--ghost' ?>"><?= $p ?></a>
        <?php endfor; ?>
        <?php if ($currentPage < $totalPages): ?>
        <a href="<?= $qs(['page' => $currentPage + 1]) ?>" class="btn btn--secondary btn--sm">
            Next <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
        </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
