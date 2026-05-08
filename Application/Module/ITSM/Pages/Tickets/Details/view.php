<?php
$e   = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$val = fn($v) => ($v !== null && $v !== '')
    ? htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8')
    : '<span class="text-muted">—</span>';
$fld = fn($v) => $e($v ?? '');
$sel = fn(string $field, string $opt) => ((string) ($ticket[$field] ?? '') === $opt) ? ' selected' : '';

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
?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">ITSM / <a href="/itsm/tickets/list">Tickets</a></p>
        <h1 class="dash-header__title"><?= $e($ticket['ticket_number'] ?: 'Ticket') ?></h1>
        <p class="dash-header__sub"><?= $e($ticket['title']) ?></p>
    </div>
    <div class="btn-group">
        <?php if ($editMode): ?>
        <a href="/itsm/tickets/details?id=<?= $ticket['id'] ?>" class="btn btn--ghost">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i> Cancel
        </a>
        <button type="submit" form="ticket-edit-form" class="btn btn--primary">
            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save Changes
        </button>
        <?php else: ?>
        <a href="/itsm/tickets/details?id=<?= $ticket['id'] ?>&edit" class="btn btn--secondary">
            <i class="fa-solid fa-pen" aria-hidden="true"></i> Edit
        </a>
        <a href="/itsm/tickets/list" class="btn btn--ghost">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back
        </a>
        <?php endif; ?>
    </div>
</div>

<hr class="divider--green mb-xl">

<?php if ($editError): ?>
<div class="alert alert--warning mb-md" role="alert">
    <span class="alert__icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
    <div class="alert__body"><?= $e($editError) ?></div>
</div>
<?php endif; ?>

<!-- Status Workflow -->
<div class="card mb-lg">
    <div class="step-progress<?= $ticket['status'] === 'Closed' ? ' step-progress--error' : '' ?>">
        <?php
        $statusOrder = ['New', 'In Progress', 'Pending', 'Resolved', 'Closed'];
        foreach ($statusOrder as $i => $step):
            $state = $stepStates[$step] ?? '';
        ?>
        <?php if ($i > 0): ?>
        <div class="step-progress__connector <?= $stepStates[$statusOrder[$i - 1]] === 'is-done' ? 'is-done' : '' ?>"></div>
        <?php endif; ?>
        <div class="step-progress__step <?= $state ?>">
            <div class="step-progress__node">
                <?php if ($state === 'is-done'): ?><i class="fa-solid fa-check" aria-hidden="true"></i>
                <?php elseif ($step === 'Closed'): ?><i class="fa-solid fa-lock" aria-hidden="true"></i>
                <?php else: ?><?= $i + 1 ?><?php endif; ?>
            </div>
            <span class="step-progress__label"><?= $e($step) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php if ($editMode): ?>
<form id="ticket-edit-form" method="POST"
      action="/itsm/tickets/details?id=<?= $ticket['id'] ?>&edit"
      novalidate>
<?php endif; ?>

<div class="detail-layout">

    <!-- ── Primary (left) ─────────────────────────────────────────────────── -->
    <div class="detail-layout__primary">

        <!-- Title & Description -->
        <div class="card profile-card mb-lg">
            <h2 class="profile-card__title">
                <i class="fa-solid fa-align-left" aria-hidden="true"></i>
                Details
            </h2>
            <?php if ($editMode): ?>
            <div class="edit-section">
                <div class="form-group">
                    <label class="form-label" for="title">Title <span class="form-required">*</span></label>
                    <input id="title" type="text" name="title" class="input"
                           value="<?= $fld($ticket['title']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="description">Description</label>
                    <textarea id="description" name="description" class="input" rows="6"><?= $fld($ticket['description']) ?></textarea>
                </div>
            </div>
            <?php else: ?>
            <dl class="field-list">
                <div class="field-list__row"><dt>Title</dt><dd><?= $val($ticket['title']) ?></dd></div>
            </dl>
            <?php if (!empty($ticket['description'])): ?>
            <p class="section-label">Description</p>
            <p class="field-text"><?= nl2br($val($ticket['description'])) ?></p>
            <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Classification -->
        <div class="card profile-card mb-lg">
            <h2 class="profile-card__title">
                <i class="fa-solid fa-tags" aria-hidden="true"></i>
                Classification
            </h2>
            <?php if ($editMode): ?>
            <div class="edit-section">
                <div class="form-row">
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="type">Type</label>
                        <select id="type" name="type" class="input">
                            <?php foreach (Ticket::TYPES as $t): ?>
                            <option value="<?= $e($t) ?>"<?= $sel('type', $t) ?>><?= $e($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="priority">Priority</label>
                        <select id="priority" name="priority" class="input">
                            <?php foreach (Ticket::PRIORITIES as $p): ?>
                            <option value="<?= $e($p) ?>"<?= $sel('priority', $p) ?>><?= $e($p) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="category">Category</label>
                        <select id="category" name="category" class="input">
                            <option value="">— None —</option>
                            <?php foreach (Ticket::CATEGORIES as $c): ?>
                            <option value="<?= $e($c) ?>"<?= $sel('category', $c) ?>><?= $e($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <dl class="field-list">
                <div class="field-list__row">
                    <dt>Type</dt>
                    <dd><?= $val($ticket['type']) ?></dd>
                </div>
                <div class="field-list__row">
                    <dt>Priority</dt>
                    <dd>
                        <?php if (!empty($ticket['priority'])): ?>
                        <span class="badge <?= $priorityBadge[$ticket['priority']] ?? 'badge--neutral' ?>">
                            <?= $e($ticket['priority']) ?>
                        </span>
                        <?php else: ?>—<?php endif; ?>
                    </dd>
                </div>
                <div class="field-list__row"><dt>Category</dt><dd><?= $val($ticket['category']) ?></dd></div>
            </dl>
            <?php endif; ?>
        </div>

        <!-- Resolution — only shown when ticket is Resolved or Closed -->
        <?php if (in_array($ticket['status'], ['Resolved', 'Closed'], true) || $editMode): ?>
        <div class="card profile-card mb-lg">
            <h2 class="profile-card__title">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                Resolution
            </h2>
            <?php if ($editMode): ?>
            <div class="edit-section">
                <div class="form-group">
                    <label class="form-label" for="resolution">Resolution Notes</label>
                    <textarea id="resolution" name="resolution" class="input" rows="4"
                              placeholder="How was this ticket resolved?"><?= $fld($ticket['resolution']) ?></textarea>
                </div>
            </div>
            <?php else: ?>
            <?php if (!empty($ticket['resolution'])): ?>
            <p class="field-text"><?= nl2br($val($ticket['resolution'])) ?></p>
            <?php else: ?>
            <p class="text-muted">No resolution notes.</p>
            <?php endif; ?>
            <?php if (!empty($ticket['resolved_at'])): ?>
            <dl class="field-list">
                <div class="field-list__row"><dt>Resolved</dt><dd><?= $val($ticket['resolved_at']) ?></dd></div>
                <?php if (!empty($ticket['closed_at'])): ?>
                <div class="field-list__row"><dt>Closed</dt><dd><?= $val($ticket['closed_at']) ?></dd></div>
                <?php endif; ?>
            </dl>
            <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>

    <!-- ── Aside (right) ──────────────────────────────────────────────────── -->
    <div class="detail-layout__aside">

        <!-- Status & Assignment -->
        <div class="card tile-card mb-lg">
            <div class="tile-card__header">
                <h2 class="tile-card__title">
                    <i class="fa-solid fa-circle-half-stroke" aria-hidden="true"></i>
                    Status &amp; Assignment
                </h2>
            </div>
            <?php if ($editMode): ?>
            <div class="edit-section" style="padding:1rem 1.25rem;">
                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="input">
                        <?php foreach (Ticket::STATUSES as $s): ?>
                        <option value="<?= $e($s) ?>"<?= $sel('status', $s) ?>><?= $e($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="assigned_to">Assigned To</label>
                    <select id="assigned_to" name="assigned_to" class="input">
                        <option value="">— Unassigned —</option>
                        <?php foreach ($assignableUsers as $u): ?>
                        <option value="<?= (int) $u['id'] ?>"<?= ((string) ($ticket['assigned_to'] ?? '') === (string) $u['id']) ? ' selected' : '' ?>>
                            <?= $e($u['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="reported_by_name">Reporter Name</label>
                    <input id="reported_by_name" type="text" name="reported_by_name" class="input"
                           value="<?= $fld($ticket['reported_by_name']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="reported_by_email">Reporter Email</label>
                    <input id="reported_by_email" type="email" name="reported_by_email" class="input"
                           value="<?= $fld($ticket['reported_by_email']) ?>">
                </div>
            </div>
            <?php else: ?>
            <dl class="field-list" style="padding:0 1.25rem 1rem;">
                <div class="field-list__row">
                    <dt>Status</dt>
                    <dd>
                        <span class="badge <?= $statusBadge[$ticket['status']] ?? 'badge--neutral' ?>">
                            <?= $e($ticket['status']) ?>
                        </span>
                    </dd>
                </div>
                <div class="field-list__row"><dt>Assigned To</dt><dd><?= $val($ticket['assigned_name']) ?></dd></div>
                <div class="field-list__row"><dt>Reporter</dt><dd><?= $val($ticket['reported_by_name']) ?></dd></div>
                <?php if (!empty($ticket['reported_by_email'])): ?>
                <div class="field-list__row">
                    <dt>Reporter Email</dt>
                    <dd><a href="mailto:<?= $e($ticket['reported_by_email']) ?>" class="table-link"><?= $e($ticket['reported_by_email']) ?></a></dd>
                </div>
                <?php endif; ?>
                <div class="field-list__row"><dt>Owner</dt><dd><?= $val($ticket['owner_name']) ?></dd></div>
            </dl>
            <?php endif; ?>
        </div>

        <!-- Record -->
        <div class="card tile-card mb-lg">
            <div class="tile-card__header">
                <h2 class="tile-card__title">
                    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                    Record
                </h2>
            </div>
            <dl class="field-list" style="padding:0 1.25rem 1rem;">
                <div class="field-list__row"><dt>Ticket #</dt><dd><?= $val($ticket['ticket_number']) ?></dd></div>
                <div class="field-list__row"><dt>ID</dt><dd><?= (int) $ticket['id'] ?></dd></div>
                <div class="field-list__row"><dt>Created</dt><dd><?= $val($ticket['created_at']) ?></dd></div>
                <div class="field-list__row"><dt>Updated</dt><dd><?= $val($ticket['updated_at']) ?></dd></div>
            </dl>
        </div>

    </div>
</div>

<?php if ($editMode): ?>
<div class="profile-card__footer profile-card__footer--end mb-xl">
    <a href="/itsm/tickets/details?id=<?= $ticket['id'] ?>" class="btn btn--ghost">Cancel</a>
    <button type="submit" form="ticket-edit-form" class="btn btn--primary">
        <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save Changes
    </button>
</div>
</form>
<?php endif; ?>
