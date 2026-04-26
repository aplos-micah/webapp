<?php
$pageTitle = 'OAuth Sessions';

$now = time();

$codeStatus = function(array $code) use ($now): array {
    if ($code['used_at'] !== null)              return ['badge--neutral', 'Used'];
    if (strtotime($code['expires_at']) <= $now) return ['badge--warning', 'Expired'];
    return ['badge--success', 'Pending'];
};

// ── Query-string helper (preserves tab, ctab, page) ──────────────────────────
$qs = fn(array $overrides) => '?' . http_build_query(array_filter(
    array_merge(['tab' => $tab, 'ctab' => $ctab, 'page' => $currentPage], $overrides),
    fn($v) => $v !== '' && $v !== null
));

// ── Pagination HTML ───────────────────────────────────────────────────────────
$paginationHtml = '';
if ($totalPages > 1) {
    ob_start(); ?>
    <div class="pagination">
        <span class="pagination__info">
            <?= number_format($offset + 1) ?>–<?= number_format(min($offset + TOKENS_PER_PAGE, $totalCount)) ?> of <?= number_format($totalCount) ?>
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

// ── Token table helper ────────────────────────────────────────────────────────
$tokenTable = function(array $tokens, string $expiryLabel, string $postAction, string $actionLabel, string $actionIcon) use ($tab, $currentPage): void { ?>
<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Client &amp; Dates</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tokens as $t): ?>
            <?php $tid = (int) $t['id']; ?>
            <tr data-token-id="<?= $tid ?>" class="row-clickable">
                <td>
                    <p class="cell-primary"><?= htmlspecialchars($t['user_name'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="text-muted cell-secondary"><?= htmlspecialchars($t['user_email'], ENT_QUOTES, 'UTF-8') ?></p>
                </td>
                <td>
                    <p class="cell-primary"><code><?= htmlspecialchars($t['client_id'], ENT_QUOTES, 'UTF-8') ?></code></p>
                    <p class="text-muted cell-secondary">Issued: <?= htmlspecialchars($t['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="text-muted cell-secondary"><?= $expiryLabel ?>: <?= htmlspecialchars($t['expires_at'], ENT_QUOTES, 'UTF-8') ?></p>
                </td>
                <td>
                    <form method="POST" action="/admin/manageoauth"
                          onsubmit="return confirm('<?= htmlspecialchars($actionLabel, ENT_QUOTES) ?> this token?')">
                        <input type="hidden" name="_action"  value="<?= htmlspecialchars($postAction, ENT_QUOTES) ?>">
                        <input type="hidden" name="token_id" value="<?= $tid ?>">
                        <input type="hidden" name="_tab"     value="<?= htmlspecialchars($tab, ENT_QUOTES) ?>">
                        <input type="hidden" name="_page"    value="<?= $currentPage ?>">
                        <button type="submit" class="btn btn--ghost btn--sm">
                            <i class="fa-solid <?= $actionIcon ?>" aria-hidden="true"></i> <?= $actionLabel ?>
                        </button>
                    </form>
                </td>
            </tr>
            <tr class="li-edit-row" id="token-edit-<?= $tid ?>" hidden>
                <td colspan="3" class="li-edit-cell">
                    <form method="POST" action="/admin/manageoauth" novalidate>
                        <input type="hidden" name="_action"  value="update_token_expiry">
                        <input type="hidden" name="token_id" value="<?= $tid ?>">
                        <input type="hidden" name="_tab"     value="<?= htmlspecialchars($tab, ENT_QUOTES) ?>">
                        <input type="hidden" name="_page"    value="<?= $currentPage ?>">
                        <div class="li-edit-grid">
                            <div>
                                <div class="form-row">
                                    <div class="form-group form-group--grow">
                                        <label class="form-label" for="exp_<?= $tid ?>">Expiry Date &amp; Time</label>
                                        <input id="exp_<?= $tid ?>" type="datetime-local" name="expires_at" class="input"
                                               value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($t['expires_at'])), ENT_QUOTES, 'UTF-8') ?>"
                                               required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn--ghost btn--sm token-edit-cancel">Cancel</button>
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
<?php };
?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">Admin</p>
        <h1 class="dash-header__title">OAuth Sessions</h1>
        <p class="dash-header__sub"><?= count($clients) ?> registered client<?= count($clients) !== 1 ? 's' : '' ?> &middot; <?= $activeCount ?> active token<?= $activeCount !== 1 ? 's' : '' ?></p>
    </div>
    <form method="POST" action="/admin/manageoauth"
          onsubmit="return confirm('Purge all expired tokens and used authorization codes?')">
        <input type="hidden" name="_action" value="purge_expired">
        <input type="hidden" name="_tab"    value="<?= htmlspecialchars($tab, ENT_QUOTES) ?>">
        <input type="hidden" name="_page"   value="<?= $currentPage ?>">
        <button type="submit" class="btn btn--secondary">
            <i class="fa-solid fa-broom" aria-hidden="true"></i> Purge Expired
        </button>
    </form>
</div>

<hr class="divider--green mb-xl">

<!-- Stats -->
<div class="dash-stats mb-xl">
    <div class="dash-stat card">
        <p class="dash-stat__label">Registered Clients</p>
        <p class="dash-stat__value"><?= count($clients) ?></p>
    </div>
    <div class="dash-stat card">
        <p class="dash-stat__label">Active Tokens</p>
        <p class="dash-stat__value"><?= $activeCount ?></p>
    </div>
    <div class="dash-stat card">
        <p class="dash-stat__label">Expired Tokens</p>
        <p class="dash-stat__value"><?= $expiredCount ?></p>
    </div>
    <div class="dash-stat card">
        <p class="dash-stat__label">Recent Auth Codes</p>
        <p class="dash-stat__value"><?= count($codes) ?></p>
    </div>
</div>

<!-- ── Registered Clients ──────────────────────────────────────────────────── -->
<div class="section-header">
    <h2 class="section-header__title">Registered Clients</h2>
    <div class="btn-group">
        <button id="register-client-toggle" type="button" class="btn btn--secondary btn--sm">
            <i class="fa-solid fa-plus" aria-hidden="true"></i> Register Client
        </button>
        <a href="<?= $qs(['ctab' => 'active', 'page' => 1]) ?>"
           class="btn btn--sm <?= $ctab === 'active' ? 'btn--primary' : 'btn--ghost' ?>">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            Active <span class="badge badge--neutral badge--spaced"><?= $activeClientCount ?></span>
        </a>
        <a href="<?= $qs(['ctab' => 'disabled', 'page' => 1]) ?>"
           class="btn btn--sm <?= $ctab === 'disabled' ? 'btn--primary' : 'btn--ghost' ?>">
            <i class="fa-solid fa-circle-pause" aria-hidden="true"></i>
            Disabled <span class="badge badge--neutral badge--spaced"><?= $disabledClientCount ?></span>
        </a>
    </div>
</div>

<div id="register-client-panel" class="card mb-md" hidden>
    <form method="POST" action="/admin/manageoauth" novalidate>
        <input type="hidden" name="_action" value="register_client">
        <input type="hidden" name="_tab"    value="<?= htmlspecialchars($tab, ENT_QUOTES) ?>">
        <div class="li-edit-grid">
            <div>
                <div class="form-row">
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="new_client_id">Client ID <span class="form-required">*</span></label>
                        <input id="new_client_id" type="text" name="client_id" class="input"
                               placeholder="e.g. ClaudeAI, YourApplication" autocomplete="off" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="new_client_name">Name <span class="form-required">*</span></label>
                        <input id="new_client_name" type="text" name="name" class="input"
                               placeholder="e.g. Claude.ai MCP Connector" autocomplete="off" required>
                    </div>
                </div>
            </div>
            <div>
                <div class="form-row">
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="new_redirect_uris">Allowed Redirect URIs <span class="form-required">*</span></label>
                        <textarea id="new_redirect_uris" name="redirect_uris" class="input"
                                  rows="4" placeholder="One URI per line" required></textarea>
                    </div>
                </div>
            </div>
        </div>
        <hr class="panel-rule">
        <dl class="help-dl">
            <dt class="help-dl__term">Client ID</dt>
            <dd class="help-dl__detail">A unique machine-readable identifier for the application. Must match exactly what the connecting application sends in its OAuth requests (e.g. <code>ClaudeAI, NewApp</code>).</dd>
            <dt class="help-dl__term">Name</dt>
            <dd class="help-dl__detail">A human-readable label shown on the consent screen and in this admin panel (e.g. <em>Claude.ai MCP Connector</em>).</dd>
            <dt class="help-dl__term">Redirect URIs</dt>
            <dd class="help-dl__detail">The exact callback URLs the application is permitted to redirect to after authorization. Enter one per line. Any URI not listed here will be rejected during the OAuth flow.</dd>
        </dl>
        <div class="form-actions">
            <button type="button" id="register-client-cancel" class="btn btn--ghost btn--sm">Cancel</button>
            <button type="submit" class="btn btn--primary btn--sm">
                <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Register
            </button>
        </div>
    </form>
</div>

<?php if (empty($clients)): ?>
<div class="card dash-panel mb-xl">
    <div class="dash-panel__empty">
        <i class="fa-solid fa-plug" aria-hidden="true"></i>
        <p><?= $ctab === 'disabled' ? 'No disabled clients.' : 'No active clients registered.' ?></p>
    </div>
</div>
<?php else: ?>
<?php foreach ($clients as $c): ?>
<?php $active = (bool) $c['is_active']; ?>
<div class="card mb-md<?= $active ? '' : ' card--faded' ?>">
    <div class="client-card__inner">
        <div>
            <div class="client-card__meta">
                <p class="client-card__name"><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php if (!$active): ?>
                <span class="badge badge--warning">Disabled</span>
                <?php else: ?>
                <span class="badge badge--success">Active</span>
                <?php endif; ?>
            </div>
            <p class="text-muted client-card__sub">Client ID: <code><?= htmlspecialchars($c['client_id'], ENT_QUOTES, 'UTF-8') ?></code></p>
            <p class="client-card__uri-label">Allowed Redirect URIs</p>
            <?php foreach (json_decode($c['redirect_uris'], true) ?? [] as $uri): ?>
            <p class="text-muted client-card__uri"><?= htmlspecialchars($uri, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endforeach; ?>
            <p class="text-muted client-card__date">Registered <?= htmlspecialchars($c['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="client-card__actions">
            <form method="POST" action="/admin/manageoauth"
                  onsubmit="return confirm('Revoke all tokens for \'<?= htmlspecialchars($c['client_id'], ENT_QUOTES, 'UTF-8') ?>\'?')">
                <input type="hidden" name="_action"   value="revoke_client_tokens">
                <input type="hidden" name="client_id" value="<?= htmlspecialchars($c['client_id'], ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="_tab"      value="<?= htmlspecialchars($tab, ENT_QUOTES) ?>">
                <input type="hidden" name="_ctab"     value="<?= htmlspecialchars($ctab, ENT_QUOTES) ?>">
                <button type="submit" class="btn btn--ghost btn--sm">
                    <i class="fa-solid fa-ban" aria-hidden="true"></i> Revoke All Tokens
                </button>
            </form>
            <?php if ($active): ?>
            <form method="POST" action="/admin/manageoauth"
                  onsubmit="return confirm('Disable \'<?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?>\'? No new tokens will be issued until it is re-enabled.')">
                <input type="hidden" name="_action"      value="disable_client">
                <input type="hidden" name="client_db_id" value="<?= (int) $c['id'] ?>">
                <input type="hidden" name="_tab"         value="<?= htmlspecialchars($tab, ENT_QUOTES) ?>">
                <input type="hidden" name="_ctab"        value="<?= htmlspecialchars($ctab, ENT_QUOTES) ?>">
                <button type="submit" class="btn btn--ghost btn--sm">
                    <i class="fa-solid fa-circle-pause" aria-hidden="true"></i> Disable
                </button>
            </form>
            <?php else: ?>
            <form method="POST" action="/admin/manageoauth">
                <input type="hidden" name="_action"      value="enable_client">
                <input type="hidden" name="client_db_id" value="<?= (int) $c['id'] ?>">
                <input type="hidden" name="_tab"         value="<?= htmlspecialchars($tab, ENT_QUOTES) ?>">
                <input type="hidden" name="_ctab"        value="<?= htmlspecialchars($ctab, ENT_QUOTES) ?>">
                <button type="submit" class="btn btn--secondary btn--sm">
                    <i class="fa-solid fa-circle-play" aria-hidden="true"></i> Enable
                </button>
            </form>
            <form method="POST" action="/admin/manageoauth"
                  onsubmit="return confirm('Permanently delete \'<?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?>\'? This cannot be undone.')">
                <input type="hidden" name="_action"      value="delete_client">
                <input type="hidden" name="client_db_id" value="<?= (int) $c['id'] ?>">
                <input type="hidden" name="_tab"         value="<?= htmlspecialchars($tab, ENT_QUOTES) ?>">
                <input type="hidden" name="_ctab"        value="<?= htmlspecialchars($ctab, ENT_QUOTES) ?>">
                <button type="submit" class="btn btn--ghost btn--sm">
                    <i class="fa-solid fa-trash" aria-hidden="true"></i> Delete
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
<div class="mb-xl"></div>
<?php endif; ?>

<!-- ── Access Tokens ───────────────────────────────────────────────────────── -->
<div class="section-header">
    <h2 class="section-header__title">Access Tokens</h2>
    <div class="btn-group">
        <a href="<?= $qs(['tab' => 'active', 'page' => 1]) ?>"
           class="btn btn--sm <?= $tab === 'active' ? 'btn--primary' : 'btn--ghost' ?>">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            Active <span class="badge badge--neutral badge--spaced"><?= $activeCount ?></span>
        </a>
        <a href="<?= $qs(['tab' => 'expired', 'page' => 1]) ?>"
           class="btn btn--sm <?= $tab === 'expired' ? 'btn--primary' : 'btn--ghost' ?>">
            <i class="fa-solid fa-clock" aria-hidden="true"></i>
            Expired <span class="badge badge--neutral badge--spaced"><?= $expiredCount ?></span>
        </a>
    </div>
</div>
<p class="text-muted mb-sm text-sm">Double-click a row to edit the expiry date.</p>

<?php if ($tab === 'active'): ?>

<?php if (empty($activeTokens)): ?>
<div class="card dash-panel mb-xl">
    <div class="dash-panel__empty">
        <i class="fa-solid fa-key" aria-hidden="true"></i>
        <p>No active tokens.</p>
    </div>
</div>
<?php else: ?>
<div class="card mb-xl">
    <?= $paginationHtml ?>
    <?php $tokenTable($activeTokens, 'Expires', 'revoke_token', 'Revoke', 'fa-ban'); ?>
    <?= $paginationHtml ?>
</div>
<?php endif; ?>

<?php else: ?>

<?php if (empty($expiredTokens)): ?>
<div class="card dash-panel mb-xl">
    <div class="dash-panel__empty">
        <i class="fa-solid fa-clock" aria-hidden="true"></i>
        <p>No expired tokens awaiting purge.</p>
    </div>
</div>
<?php else: ?>
<div class="card mb-xl">
    <?= $paginationHtml ?>
    <?php $tokenTable($expiredTokens, 'Expired', 'revoke_token', 'Delete', 'fa-trash'); ?>
    <?= $paginationHtml ?>
</div>
<?php endif; ?>

<?php endif; ?>

<!-- ── Recent Authorization Codes ─────────────────────────────────────────── -->
<div class="section-header--sm">
    <h2 class="section-header__title">Recent Authorization Codes</h2>
    <form method="POST" action="/admin/manageoauth"
          onsubmit="return confirm('Purge all used and expired authorization codes?')">
        <input type="hidden" name="_action" value="purge_codes">
        <input type="hidden" name="_tab"    value="<?= htmlspecialchars($tab, ENT_QUOTES) ?>">
        <input type="hidden" name="_page"   value="<?= $currentPage ?>">
        <button type="submit" class="btn btn--ghost btn--sm">
            <i class="fa-solid fa-broom" aria-hidden="true"></i> Purge Used &amp; Expired
        </button>
    </form>
</div>
<p class="text-muted mb-md">Last 50 requests — codes are single-use and expire after 10 minutes.</p>

<?php if (empty($codes)): ?>
<div class="card dash-panel">
    <div class="dash-panel__empty">
        <i class="fa-solid fa-ticket" aria-hidden="true"></i>
        <p>No authorization codes on record.</p>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Client &amp; Timestamps</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($codes as $c): ?>
                <?php [$badgeClass, $statusLabel] = $codeStatus($c); ?>
                <?php $unusable = $c['used_at'] !== null || strtotime($c['expires_at']) <= $now; ?>
                <tr>
                    <td>
                        <p class="cell-primary"><?= htmlspecialchars($c['user_name'], ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="text-muted cell-secondary"><?= htmlspecialchars($c['user_email'], ENT_QUOTES, 'UTF-8') ?></p>
                    </td>
                    <td>
                        <p class="cell-primary"><code><?= htmlspecialchars($c['client_id'], ENT_QUOTES, 'UTF-8') ?></code></p>
                        <p class="text-muted cell-secondary">Requested: <?= htmlspecialchars($c['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="text-muted cell-secondary">
                            <?= $c['used_at']
                                ? 'Used: '    . htmlspecialchars($c['used_at'],    ENT_QUOTES, 'UTF-8')
                                : 'Expires: ' . htmlspecialchars($c['expires_at'], ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </td>
                    <td><span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span></td>
                    <td>
                        <?php if ($unusable): ?>
                        <form method="POST" action="/admin/manageoauth"
                              onsubmit="return confirm('Delete this authorization code?')">
                            <input type="hidden" name="_action"  value="delete_code">
                            <input type="hidden" name="code_id"  value="<?= (int) $c['id'] ?>">
                            <input type="hidden" name="_tab"     value="<?= htmlspecialchars($tab, ENT_QUOTES) ?>">
                            <input type="hidden" name="_page"    value="<?= $currentPage ?>">
                            <button type="submit" class="btn btn--ghost btn--sm">
                                <i class="fa-solid fa-trash" aria-hidden="true"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
