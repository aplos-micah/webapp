<?php
$pageTitle = 'Projects';
$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

$qs = fn(array $overrides) => '?' . http_build_query(array_filter(
    array_merge([
        'search'   => $search,
        'sort'     => $sort,
        'dir'      => $dir,
        'status'   => $status,
        'phase'    => $phase,
        'priority' => $priority,
        'page'     => $currentPage,
    ], $overrides),
    fn($v) => $v !== '' && $v !== null
));

$sortLink = fn(string $col) => $qs(['sort' => $col, 'dir' => ($sort === $col && $dir === 'asc') ? 'desc' : 'asc', 'page' => 1]);
$sortIcon = function(string $col) use ($sort, $dir): string {
    if ($sort !== $col) return '<i class="fa-solid fa-sort sort-icon sort-icon--idle" aria-hidden="true"></i>';
    return '<i class="fa-solid ' . ($dir === 'asc' ? 'fa-sort-up' : 'fa-sort-down') . ' sort-icon sort-icon--active" aria-hidden="true"></i>';
};

$statusBadge = [
    'Draft'     => 'badge--neutral',
    'Active'    => 'badge--success',
    'On Hold'   => 'badge--warning',
    'Completed' => 'badge--info',
    'Cancelled' => 'badge--neutral',
];
$priorityBadge = [
    'Critical' => 'badge--warning',
    'High'     => 'badge--info',
    'Medium'   => 'badge--neutral',
    'Low'      => 'badge--neutral',
];

$paginationHtml = '';
if ($totalPages > 1) {
    ob_start(); ?>
    <div class="pagination">
        <span class="pagination__info">
            <?= number_format($offset + 1) ?>–<?= number_format(min($offset + PROJECTS_PER_PAGE, $totalCount)) ?> of <?= number_format($totalCount) ?>
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

<div class="dash-header">
    <div>
        <p class="eyebrow">Projects</p>
        <h1 class="dash-header__title">Projects</h1>
        <p class="dash-header__sub"><?= number_format($totalCount) ?> project<?= $totalCount !== 1 ? 's' : '' ?></p>
    </div>
    <div>
        <a href="/projects/projects/new" class="btn btn--primary">
            <i class="fa-solid fa-plus" aria-hidden="true"></i> New Project
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<form method="GET" action="/projects/projects/list" class="mb-md">
    <div class="form-row">
        <div class="form-group form-group--grow">
            <div class="list-search__field">
                <i class="fa-solid fa-magnifying-glass list-search__icon" aria-hidden="true"></i>
                <input
                    type="search"
                    name="search"
                    class="input list-search__input"
                    placeholder="Search by name or description…"
                    value="<?= $e($search) ?>"
                    autocomplete="off"
                    hx-get="/projects/projects/list"
                    hx-trigger="input delay:300ms"
                    hx-target="#project-results"
                    hx-select="#project-results"
                    hx-swap="outerHTML"
                    hx-include="closest form"
                >
            </div>
        </div>
        <div class="form-group">
            <select name="status" class="input" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <?php foreach (Project::STATUSES as $s): ?>
                <option value="<?= $e($s) ?>"<?= $status === $s ? ' selected' : '' ?>><?= $e($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <select name="phase" class="input" onchange="this.form.submit()">
                <option value="">All Phases</option>
                <?php foreach (Project::PHASES as $ph): ?>
                <option value="<?= $e($ph) ?>"<?= $phase === $ph ? ' selected' : '' ?>><?= $e($ph) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <select name="priority" class="input" onchange="this.form.submit()">
                <option value="">All Priorities</option>
                <?php foreach (Project::PRIORITIES as $pr): ?>
                <option value="<?= $e($pr) ?>"<?= $priority === $pr ? ' selected' : '' ?>><?= $e($pr) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <input type="hidden" name="sort" value="<?= $e($sort) ?>">
        <input type="hidden" name="dir"  value="<?= $e($dir) ?>">
        <div class="form-group">
            <button type="submit" class="btn btn--secondary">Search</button>
        </div>
        <?php if ($search !== '' || $status !== '' || $phase !== '' || $priority !== ''): ?>
        <div class="form-group">
            <a href="/projects/projects/list" class="btn btn--ghost">Clear</a>
        </div>
        <?php endif; ?>
    </div>
</form>

<div id="project-results">
<?php if (empty($projects) && $search === '' && $status === '' && $phase === '' && $priority === ''): ?>

<div class="card content-panel">
    <div class="content-panel__empty">
        <i class="fa-regular fa-diagram-project" aria-hidden="true"></i>
        <p>No projects yet. <a href="/projects/projects/new">Create the first project</a>.</p>
    </div>
</div>

<?php elseif (empty($projects)): ?>

<div class="card content-panel">
    <div class="content-panel__empty">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <p>No projects match your filters.</p>
    </div>
</div>

<?php else: ?>

<div class="card<?= empty($projects) ? ' content-panel' : '' ?>">
    <?php if (!empty($projects)): ?><?= $paginationHtml ?><?php endif; ?>
    <?= DataTable::render([
        'columns' => [
            ['label' => 'Name',     'sort' => 'name',     'primary' => true,
             'render' => fn($r, $e) => '<a href="/projects/projects/details?id=' . (int) $r['id'] . '" class="table-link">' . $e($r['name']) . '</a>'],
            ['label' => 'Status',   'key' => 'status',   'sort' => 'status',   'badge' => $statusBadge],
            ['label' => 'Phase',    'key' => 'phase',    'sort' => 'phase'],
            ['label' => 'Priority', 'key' => 'priority', 'sort' => 'priority', 'badge' => $priorityBadge],
            ['label' => 'Owner',    'key' => 'owner_name'],
            ['label' => 'Due Date', 'sort' => 'due_date',
             'render' => fn($r, $e) => (function($r, $e) {
                 $isOverdue = !empty($r['due_date']) && $r['due_date'] < date('Y-m-d')
                     && !in_array($r['status'], ['Completed', 'Cancelled'], true);
                 $date = $e(substr($r['due_date'] ?? '', 0, 10) ?: '—');
                 return $isOverdue
                     ? '<span class="text-orange">' . $date . ' <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span>'
                     : $date;
             })($r, $e)],
        ],
        'rows'           => $projects,
        'sort'           => $sort, 'dir' => $dir, 'qs' => $qs,
        'has_filters'    => $search !== '' || $status !== '' || $phase !== '' || $priority !== '',
        'empty'          => ['icon' => 'fa-regular fa-diagram-project', 'message' => 'No projects yet.',
                             'link' => ['href' => '/projects/projects/new', 'text' => 'Create the first project']],
        'filtered_empty' => 'No projects match your filters.',
    ]) ?>
    <?php if (!empty($projects)): ?><?= $paginationHtml ?><?php endif; ?>
</div>

<?php endif; ?>
</div>
