<?php
$pageTitle = 'Users';

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
            <?= number_format($offset + 1) ?>–<?= number_format(min($offset + USERS_PER_PAGE, $totalCount)) ?> of <?= number_format($totalCount) ?>
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

$userTypeBadge = [
    'admin'   => 'badge--warning',
    'manager' => 'badge--info',
    'user'    => 'badge--neutral',
    'free'    => 'badge--neutral',
];
?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">Admin</p>
        <h1 class="dash-header__title">Users</h1>
        <p class="dash-header__sub"><?= number_format($totalCount) ?> user<?= $totalCount !== 1 ? 's' : '' ?></p>
    </div>
</div>

<hr class="divider--green mb-xl">

<!-- Search -->
<form method="GET" action="/admin/userlist" class="list-search mb-md">
    <input type="hidden" name="sort" value="<?= htmlspecialchars($sort, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="dir"  value="<?= htmlspecialchars($dir,  ENT_QUOTES, 'UTF-8') ?>">
    <div class="list-search__field">
        <i class="fa-solid fa-magnifying-glass list-search__icon" aria-hidden="true"></i>
        <input type="search" name="search" class="input list-search__input"
               placeholder="Search by name or email…"
               value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
               autocomplete="off">
    </div>
    <button type="submit" class="btn btn--secondary">Search</button>
    <?php if ($search !== ''): ?>
    <a href="/admin/userlist" class="btn btn--ghost">Clear</a>
    <?php endif; ?>
</form>

<?php if (empty($users) && $search === ''): ?>

<div class="card dash-panel">
    <div class="dash-panel__empty">
        <i class="fa-regular fa-user" aria-hidden="true"></i>
        <p>No users found.</p>
    </div>
</div>

<?php elseif (empty($users)): ?>

<div class="card dash-panel">
    <div class="dash-panel__empty">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <p>No users match <strong><?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?></strong>.</p>
    </div>
</div>

<?php else: ?>

<div class="card">
    <?= $paginationHtml ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th><a href="<?= $sortLink('name') ?>" class="sort-link">Name <?= $sortIcon('name') ?></a></th>
                    <th><a href="<?= $sortLink('email') ?>" class="sort-link">Email <?= $sortIcon('email') ?></a></th>
                    <th><a href="<?= $sortLink('user_type') ?>" class="sort-link">Type <?= $sortIcon('user_type') ?></a></th>
                    <th>CRM Access</th>
                    <th><a href="<?= $sortLink('is_active') ?>" class="sort-link">Active <?= $sortIcon('is_active') ?></a></th>
                    <th><a href="<?= $sortLink('created_at') ?>" class="sort-link">Created <?= $sortIcon('created_at') ?></a></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <?php $uid = (int) $u['id']; ?>
                <tr data-user-id="<?= $uid ?>" class="row-clickable">
                    <td><?= htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <a href="mailto:<?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?>" class="table-link">
                            <?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </td>
                    <td>
                        <span class="badge <?= $userTypeBadge[$u['user_type'] ?? ''] ?? 'badge--neutral' ?>">
                            <?= htmlspecialchars($u['user_type'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($u['Module_CRM'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <?php if ($u['is_active']): ?>
                        <span class="badge badge--success">Active</span>
                        <?php else: ?>
                        <span class="badge badge--neutral">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($u['created_at'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <tr class="li-edit-row" id="user-edit-<?= $uid ?>" hidden>
                    <td colspan="6" class="li-edit-cell">
                        <form method="POST" action="/admin/userlist" novalidate>
                            <input type="hidden" name="_action" value="update_user">
                            <input type="hidden" name="user_id" value="<?= $uid ?>">
                            <input type="hidden" name="_search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="_sort"   value="<?= htmlspecialchars($sort,   ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="_dir"    value="<?= htmlspecialchars($dir,    ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="_page"   value="<?= $currentPage ?>">
                            <div class="li-edit-grid">
                                <div>
                                    <div class="form-row">
                                        <div class="form-group form-group--grow">
                                            <label class="form-label" for="u_name_<?= $uid ?>">Name <span class="form-required">*</span></label>
                                            <input id="u_name_<?= $uid ?>" type="text" name="name" class="input"
                                                   value="<?= htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group form-group--grow">
                                            <label class="form-label" for="u_email_<?= $uid ?>">Email <span class="form-required">*</span></label>
                                            <input id="u_email_<?= $uid ?>" type="email" name="email" class="input"
                                                   value="<?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="form-row">
                                        <div class="form-group form-group--grow">
                                            <label class="form-label" for="u_type_<?= $uid ?>">User Type</label>
                                            <select id="u_type_<?= $uid ?>" name="user_type" class="input">
                                                <?php foreach (['admin', 'manager', 'user', 'free'] as $t): ?>
                                                <option value="<?= $t ?>"<?= ($u['user_type'] === $t) ? ' selected' : '' ?>><?= ucfirst($t) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group form-group--grow">
                                            <label class="form-label" for="u_crm_<?= $uid ?>">CRM Access</label>
                                            <select id="u_crm_<?= $uid ?>" name="Module_CRM" class="input">
                                                <?php foreach (['Free', 'User', 'Manager'] as $lvl): ?>
                                                <option value="<?= $lvl ?>"<?= ($u['Module_CRM'] === $lvl) ? ' selected' : '' ?>><?= $lvl ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group form-group--grow">
                                            <label class="form-label" for="u_active_<?= $uid ?>">Status</label>
                                            <select id="u_active_<?= $uid ?>" name="is_active" class="input">
                                                <option value="1"<?= $u['is_active'] ? ' selected' : '' ?>>Active</option>
                                                <option value="0"<?= !$u['is_active'] ? ' selected' : '' ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn btn--ghost btn--sm user-edit-cancel">Cancel</button>
                                <button type="submit" class="btn btn--primary btn--sm">
                                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save
                                </button>
                            </div>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $paginationHtml ?>
</div>

<?php endif; ?>

