<?php
$pageTitle = 'Companies';

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
            <?= number_format($offset + 1) ?>–<?= number_format(min($offset + COMPANIES_PER_PAGE, $totalCount)) ?> of <?= number_format($totalCount) ?>
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
        <p class="eyebrow">Admin</p>
        <h1 class="dash-header__title">Companies</h1>
        <p class="dash-header__sub"><?= number_format($totalCount) ?> company<?= $totalCount !== 1 ? 'ies' : '' ?></p>
    </div>
</div>

<hr class="divider--green mb-xl">

<!-- Search -->
<form method="GET" action="/admin/companylist" class="list-search mb-md">
    <input type="hidden" name="sort" value="<?= htmlspecialchars($sort, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="dir"  value="<?= htmlspecialchars($dir,  ENT_QUOTES, 'UTF-8') ?>">
    <div class="list-search__field">
        <i class="fa-solid fa-magnifying-glass list-search__icon" aria-hidden="true"></i>
        <input type="search" name="search" class="input list-search__input"
               placeholder="Search by name, email, city, or website…"
               value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
               autocomplete="off">
    </div>
    <button type="submit" class="btn btn--secondary">Search</button>
    <?php if ($search !== ''): ?>
    <a href="/admin/companylist" class="btn btn--ghost">Clear</a>
    <?php endif; ?>
</form>

<?php if (empty($companies) && $search === ''): ?>

<div class="card dash-panel">
    <div class="dash-panel__empty">
        <i class="fa-regular fa-building" aria-hidden="true"></i>
        <p>No companies found.</p>
    </div>
</div>

<?php elseif (empty($companies)): ?>

<div class="card dash-panel">
    <div class="dash-panel__empty">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <p>No companies match <strong><?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?></strong>.</p>
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
                    <th><a href="<?= $sortLink('city') ?>" class="sort-link">City <?= $sortIcon('city') ?></a></th>
                    <th>Website</th>
                    <th>Members</th>
                    <th><a href="<?= $sortLink('created_at') ?>" class="sort-link">Created <?= $sortIcon('created_at') ?></a></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($companies as $c): ?>
                <?php $cid = (int) $c['id']; ?>
                <tr data-company-id="<?= $cid ?>" style="cursor:pointer;">
                    <td><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <?php if (!empty($c['email'])): ?>
                        <a href="mailto:<?= htmlspecialchars($c['email'], ENT_QUOTES, 'UTF-8') ?>" class="table-link">
                            <?= htmlspecialchars($c['email'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                        <?php else: ?>
                        —
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($c['city'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <?php if (!empty($c['website'])): ?>
                        <a href="<?= htmlspecialchars($c['website'], ENT_QUOTES, 'UTF-8') ?>"
                           target="_blank" rel="noopener noreferrer" class="table-link">
                            <?= htmlspecialchars($c['website'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                        <?php else: ?>
                        —
                        <?php endif; ?>
                    </td>
                    <td><?= (int) $c['member_count'] ?></td>
                    <td><?= htmlspecialchars($c['created_at'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <tr class="li-edit-row" id="company-edit-<?= $cid ?>" hidden>
                    <td colspan="6" class="li-edit-cell">
                        <form method="POST" action="/admin/companylist" novalidate>
                            <input type="hidden" name="_action"    value="update_company">
                            <input type="hidden" name="company_id" value="<?= $cid ?>">
                            <input type="hidden" name="_search"    value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="_sort"      value="<?= htmlspecialchars($sort,   ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="_dir"       value="<?= htmlspecialchars($dir,    ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="_page"      value="<?= $currentPage ?>">
                            <div class="li-edit-grid">
                                <div>
                                    <div class="form-row">
                                        <div class="form-group form-group--grow">
                                            <label class="form-label" for="c_name_<?= $cid ?>">Name <span class="form-required">*</span></label>
                                            <input id="c_name_<?= $cid ?>" type="text" name="name" class="input"
                                                   value="<?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group form-group--grow">
                                            <label class="form-label" for="c_email_<?= $cid ?>">Email</label>
                                            <input id="c_email_<?= $cid ?>" type="email" name="email" class="input"
                                                   value="<?= htmlspecialchars($c['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        </div>
                                        <div class="form-group form-group--grow">
                                            <label class="form-label" for="c_phone_<?= $cid ?>">Phone</label>
                                            <input id="c_phone_<?= $cid ?>" type="text" name="phone" class="input"
                                                   value="<?= htmlspecialchars($c['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group form-group--grow">
                                            <label class="form-label" for="c_website_<?= $cid ?>">Website</label>
                                            <input id="c_website_<?= $cid ?>" type="text" name="website" class="input"
                                                   value="<?= htmlspecialchars($c['website'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="form-row">
                                        <div class="form-group form-group--grow">
                                            <label class="form-label" for="c_address_<?= $cid ?>">Address</label>
                                            <input id="c_address_<?= $cid ?>" type="text" name="address" class="input"
                                                   value="<?= htmlspecialchars($c['address'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group form-group--grow">
                                            <label class="form-label" for="c_city_<?= $cid ?>">City</label>
                                            <input id="c_city_<?= $cid ?>" type="text" name="city" class="input"
                                                   value="<?= htmlspecialchars($c['city'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="c_state_<?= $cid ?>">State</label>
                                            <input id="c_state_<?= $cid ?>" type="text" name="state" class="input"
                                                   value="<?= htmlspecialchars($c['state'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                   style="width:6rem;">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="c_zip_<?= $cid ?>">ZIP</label>
                                            <input id="c_zip_<?= $cid ?>" type="text" name="zip" class="input"
                                                   value="<?= htmlspecialchars($c['zip'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                   style="width:7rem;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="display:flex;justify-content:flex-end;gap:0.5rem;margin-top:0.75rem;">
                                <button type="button" class="btn btn--ghost btn--sm company-edit-cancel">Cancel</button>
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

