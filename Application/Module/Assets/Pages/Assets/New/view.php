<?php
$pageTitle = 'New Asset';
$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$old = fn(string $k) => $e($_POST[$k] ?? '');
?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">Assets</p>
        <h1 class="dash-header__title">New Asset</h1>
    </div>
    <div>
        <a href="/assets/assets/list" class="btn btn--ghost">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to Assets
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<?php if ($error): ?>
<div class="alert alert--error mb-md"><?= $e($error) ?></div>
<?php endif; ?>

<form method="POST" action="/assets/assets/new">

    <div class="content-panels mb-xl">

        <!-- Asset Details -->
        <div class="card content-panel">
            <h2 class="content-panel__title">
                <i class="fa-solid fa-laptop" aria-hidden="true"></i>
                Asset Details
            </h2>

            <div class="form-group">
                <label class="label" for="name">Name <span class="required-star">*</span></label>
                <input type="text" id="name" name="name" class="input" value="<?= $old('name') ?>" required autocomplete="off">
            </div>

            <div class="form-row">
                <div class="form-group form-group--half">
                    <label class="label" for="type">Type</label>
                    <select id="type" name="type" class="input">
                        <?php foreach (Asset::TYPES as $t): ?>
                        <option value="<?= $e($t) ?>"<?= ($old('type') ?: 'Hardware') === $t ? ' selected' : '' ?>><?= $e($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group form-group--half">
                    <label class="label" for="category">Category</label>
                    <select id="category" name="category" class="input">
                        <option value="">— Select —</option>
                        <?php foreach (Asset::CATEGORIES as $c): ?>
                        <option value="<?= $e($c) ?>"<?= $old('category') === $c ? ' selected' : '' ?>><?= $e($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group form-group--half">
                    <label class="label" for="manufacturer">Manufacturer</label>
                    <input type="text" id="manufacturer" name="manufacturer" class="input" value="<?= $old('manufacturer') ?>" autocomplete="off">
                </div>
                <div class="form-group form-group--half">
                    <label class="label" for="model">Model</label>
                    <input type="text" id="model" name="model" class="input" value="<?= $old('model') ?>" autocomplete="off">
                </div>
            </div>

            <div class="form-group">
                <label class="label" for="serial_number">Serial Number</label>
                <input type="text" id="serial_number" name="serial_number" class="input" value="<?= $old('serial_number') ?>" autocomplete="off">
            </div>

            <div class="form-group">
                <label class="label" for="status">Status</label>
                <select id="status" name="status" class="input">
                    <?php foreach (Asset::STATUSES as $s): ?>
                    <option value="<?= $e($s) ?>"<?= ($old('status') ?: 'Active') === $s ? ' selected' : '' ?>><?= $e($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?= RichTextArea::render([
                'name'   => 'notes',
                'label'  => 'Notes',
                'value'  => $old('notes'),
                'rows'   => 4,
                'class'  => 'textarea',
                'preset' => 'moderate',
            ]) ?>
        </div>

        <!-- Assignment & Financials -->
        <div class="card content-panel">
            <h2 class="content-panel__title">
                <i class="fa-solid fa-user-check" aria-hidden="true"></i>
                Assignment &amp; Financials
            </h2>

            <div class="form-group">
                <label class="label" for="assigned_to">Assigned To</label>
                <select id="assigned_to" name="assigned_to" class="input">
                    <option value="">— Unassigned —</option>
                    <?php foreach ($assignableUsers as $u): ?>
                    <option value="<?= (int) $u['id'] ?>"<?= (int) ($old('assigned_to') ?: 0) === (int) $u['id'] ? ' selected' : '' ?>>
                        <?= $e($u['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="label" for="location">Location</label>
                <input type="text" id="location" name="location" class="input" value="<?= $old('location') ?>" autocomplete="off" placeholder="e.g. Building A, Desk 12">
            </div>

            <div class="form-row">
                <div class="form-group form-group--half">
                    <label class="label" for="purchase_date">Purchase Date</label>
                    <input type="date" id="purchase_date" name="purchase_date" class="input" value="<?= $old('purchase_date') ?>">
                </div>
                <div class="form-group form-group--half">
                    <label class="label" for="warranty_expires">Warranty Expires</label>
                    <input type="date" id="warranty_expires" name="warranty_expires" class="input" value="<?= $old('warranty_expires') ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="label" for="cost">Cost</label>
                <input type="number" id="cost" name="cost" class="input" value="<?= $old('cost') ?>" step="0.01" min="0" placeholder="0.00">
            </div>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn--primary">Save Asset</button>
        <a href="/assets/assets/list" class="btn btn--ghost">Cancel</a>
    </div>

</form>
