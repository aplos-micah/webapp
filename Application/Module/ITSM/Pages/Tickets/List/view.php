<?php
$pageTitle = 'Tickets';

$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

$qs = fn(array $overrides) => '?' . http_build_query(array_filter(
    array_merge([
        'search'   => $search,
        'sort'     => $sort,
        'dir'      => $dir,
        'status'   => $status,
        'priority' => $priority,
        'type'     => $type,
        'page'     => $currentPage,
    ], $overrides),
    fn($v) => $v !== '' && $v !== null
));

$sortLink = fn(string $col) => $qs(['sort' => $col, 'dir' => ($sort === $col && $dir === 'asc') ? 'desc' : 'asc', 'page' => 1]);
$sortIcon = function(string $col) use ($sort, $dir): string {
    if ($sort !== $col) return '<i class="fa-solid fa-sort sort-icon sort-icon--idle" aria-hidden="true"></i>';
    return '<i class="fa-solid ' . ($dir === 'asc' ? 'fa-sort-up' : 'fa-sort-down') . ' sort-icon sort-icon--active" aria-hidden="true"></i>';
};

$priorityBadge = [
    'Critical' => 'badge--warning',
    'High'     => 'badge--info',
    'Medium'   => 'badge--neutral',
    'Low'      => 'badge--neutral',
];
$statusBadge = [
    'New'         => 'badge--neutral',
    'In Progress' => 'badge--info',
    'Pending'     => 'badge--warning',
    'Resolved'    => 'badge--success',
    'Closed'      => 'badge--neutral',
];

$paginationHtml = '';
if ($totalPages > 1) {
    ob_start(); ?>
    <div class="pagination">
        <span class="pagination__info">
            <?= number_format($offset + 1) ?>–<?= number_format(min($offset + ITSM_PER_PAGE, $totalCount)) ?> of <?= number_format($totalCount) ?>
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
        <p class="eyebrow">ITSM</p>
        <h1 class="dash-header__title">Tickets</h1>
        <p class="dash-header__sub"><?= number_format($totalCount) ?> ticket<?= $totalCount !== 1 ? 's' : '' ?></p>
    </div>
    <div>
        <a href="/itsm/tickets/new" class="btn btn--primary">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            New Ticket
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<!-- Search + Filters -->
<form method="GET" action="/itsm/tickets/list" class="mb-md">
    <div class="form-row">
        <div class="form-group form-group--grow">
            <div class="list-search__field">
                <i class="fa-solid fa-magnifying-glass list-search__icon" aria-hidden="true"></i>
                <input
                    type="search"
                    name="search"
                    class="input list-search__input"
                    placeholder="Search by title or ticket number…"
                    value="<?= $e($search) ?>"
                    autocomplete="off"
                    hx-get="/itsm/tickets/list"
                    hx-trigger="input delay:300ms"
                    hx-target="#ticket-results"
                    hx-select="#ticket-results"
                    hx-swap="outerHTML"
                    hx-include="closest form"
                >
            </div>
        </div>
        <div class="form-group">
            <select name="status" class="input" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <?php foreach (Ticket::STATUSES as $s): ?>
                <option value="<?= $e($s) ?>"<?= $status === $s ? ' selected' : '' ?>><?= $e($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <select name="priority" class="input" onchange="this.form.submit()">
                <option value="">All Priorities</option>
                <?php foreach (Ticket::PRIORITIES as $p): ?>
                <option value="<?= $e($p) ?>"<?= $priority === $p ? ' selected' : '' ?>><?= $e($p) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <select name="type" class="input" onchange="this.form.submit()">
                <option value="">All Types</option>
                <?php foreach (Ticket::TYPES as $t): ?>
                <option value="<?= $e($t) ?>"<?= $type === $t ? ' selected' : '' ?>><?= $e($t) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <input type="hidden" name="sort" value="<?= $e($sort) ?>">
        <input type="hidden" name="dir"  value="<?= $e($dir) ?>">
        <div class="form-group">
            <button type="submit" class="btn btn--secondary">Search</button>
        </div>
        <?php if ($search !== '' || $status !== '' || $priority !== '' || $type !== ''): ?>
        <div class="form-group">
            <a href="/itsm/tickets/list" class="btn btn--ghost">Clear</a>
        </div>
        <?php endif; ?>
    </div>
</form>

<div id="ticket-results">
<?php if (empty($tickets) && $search === '' && $status === '' && $priority === '' && $type === ''): ?>

<div class="card content-panel">
    <div class="content-panel__empty">
        <i class="fa-regular fa-ticket" aria-hidden="true"></i>
        <p>No tickets yet. <a href="/itsm/tickets/new">Create the first ticket</a>.</p>
    </div>
</div>

<?php elseif (empty($tickets)): ?>

<div class="card content-panel">
    <div class="content-panel__empty">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <p>No tickets match your filters.</p>
    </div>
</div>

<?php else: ?>

<div class="card">
    <?= $paginationHtml ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th><a href="<?= $sortLink('ticket_number') ?>" class="sort-link">Ticket # <?= $sortIcon('ticket_number') ?></a></th>
                    <th><a href="<?= $sortLink('title') ?>" class="sort-link">Title <?= $sortIcon('title') ?></a></th>
                    <th><a href="<?= $sortLink('type') ?>" class="sort-link">Type <?= $sortIcon('type') ?></a></th>
                    <th><a href="<?= $sortLink('priority') ?>" class="sort-link">Priority <?= $sortIcon('priority') ?></a></th>
                    <th><a href="<?= $sortLink('status') ?>" class="sort-link">Status <?= $sortIcon('status') ?></a></th>
                    <th>Assigned To</th>
                    <th><a href="<?= $sortLink('created_at') ?>" class="sort-link">Created <?= $sortIcon('created_at') ?></a></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $ticket): ?>
                <tr>
                    <td>
                        <a href="/itsm/tickets/details?id=<?= (int) $ticket['id'] ?>" class="table-link">
                            <?= $e($ticket['ticket_number'] ?: 'TKT-??????') ?>
                        </a>
                    </td>
                    <td><?= $e($ticket['title']) ?></td>
                    <td><?= $e($ticket['type'] ?? '—') ?></td>
                    <td>
                        <?php if (!empty($ticket['priority'])): ?>
                        <span class="badge <?= $priorityBadge[$ticket['priority']] ?? 'badge--neutral' ?>">
                            <?= $e($ticket['priority']) ?>
                        </span>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($ticket['status'])): ?>
                        <span class="badge <?= $statusBadge[$ticket['status']] ?? 'badge--neutral' ?>">
                            <?= $e($ticket['status']) ?>
                        </span>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td><?= $e($ticket['assigned_name'] ?? '—') ?></td>
                    <td><?= $e(substr($ticket['created_at'] ?? '', 0, 10)) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $paginationHtml ?>
</div>

<?php endif; ?>
</div>
