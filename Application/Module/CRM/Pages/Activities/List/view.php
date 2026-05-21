<?php
$e  = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$qs = fn(array $overrides) => '?' . http_build_query(array_filter(
    array_merge(['search' => $search, 'sort' => $sort, 'dir' => $dir, 'page' => $currentPage], $overrides),
    fn($v) => $v !== '' && $v !== null
));
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
    <?= DataTable::render([
        'columns' => [
            ['label' => 'Date',      'key' => 'activity_date', 'sort' => 'activity_date', 'primary' => true,
             'href'  => fn($r) => '/crm/activities/details?id=' . (int) $r['id']],
            ['label' => 'Type',      'key' => 'type_name',
             'render' => fn($r, $e) => '<span class="badge badge--info">' . $e($r['type_name']) . '</span>'],
            ['label' => 'Linked To', 'key' => 'account_name',
             'render' => fn($r, $e) => implode(', ', array_filter([
                 $r['account_name']     ? '<a href="/crm/accounts/details?id='      . (int)$r['account_id']     . '" class="table-link">' . $e($r['account_name'])     . '</a>' : null,
                 $r['contact_name']     ? '<a href="/crm/contacts/details?id='      . (int)$r['contact_id']     . '" class="table-link">' . $e($r['contact_name'])     . '</a>' : null,
                 $r['opportunity_name'] ? '<a href="/crm/opportunities/details?id=' . (int)$r['opportunity_id'] . '" class="table-link">' . $e($r['opportunity_name']) . '</a>' : null,
             ])) ?: '—'],
            ['label' => 'Duration',  'key' => 'duration_minutes',
             'render' => fn($r, $e) => $r['duration_minutes'] ? $e($r['duration_minutes']) . ' min' : '—'],
            ['label' => 'Cost',      'key' => 'cost', 'sort' => 'cost',
             'render' => fn($r, $e) => $r['cost'] !== null ? '$' . number_format((float) $r['cost'], 2) : '—'],
            ['label' => 'Outcome',   'key' => 'outcome', 'sort' => 'outcome',
             'badge' => [
                 'Positive'           => 'badge--success',
                 'Neutral'            => 'badge--neutral',
                 'Negative'           => 'badge--danger',
                 'Completed'          => 'badge--success',
                 'No Response'        => 'badge--neutral',
                 'Follow-up Required' => 'badge--warning',
                 'Cancelled'          => 'badge--neutral',
             ]],
            ['label' => 'Owner',     'key' => 'owner_name'],
        ],
        'rows'        => $activities,
        'all_rows'    => $allActivities,
        'download'    => 'activities',
        'sort'        => $sort,
        'dir'         => $dir,
        'qs'          => $qs,
        'has_filters' => $search !== '',
        'empty'       => [
            'icon'    => 'fa-regular fa-calendar-xmark',
            'message' => 'No activities logged yet.',
            'link'    => ['href' => '/crm/activities/new', 'text' => 'Log the first one.'],
        ],
        'filtered_empty' => 'No activities match your search.',
    ]) ?>
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
