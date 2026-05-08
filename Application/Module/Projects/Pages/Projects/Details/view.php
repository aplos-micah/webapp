<?php
$e   = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$val = fn($v) => ($v !== null && $v !== '') ? htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">—</span>';
$sel = fn(string $field, string $opt) => ((string) ($project[$field] ?? '') === $opt) ? ' selected' : '';

$statusBadge = [
    'Draft'     => 'badge--neutral',
    'Active'    => 'badge--success',
    'On Hold'   => 'badge--warning',
    'Completed' => 'badge--info',
    'Cancelled' => 'badge--neutral',
];
$priorityBadge = [
    'Critical' => 'badge--warning',
    'High'     => 'badge--info',
    'Medium'   => 'badge--neutral',
    'Low'      => 'badge--neutral',
];

$isOverdue = !empty($project['due_date'])
    && $project['due_date'] < date('Y-m-d')
    && !in_array($project['status'], ['Completed', 'Cancelled'], true);
?>

<div class="dash-header">
    <div>
        <p class="eyebrow">Projects / <a href="/projects/projects/list">Projects</a></p>
        <h1 class="dash-header__title"><?= $e($project['name']) ?></h1>
        <p class="dash-header__sub">
            <span class="badge <?= $statusBadge[$project['status']] ?? 'badge--neutral' ?>"><?= $e($project['status']) ?></span>
            &nbsp;
            <span class="badge <?= $priorityBadge[$project['priority']] ?? 'badge--neutral' ?>"><?= $e($project['priority']) ?></span>
        </p>
    </div>
    <div class="btn-group">
        <?php if ($editMode): ?>
        <a href="/projects/projects/details?id=<?= $project['id'] ?>" class="btn btn--ghost">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i> Cancel
        </a>
        <button type="submit" form="project-edit-form" class="btn btn--primary">
            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save Changes
        </button>
        <?php else: ?>
        <a href="/projects/projects/details?id=<?= $project['id'] ?>&edit" class="btn btn--secondary">
            <i class="fa-solid fa-pen" aria-hidden="true"></i> Edit
        </a>
        <a href="/projects/projects/list" class="btn btn--ghost">
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

<!-- Phase workflow bar -->
<div class="card mb-lg">
    <div class="step-progress<?= in_array($project['status'], ['Cancelled'], true) ? ' step-progress--error' : '' ?>">
        <?php foreach (Project::PHASES as $i => $step):
            $state = $stepStates[$step] ?? '';
        ?>
        <?php if ($i > 0): ?>
        <div class="step-progress__connector <?= $stepStates[Project::PHASES[$i - 1]] === 'is-done' ? 'is-done' : '' ?>"></div>
        <?php endif; ?>
        <div class="step-progress__step <?= $state ?>">
            <div class="step-progress__node">
                <?php if ($state === 'is-done'): ?><i class="fa-solid fa-check" aria-hidden="true"></i>
                <?php elseif ($step === 'Closure'): ?><i class="fa-solid fa-flag-checkered" aria-hidden="true"></i>
                <?php else: ?><?= $i + 1 ?><?php endif; ?>
            </div>
            <span class="step-progress__label"><?= $e($step) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php if ($editMode): ?>
<form id="project-edit-form" method="POST" action="/projects/projects/details?id=<?= $project['id'] ?>&edit" novalidate>
<?php endif; ?>

<div class="detail-layout">

    <!-- Primary -->
    <div class="detail-layout__primary">

        <!-- Details card -->
        <div class="card profile-card mb-lg">
            <h2 class="profile-card__title">
                <i class="fa-solid fa-diagram-project" aria-hidden="true"></i>
                Project Details
            </h2>
            <?php if ($editMode): ?>
            <div class="edit-section">
                <div class="form-group">
                    <label class="form-label" for="name">Name <span class="form-required">*</span></label>
                    <input id="name" type="text" name="name" class="input" value="<?= $e($project['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="description">Description</label>
                    <textarea id="description" name="description" class="input" rows="5"><?= $e($project['description'] ?? '') ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="status">Status</label>
                        <select id="status" name="status" class="input">
                            <?php foreach (Project::STATUSES as $s): ?>
                            <option value="<?= $e($s) ?>"<?= $sel('status', $s) ?>><?= $e($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="phase">Phase</label>
                        <select id="phase" name="phase" class="input">
                            <?php foreach (Project::PHASES as $ph): ?>
                            <option value="<?= $e($ph) ?>"<?= $sel('phase', $ph) ?>><?= $e($ph) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="priority">Priority</label>
                        <select id="priority" name="priority" class="input">
                            <?php foreach (Project::PRIORITIES as $pr): ?>
                            <option value="<?= $e($pr) ?>"<?= $sel('priority', $pr) ?>><?= $e($pr) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="notes">Notes</label>
                    <textarea id="notes" name="notes" class="input" rows="4"><?= $e($project['notes'] ?? '') ?></textarea>
                </div>
            </div>
            <?php else: ?>
            <dl class="field-list">
                <div class="field-list__row"><dt>Name</dt><dd><?= $val($project['name']) ?></dd></div>
                <div class="field-list__row"><dt>Status</dt><dd><span class="badge <?= $statusBadge[$project['status']] ?? 'badge--neutral' ?>"><?= $e($project['status']) ?></span></dd></div>
                <div class="field-list__row"><dt>Phase</dt><dd><?= $val($project['phase']) ?></dd></div>
                <div class="field-list__row"><dt>Priority</dt><dd><span class="badge <?= $priorityBadge[$project['priority']] ?? 'badge--neutral' ?>"><?= $e($project['priority']) ?></span></dd></div>
            </dl>
            <?php if (!empty($project['description'])): ?>
            <p class="section-label">Description</p>
            <p class="field-text"><?= nl2br($val($project['description'])) ?></p>
            <?php endif; ?>
            <?php if (!empty($project['notes'])): ?>
            <p class="section-label">Notes</p>
            <p class="field-text"><?= nl2br($val($project['notes'])) ?></p>
            <?php endif; ?>
            <?php endif; ?>
        </div>

    </div>

    <!-- Aside -->
    <div class="detail-layout__aside">

        <!-- Schedule & Ownership card -->
        <div class="card profile-card mb-lg">
            <h2 class="profile-card__title">
                <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                Schedule &amp; Ownership
            </h2>
            <?php if ($editMode): ?>
            <div class="edit-section">
                <div class="form-group">
                    <label class="form-label" for="owner_id">Project Owner</label>
                    <select id="owner_id" name="owner_id" class="input">
                        <option value="">— Unassigned —</option>
                        <?php foreach ($assignableUsers as $u): ?>
                        <option value="<?= (int) $u['id'] ?>"<?= (int) ($project['owner_id'] ?? 0) === (int) $u['id'] ? ' selected' : '' ?>>
                            <?= $e($u['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="start_date">Start Date</label>
                        <input type="date" id="start_date" name="start_date" class="input" value="<?= $e($project['start_date'] ?? '') ?>">
                    </div>
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="due_date">Due Date</label>
                        <input type="date" id="due_date" name="due_date" class="input" value="<?= $e($project['due_date'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="budget">Budget</label>
                    <input type="number" id="budget" name="budget" class="input" value="<?= $e($project['budget'] ?? '') ?>" step="0.01" min="0" placeholder="0.00">
                </div>
            </div>
            <?php else: ?>
            <dl class="field-list">
                <div class="field-list__row"><dt>Owner</dt><dd><?= $val($project['owner_name']) ?></dd></div>
                <div class="field-list__row"><dt>Start Date</dt><dd><?= $val(substr($project['start_date'] ?? '', 0, 10)) ?></dd></div>
                <div class="field-list__row">
                    <dt>Due Date</dt>
                    <dd>
                        <?php if ($isOverdue): ?>
                        <span class="text-orange">
                            <?= $e(substr($project['due_date'], 0, 10)) ?>
                            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                        </span>
                        <?php else: ?>
                        <?= $val(substr($project['due_date'] ?? '', 0, 10)) ?>
                        <?php endif; ?>
                    </dd>
                </div>
                <?php if (!empty($project['completed_date'])): ?>
                <div class="field-list__row"><dt>Completed</dt><dd><?= $val(substr($project['completed_date'], 0, 10)) ?></dd></div>
                <?php endif; ?>
                <div class="field-list__row"><dt>Budget</dt><dd><?= !empty($project['budget']) ? '$' . number_format((float) $project['budget'], 2) : '—' ?></dd></div>
            </dl>
            <?php endif; ?>
        </div>

        <!-- Record card -->
        <div class="card profile-card mb-lg">
            <h2 class="profile-card__title">
                <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
                Record
            </h2>
            <dl class="field-list">
                <div class="field-list__row"><dt>Created</dt><dd><?= $e(substr($project['created_at'] ?? '', 0, 10)) ?></dd></div>
                <div class="field-list__row"><dt>Last Updated</dt><dd><?= $e(substr($project['updated_at'] ?? '', 0, 10)) ?></dd></div>
            </dl>
        </div>

    </div>
</div>

<?php if ($editMode): ?>
</form>
<?php endif; ?>
