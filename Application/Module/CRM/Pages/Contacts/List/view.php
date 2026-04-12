<?php
$pageTitle = 'Contacts';

// Build a query string preserving current state, with overrides applied
$qs = fn(array $overrides) => '?' . http_build_query(array_filter(
    array_merge(['search' => $search, 'sort' => $sort, 'dir' => $dir, 'page' => $currentPage], $overrides),
    fn($v) => $v !== '' && $v !== null
));

// Sort link for a column header: toggles dir if already sorted by that column, resets to page 1
$sortLink = function(string $col) use ($sort, $dir, $qs): string {
    $newDir = ($sort === $col && $dir === 'asc') ? 'desc' : 'asc';
    return $qs(['sort' => $col, 'dir' => $newDir, 'page' => 1]);
};

// Sort indicator icon
$sortIcon = function(string $col) use ($sort, $dir): string {
    if ($sort !== $col) return '<i class="fa-solid fa-sort sort-icon sort-icon--idle" aria-hidden="true"></i>';
    $icon = $dir === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
    return "<i class=\"fa-solid {$icon} sort-icon sort-icon--active\" aria-hidden=\"true\"></i>";
};

// Pagination HTML — rendered above and below the table
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
            <a href="<?= $qs(['page' => $p]) ?>" class="btn btn--sm <?= $p === $currentPage ? 'btn--primary' : 'btn--ghost' ?>">
                <?= $p ?>
            </a>
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

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">CRM / Contacts</p>
        <h1 class="dash-header__title">Contacts</h1>
        <p class="dash-header__sub"><?= number_format($totalCount) ?> contact<?= $totalCount !== 1 ? 's' : '' ?></p>
    </div>
    <div>
        <a href="/crm/contacts/new" class="btn btn--primary">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            New Contact
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<!-- Search -->
<form method="GET" action="/crm/contacts/list" class="list-search mb-md">
    <input type="hidden" name="sort" value="<?= htmlspecialchars($sort, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="dir"  value="<?= htmlspecialchars($dir,  ENT_QUOTES, 'UTF-8') ?>">
    <div class="list-search__field">
        <i class="fa-solid fa-magnifying-glass list-search__icon" aria-hidden="true"></i>
        <input
            type="search"
            name="search"
            class="input list-search__input"
            placeholder="Search by name, company, email, or job title…"
            value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
            autocomplete="off"
        >
    </div>
    <button type="submit" class="btn btn--secondary">Search</button>
    <?php if ($search !== ''): ?>
    <a href="/crm/contacts/list" class="btn btn--ghost">Clear</a>
    <?php endif; ?>
</form>

<?php if (empty($contacts) && $search === ''): ?>

<div class="card dash-panel">
    <div class="dash-panel__empty">
        <i class="fa-regular fa-address-card" aria-hidden="true"></i>
        <p>No contacts yet. <a href="/crm/contacts/new">Add your first contact</a>.</p>
    </div>
</div>

<?php elseif (empty($contacts)): ?>

<div class="card dash-panel">
    <div class="dash-panel__empty">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <p>No contacts match <strong><?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?></strong>.</p>
    </div>
</div>

<?php else: ?>

<div class="card">
    <?= $paginationHtml ?>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th><a href="<?= $sortLink('last_name') ?>" class="sort-link">Name <?= $sortIcon('last_name') ?></a></th>
                    <th>Account</th>
                    <th><a href="<?= $sortLink('job_title') ?>" class="sort-link">Job Title <?= $sortIcon('job_title') ?></a></th>
                    <th><a href="<?= $sortLink('email') ?>" class="sort-link">Email <?= $sortIcon('email') ?></a></th>
                    <th><a href="<?= $sortLink('status') ?>" class="sort-link">Status <?= $sortIcon('status') ?></a></th>
                    <th><a href="<?= $sortLink('lifecycle_stage') ?>" class="sort-link">Lifecycle Stage <?= $sortIcon('lifecycle_stage') ?></a></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $contact): ?>
                <?php
                    $fullName = trim(
                        htmlspecialchars($contact['first_name'] ?? '', ENT_QUOTES, 'UTF-8') . ' ' .
                        htmlspecialchars($contact['last_name']  ?? '', ENT_QUOTES, 'UTF-8')
                    );
                ?>
                <tr>
                    <td>
                        <a href="/crm/contacts/details?id=<?= (int) $contact['id'] ?>" class="table-link">
                            <?= $fullName ?>
                        </a>
                    </td>
                    <td>
                        <?php if (!empty($contact['account_id']) && !empty($contact['account_name'])): ?>
                        <a href="/crm/accounts/details?id=<?= (int) $contact['account_id'] ?>" class="table-link">
                            <?= htmlspecialchars($contact['account_name'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                        <?php else: ?>
                        —
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($contact['job_title']       ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <?php if (!empty($contact['email'])): ?>
                        <a href="mailto:<?= htmlspecialchars($contact['email'], ENT_QUOTES, 'UTF-8') ?>" class="table-link">
                            <?= htmlspecialchars($contact['email'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                        <?php else: ?>
                        —
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($contact['status'])): ?>
                        <span class="badge badge--info"><?= htmlspecialchars($contact['status'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php else: ?>
                        —
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($contact['lifecycle_stage'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?= $paginationHtml ?>
</div>

<?php endif; ?>
