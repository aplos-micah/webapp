<?php
$pageTitle = 'System Logs';

$qs = fn(array $overrides) => '?' . http_build_query(array_filter(
    array_merge(['level' => $levelFilter, 'per_page' => $perPage, 'page' => $currentPage], $overrides),
    fn($v) => $v !== '' && $v !== null
));

$levelBadge = [
    'ERROR'   => 'badge--error',
    'WARNING' => 'badge--warning',
    'INFO'    => 'badge--info',
];

function formatBytes(int $bytes): string
{
    if ($bytes < 1024)    return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    return round($bytes / 1048576, 2) . ' MB';
}

$paginationHtml = '';
if ($totalPages > 1) {
    ob_start(); ?>
    <div class="pagination">
        <span class="pagination__info">
            <?= number_format($offset + 1) ?>–<?= number_format(min($offset + $perPage, $totalCount)) ?> of <?= number_format($totalCount) ?>
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
        <h1 class="dash-header__title">System Logs</h1>
        <p class="dash-header__sub">
            <?= number_format($totalCount) ?> entr<?= $totalCount !== 1 ? 'ies' : 'y' ?>
            <?php if ($levelFilter): ?>
            &mdash; filtered to <strong><?= htmlspecialchars($levelFilter, ENT_QUOTES, 'UTF-8') ?></strong>
            <?php endif; ?>
        </p>
    </div>
    <div class="btn-group">
        <form method="POST" action="/admin/logviewer"
              onsubmit="return confirm('Archive the current log and start a fresh one?')">
            <input type="hidden" name="action" value="archive">
            <button type="submit" class="btn btn--secondary">
                <i class="fa-solid fa-box-archive" aria-hidden="true"></i> Archive
            </button>
        </form>
        <form method="POST" action="/admin/logviewer"
              onsubmit="return confirm('Clear all current log entries? This cannot be undone.')">
            <input type="hidden" name="action" value="clear">
            <button type="submit" class="btn btn--ghost">
                <i class="fa-solid fa-trash" aria-hidden="true"></i> Clear
            </button>
        </form>
    </div>
</div>

<hr class="divider--green mb-xl">

<!-- Filter toolbar -->
<div class="filter-bar">

    <div class="filter-bar__group">
        <?php foreach (['' => 'All', 'ERROR' => 'ERROR', 'WARNING' => 'WARNING', 'INFO' => 'INFO'] as $val => $label): ?>
        <a href="<?= $qs(['level' => $val, 'page' => 1]) ?>"
           class="btn btn--sm <?= $levelFilter === $val ? 'btn--primary' : 'btn--ghost' ?>">
            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
        </a>
        <?php endforeach; ?>
    </div>

    <form method="GET" action="/admin/logviewer" class="filter-bar__perpage">
        <?php if ($levelFilter): ?>
        <input type="hidden" name="level" value="<?= htmlspecialchars($levelFilter, ENT_QUOTES, 'UTF-8') ?>">
        <?php endif; ?>
        <input type="hidden" name="page" value="1">
        <label for="per_page" class="filter-bar__label">Per page:</label>
        <select id="per_page" name="per_page" class="input input--auto"
                onchange="this.form.submit()">
            <?php foreach ([100, 200, 300, 500, 1000] as $opt): ?>
            <option value="<?= $opt ?>"<?= $perPage === $opt ? ' selected' : '' ?>><?= $opt ?></option>
            <?php endforeach; ?>
        </select>
    </form>

</div>

<?php if (empty($entries)): ?>

<div class="card dash-panel">
    <div class="dash-panel__empty">
        <i class="fa-regular fa-file-lines" aria-hidden="true"></i>
        <p>
            <?php if ($levelFilter): ?>
            No <strong><?= htmlspecialchars($levelFilter, ENT_QUOTES, 'UTF-8') ?></strong> entries in the current log.
            <?php else: ?>
            The log is empty.
            <?php endif; ?>
        </p>
    </div>
</div>

<?php else: ?>

<form id="entries-form" method="POST" action="/admin/logviewer">
    <input type="hidden" name="action" value="delete_selected">
    <?php if ($levelFilter): ?>
    <input type="hidden" name="_level" value="<?= htmlspecialchars($levelFilter, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <input type="hidden" name="_per_page" value="<?= $perPage ?>">
    <input type="hidden" name="_page"     value="<?= $currentPage ?>">

    <!-- Selection toolbar -->
    <div id="entries-toolbar" class="log-toolbar">
        <span id="entries-count" class="log-toolbar__count"></span>
        <button type="submit" class="btn btn--ghost btn--sm"
                onclick="return confirm('Delete selected entries? This cannot be undone.')">
            <i class="fa-solid fa-trash" aria-hidden="true"></i> Delete Selected
        </button>
        <button type="button" class="btn btn--ghost btn--sm" id="entries-clear-sel">
            Clear Selection
        </button>
    </div>

    <div class="card">
        <?= $paginationHtml ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="col-check">
                            <input type="checkbox" id="entries-select-all" title="Select all on this page">
                        </th>
                        <th class="col-rownum">#</th>
                        <th class="col-timestamp">Timestamp</th>
                        <th class="col-level">Level</th>
                        <th>Message</th>
                        <th class="col-details">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $i => $entry): ?>
                    <?php
                        $rowNum  = $totalCount - $offset - $i;
                        $level   = $entry['level']   ?? '';
                        $message = $entry['message'] ?? '';
                        $ts      = $entry['ts']      ?? '';
                        $context = $entry['context'] ?? null;
                        $idx     = (int) $entry['_idx'];
                    ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="selected[]" value="<?= $idx ?>" class="entry-cb">
                        </td>
                        <td class="td-rownum">
                            <?= $rowNum ?>
                        </td>
                        <td class="td-timestamp">
                            <?= htmlspecialchars($ts, ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td>
                            <span class="badge <?= $levelBadge[$level] ?? 'badge--neutral' ?>">
                                <?= htmlspecialchars($level, ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </td>
                        <td class="td-message">
                            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td>
                            <?php
                                $request = $entry['request'] ?? [];
                                $hasDetails = $context || !empty($request);
                                $userName = $request['user_name'] ?? null;
                                $userType = $request['user_type'] ?? null;
                                $ip       = $request['ip']        ?? null;
                                $browser  = $request['browser']   ?? null;
                                $method   = $request['method']    ?? null;
                                $uri      = $request['uri']       ?? null;
                            ?>
                            <?php if ($hasDetails): ?>
                            <div class="td-details-wrap">
                                <?php if ($userName): ?>
                                <div>
                                    <i class="fa-solid fa-user log-icon" aria-hidden="true"></i>
                                    <?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?>
                                    <?php if ($userType): ?>
                                    <span class="log-meta">(<?= htmlspecialchars($userType, ENT_QUOTES, 'UTF-8') ?>)</span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                <?php if ($ip || $browser): ?>
                                <div class="log-meta">
                                    <i class="fa-solid fa-globe log-icon" aria-hidden="true"></i>
                                    <?= htmlspecialchars(implode(' · ', array_filter([$ip, $browser])), ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <?php endif; ?>
                                <?php if ($method && $uri): ?>
                                <div class="log-meta--mono">
                                    <?= htmlspecialchars($method . ' ' . $uri, ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <?php endif; ?>
                                <?php if ($context || count($request) > 5): ?>
                                <details class="log-details">
                                    <summary class="log-details__summary">Full details</summary>
                                    <?php if ($context): ?>
                                    <pre class="log-pre"><?= htmlspecialchars(json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?></pre>
                                    <?php endif; ?>
                                    <?php if (!empty($request)): ?>
                                    <pre class="log-pre"><?= htmlspecialchars(json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?></pre>
                                    <?php endif; ?>
                                </details>
                                <?php endif; ?>
                            </div>
                            <?php else: ?>
                            <span class="log-empty">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= $paginationHtml ?>
    </div>
</form>

<?php endif; ?>

<?php if (!empty($archivedFiles)): ?>

<h2 class="log-archive-heading">Archived Logs</h2>

<form id="archives-form" method="POST" action="/admin/logviewer">
    <input type="hidden" name="action" value="delete_archives">

    <!-- Selection toolbar -->
    <div id="archives-toolbar" class="log-toolbar">
        <span id="archives-count" class="log-toolbar__count"></span>
        <button type="submit" class="btn btn--ghost btn--sm"
                onclick="return confirm('Delete selected archived logs? This cannot be undone.')">
            <i class="fa-solid fa-trash" aria-hidden="true"></i> Delete Selected
        </button>
        <button type="button" class="btn btn--ghost btn--sm" id="archives-clear-sel">
            Clear Selection
        </button>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="col-check">
                            <input type="checkbox" id="archives-select-all" title="Select all">
                        </th>
                        <th>File</th>
                        <th>Entries</th>
                        <th>Size</th>
                        <th>Archived</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($archivedFiles as $archive): ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="filenames[]"
                                   value="<?= htmlspecialchars($archive['name'], ENT_QUOTES, 'UTF-8') ?>"
                                   class="archive-cb">
                        </td>
                        <td class="td-mono-sm">
                            <?= htmlspecialchars($archive['name'], ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td><?= number_format($archive['entries']) ?></td>
                        <td><?= formatBytes((int) $archive['size']) ?></td>
                        <td class="td-sm">
                            <?= date('Y-m-d H:i:s', (int) $archive['mtime']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</form>

<?php endif; ?>
