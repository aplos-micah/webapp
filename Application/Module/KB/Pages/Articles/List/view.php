<?php
$pageTitle = 'Knowledge Base';

$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

$qs = fn(array $overrides) => '?' . http_build_query(array_filter(
    array_merge([
        'search'   => $search,
        'sort'     => $sort,
        'dir'      => $dir,
        'status'   => $status,
        'category' => $category,
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
    'Published' => 'badge--success',
    'Archived'  => 'badge--neutral',
];

$paginationHtml = '';
if ($totalPages > 1) {
    ob_start(); ?>
    <div class="pagination">
        <span class="pagination__info">
            <?= number_format($offset + 1) ?>–<?= number_format(min($offset + KB_PER_PAGE, $totalCount)) ?> of <?= number_format($totalCount) ?>
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
        <p class="eyebrow">Knowledge Base</p>
        <h1 class="dash-header__title">Articles</h1>
        <p class="dash-header__sub"><?= number_format($totalCount) ?> article<?= $totalCount !== 1 ? 's' : '' ?></p>
    </div>
    <div>
        <a href="/kb/articles/new" class="btn btn--primary">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            New Article
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<!-- Search + Filters -->
<form method="GET" action="/kb/articles/list" class="mb-md">
    <div class="form-row">
        <div class="form-group form-group--grow">
            <div class="list-search__field">
                <i class="fa-solid fa-magnifying-glass list-search__icon" aria-hidden="true"></i>
                <input
                    type="search"
                    name="search"
                    class="input list-search__input"
                    placeholder="Search by title, content, or tags…"
                    value="<?= $e($search) ?>"
                    autocomplete="off"
                    hx-get="/kb/articles/list"
                    hx-trigger="input delay:300ms"
                    hx-target="#article-results"
                    hx-select="#article-results"
                    hx-swap="outerHTML"
                    hx-include="closest form"
                >
            </div>
        </div>
        <div class="form-group">
            <select name="status" class="input" onchange="this.form.submit()">
                <option value="">Published</option>
                <?php foreach (Article::STATUSES as $s): ?>
                <option value="<?= $e($s) ?>"<?= $status === $s ? ' selected' : '' ?>><?= $e($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <select name="category" class="input" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach (Article::CATEGORIES as $c): ?>
                <option value="<?= $e($c) ?>"<?= $category === $c ? ' selected' : '' ?>><?= $e($c) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <input type="hidden" name="sort" value="<?= $e($sort) ?>">
        <input type="hidden" name="dir"  value="<?= $e($dir) ?>">
        <div class="form-group">
            <button type="submit" class="btn btn--secondary">Search</button>
        </div>
        <?php if ($search !== '' || $status !== '' || $category !== ''): ?>
        <div class="form-group">
            <a href="/kb/articles/list" class="btn btn--ghost">Clear</a>
        </div>
        <?php endif; ?>
    </div>
</form>

<div id="article-results">
<?php if (empty($articles) && $search === '' && $status === '' && $category === ''): ?>

<div class="card content-panel">
    <div class="content-panel__empty">
        <i class="fa-regular fa-file-lines" aria-hidden="true"></i>
        <p>No articles yet. <a href="/kb/articles/new">Write the first article</a>.</p>
    </div>
</div>

<?php elseif (empty($articles)): ?>

<div class="card content-panel">
    <div class="content-panel__empty">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <p>No articles match your filters.</p>
    </div>
</div>

<?php else: ?>

<div class="card">
    <?= $paginationHtml ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th><a href="<?= $sortLink('title') ?>" class="sort-link">Title <?= $sortIcon('title') ?></a></th>
                    <th><a href="<?= $sortLink('category') ?>" class="sort-link">Category <?= $sortIcon('category') ?></a></th>
                    <th><a href="<?= $sortLink('status') ?>" class="sort-link">Status <?= $sortIcon('status') ?></a></th>
                    <th>Author</th>
                    <th><a href="<?= $sortLink('view_count') ?>" class="sort-link">Views <?= $sortIcon('view_count') ?></a></th>
                    <th><a href="<?= $sortLink('updated_at') ?>" class="sort-link">Updated <?= $sortIcon('updated_at') ?></a></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $article): ?>
                <tr>
                    <td>
                        <a href="/kb/articles/details?id=<?= (int) $article['id'] ?>" class="table-link">
                            <?= $e($article['title']) ?>
                        </a>
                    </td>
                    <td><?= $e($article['category'] ?? '—') ?></td>
                    <td>
                        <span class="badge <?= $statusBadge[$article['status']] ?? 'badge--neutral' ?>">
                            <?= $e($article['status']) ?>
                        </span>
                    </td>
                    <td><?= $e($article['author_name'] ?? '—') ?></td>
                    <td><?= number_format((int) ($article['view_count'] ?? 0)) ?></td>
                    <td><?= $e(substr($article['updated_at'] ?? '', 0, 10)) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $paginationHtml ?>
</div>

<?php endif; ?>
</div>
