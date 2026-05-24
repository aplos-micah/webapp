<?php
$e   = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$val = fn($v) => ($v !== null && $v !== '') ? htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">—</span>';

$outcomeBadge = [
    'Positive'           => 'badge--success',
    'Neutral'            => 'badge--neutral',
    'Negative'           => 'badge--danger',
    'Completed'          => 'badge--success',
    'No Response'        => 'badge--neutral',
    'Follow-up Required' => 'badge--warning',
    'Cancelled'          => 'badge--neutral',
];
?>

<div class="dash-header">
    <div>
        <p class="eyebrow">CRM / <a href="/crm/activities/list">Activities</a></p>
        <h1 class="dash-header__title"><?= $e($activity['type_name']) ?></h1>
        <p class="dash-header__sub"><?= $e($activity['activity_date']) ?></p>
    </div>
    <div class="btn-group">
        <?php if ($editMode): ?>
        <a href="/crm/activities/details?id=<?= (int)$activity['id'] ?>" class="btn btn--ghost">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i> Cancel
        </a>
        <button type="submit" form="activity-edit-form" class="btn btn--primary">
            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save
        </button>
        <?php else: ?>
        <?php if ($canEdit): ?>
        <a href="/crm/activities/details?id=<?= (int)$activity['id'] ?>&edit" class="btn btn--secondary">
            <i class="fa-solid fa-pen" aria-hidden="true"></i> Edit
        </a>
        <?php endif; ?>
        <?php if ($canDelete): ?>
        <form method="POST" action="/crm/activities/details?id=<?= (int)$activity['id'] ?>"
              onsubmit="return confirm('Delete this activity? This cannot be undone.')">
            <input type="hidden" name="action" value="delete">
            <button type="submit" class="btn btn--danger">
                <i class="fa-solid fa-trash" aria-hidden="true"></i> Delete
            </button>
        </form>
        <?php endif; ?>
        <a href="/crm/activities/list" class="btn btn--ghost">
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

<?php if ($editMode): ?>
<form id="activity-edit-form" method="POST" action="/crm/activities/details?id=<?= (int)$activity['id'] ?>&edit" novalidate>
    <input type="hidden" name="action" value="update">
<?php endif; ?>

<div class="card profile-card">

    <?php if ($editMode): ?>
    <div class="edit-section">
        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="activity_type_id">Activity Type <span class="form-required">*</span></label>
                <select id="activity_type_id" name="activity_type_id" class="input" required>
                    <?php foreach ($activeTypes as $t): ?>
                    <option value="<?= (int)$t['id'] ?>"
                        <?= (int)$activity['activity_type_id'] === (int)$t['id'] ? ' selected' : '' ?>>
                        <?= $e($t['name']) ?> ($<?= number_format((float)$t['average_cost'], 2) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="activity_date">Date <span class="form-required">*</span></label>
                <input id="activity_date" type="date" name="activity_date" class="input" required
                       value="<?= $e($activity['activity_date']) ?>">
            </div>
            <div class="form-group" style="width:8rem">
                <label class="form-label" for="duration_minutes">Duration (min)</label>
                <input id="duration_minutes" type="number" name="duration_minutes" class="input"
                       min="1" value="<?= $e($activity['duration_minutes']) ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group" style="width:10rem">
                <label class="form-label" for="cost">Cost ($)</label>
                <input id="cost" type="number" name="cost" class="input"
                       step="0.01" min="0" value="<?= $e($activity['cost']) ?>">
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="outcome">Outcome</label>
                <select id="outcome" name="outcome" class="input">
                    <option value="">— Select outcome —</option>
                    <?php foreach (Activity::OUTCOMES as $o): ?>
                    <option value="<?= $e($o) ?>"<?= ($activity['outcome'] ?? '') === $o ? ' selected' : '' ?>>
                        <?= $e($o) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <?= RichTextArea::render([
            'name'   => 'notes',
            'label'  => 'Notes',
            'value'  => $activity['notes'],
            'rows'   => 3,
            'preset' => 'moderate',
        ]) ?>

        <hr class="divider mb-md">
        <p class="form-hint mb-md">At least one link is required.</p>

        <div class="form-group">
            <label class="form-label">Account</label>
            <div class="entity-lookup" data-initial-name="<?= $e($activity['account_name']) ?>">
                <input type="text" class="input entity-lookup__input" placeholder="Search accounts…" autocomplete="off">
                <input type="hidden" name="account_id" class="entity-lookup__value" value="<?= $e($activity['account_id']) ?>">
                <div class="entity-lookup__results" hidden></div>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Contact</label>
            <div class="entity-lookup entity-lookup--contact" data-initial-name="<?= $e($activity['contact_name']) ?>">
                <input type="text" class="input entity-lookup__input" placeholder="Search contacts…" autocomplete="off">
                <input type="hidden" name="contact_id" class="entity-lookup__value" value="<?= $e($activity['contact_id']) ?>">
                <div class="entity-lookup__results" hidden></div>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Opportunity</label>
            <div class="entity-lookup entity-lookup--opportunity" data-initial-name="<?= $e($activity['opportunity_name']) ?>">
                <input type="text" class="input entity-lookup__input" placeholder="Search opportunities…" autocomplete="off">
                <input type="hidden" name="opportunity_id" class="entity-lookup__value" value="<?= $e($activity['opportunity_id']) ?>">
                <div class="entity-lookup__results" hidden></div>
            </div>
        </div>
    </div>

    <?php else: ?>
    <dl class="field-list">
        <div class="field-list__row"><dt>Type</dt><dd><span class="badge badge--info"><?= $e($activity['type_name']) ?></span></dd></div>
        <div class="field-list__row"><dt>Date</dt><dd><?= $val($activity['activity_date']) ?></dd></div>
        <div class="field-list__row"><dt>Duration</dt><dd><?= $activity['duration_minutes'] ? $val($activity['duration_minutes']) . ' min' : '<span class="text-muted">—</span>' ?></dd></div>
        <div class="field-list__row">
            <dt>Cost</dt>
            <dd><?= $activity['cost'] !== null ? '$' . number_format((float)$activity['cost'], 2) : '<span class="text-muted">—</span>' ?></dd>
        </div>
        <div class="field-list__row">
            <dt>Outcome</dt>
            <dd><?php if ($activity['outcome']): ?>
                <span class="badge <?= $outcomeBadge[$activity['outcome']] ?? 'badge--neutral' ?>"><?= $e($activity['outcome']) ?></span>
            <?php else: ?><span class="text-muted">—</span><?php endif; ?></dd>
        </div>
        <div class="field-list__row"><dt>Notes</dt><dd><?= $val($activity['notes']) ?></dd></div>
        <div class="field-list__row"><dt>Owner</dt><dd><?= $val($activity['owner_name']) ?></dd></div>
    </dl>

    <hr class="divider mb-md">
    <h3 class="profile-card__title" style="font-size:0.85rem;margin-bottom:0.5rem">Linked To</h3>
    <dl class="field-list">
        <div class="field-list__row">
            <dt>Account</dt>
            <dd><?php if ($activity['account_id']): ?>
                <a href="/crm/accounts/details?id=<?= (int)$activity['account_id'] ?>"><?= $e($activity['account_name']) ?></a>
            <?php else: ?><span class="text-muted">—</span><?php endif; ?></dd>
        </div>
        <div class="field-list__row">
            <dt>Contact</dt>
            <dd><?php if ($activity['contact_id']): ?>
                <a href="/crm/contacts/details?id=<?= (int)$activity['contact_id'] ?>"><?= $e($activity['contact_name']) ?></a>
            <?php else: ?><span class="text-muted">—</span><?php endif; ?></dd>
        </div>
        <div class="field-list__row">
            <dt>Opportunity</dt>
            <dd><?php if ($activity['opportunity_id']): ?>
                <a href="/crm/opportunities/details?id=<?= (int)$activity['opportunity_id'] ?>"><?= $e($activity['opportunity_name']) ?></a>
            <?php else: ?><span class="text-muted">—</span><?php endif; ?></dd>
        </div>
    </dl>

    <div class="field-list__row" style="margin-top:1rem;padding-top:0.75rem;border-top:1px solid var(--border)">
        <dt class="text-muted" style="font-size:0.78rem">Logged</dt>
        <dd class="text-muted" style="font-size:0.78rem"><?= $e($activity['created_at']) ?></dd>
    </div>
    <?php endif; ?>

</div>

<?php if ($editMode): ?>
</form>
<?php endif; ?>
