<?php
$pageTitle = 'New Project';
$e   = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$old = fn(string $k) => $e($_POST[$k] ?? '');
?>

<div class="dash-header">
    <div>
        <p class="eyebrow">Projects</p>
        <h1 class="dash-header__title">New Project</h1>
    </div>
    <div>
        <a href="/projects/projects/list" class="btn btn--ghost">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to Projects
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<?php if ($error): ?>
<div class="alert alert--error mb-md"><?= $e($error) ?></div>
<?php endif; ?>

<form method="POST" action="/projects/projects/new">

    <div class="content-panels mb-xl">

        <!-- Project Details -->
        <div class="card content-panel">
            <h2 class="content-panel__title">
                <i class="fa-solid fa-diagram-project" aria-hidden="true"></i>
                Project Details
            </h2>

            <div class="form-group">
                <label class="form-label" for="name">Name <span class="form-required">*</span></label>
                <input type="text" id="name" name="name" class="input" value="<?= $old('name') ?>" required autocomplete="off">
            </div>

            <?= RichTextArea::render([
                'name'   => 'description',
                'label'  => 'Description',
                'value'  => $old('description'),
                'rows'   => 4,
                'preset' => 'moderate',
            ]) ?>

            <div class="form-row">
                <div class="form-group form-group--grow">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="input">
                        <?php foreach (Project::STATUSES as $s): ?>
                        <option value="<?= $e($s) ?>"<?= ($old('status') ?: 'Draft') === $s ? ' selected' : '' ?>><?= $e($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group form-group--grow">
                    <label class="form-label" for="phase">Phase</label>
                    <select id="phase" name="phase" class="input">
                        <?php foreach (Project::PHASES as $ph): ?>
                        <option value="<?= $e($ph) ?>"<?= ($old('phase') ?: 'Initiation') === $ph ? ' selected' : '' ?>><?= $e($ph) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group form-group--grow">
                    <label class="form-label" for="priority">Priority</label>
                    <select id="priority" name="priority" class="input">
                        <?php foreach (Project::PRIORITIES as $pr): ?>
                        <option value="<?= $e($pr) ?>"<?= ($old('priority') ?: 'Medium') === $pr ? ' selected' : '' ?>><?= $e($pr) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <?= RichTextArea::render([
                'name'   => 'notes',
                'label'  => 'Notes',
                'value'  => $old('notes'),
                'rows'   => 4,
                'preset' => 'moderate',
            ]) ?>
        </div>

        <!-- Schedule & Ownership -->
        <div class="card content-panel">
            <h2 class="content-panel__title">
                <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                Schedule &amp; Ownership
            </h2>

            <div class="form-group">
                <label class="form-label" for="owner_id">Project Owner</label>
                <select id="owner_id" name="owner_id" class="input">
                    <option value="">— Unassigned —</option>
                    <?php foreach ($assignableUsers as $u): ?>
                    <option value="<?= (int) $u['id'] ?>"<?= (int) ($old('owner_id') ?: 0) === (int) $u['id'] ? ' selected' : '' ?>>
                        <?= $e($u['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group form-group--grow">
                    <label class="form-label" for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" class="input" value="<?= $old('start_date') ?>">
                </div>
                <div class="form-group form-group--grow">
                    <label class="form-label" for="due_date">Due Date</label>
                    <input type="date" id="due_date" name="due_date" class="input" value="<?= $old('due_date') ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="budget">Budget</label>
                <input type="number" id="budget" name="budget" class="input" value="<?= $old('budget') ?>" step="0.01" min="0" placeholder="0.00">
            </div>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn--primary">Save Project</button>
        <a href="/projects/projects/list" class="btn btn--ghost">Cancel</a>
    </div>

</form>
