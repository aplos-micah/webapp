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
            <tr data-token-id="<?= $tid ?>" style="cursor: pointer;">
                <td>
                    <p style="margin: 0; font-weight: 500;"><?= htmlspecialchars($t['user_name'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="text-muted" style="margin: 0; font-size: var(--fs-body-sm);"><?= htmlspecialchars($t['user_email'], ENT_QUOTES, 'UTF-8') ?></p>
                </td>
                <td>
                    <p style="margin: 0; font-weight: 500;"><code><?= htmlspecialchars($t['client_id'], ENT_QUOTES, 'UTF-8') ?></code></p>
                    <p class="text-muted" style="margin: 0; font-size: var(--fs-body-sm);">Issued: <?= htmlspecialchars($t['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="text-muted" style="margin: 0; font-size: var(--fs-body-sm);"><?= $expiryLabel ?>: <?= htmlspecialchars($t['expires_at'], ENT_QUOTES, 'UTF-8') ?></p>
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
                        <div style="display:flex;justify-content:flex-end;gap:0.5rem;margin-top:0.75rem;">
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
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
    <h2 style="margin: 0;">Registered Clients</h2>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <button id="register-client-toggle" type="button" class="btn btn--secondary btn--sm">
            <i class="fa-solid fa-plus" aria-hidden="true"></i> Register Client
        </button>
        <a href="<?= $qs(['ctab' => 'active', 'page' => 1]) ?>"
           class="btn btn--sm <?= $ctab === 'active' ? 'btn--primary' : 'btn--ghost' ?>">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            Active <span class="badge badge--neutral" style="margin-left:0.35rem;"><?= $activeClientCount ?></span>
        </a>
        <a href="<?= $qs(['ctab' => 'disabled', 'page' => 1]) ?>"
           class="btn btn--sm <?= $ctab === 'disabled' ? 'btn--primary' : 'btn--ghost' ?>">
            <i class="fa-solid fa-circle-pause" aria-hidden="true"></i>
            Disabled <span class="badge badge--neutral" style="margin-left:0.35rem;"><?= $disabledClientCount ?></span>
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
        <hr style="margin: 1rem 0; border: none; border-top: 1px solid var(--color-border, #e5e7eb);">
        <dl style="display: grid; grid-template-columns: max-content 1fr; gap: 0.25rem 1rem; font-size: var(--fs-body-sm); margin: 0;">
            <dt style="font-weight: 600; color: var(--color-mid-blue);">Client ID</dt>
            <dd style="margin: 0;">A unique machine-readable identifier for the application. Must match exactly what the connecting application sends in its OAuth requests (e.g. <code>ClaudeAI, NewApp</code>).</dd>
            <dt style="font-weight: 600; color: var(--color-mid-blue);">Name</dt>
            <dd style="margin: 0;">A human-readable label shown on the consent screen and in this admin panel (e.g. <em>Claude.ai MCP Connector</em>).</dd>
            <dt style="font-weight: 600; color: var(--color-mid-blue);">Redirect URIs</dt>
            <dd style="margin: 0;">The exact callback URLs the application is permitted to redirect to after authorization. Enter one per line. Any URI not listed here will be rejected during the OAuth flow.</dd>
        </dl>
        <div style="display:flex;justify-content:flex-end;gap:0.5rem;margin-top:1rem;">
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
<div class="card mb-md" style="<?= $active ? '' : 'opacity: 0.7;' ?>">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <p style="font-weight: 600; margin: 0;"><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php if (!$active): ?>
                <span class="badge badge--warning">Disabled</span>
                <?php else: ?>
                <span class="badge badge--success">Active</span>
                <?php endif; ?>
            </div>
            <p class="text-muted" style="margin: 0 0 0.75rem; font-size: var(--fs-body-sm);">Client ID: <code><?= htmlspecialchars($c['client_id'], ENT_QUOTES, 'UTF-8') ?></code></p>
            <p style="margin: 0 0 0.25rem; font-size: var(--fs-body-sm); font-weight: 500;">Allowed Redirect URIs</p>
            <?php foreach (json_decode($c['redirect_uris'], true) ?? [] as $uri): ?>
            <p class="text-muted" style="margin: 0; font-size: var(--fs-body-sm);"><?= htmlspecialchars($uri, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endforeach; ?>
            <p class="text-muted" style="margin: 0.75rem 0 0; font-size: var(--fs-body-sm);">Registered <?= htmlspecialchars($c['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div style="display: flex; flex-direction: column; gap: 0.5rem; flex-shrink: 0; align-items: flex-end;">
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
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
    <h2 style="margin: 0;">Access Tokens</h2>
    <div style="display: flex; gap: 0.5rem;">
        <a href="<?= $qs(['tab' => 'active', 'page' => 1]) ?>"
           class="btn btn--sm <?= $tab === 'active' ? 'btn--primary' : 'btn--ghost' ?>">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            Active <span class="badge badge--neutral" style="margin-left:0.35rem;"><?= $activeCount ?></span>
        </a>
        <a href="<?= $qs(['tab' => 'expired', 'page' => 1]) ?>"
           class="btn btn--sm <?= $tab === 'expired' ? 'btn--primary' : 'btn--ghost' ?>">
            <i class="fa-solid fa-clock" aria-hidden="true"></i>
            Expired <span class="badge badge--neutral" style="margin-left:0.35rem;"><?= $expiredCount ?></span>
        </a>
    </div>
</div>
<p class="text-muted mb-sm" style="font-size: var(--fs-body-sm);">Double-click a row to edit the expiry date.</p>

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
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
    <h2 style="margin: 0;">Recent Authorization Codes</h2>
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
                        <p style="margin: 0; font-weight: 500;"><?= htmlspecialchars($c['user_name'], ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="text-muted" style="margin: 0; font-size: var(--fs-body-sm);"><?= htmlspecialchars($c['user_email'], ENT_QUOTES, 'UTF-8') ?></p>
                    </td>
                    <td>
                        <p style="margin: 0; font-weight: 500;"><code><?= htmlspecialchars($c['client_id'], ENT_QUOTES, 'UTF-8') ?></code></p>
                        <p class="text-muted" style="margin: 0; font-size: var(--fs-body-sm);">Requested: <?= htmlspecialchars($c['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="text-muted" style="margin: 0; font-size: var(--fs-body-sm);">
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

